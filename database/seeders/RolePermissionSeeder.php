<?php

namespace Database\Seeders;

use App\Providers\ModuleServiceProvider;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Core permissions
        $permissions = [
            'view users', 'create users', 'edit users', 'delete users',
            'view roles', 'create roles', 'edit roles', 'delete roles',
            'view permissions', 'create permissions', 'edit permissions', 'delete permissions',
            'view activity logs', 'delete activity logs',
            'view dashboard',

            // Blog (Post) permissions
            'view posts', 'create posts', 'edit posts', 'delete posts',

            // Category permissions
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view blog categories', 'create blog categories', 'edit blog categories', 'delete blog categories',

            // Comment permissions
            'view comments', 'create comments', 'edit comments', 'delete comments',

            // Newsletter Subscriber permissions
            'view newsletter subscribers', 'create newsletter subscribers', 'edit newsletter subscribers', 'delete newsletter subscribers',

            // Media permissions
            'view media', 'create media', 'edit media', 'delete media',

            // Settings permissions
            'view settings', 'edit settings',

            // Page permissions
            'view pages', 'create pages', 'edit pages', 'delete pages',

            // Redirect permissions
            'view redirects', 'create redirects', 'edit redirects', 'delete redirects',

            // SEO Audit permissions
            'view seo audit',

            // Broken link permissions
            'view broken links', 'delete broken links',

            // Service permissions
            'view services', 'create services', 'edit services', 'delete services',
        ];

        // Discover and merge module permissions
        $modules = ModuleServiceProvider::discoverModules();
        foreach ($modules as $module) {
            $permissions = array_merge($permissions, $module->permissions());
        }

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Create roles and assign permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // Core editor permissions
        $editorPermissions = [
            'view posts', 'create posts', 'edit posts', 'delete posts',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view blog categories', 'create blog categories', 'edit blog categories', 'delete blog categories',
            'view comments', 'create comments', 'edit comments', 'delete comments',
            'view newsletter subscribers', 'create newsletter subscribers', 'edit newsletter subscribers', 'delete newsletter subscribers',
            'view media', 'create media', 'edit media', 'delete media',
            'view pages', 'create pages', 'edit pages', 'delete pages',
            'view redirects', 'create redirects', 'edit redirects', 'delete redirects',
            'view seo audit',
            'view broken links', 'delete broken links',
            'view dashboard',
            'view services', 'create services', 'edit services', 'delete services',
        ];

        // Core user permissions
        $userPermissions = [
            'view posts',
            'view categories',
            'view blog categories',
            'view media',
            'view pages',
            'view dashboard',
            'view services',
        ];

        // Merge module role permissions
        foreach ($modules as $module) {
            $moduleRoles = $module->roles();

            if (isset($moduleRoles['editor'])) {
                $editorPerms = $moduleRoles['editor'] === '*' ? $module->permissions() : $moduleRoles['editor'];
                $editorPermissions = array_merge($editorPermissions, $editorPerms);
            }

            if (isset($moduleRoles['user'])) {
                $userPerms = $moduleRoles['user'] === '*' ? $module->permissions() : $moduleRoles['user'];
                $userPermissions = array_merge($userPermissions, $userPerms);
            }
        }

        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editor->syncPermissions($editorPermissions);

        $user = Role::firstOrCreate(['name' => 'user']);
        $user->syncPermissions($userPermissions);
    }
}
