<?php

namespace App\Modules;

use Illuminate\Support\Str;

abstract class BaseModule
{
    /**
     * Return the list of permissions this module requires.
     * Example: ['view bookings', 'create bookings', 'edit bookings', 'delete bookings']
     */
    abstract public function permissions(): array;

    /**
     * Return the role-to-permissions mapping for this module.
     * Use '*' to grant all module permissions to a role.
     *
     * Example:
     * [
     *     'admin'  => '*',
     *     'editor' => ['view bookings', 'create bookings', 'edit bookings'],
     *     'user'   => ['view bookings'],
     * ]
     */
    abstract public function roles(): array;

    /**
     * Return model-to-policy class mappings for this module.
     * Example: [\App\Modules\Booking\Models\Booking::class => \App\Modules\Booking\Policies\BookingPolicy::class]
     *
     * @return array<class-string, class-string>
     */
    public function policies(): array
    {
        return [];
    }

    /**
     * Return the absolute path to this module's migrations directory.
     * Defaults to `database/migrations/` inside the module folder.
     */
    public function migrationPath(): string
    {
        $reflection = new \ReflectionClass(static::class);
        $moduleDir = dirname($reflection->getFileName());

        return $moduleDir . '/database/migrations';
    }

    /**
     * Return the absolute path to this module's Filament Resources directory.
     * Defaults to `Filament/Resources/` inside the module folder.
     */
    public function filamentResourcePath(): string
    {
        $reflection = new \ReflectionClass(static::class);
        $moduleDir = dirname($reflection->getFileName());

        return $moduleDir . '/Filament/Resources';
    }

    /**
     * Return the namespace for this module's Filament Resources.
     */
    public function filamentResourceNamespace(): string
    {
        $reflection = new \ReflectionClass(static::class);
        $namespace = $reflection->getNamespaceName();

        return $namespace . '\\Filament\\Resources';
    }

    /**
     * Get a human-readable name for this module.
     * Defaults to the folder name (e.g. 'BookingSystem' → 'Booking System').
     */
    public function name(): string
    {
        $reflection = new \ReflectionClass(static::class);
        $className = class_basename(static::class);
        // Remove "Module" suffix: "BookingModule" → "Booking"
        $name = Str::replaceLast('Module', '', $className);

        return Str::headline($name);
    }

    /**
     * Return the Filament navigation group label for this module.
     * Defaults to the module name. Override in subclass if needed.
     */
    public function navigationGroup(): string
    {
        return $this->name();
    }

    /**
     * Return the absolute path to this module's web/api routes file if it exists.
     * Defaults to `routes/web.php` inside the module folder.
     */
    public function routesPath(): ?string
    {
        $reflection = new \ReflectionClass(static::class);
        $moduleDir = dirname($reflection->getFileName());
        $path = $moduleDir . '/routes/web.php';

        return file_exists($path) ? $path : null;
    }

    /**
     * Return the list of console command classes registered by this module.
     */
    public function commands(): array
    {
        return [];
    }
}

