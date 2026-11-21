<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\HandleRedirects::class);
        $middleware->append(\App\Http\Middleware\LogBrokenLinks::class);
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('app:generate-recurring-invoices')->daily();
        $schedule->command('app:send-invoice-reminders')->daily();
        $schedule->command('app:process-monthly-payroll')->monthlyOn(28, '00:00');
        $schedule->command('app:send-task-deadline-alerts')->dailyAt('09:00');
    })->create();
