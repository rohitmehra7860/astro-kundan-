<?php

namespace App\Providers;

use App\Modules\BaseModule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Cached array of discovered module instances.
     *
     * @var BaseModule[]
     */
    protected static array $modules = [];

    /**
     * Register module services.
     */
    public function register(): void
    {
        // Nothing to register at this stage
    }

    /**
     * Bootstrap module services: discover modules and register their
     * migrations, policies, and Filament resources.
     */
    public function boot(): void
    {
        $modules = static::discoverModules();

        foreach ($modules as $module) {
            $this->registerMigrations($module);
            $this->registerPolicies($module);
            $this->registerRoutes($module);
            $this->registerCommands($module);
            $this->registerViews($module);
        }
    }

    /**
     * Scan app/Modules/ and instantiate every *Module.php definition class.
     *
     * @return BaseModule[]
     */
    public static function discoverModules(): array
    {
        if (!empty(static::$modules)) {
            return static::$modules;
        }

        $modulesPath = app_path('Modules');

        if (!File::isDirectory($modulesPath)) {
            return [];
        }

        $directories = File::directories($modulesPath);

        foreach ($directories as $directory) {
            $dirName = basename($directory);

            // Find the *Module.php file in the directory root
            $moduleFile = $directory . '/' . $dirName . 'Module.php';

            if (!File::exists($moduleFile)) {
                // Try a simpler naming: look for any file ending in Module.php
                $candidates = glob($directory . '/*Module.php');
                if (empty($candidates)) {
                    continue;
                }
                $moduleFile = $candidates[0];
            }

            // Derive the FQCN from the file path
            $className = 'App\\Modules\\' . $dirName . '\\' . pathinfo($moduleFile, PATHINFO_FILENAME);

            if (!class_exists($className)) {
                continue;
            }

            $instance = new $className();

            if ($instance instanceof BaseModule) {
                static::$modules[$dirName] = $instance;
            }
        }

        return static::$modules;
    }

    /**
     * Register migrations from the module's database/migrations directory.
     */
    protected function registerMigrations(BaseModule $module): void
    {
        $path = $module->migrationPath();

        if (File::isDirectory($path)) {
            $this->loadMigrationsFrom($path);
        }
    }

    /**
     * Register Gate policies defined by the module.
     */
    protected function registerPolicies(BaseModule $module): void
    {
        foreach ($module->policies() as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }

    /**
     * Register routes from the module's routes/web.php.
     */
    protected function registerRoutes(BaseModule $module): void
    {
        $path = $module->routesPath();

        if ($path) {
            $this->loadRoutesFrom($path);
        }
    }

    /**
     * Register commands defined by the module.
     */
    protected function registerCommands(BaseModule $module): void
    {
        if ($this->app->runningInConsole() && !empty($module->commands())) {
            $this->commands($module->commands());
        }
    }

    /**
     * Register view templates from the module's resources/views.
     */
    protected function registerViews(BaseModule $module): void
    {
        $reflection = new \ReflectionClass($module);
        $moduleDir = dirname($reflection->getFileName());
        $path = $moduleDir . '/resources/views';

        if (File::isDirectory($path)) {
            $this->loadViewsFrom($path, \Illuminate\Support\Str::kebab($module->name()));
        }
    }


    /**
     * Get all Filament resource discovery paths for use in AdminPanelProvider.
     *
     * @return array<array{path: string, namespace: string}>
     */
    public static function getFilamentResourcePaths(): array
    {
        $paths = [];

        foreach (static::discoverModules() as $module) {
            $path = $module->filamentResourcePath();

            if (File::isDirectory($path)) {
                $paths[] = [
                    'path'      => $path,
                    'namespace' => $module->filamentResourceNamespace(),
                ];
            }
        }

        return $paths;
    }
}
