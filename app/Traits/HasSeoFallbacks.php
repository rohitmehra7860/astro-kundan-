<?php

namespace App\Traits;

use App\Models\SeoMetadata;
use Illuminate\Support\Str;

trait HasSeoFallbacks
{
    /**
     * Format SEO metadata for API response with dynamic fallback generation.
     *
     * @param  mixed  $model  (Post, Page, Category)
     * @return array
     */
    protected function getSeoFields($model)
    {
        $seo        = $model->seoMetadata;
        $frontendUrl = env('FRONTEND_URL', config('app.url'));
        $siteLocale  = \App\Models\Setting::get('site_locale', 'en_US');

        // Determine title & content based on model type
        $title   = $model->title ?? $model->name ?? '';
        $content = $model->content ?? $model->description ?? '';

        // Strip HTML tags and clean up whitespace for description fallbacks
        $cleanContent       = trim(preg_replace('/\s+/', ' ', strip_tags($content)));
        $fallbackDescription = Str::limit($cleanContent, 155, '');

        // Determine default canonical URL
        $slug          = $model->slug ?? '';
        $canonicalPath = '';
        if ($model instanceof \App\Models\Post) {
            $canonicalPath = '/blog/' . $slug;
        } elseif ($model instanceof \App\Models\Page) {
            $canonicalPath = '/' . $slug;
        } elseif ($model instanceof \App\Models\Category) {
            $canonicalPath = '/blog/category/' . $slug;
        } elseif ($model instanceof \App\Models\Service) {
            $canonicalPath = '/services/' . $slug;
        }
        $fallbackCanonical = rtrim($frontendUrl, '/') . $canonicalPath;

        // Determine fallback OG image URL
        $fallbackOgImage = null;
        if ($model instanceof \App\Models\Post || $model instanceof \App\Models\Page || $model instanceof \App\Models\Category || $model instanceof \App\Models\Service) {
            $fallbackOgImage = $model->featuredImage?->getFirstMediaUrl('gallery') ?: null;
        }

        // Determine og:type
        $ogType = 'website';
        if ($model instanceof \App\Models\Post) {
            $ogType = 'article';
        }

        // Resolve resolved values for reuse
        $ogImageUrl      = ($seo && $seo->ogImage)
            ? $seo->ogImage->getFirstMediaUrl('gallery')
            : $fallbackOgImage;
        $metaTitle       = (!empty($seo->meta_title))       ? $seo->meta_title       : $title;
        $metaDescription = (!empty($seo->meta_description)) ? $seo->meta_description : $fallbackDescription;
        $ogTitle         = (!empty($seo->og_title))         ? $seo->og_title         : $metaTitle;
        $ogDescription   = (!empty($seo->og_description))   ? $seo->og_description   : $metaDescription;

        // Twitter/X: large image card for posts, compact summary for pages/categories
        $twitterCard = ($model instanceof \App\Models\Post)
            ? 'summary_large_image'
            : 'summary';

        // Extract @handle from twitter URL setting
        $twitterUrl    = \App\Models\Setting::get('social_twitter', '');
        $twitterHandle = '';
        if ($twitterUrl) {
            $parts         = explode('/', rtrim($twitterUrl, '/'));
            $handle        = end($parts);
            $twitterHandle = $handle ? '@' . ltrim($handle, '@') : '';
        }

        $fields = [
            // Core meta
            'meta_title'          => $metaTitle,
            'meta_description'    => $metaDescription,
            'meta_keywords'       => (!empty($seo->meta_keywords)) ? $seo->meta_keywords : '',
            'canonical_url'       => (!empty($seo->canonical_url)) ? $seo->canonical_url : $fallbackCanonical,
            'robots_instructions' => (!empty($seo->robots_instructions)) ? $seo->robots_instructions : 'index, follow',

            // Open Graph
            'og_type'             => $ogType,
            'og_locale'           => $siteLocale,
            'og_title'            => $ogTitle,
            'og_description'      => $ogDescription,
            'og_image_url'        => $ogImageUrl,

            // Twitter / X Card
            'twitter_card'        => $twitterCard,
            'twitter_title'       => $ogTitle,
            'twitter_description' => $ogDescription,
            'twitter_image'       => $ogImageUrl,
            'twitter_site'        => $twitterHandle,

            // FAQ Schema JSON-LD
            'faq_json_ld'         => ($seo && !empty($seo->faqs)) ? $this->getFaqJsonLd($seo->faqs) : null,

            // Structured Schema JSON-LD (Fallback to global Organization schema if empty)
            'structured_schema_json_ld' => ($seo && !empty($seo->schema_type)) 
                ? $this->getStructuredSchemaJsonLd($seo->schema_type, $seo->schema_data ?? []) 
                : $this->getGlobalOrganizationSchema(),
        ];

        // Article-specific OG tags — only for Posts
        if ($model instanceof \App\Models\Post) {
            $siteName = \App\Models\Setting::get('site_name', config('app.name', 'Laravel'));
            $fields['article_published_time'] = $model->created_at?->toIso8601String();
            $fields['article_modified_time']  = $model->updated_at?->toIso8601String();
            $fields['article_author']         = $siteName;
            $fields['article_section']        = $model->category?->name ?? '';

            // Reading time (avg 200 words per minute)
            $wordCount                      = str_word_count(strip_tags($content));
            $readingTimeMinutes             = max(1, (int) ceil($wordCount / 200));
            $fields['word_count']           = $wordCount;
            $fields['reading_time_minutes'] = $readingTimeMinutes;
        }



        // Global killswitch: if Site Settings "Allow search engine indexing" is OFF,
        // force noindex on every page/post/category regardless of per-page setting.
        $globalIndexing = \App\Models\Setting::get('public_indexing', '1');
        if (empty($globalIndexing) || $globalIndexing === '0' || $globalIndexing === false) {
            $fields['robots_instructions'] = 'noindex, nofollow';
        }

        $fields['schema_json_ld']     = $this->getSchemaJsonLd($model, $fields);
        $fields['breadcrumb_json_ld'] = $this->getBreadcrumbJsonLd($model, $fields);

        return $fields;
    }

