<?php

namespace App\Console\Commands;

use App\Providers\ModuleServiceProvider;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SeedModulePermissions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'module:seed
                            {--module= : Seed permissions for a specific module only}
                            {--fresh : Remove all module permissions before re-seeding}';

    /**
     * The console command description.
     */
    protected $description = 'Seed permissions and role assignments from all discovered modules';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $modules = ModuleServiceProvider::discoverModules();

        if (empty($modules)) {
            $this->warn('No modules found in app/Modules/');
            return self::SUCCESS;
        }

        $targetModule = $this->option('module');

        $this->info('');
        $this->info('🔌 Module Permission Seeder');
        $this->info(str_repeat('─', 40));

        $totalPermissions = 0;

        foreach ($modules as $dirName => $module) {
            // Filter by specific module if requested
            if ($targetModule && strtolower($dirName) !== strtolower($targetModule)) {
                continue;
            }

            $moduleName = $module->name();
            $permissions = $module->permissions();
            $roles = $module->roles();

            if (empty($permissions)) {
                $this->line("  ⏭  {$moduleName} — no permissions defined, skipping.");
                continue;
            }

            $this->info("  📦 {$moduleName}");

            // Create permissions
            $createdCount = 0;
            foreach ($permissions as $perm) {
                $created = Permission::firstOrCreate(['name' => $perm]);
                if ($created->wasRecentlyCreated) {
                    $createdCount++;
                }
            }

            $existingCount = count($permissions) - $createdCount;
            $this->line("     ✅ {$createdCount} permissions created, {$existingCount} already existed");
            $totalPermissions += $createdCount;

            // Assign permissions to roles
            foreach ($roles as $roleName => $rolePermissions) {
                $role = Role::firstOrCreate(['name' => $roleName]);

                if ($rolePermissions === '*') {
                    // Grant all module permissions
                    $permsToGive = $permissions;
                } else {
                    $permsToGive = (array) $rolePermissions;
                }

                // Use givePermissionTo to ADD without removing existing
                foreach ($permsToGive as $perm) {
                    if (!$role->hasPermissionTo($perm)) {
                        $role->givePermissionTo($perm);
                    }
                }

                $count = count($permsToGive);
                $this->line("     👤 {$roleName} → {$count} permissions assigned");
            }

            $this->info('');
        }

        $this->info(str_repeat('─', 40));
        $this->info("✨ Done! {$totalPermissions} new permissions created.");
        $this->info('');

        // Clear permission cache so changes take effect immediately
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return self::SUCCESS;
    }
}
