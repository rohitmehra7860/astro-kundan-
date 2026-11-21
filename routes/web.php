<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\WebPageController;

Route::get('/', [WebPageController::class, 'home'])->name('home');

Route::get('/admin/verify-email', \App\Filament\Pages\Auth\VerifyEmail::class)->name('filament.admin.auth.verify-email');

Route::get('/blog/category/{slug}', [WebPageController::class, 'category'])->name('categories.show');
Route::get('/blog/{slug}', [WebPageController::class, 'post'])->name('web.posts.show');

Route::get('/services', [WebPageController::class, 'show'])->defaults('slug', 'services')->name('services.index');
Route::get('/services/category/{slug}', [WebPageController::class, 'category'])->name('services.category.show');
Route::get('/services/{slug}', [WebPageController::class, 'service'])->name('services.show');


Route::get('/admin/clear-cache-redirect', function () {
    if (!auth()->check() || !auth()->user()->hasRole('admin')) {
        abort(403);
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');

        // Use session flash or Filament notification dispatcher if session available
        session()->flash('cache_cleared_message', 'System cache cleared successfully!');

        // Direct integration with Filament notification system
        \Filament\Notifications\Notification::make()
            ->title('System cache cleared successfully!')
            ->success()
            ->send();
    } catch (\Exception $e) {
        \Filament\Notifications\Notification::make()
            ->title('Error clearing cache')
            ->body($e->getMessage())
            ->danger()
            ->send();
    }

    return redirect('/admin');
});


Route::post('/admin/media/upload-ajax', [\App\Http\Controllers\Admin\MediaUploadController::class, 'uploadAjax'])
    ->middleware(['auth'])
    ->name('admin.media.upload-ajax');

Route::get('/sitemap.xml', [App\Http\Controllers\Api\SitemapController::class, 'generate'])->name('sitemap');

if (app()->environment('local')) {
    Route::get('/preview-contact-mail', function () {
        $lead = new \App\Modules\ContactLeads\Models\ContactLead([
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'phone' => '+1 555-0199',
            'subject' => 'Website Design Quotation',
            'message' => "Hello!\n\nI would like to request a quote for building our corporate website. Please send over your pricing details.\n\nThanks,\nJohn",
            'source' => 'Contact Form',
            'created_at' => now(),
        ]);

        return new \App\Mail\ContactLeadNotificationMail($lead);
    });
}

Route::get('/run-migrations', function () {
    $secret = request()->query('key');
    $configuredSecret = env('MIGRATION_KEY', 'my-secure-migration-key');

    if (!app()->environment('local') && $secret !== $configuredSecret) {
        abort(403, 'Unauthorized migration request.');
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        return response("<pre>Migration Successful:\n\n" . e($output) . "</pre>");
    } catch (\Exception $e) {
        return response("<pre>Migration Failed:\n\n" . e($e->getMessage()) . "</pre>", 500);
    }
});

// Catch-all dynamic pages route (must be at bottom)
Route::get('/{slug}', [WebPageController::class, 'show'])->name('pages.show');
