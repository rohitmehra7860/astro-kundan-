<?php

namespace App\Modules\ContactLeads\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ContactLead extends Model
{
    use SoftDeletes, LogsActivity;

    protected static function booted()
    {
        static::created(function ($lead) {
            // Fetch recipient email addresses from settings
            $emailsJson = \App\Models\Setting::get('mail_receive_address', '[]');
            $emails = json_decode($emailsJson, true) ?: [];

            // Fallback to site_email if no receiver emails configured
            if (empty($emails)) {
                $siteEmail = \App\Models\Setting::get('site_email');
                if ($siteEmail) {
                    $emails = [$siteEmail];
                }
            }

            // Send email notification to all receivers
            if (!empty($emails)) {
                try {
                    \Illuminate\Support\Facades\Mail::to($emails)->queue(new \App\Mail\ContactLeadNotificationMail($lead));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send contact lead email notification: ' . $e->getMessage());
                }
            }
        });
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'source',
        'ip_address',
        'notes',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    /**
     * Scope: only new/unread leads.
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope: only read leads.
     */
    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    /**
     * Scope: only replied leads.
     */
    public function scopeReplied($query)
    {
        return $query->where('status', 'replied');
    }

    /**
     * Scope: only archived leads.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Mark the lead as read.
     */
    public function markAsRead(): void
    {
        if ($this->status === 'new') {
            $this->update(['status' => 'read']);
        }
    }

    /**
     * Mark the lead as replied.
     */
    public function markAsReplied(): void
    {
        $this->update([
            'status'     => 'replied',
            'replied_at' => now(),
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Contact Lead {$eventName}");
    }
}
