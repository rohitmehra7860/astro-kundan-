<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\Category;
use App\Models\Post;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $user->assignRole('admin');
        // 1. Create all System Pages with default SEO configurations
        $systemPages = [
            'home' => [
                'title' => 'Home',
                'content' => 'Welcome to our homepage.',
            ],
            'about' => [
                'title' => 'About',
                'content' => 'Learn more about our company.',
            ],
            'contact' => [
                'title' => 'Contact',
                'content' => 'Get in touch with us.',
            ],
            'blog-category' => [
                'title' => 'Blog Category',
                'content' => 'Browse our blog categories.',
            ],
            'service-category' => [
                'title' => 'Service Category',
                'content' => 'Browse our service categories.',
            ],
            'blog' => [
                'title' => 'Blog',
                'content' => 'Read our latest posts and announcements.',
            ],
            'services' => [
                'title' => 'Services',
                'content' => 'Discover our premium services.',
            ],
        ];

        foreach ($systemPages as $slug => $info) {
            $p = Page::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $info['title'],
                    'content' => $info['content'],
                    'status' => 'published',
                    'is_system' => true,
                ]
            );

            $p->seoMetadata()->firstOrCreate(
                ['seoable_id' => $p->id, 'seoable_type' => Page::class],
                [
                    'meta_title' => $info['title'] . ' - SEO Title',
                    'meta_description' => $info['title'] . ' SEO Description',
                    'robots_instructions' => 'index, follow',
                ]
            );
        }

        // 2. Create Category and SEO
        $category = Category::firstOrCreate(
            ['slug' => 'tech'],
            [
                'name' => 'Tech',
                'description' => 'Technology updates and articles.',
            ]
        );
        $category->seoMetadata()->updateOrCreate(
            ['seoable_id' => $category->id, 'seoable_type' => Category::class],
            [
                'meta_title' => 'Tech Category SEO',
                'meta_description' => 'Category Description',
                'robots_instructions' => 'index, follow',
            ]
        );

        // 3. Create Post and SEO
        $post = Post::firstOrCreate(
            ['slug' => 'first-post'],
            [
                'title' => 'First Post',
                'content' => 'This is our first post content details.',
                'status' => 'published',
                'category_id' => $category->id,
                'author_id' => 1,
            ]
        );
        $post->seoMetadata()->updateOrCreate(
            ['seoable_id' => $post->id, 'seoable_type' => Post::class],
            [
                'meta_title' => 'First Post SEO Title',
                'meta_description' => 'First Post SEO Description',
                'robots_instructions' => 'index, follow',
            ]
        );
    }
}
