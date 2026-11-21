<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(\Spatie\Activitylog\Models\Activity::class, \App\Policies\ActivityPolicy::class);

        // Load SMTP config dynamically from settings database table
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $mailHost = \App\Models\Setting::get('mail_host');
                if ($mailHost) {
                    config([
                        'mail.default' => 'smtp',
                        'mail.mailers.smtp.host' => $mailHost,
                        'mail.mailers.smtp.port' => (int) \App\Models\Setting::get('mail_port', 587),
                        'mail.mailers.smtp.username' => \App\Models\Setting::get('mail_username'),
                        'mail.mailers.smtp.password' => \App\Models\Setting::get('mail_password'),
                        'mail.mailers.smtp.encryption' => \App\Models\Setting::get('mail_encryption') === 'none' ? null : \App\Models\Setting::get('mail_encryption'),
                        'mail.from.address' => \App\Models\Setting::get('mail_from_address'),
                        'mail.from.name' => \App\Models\Setting::get('mail_from_name'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Prevent failure during migrations or installation before DB is ready
        }
    }
}
