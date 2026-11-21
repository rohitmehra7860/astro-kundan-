<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $title = 'Site Settings';

    protected string $view = 'filament.pages.site-settings';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        // Migrate legacy Contact Info to contact_locations if empty
        $contactLocations = Setting::get('contact_locations', '');
        if (empty($contactLocations)) {
            $migratedLocations = [];
            $legacyEmail = Setting::get('site_email', '');
            $legacyPhone = Setting::get('site_phone', '');
            $legacyAddress = Setting::get('site_address', '');
            if (!empty($legacyEmail) || !empty($legacyPhone) || !empty($legacyAddress)) {
                $migratedLocations[] = [
                    'name' => 'Main Office',
                    'address' => $legacyAddress,
                    'phones' => !empty($legacyPhone) ? array_map('trim', explode(',', $legacyPhone)) : [],
                    'emails' => !empty($legacyEmail) ? array_map('trim', explode(',', $legacyEmail)) : [],
                ];
            }
            $contactLocationsData = $migratedLocations;
        } else {
            $contactLocationsData = json_decode($contactLocations, true);
        }

        // Migrate legacy Social Links to social_links if empty
        $socialLinks = Setting::get('social_links', '');
        if (empty($socialLinks)) {
            $migratedSocials = [];
            $legacyKeys = [
                'facebook' => 'social_facebook',
                'twitter' => 'social_twitter',
                'instagram' => 'social_instagram',
                'linkedin' => 'social_linkedin',
                'youtube' => 'social_youtube',
            ];
            foreach ($legacyKeys as $platform => $key) {
                $val = Setting::get($key, '');
                if (!empty($val)) {
                    $migratedSocials[] = [
                        'platform' => $platform,
                        'custom_platform' => null,
                        'url' => $val,
                        'icon_id' => null,
                    ];
                }
            }
            $socialLinksData = $migratedSocials;
        } else {
            $socialLinksData = json_decode($socialLinks, true);
        }

        $this->form->fill([
            'site_name' => Setting::get('site_name', ''),
            'site_tagline' => Setting::get('site_tagline', ''),
            'site_description' => Setting::get('site_description', ''),
            'site_currency' => Setting::get('site_currency', 'USD'),
            'light_logo_id' => Setting::get('light_logo_id', ''),
            'dark_logo_id' => Setting::get('dark_logo_id', ''),
            'favicon_id' => Setting::get('favicon_id', ''),
            'site_email' => Setting::get('site_email', ''),
            'site_phone' => Setting::get('site_phone', ''),
            'site_address' => Setting::get('site_address', ''),
            'social_facebook' => Setting::get('social_facebook', ''),
            'social_twitter' => Setting::get('social_twitter', ''),
            'social_instagram' => Setting::get('social_instagram', ''),
            'social_linkedin' => Setting::get('social_linkedin', ''),
            'social_youtube' => Setting::get('social_youtube', ''),
            'contact_locations' => $contactLocationsData,
            'social_links' => $socialLinksData,
            'footer_text' => Setting::get('footer_text', ''),
            'google_analytics_id' => Setting::get('google_analytics_id', ''),
            'google_tag_manager_id' => Setting::get('google_tag_manager_id', ''),
            'meta_pixel_id' => Setting::get('meta_pixel_id', ''),
            'custom_head_scripts' => Setting::get('custom_head_scripts', ''),
            'custom_body_scripts' => Setting::get('custom_body_scripts', ''),
            'google_site_verification' => Setting::get('google_site_verification', ''),
            'bing_site_verification' => Setting::get('bing_site_verification', ''),
            'public_indexing' => (bool) Setting::get('public_indexing', '1'),
            'fallback_og_image_id' => Setting::get('fallback_og_image_id', ''),
            'site_locale' => Setting::get('site_locale', 'en_US'),
            'site_search_url' => Setting::get('site_search_url', ''),
            'mail_host' => Setting::get('mail_host', ''),
            'mail_port' => Setting::get('mail_port', '587'),
            'mail_username' => Setting::get('mail_username', ''),
            'mail_password' => Setting::get('mail_password', ''),
            'mail_encryption' => Setting::get('mail_encryption', 'tls'),
            'mail_from_address' => Setting::get('mail_from_address', ''),
            'mail_from_name' => Setting::get('mail_from_name', ''),
            'mail_receive_address' => json_decode(Setting::get('mail_receive_address', '[]'), true) ?: [],
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('General')
                    ->description('Basic site information')
                    ->icon('heroicon-o-globe-alt')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Site Name')
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('site_tagline')
                            ->label('Tagline')
                            ->maxLength(255),
                        Textarea::make('site_description')
                            ->label('Site Description')
                            ->rows(3)
                            ->maxLength(500),
                        Select::make('site_currency')
                            ->label('Site Currency')
                            ->options([
                                'USD' => 'USD ($)',
                                'EUR' => 'EUR (€)',
                                'GBP' => 'GBP (£)',
                                'INR' => 'INR (₹)',
                                'CAD' => 'CAD ($)',
                                'AUD' => 'AUD ($)',
                            ])
                            ->default('USD')
                            ->required(),
                        Select::make('site_locale')
                            ->label('Site Locale')
                            ->helperText('Used for og:locale meta tag and hreflang')
                            ->options(function () {
                                return cache()->rememberForever('site_locales_list', function () {
                                    $locales = [];
                                    foreach (\ResourceBundle::getLocales('') as $locale) {
                                        if (strlen($locale) >= 5 && (str_contains($locale, '_') || str_contains($locale, '-'))) {
                                            $clean = str_replace('-', '_', $locale);
                                            $locales[$clean] = \Locale::getDisplayName($locale, 'en') . " ({$clean})";
                                        }
                                    }
                                    asort($locales);
                                    return $locales;
                                });
                            })
                            ->searchable()
                            ->default('en_US')
                            ->required(),
                    ])
                    ->columns(1),

                Section::make('Branding (Logos & Favicon)')
                    ->description('Select site logos and favicon from the Media Gallery or upload them directly')
                    ->icon('heroicon-o-photo')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        ...\App\Filament\Shared\ImagePicker::make('light_logo_id', 'Light Theme Logo'),
                        ...\App\Filament\Shared\ImagePicker::make('dark_logo_id', 'Dark Theme Logo'),
                        ...\App\Filament\Shared\ImagePicker::make('favicon_id', 'Website Favicon'),
                    ])
                    ->columns(2),


                Section::make('Contact Information')
                    ->description('Manage company locations and their contact details')
                    ->icon('heroicon-o-envelope')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('contact_locations')
                            ->label('Locations')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Location Name / Branch')
                                    ->required()
                                    ->placeholder('e.g. Head Office, New York Branch')
                                    ->maxLength(255),
                                Textarea::make('address')
                                    ->label('Full Address')
                                    ->required()
                                    ->rows(2)
                                    ->maxLength(500),
                                TagsInput::make('phones')
                                    ->label('Phone Numbers')
                                    ->placeholder('Add phone number and press Enter')
                                    ->helperText('You can add multiple phone numbers.'),
                                TagsInput::make('emails')
                                    ->label('Email Addresses')
                                    ->placeholder('Add email address and press Enter')
                                    ->helperText('You can add multiple email addresses.'),
                                Textarea::make('map_iframe')
                                    ->label('Google Map Iframe HTML')
                                    ->placeholder('<iframe src="https://www.google.com/maps/embed?..." width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>')
                                    ->helperText('Paste the HTML embed code from Google Maps share options.')
                                    ->rows(3)
                                    ->maxLength(1000)
                                    ->nullable(),
                            ])
                            ->collapsible()
                            ->default([])
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Social Media Links')
                    ->description('Manage your social media profiles and custom platforms with icons')
                    ->icon('heroicon-o-share')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('social_links')
                            ->label('Social Media Profiles')
                            ->schema([
                                Select::make('platform')
                                    ->label('Platform')
                                    ->options([
                                        'facebook' => 'Facebook',
                                        'twitter' => 'Twitter / X',
                                        'instagram' => 'Instagram',
                                        'linkedin' => 'LinkedIn',
                                        'youtube' => 'YouTube',
                                        'tiktok' => 'TikTok',
                                        'github' => 'GitHub',
                                        'pinterest' => 'Pinterest',
                                        'other' => 'Other / Custom',
                                    ])
                                    ->required()
                                    ->reactive(),
                                TextInput::make('custom_platform')
                                    ->label('Custom Platform Name')
                                    ->hidden(fn ($get) => $get('platform') !== 'other')
                                    ->required(fn ($get) => $get('platform') === 'other')
                                    ->maxLength(255),
                                Select::make('icon_id')
                                    ->label('Choose Icon from Media')
                                    ->options(fn () => \App\Models\MediaUpload::all()->mapWithKeys(function ($media) {
                                        $imgUrl = $media->getFirstMediaUrl('gallery', 'thumb');
                                        return [
                                            $media->id => "
                                                <div class='flex items-center gap-2' style='display: flex; align-items: center; gap: 8px;'>
                                                    <img src='{$imgUrl}' class='w-8 h-8 rounded object-cover' style='width: 32px; height: 32px; border-radius: 4px; object-fit: cover;' />
                                                    <span>{$media->title}</span>
                                                </div>
                                            "
                                        ];
                                    }))
                                    ->allowHtml()
                                    ->searchable()
                                    ->preload(),
                                FileUpload::make('icon_file')
                                    ->label('Or Upload Custom Icon')
                                    ->image()
                                    ->disk('public')
                                    ->directory('social_icons')
                                    ->imageEditor(),
                                TextInput::make('url')
                                    ->label('Profile URL')
                                    ->url()
                                    ->required()
                                    ->placeholder('https://...')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->default([])
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Footer')
                    ->description('Footer content')
                    ->icon('heroicon-o-bars-3-bottom-left')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('footer_text')
                            ->label('Footer Copyright Text')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->columns(1),

                Section::make('Tracking & Scripts')
                    ->description('Analytics, tag management, and custom scripts')
                    ->icon('heroicon-o-code-bracket')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('google_analytics_id')
                            ->label('Google Analytics 4 (GA4)')
                            ->placeholder('G-XXXXXXXXXX')
                            ->helperText('Measurement ID → Admin → Data Streams → your stream')
                            ->maxLength(50),
                        TextInput::make('google_tag_manager_id')
                            ->label('Google Tag Manager')
                            ->placeholder('GTM-XXXXXXX')
                            ->helperText('Container ID → workspace top bar')
                            ->maxLength(50),
                        TextInput::make('meta_pixel_id')
                            ->label('Meta (Facebook) Pixel')
                            ->placeholder('123456789012345')
                            ->helperText('Events Manager → Data Sources → Pixel ID')
                            ->maxLength(50),
                        Textarea::make('custom_head_scripts')
                            ->label('Custom <head> Scripts')
                            ->helperText('Injected before </head> — use for any third-party scripts like Hotjar, Clarity, etc.')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('custom_body_scripts')
                            ->label('Custom <body> Scripts')
                            ->helperText('Injected after <body> — use for GTM noscript fallbacks, chat widgets, etc.')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('SEO Settings')
                    ->description('Search engine verification, indexing control, and default Open Graph sharing image')
                    ->icon('heroicon-o-magnifying-glass')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('google_site_verification')
                            ->label('Google Search Console Verification')
                            ->placeholder('abc123xyz...')
                            ->helperText('The content value from your Google Search Console HTML meta tag')
                            ->maxLength(255),
                        TextInput::make('bing_site_verification')
                            ->label('Bing Webmaster Tools Verification')
                            ->placeholder('abc123xyz...')
                            ->helperText('The content value from your Bing Webmaster Tools meta tag')
                            ->maxLength(255),
                        Toggle::make('public_indexing')
                            ->label('Allow search engine indexing')
                            ->helperText('Disable this on staging/dev environments to prevent Google from indexing the site')
                            ->onIcon('heroicon-m-eye')
                            ->offIcon('heroicon-m-eye-slash')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(true)
                            ->columnSpanFull(),
                        ...\App\Filament\Shared\ImagePicker::make('fallback_og_image_id', 'Fallback Open Graph (OG) Image'),
                        TextInput::make('site_search_url')
                            ->label('Site Search URL (for Google Sitelinks Searchbox)')
                            ->url()
                            ->placeholder('https://yoursite.com/search?q=')
                            ->helperText('Enables a search box directly in Google results. Example: https://yoursite.com/search?q=')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('SMTP / Mail Settings')
                    ->description('Configure how the application sends emails')
                    ->icon('heroicon-o-envelope-open')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('mail_host')
                            ->label('SMTP Host')
                            ->placeholder('smtp.mailtrap.io')
                            ->maxLength(255),
                        TextInput::make('mail_port')
                            ->label('SMTP Port')
                            ->placeholder('587')
                            ->maxLength(10),
                        TextInput::make('mail_username')
                            ->label('SMTP Username')
                            ->placeholder('username')
                            ->maxLength(255),
                        TextInput::make('mail_password')
                            ->label('SMTP Password')
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                        Select::make('mail_encryption')
                            ->label('Encryption')
                            ->options([
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                                'none' => 'None',
                            ])
                            ->default('tls'),
                        TextInput::make('mail_from_address')
                            ->label('From Email Address')
                            ->placeholder('noreply@yourdomain.com')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('mail_from_name')
                            ->label('From Email Name')
                            ->placeholder('My Application')
                            ->maxLength(255),
                        TagsInput::make('mail_receive_address')
                            ->label('Receiver Email Addresses')
                            ->placeholder('Add email address and press Enter')
                            ->helperText('All contact form submissions, leads, and system notifications will be sent to these email addresses.')
                            ->rules([
                                'array',
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        foreach ($value as $email) {
                                            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                                $fail("The '{$email}' address is not a valid email address format.");
                                            }
                                        }
                                    };
                                }
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // All top-level settings images are handled directly via the Media Gallery modal uploader.

        // Process custom social icons file uploads in the social_links repeater
        $socialLinks = $data['social_links'] ?? [];
        foreach ($socialLinks as $index => $link) {
            if (!empty($link['icon_file'])) {
                $filePath = $link['icon_file'];
                if (is_array($filePath)) {
                    $filePath = array_key_first($filePath);
                }
                $diskPath = \Illuminate\Support\Facades\Storage::disk('public')->path($filePath);
                if (file_exists($diskPath)) {
                    $platformName = $link['platform'] === 'other' ? ($link['custom_platform'] ?? 'Custom') : $link['platform'];
                    $title = ucwords($platformName) . ' Icon';
                    $mediaUpload = \App\Models\MediaUpload::create([
                        'title' => $title,
                        'alt_text' => $title,
                    ]);
                    $mediaUpload->addMedia($diskPath)->toMediaCollection('gallery');
                    $socialLinks[$index]['icon_id'] = $mediaUpload->id;
                }
            }
            unset($socialLinks[$index]['icon_file']);
        }
        $data['social_links'] = $socialLinks;

        // Encode array data for storage in the DB
        if (isset($data['contact_locations'])) {
            Setting::set('contact_locations', json_encode($data['contact_locations']));
            unset($data['contact_locations']);
        }
        if (isset($data['social_links'])) {
            Setting::set('social_links', json_encode($data['social_links']));
            unset($data['social_links']);
        }
        if (isset($data['mail_receive_address'])) {
            Setting::set('mail_receive_address', json_encode($data['mail_receive_address']));
            unset($data['mail_receive_address']);
        }

        // Save all other settings
        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        Setting::flushCache();

        // Refresh the form data
        $this->mount();

        Notification::make()
            ->title('Settings saved successfully!')
            ->success()
            ->send();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view settings') ?? false;
    }
}
