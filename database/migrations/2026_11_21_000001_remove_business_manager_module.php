<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Remove the retired Business Manager schema and its module-only permissions.
     */
    public function up(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            foreach ([
                'bm_email_logs',
                'bm_payrolls',
                'bm_leaves',
                'bm_time_logs',
                'bm_payments',
                'bm_invoices',
                'bm_expenses',
                'bm_tasks',
                'bm_projects',
                'bm_leads',
                'bm_company_user',
                'bm_employees',
                'bm_services',
                'bm_clients',
                'bm_companies',
            ] as $table) {
                Schema::dropIfExists($table);
            }
        });

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')
                ->where(function ($query): void {
                    foreach (['view bm %', 'create bm %', 'edit bm %', 'delete bm %'] as $pattern) {
                        $query->orWhere('name', 'like', $pattern);
                    }
                    $query->orWhereIn('name', [
                        'view companies',
                        'create companies',
                        'edit companies',
                        'delete companies',
                    ]);
                })
                ->delete();

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    /**
     * This removal is irreversible because the dropped module tables contained data.
     */
    public function down(): void
    {
        // The module and its schema are intentionally retired.
    }
};
