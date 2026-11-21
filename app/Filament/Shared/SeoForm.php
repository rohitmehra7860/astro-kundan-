<?php

namespace App\Filament\Shared;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TagsInput;



class SeoForm
{
    public static function make(bool $showSchema = true): Section
    {
        $schemaComponents = [
            TextInput::make('meta_title')
                ->label('Meta Title')
                ->maxLength(70)
                ->helperText('Recommended: 50-60 characters. Fallback is the main title if left blank.'),

            Textarea::make('meta_description')
                ->label('Meta Description')
                ->rows(3)
                ->maxLength(160)
                ->helperText('Recommended: 150-160 characters. Fallback is a snippet of content if left blank.'),

            TextInput::make('meta_keywords')
                ->label('Meta Keywords')
                ->placeholder('e.g. tag1, tag2, tag3')
                ->helperText('Comma-separated search keywords.'),

            \Filament\Forms\Components\Toggle::make('robots_indexable')
                ->label('Allow search engines to index this page')
                ->helperText('Turn OFF to hide this specific page from Google. Note: if indexing is disabled globally in Site Settings, the entire site is hidden regardless of this toggle.')
                ->onIcon('heroicon-m-eye')
                ->offIcon('heroicon-m-eye-slash')
                ->onColor('success')
                ->offColor('danger')
                ->default(true)
                ->columnSpanFull(),

            TextInput::make('canonical_url')
                ->label('Canonical URL')
                ->url()
                ->placeholder('https://yourdomain.com/url')
                ->helperText('Preferred page URL to prevent duplicate content flags.'),

            Section::make('Social Media (Open Graph)')
                ->description('Customize visual card layout for Facebook, Twitter, WhatsApp, LinkedIn, etc.')
                ->collapsed()
                ->schema([
                    TextInput::make('og_title')
                        ->label('Social Share Title')
                        ->placeholder('Defaults to Meta Title'),

                    Textarea::make('og_description')
                        ->label('Social Share Description')
                        ->rows(2)
                        ->placeholder('Defaults to Meta Description'),

                    ...\App\Filament\Shared\ImagePicker::make('og_image_id', 'Social Share Image (OG Image)'),
                ]),

            Section::make('Frequently Asked Questions (FAQ)')
                ->description('Add frequently asked questions to generate Google-compliant FAQPage JSON-LD structured markup.')
                ->collapsed()
                ->schema([
                    \Filament\Forms\Components\Repeater::make('faqs')
                        ->label('FAQ List')
                        ->schema([
                            TextInput::make('question')
                                ->required()
                                ->placeholder('e.g., What is your shipping time?'),
                            Textarea::make('answer')
                                ->required()
                                ->rows(3)
                                ->placeholder('e.g., We ship all orders within 2-3 business days.'),
                        ])
                        ->itemLabel(fn(array $state): ?string => $state['question'] ?? null)
                        ->collapsible()
                        ->default([])
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ];

        if ($showSchema) {
            $schemaComponents[] = Section::make('Structured Schema Markup')
                ->description('Select a schema type and fill out details to generate Google-compliant JSON-LD structured data.')
                ->collapsed()
                ->schema([
                    Select::make('schema_type')
                        ->label('Schema Type')
                        ->options([
                            'event' => 'Event',
                            'job_posting' => 'Job Posting',
                            'local_business' => 'Local Business',
                            'product' => 'Product',
                            'organization' => 'Organization',
                        ])
                        ->placeholder('None')
                        ->default(null)
                        ->live() // Dynamic visibility toggle
                        ->columnSpanFull(),

                    // Event Fields Group
                    Section::make('Event Details')
                        ->visible(fn($get) => $get('schema_type') === 'event')
                        ->schema([
                            TextInput::make('schema_data.event_name')
                                ->label('Event Name')
                                ->required(fn($get) => $get('schema_type') === 'event')
                                ->placeholder('e.g. Annual Tech Conference'),
                            TextInput::make('schema_data.event_url')
                                ->label('Event URL')
                                ->url()
                                ->placeholder('e.g. https://yourdomain.com/events/tech-conf'),
                            \Filament\Forms\Components\DateTimePicker::make('schema_data.event_start')
                                ->label('Start Date & Time')
                                ->required(fn($get) => $get('schema_type') === 'event'),
                            \Filament\Forms\Components\DateTimePicker::make('schema_data.event_end')
                                ->label('End Date & Time')
                                ->required(fn($get) => $get('schema_type') === 'event'),
                            Select::make('schema_data.event_mode')
                                ->label('Attendance Mode')
                                ->options([
                                    'https://schema.org/OfflineEventAttendanceMode' => 'Physical (Offline)',
                                    'https://schema.org/OnlineEventAttendanceMode' => 'Online (Virtual)',
                                    'https://schema.org/MixedEventAttendanceMode' => 'Mixed (Hybrid)',
                                ])
                                ->default('https://schema.org/OfflineEventAttendanceMode')
                                ->required(fn($get) => $get('schema_type') === 'event'),
                            TextInput::make('schema_data.event_location_name')
                                ->label('Venue / Platform Name')
                                ->required(fn($get) => $get('schema_type') === 'event')
                                ->placeholder('e.g. Grand Plaza Hall or Zoom Meetings'),
                            TextInput::make('schema_data.event_location_address_or_url')
                                ->label('Venue Address or Stream URL')
                                ->required(fn($get) => $get('schema_type') === 'event')
                                ->placeholder('e.g. 123 Main St, New York or https://zoom.us/j/123'),
                            TextInput::make('schema_data.event_ticket_price')
                                ->label('Ticket Price')
                                ->numeric()
                                ->placeholder('e.g. 49.99'),
                            TextInput::make('schema_data.event_ticket_currency')
                                ->label('Currency')
                                ->default('USD')
                                ->placeholder('e.g. USD'),
                            TextInput::make('schema_data.event_ticket_url')
                                ->label('Ticket Buy URL')
                                ->url()
                                ->placeholder('e.g. https://yourdomain.com/tickets'),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),

                    // Job Posting Fields Group
                    Section::make('Job Details')
                        ->visible(fn($get) => $get('schema_type') === 'job_posting')
                        ->schema([
                            TextInput::make('schema_data.job_title')
                                ->label('Job Title')
                                ->required(fn($get) => $get('schema_type') === 'job_posting')
                                ->placeholder('e.g. Senior PHP Developer'),
                            Textarea::make('schema_data.job_description')
                                ->label('Job Description Summary')
                                ->rows(3)
                                ->required(fn($get) => $get('schema_type') === 'job_posting')
                                ->placeholder('Brief summary of job responsibilities and requirements'),
                            TextInput::make('schema_data.job_company')
                                ->label('Company Name')
                                ->required(fn($get) => $get('schema_type') === 'job_posting')
                                ->placeholder('e.g. Acme Corp'),
                            TextInput::make('schema_data.job_company_website')
                                ->label('Company Website')
                                ->url()
                                ->placeholder('e.g. https://acmecorp.com'),
                            TextInput::make('schema_data.job_location')
                                ->label('Location (City, State, Country or "Remote")')
                                ->required(fn($get) => $get('schema_type') === 'job_posting')
                                ->placeholder('e.g. New York, NY or Remote'),
                            Select::make('schema_data.job_employment_type')
                                ->label('Employment Type')
                                ->options([
                                    'FULL_TIME' => 'Full-time',
                                    'PART_TIME' => 'Part-time',
                                    'CONTRACT' => 'Contract',
                                    'TEMPORARY' => 'Temporary',
                                    'INTERN' => 'Internship',
                                ])
                                ->default('FULL_TIME')
                                ->required(fn($get) => $get('schema_type') === 'job_posting'),
                            TextInput::make('schema_data.job_salary')
                                ->label('Base Annual Salary')
                                ->numeric()
                                ->placeholder('e.g. 85000'),
                            TextInput::make('schema_data.job_salary_currency')
                                ->label('Currency')
                                ->default('USD')
                                ->placeholder('e.g. USD'),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),

                    // Local Business Fields Group
                    Section::make('Local Business Details')
                        ->visible(fn($get) => $get('schema_type') === 'local_business')
                        ->schema([
                            TextInput::make('schema_data.business_name')
                                ->label('Business Name')
                                ->required(fn($get) => $get('schema_type') === 'local_business')
                                ->placeholder('e.g. New York Dental Clinic'),
                            TextInput::make('schema_data.business_type')
                                ->label('Business Type (Schema.org class)')
                                ->default('LocalBusiness')
                                ->required(fn($get) => $get('schema_type') === 'local_business')
                                ->placeholder('e.g. LocalBusiness, Restaurant, Dentist'),
                            TextInput::make('schema_data.business_phone')
                                ->label('Phone Number')
                                ->tel()
                                ->required(fn($get) => $get('schema_type') === 'local_business')
                                ->placeholder('e.g. +1 555-123-4567'),
                            TextInput::make('schema_data.business_street')
                                ->label('Street Address')
                                ->required(fn($get) => $get('schema_type') === 'local_business')
                                ->placeholder('e.g. 123 Main St'),
                            TextInput::make('schema_data.business_city')
                                ->label('City')
                                ->required(fn($get) => $get('schema_type') === 'local_business')
                                ->placeholder('e.g. New York'),
                            TextInput::make('schema_data.business_state')
                                ->label('State / Region')
                                ->placeholder('e.g. NY'),
                            TextInput::make('schema_data.business_zip')
                                ->label('ZIP / Postal Code')
                                ->placeholder('e.g. 10001'),
                            TextInput::make('schema_data.business_country')
                                ->label('Country Code')
                                ->default('US')
                                ->required(fn($get) => $get('schema_type') === 'local_business')
                                ->placeholder('e.g. US, IN'),
                            TextInput::make('schema_data.business_hours')
                                ->label('Opening Hours')
                                ->placeholder('e.g. Mo-Fr 09:00-17:00'),
                            TextInput::make('schema_data.business_price_range')
                                ->label('Price Range ($, $$, or $$$)')
                                ->placeholder('e.g. $$'),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),

                    // Product Fields Group
                    Section::make('Product Details')
                        ->visible(fn($get) => $get('schema_type') === 'product')
                        ->schema([
                            TextInput::make('schema_data.product_name')
                                ->label('Product Name')
                                ->required(fn($get) => $get('schema_type') === 'product')
                                ->placeholder('e.g. Premium Consulting Package'),
                            TextInput::make('schema_data.product_brand')
                                ->label('Brand Name')
                                ->placeholder('e.g. Acme Corp'),
                            TextInput::make('schema_data.product_sku')
                                ->label('SKU')
                                ->placeholder('e.g. PROD-1234'),
                            TextInput::make('schema_data.product_price')
                                ->label('Price')
                                ->numeric()
                                ->required(fn($get) => $get('schema_type') === 'product')
                                ->placeholder('e.g. 99.99'),
                            TextInput::make('schema_data.product_currency')
                                ->label('Currency Code')
                                ->default('INR')
                                ->required(fn($get) => $get('schema_type') === 'product')
                                ->placeholder('e.g. INR, USD'),
                            Select::make('schema_data.product_availability')
                                ->label('Availability')
                                ->options([
                                    'https://schema.org/InStock' => 'In Stock',
                                    'https://schema.org/OutOfStock' => 'Out of Stock',
                                ])
                                ->default('https://schema.org/InStock')
                                ->required(fn($get) => $get('schema_type') === 'product'),
                            Textarea::make('schema_data.product_description')
                                ->label('Product Description')
                                ->placeholder('Short summary of the product or service')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),

                    // Organization Fields Group
                    Section::make('Organization Details')
                        ->visible(fn($get) => $get('schema_type') === 'organization')
                        ->schema([
                            TextInput::make('schema_data.org_name')
                                ->label('Organization Name')
                                ->required(fn($get) => $get('schema_type') === 'organization')
                                ->placeholder('e.g. Acme Corporation Ltd.'),
                            TextInput::make('schema_data.org_url')
                                ->label('Official Website URL')
                                ->url()
                                ->required(fn($get) => $get('schema_type') === 'organization')
                                ->placeholder('e.g. https://acme.org'),
                            TextInput::make('schema_data.org_logo')
                                ->label('Logo URL')
                                ->url()
                                ->placeholder('e.g. https://acme.org/logo.png'),
                            TextInput::make('schema_data.org_phone')
                                ->label('Contact Phone')
                                ->tel()
                                ->placeholder('e.g. +1-555-987-6543'),
                            Textarea::make('schema_data.org_socials')
                                ->label('Social Profiles (One URL per line)')
                                ->placeholder("https://facebook.com/acme\nhttps://twitter.com/acme")
                                ->rows(3)
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull();
        }

        return Section::make('SEO & Metadata')
            ->description('Configure search engine and social media optimization tags')
            ->icon('heroicon-o-presentation-chart-line')
            ->relationship('seoMetadata') // Automatically handles saving/loading one-to-one morphOne relation
            ->collapsed() // Keeps form clean and tidy by default
            ->schema($schemaComponents);
    }
}
