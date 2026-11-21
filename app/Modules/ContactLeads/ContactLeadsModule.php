<?php

namespace App\Modules\ContactLeads;

use App\Modules\BaseModule;

class ContactLeadsModule extends BaseModule
{
    public function permissions(): array
    {
        return [
            'view contact leads',
            'create contact leads',
            'edit contact leads',
            'delete contact leads',
        ];
    }

    public function roles(): array
    {
        return [
            'admin'  => '*',
            'editor' => ['view contact leads', 'edit contact leads'],
            'user'   => ['view contact leads'],
        ];
    }

    public function policies(): array
    {
        return [
            \App\Modules\ContactLeads\Models\ContactLead::class => \App\Modules\ContactLeads\Policies\ContactLeadPolicy::class,
        ];
    }
}