    /**
     * Generate Schema.org JSON-LD structured data payload.
     *
     * @param  mixed  $model
     * @param  array  $seoFields
     * @return array|null
     */
    protected function getSchemaJsonLd($model, array $seoFields): ?array
    {
        $title       = $seoFields['meta_title']       ?: '';
        $description = $seoFields['meta_description'] ?: '';
        $canonical   = $seoFields['canonical_url']    ?: '';
        $imageUrl    = $seoFields['og_image_url']      ?: null;
        $frontendUrl = env('FRONTEND_URL', config('app.url'));
        $siteName    = \App\Models\Setting::get('site_name', config('app.name', 'Laravel'));

        $webSiteSchema = [
            '@type' => 'WebSite',
            '@id'   => rtrim($frontendUrl, '/') . '/#website',
            'name'  => $siteName,
            'url'   => $frontendUrl,
        ];

        if ($model instanceof \App\Models\Page) {
            return [
                '@context'    => 'https://schema.org',
                '@type'       => 'WebPage',
                '@id'         => $canonical . '#webpage',
                'url'         => $canonical,
                'name'        => $title,
                'description' => $description,
                'isPartOf'    => $webSiteSchema,
                'image'       => $imageUrl,
            ];
        }

        if ($model instanceof \App\Models\Post) {
            $schema = [
                '@context'    => 'https://schema.org',
                '@type'       => 'BlogPosting',
                '@id'         => $canonical . '#blogposting',
                'isPartOf'    => [
                    '@type'   => 'WebPage',
                    '@id'     => $canonical . '#webpage',
                    'url'     => $canonical,
                    'name'    => $title,
                    'isPartOf' => $webSiteSchema,
                ],
                'headline'      => $title,
                'description'   => $description,
                'image'         => $imageUrl,
                'datePublished' => $model->created_at?->toIso8601String(),
                'dateModified'  => $model->updated_at?->toIso8601String(),
                'author'        => [
                    '@type' => 'Organization',
                    'name'  => $siteName,
                ],
                'publisher'     => [
                    '@type' => 'Organization',
                    'name'  => $siteName,
                ],
            ];

            // Enrich with word count and reading time if available
            if (!empty($seoFields['word_count'])) {
                $schema['wordCount']    = $seoFields['word_count'];
                $schema['timeRequired'] = 'PT' . $seoFields['reading_time_minutes'] . 'M';
            }

            return $schema;
        }



        if ($model instanceof \App\Models\Category) {
            return [
                '@context'    => 'https://schema.org',
                '@type'       => 'CollectionPage',
                '@id'         => $canonical . '#collectionpage',
                'url'         => $canonical,
                'name'        => $model->name ?? $title,
                'description' => $model->description ?? $description,
                'isPartOf'    => $webSiteSchema,
                'image'       => $imageUrl,
            ];
        }

        if ($model instanceof \App\Models\Service) {
            return [
                '@context'    => 'https://schema.org',
                '@type'       => 'Service',
                '@id'         => $canonical . '#service',
                'url'         => $canonical,
                'name'        => $title,
                'description' => $description,
                'provider'    => [
                    '@type' => 'Organization',
                    'name'  => $siteName,
                    'url'   => $frontendUrl,
                ],
                'image'       => $imageUrl,
            ];
        }

        return null;
    }

