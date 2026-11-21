# Modules Directory

Each subfolder here is a **self-contained feature module**.

## How to Add a Module

1. Create a folder: `app/Modules/YourFeature/`
2. Add a `YourFeatureModule.php` that extends `BaseModule`
3. Add your Models, Filament Resources, Policies, Controllers, and Migrations inside
4. Run:
   ```bash
   php artisan migrate              # Creates database tables
   php artisan module:seed          # Seeds permissions & role assignments
   ```
5. Done! The feature is live in the admin panel.

## How to Remove a Module

1. Delete the module folder
2. Optionally run `php artisan migrate:rollback` for that module's migrations

## Module Structure

```
app/Modules/YourFeature/
├── YourFeatureModule.php          ← Module definition (required)
├── Models/
├── Filament/Resources/
├── Policies/
├── Controllers/
└── database/migrations/
```

## Example Module Definition

```php
<?php

namespace App\Modules\YourFeature;

use App\Modules\BaseModule;

class YourFeatureModule extends BaseModule
{
    public function permissions(): array
    {
        return [
            'view your-features',
            'create your-features',
            'edit your-features',
            'delete your-features',
        ];
    }

    public function roles(): array
    {
        return [
            'admin'  => '*',
            'editor' => ['view your-features', 'create your-features', 'edit your-features'],
            'user'   => ['view your-features'],
        ];
    }

    public function policies(): array
    {
        return [
            \App\Modules\YourFeature\Models\YourModel::class => \App\Modules\YourFeature\Policies\YourModelPolicy::class,
        ];
    }
}
```

## Available Commands

| Command | Description |
|---------|-------------|
| `php artisan module:seed` | Seed permissions for ALL modules |
| `php artisan module:seed --module=YourFeature` | Seed permissions for ONE module |