    /**
     * Generate Schema.org BreadcrumbList JSON-LD for improved SERP display.
     *
     * @param  mixed  $model
     * @param  array  $seoFields
     * @return array|null
     */
    protected function getBreadcrumbJsonLd($model, array $seoFields): ?array
    {
        $frontendUrl = rtrim(env('FRONTEND_URL', config('app.url')), '/');
        $siteName    = \App\Models\Setting::get('site_name', config('app.name', 'Laravel'));

        // Start with Home
        $items = [
            [
                '@type'    => 'ListItem',
                'position' => 1,
                'name'     => $siteName,
                'item'     => $frontendUrl,
            ],
        ];

        if ($model instanceof \App\Models\Post) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => 'Blog',
                'item'     => $frontendUrl . '/blog',
            ];
            if (!empty($model->category)) {
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => 3,
                    'name'     => $model->category->name,
                    'item'     => $frontendUrl . '/blog/category/' . $model->category->slug,
                ];
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => 4,
                    'name'     => $seoFields['meta_title'],
                    'item'     => $seoFields['canonical_url'],
                ];
            } else {
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => 3,
                    'name'     => $seoFields['meta_title'],
                    'item'     => $seoFields['canonical_url'],
                ];
            }
        } elseif ($model instanceof \App\Models\Page) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => $seoFields['meta_title'],
                'item'     => $seoFields['canonical_url'],
            ];
        } elseif ($model instanceof \App\Models\Category) {
            $isServiceCat = ($model->type === 'service');
            $sectionLabel = $isServiceCat ? 'Services' : 'Blog';
            $sectionUrl   = $isServiceCat ? '/services' : '/blog';

            $items[]    = [
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => $sectionLabel,
                'item'     => $frontendUrl . $sectionUrl,
            ];
            $items[] = [
                '@type'    => 'ListItem',
                'position' => 3,
                'name'     => $model->name,
                'item'     => $seoFields['canonical_url'],
            ];
        } elseif ($model instanceof \App\Models\Service) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => 'Services',
                'item'     => $frontendUrl . '/services',
            ];
            if (!empty($model->category)) {
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => 3,
                    'name'     => $model->category->name,
                    'item'     => $frontendUrl . '/services/category/' . $model->category->slug,
                ];
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => 4,
                    'name'     => $seoFields['meta_title'],
                    'item'     => $seoFields['canonical_url'],
                ];
            } else {
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => 3,
                    'name'     => $seoFields['meta_title'],
                    'item'     => $seoFields['canonical_url'],
                ];
            }
        }

        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * Generate FAQPage JSON-LD schema markup.
     *
     * @param  array  $faqs
     * @return array|null
     */
    protected function getFaqJsonLd(array $faqs): ?array
    {
        $entities = [];

        foreach ($faqs as $faq) {
            $question = trim($faq['question'] ?? '');
            $answer = trim($faq['answer'] ?? '');

            if (!empty($question) && !empty($answer)) {
                $entities[] = [
                    '@type' => 'Question',
                    'name' => $question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $answer,
                    ],
                ];
            }
        }

        if (empty($entities)) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities,
        ];
    }

    /**
     * Generate Structured Schema.org JSON-LD payload (Event, JobPosting, LocalBusiness).
     */
    protected function getStructuredSchemaJsonLd(string $type, array $data): ?array
    {
        if (empty($type)) {
            return null;
        }

        switch ($type) {
            case 'event':
                $payload = [
                    '@context' => 'https://schema.org',
                    '@type' => 'Event',
                    'name' => $data['event_name'] ?? '',
                    'url' => $data['event_url'] ?? '',
                ];

                if (!empty($data['event_start'])) {
                    $payload['startDate'] = \Illuminate\Support\Carbon::parse($data['event_start'])->toIso8601String();
                }
                if (!empty($data['event_end'])) {
                    $payload['endDate'] = \Illuminate\Support\Carbon::parse($data['event_end'])->toIso8601String();
                }
                if (!empty($data['event_mode'])) {
                    $payload['eventAttendanceMode'] = $data['event_mode'];
                }

                $locationType = 'Place';
                if (($data['event_mode'] ?? '') === 'https://schema.org/OnlineEventAttendanceMode') {
                    $locationType = 'VirtualLocation';
                    $payload['location'] = [
                        '@type' => $locationType,
                        'url' => $data['event_location_address_or_url'] ?? '',
                    ];
                } else {
                    $payload['location'] = [
                        '@type' => $locationType,
                        'name' => $data['event_location_name'] ?? '',
                        'address' => [
                            '@type' => 'PostalAddress',
                            'streetAddress' => $data['event_location_address_or_url'] ?? '',
                        ],
                    ];
                }

                if (!empty($data['event_ticket_price'])) {
                    $payload['offers'] = [
                        '@type' => 'Offer',
                        'price' => number_format((float) $data['event_ticket_price'], 2, '.', ''),
                        'priceCurrency' => $data['event_ticket_currency'] ?? 'USD',
                        'url' => $data['event_ticket_url'] ?? '',
                    ];
                }

                return $payload;

            case 'job_posting':
                $payload = [
                    '@context' => 'https://schema.org',
                    '@type' => 'JobPosting',
                    'title' => $data['job_title'] ?? '',
                    'description' => $data['job_description'] ?? '',
                    'hiringOrganization' => [
                        '@type' => 'Organization',
                        'name' => $data['job_company'] ?? '',
                        'sameAs' => $data['job_company_website'] ?? '',
                    ],
                    'jobLocation' => [
                        '@type' => 'Place',
                        'address' => [
                            '@type' => 'PostalAddress',
                            'addressLocality' => $data['job_location'] ?? '',
                        ],
                    ],
                    'employmentType' => $data['job_employment_type'] ?? 'FULL_TIME',
                ];

                if (!empty($data['job_salary'])) {
                    $payload['baseSalary'] = [
                        '@type' => 'MonetaryAmount',
                        'currency' => $data['job_salary_currency'] ?? 'USD',
                        'value' => [
                            '@type' => 'QuantitativeValue',
                            'value' => number_format((float) $data['job_salary'], 2, '.', ''),
                            'unitText' => 'YEAR',
                        ],
                    ];
                }

                return $payload;

            case 'local_business':
                $payload = [
                    '@context' => 'https://schema.org',
                    '@type' => $data['business_type'] ?? 'LocalBusiness',
                    'name' => $data['business_name'] ?? '',
                    'telephone' => $data['business_phone'] ?? '',
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => $data['business_street'] ?? '',
                        'addressLocality' => $data['business_city'] ?? '',
                        'addressRegion' => $data['business_state'] ?? '',
                        'postalCode' => $data['business_zip'] ?? '',
                        'addressCountry' => $data['business_country'] ?? 'US',
                    ],
                ];

                if (!empty($data['business_hours'])) {
                    $payload['openingHours'] = $data['business_hours'];
                }
                if (!empty($data['business_price_range'])) {
                    $payload['priceRange'] = $data['business_price_range'];
                }

                return $payload;

            case 'product':
                $payload = [
                    '@context' => 'https://schema.org',
                    '@type' => 'Product',
                    'name' => $data['product_name'] ?? '',
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => number_format((float) ($data['product_price'] ?? 0), 2, '.', ''),
                        'priceCurrency' => $data['product_currency'] ?? 'INR',
                        'availability' => $data['product_availability'] ?? 'https://schema.org/InStock',
                    ]
                ];
                if (!empty($data['product_brand'])) {
                    $payload['brand'] = [
                        '@type' => 'Brand',
                        'name' => $data['product_brand'],
                    ];
                }
                if (!empty($data['product_sku'])) {
                    $payload['sku'] = $data['product_sku'];
                }
                if (!empty($data['product_description'])) {
                    $payload['description'] = $data['product_description'];
                }
                return $payload;

            case 'organization':
                $payload = [
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    'name' => $data['org_name'] ?? '',
                    'url' => $data['org_url'] ?? '',
                ];
                if (!empty($data['org_logo'])) {
                    $payload['logo'] = $data['org_logo'];
                }
                if (!empty($data['org_phone'])) {
                    $payload['telephone'] = $data['org_phone'];
                }
                if (!empty($data['org_socials'])) {
                    if (is_string($data['org_socials'])) {
                        $payload['sameAs'] = array_values(array_filter(array_map('trim', explode("\n", $data['org_socials']))));
                    } else {
                        $payload['sameAs'] = (array) $data['org_socials'];
                    }
                }
                return $payload;
        }

        return null;
    }

    /**
     * Generate Global Organization Schema as fallback.
     */
    protected function getGlobalOrganizationSchema(): ?array
    {
        $siteName = \App\Models\Setting::get('site_name', config('app.name', ''));
        $siteUrl = rtrim(env('FRONTEND_URL', config('app.url')), '/');
        
        $orgSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => $siteUrl,
        ];
        
        $email = \App\Models\Setting::get('site_email');
        $phone = \App\Models\Setting::get('site_phone');
        $address = \App\Models\Setting::get('site_address');
        
        // Load contact locations and map them
        $contactPoints = [];
        $contactLocationsJson = \App\Models\Setting::get('contact_locations');
        if (!empty($contactLocationsJson)) {
            $locations = json_decode($contactLocationsJson, true) ?? [];
            if (!empty($locations) && is_array($locations)) {
                // Set primary contact details from the first location
                $firstLocation = $locations[0] ?? [];
                if (empty($email) && !empty($firstLocation['emails'])) {
                    $email = is_array($firstLocation['emails']) ? ($firstLocation['emails'][0] ?? null) : $firstLocation['emails'];
                }
                if (empty($phone) && !empty($firstLocation['phones'])) {
                    $phone = is_array($firstLocation['phones']) ? ($firstLocation['phones'][0] ?? null) : $firstLocation['phones'];
                }
                if (empty($address) && !empty($firstLocation['address'])) {
                    $address = $firstLocation['address'];
                }

                // Map all locations as contact points
                foreach ($locations as $loc) {
                    $point = [
                        '@type' => 'ContactPoint',
                        'contactType' => $loc['name'] ?? 'office',
                    ];
                    if (!empty($loc['address'])) {
                        $point['address'] = [
                            '@type' => 'PostalAddress',
                            'streetAddress' => $loc['address'],
                        ];
                    }
                    if (!empty($loc['emails'])) {
                        $point['email'] = is_array($loc['emails']) ? ($loc['emails'][0] ?? null) : $loc['emails'];
                    }
                    if (!empty($loc['phones'])) {
                        $point['telephone'] = is_array($loc['phones']) ? ($loc['phones'][0] ?? null) : $loc['phones'];
                    }
                    $contactPoints[] = $point;
                }
            }
        }

        if (!empty($email)) {
            $orgSchema['email'] = $email;
        }
        if (!empty($phone)) {
            $orgSchema['telephone'] = $phone;
        }
        if (!empty($address)) {
            $orgSchema['address'] = $address;
        }

        if (!empty($contactPoints)) {
            $orgSchema['contactPoint'] = $contactPoints;
        }
        
        // Prioritize Light Logo, then Dark Logo, and finally fallback to OG Image
        $logoId = \App\Models\Setting::get('light_logo_id');
        if (empty($logoId)) {
            $logoId = \App\Models\Setting::get('dark_logo_id');
        }
        if (empty($logoId)) {
            $logoId = \App\Models\Setting::get('fallback_og_image_id');
        }

        if (!empty($logoId)) {
            $media = \App\Models\MediaUpload::find($logoId);
            if ($media) {
                $orgSchema['logo'] = $media->getFirstMediaUrl('gallery', 'thumb');
            }
        }
        
        // Build social profiles sameAs
        $socialProfiles = array_filter([
            \App\Models\Setting::get('social_facebook'),
            \App\Models\Setting::get('social_twitter'),
            \App\Models\Setting::get('social_instagram'),
            \App\Models\Setting::get('social_linkedin'),
            \App\Models\Setting::get('social_youtube'),
        ]);
        
        $socialLinksJson = \App\Models\Setting::get('social_links');
        if (!empty($socialLinksJson)) {
            $socialLinks = json_decode($socialLinksJson, true) ?? [];
            foreach ($socialLinks as $link) {
                if (!empty($link['url'])) {
                    $socialProfiles[] = $link['url'];
                }
            }
        }
        
        $socialProfiles = array_values(array_unique(array_filter($socialProfiles)));
        if (!empty($socialProfiles)) {
            $orgSchema['sameAs'] = $socialProfiles;
        }
        
        $searchUrl = \App\Models\Setting::get('site_search_url');
        if (!empty($searchUrl)) {
            $orgSchema['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => rtrim($searchUrl, '=') . '={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ];
        }
        
        return $orgSchema;
    }
}
