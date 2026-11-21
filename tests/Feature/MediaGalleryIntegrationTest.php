<?php

namespace Tests\Feature;

use App\Models\MediaUpload;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaGalleryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->seed(RolePermissionSeeder::class);

        // Create an admin user and assign admin role
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->adminUser->assignRole('admin');
    }

    public function test_can_list_posts_with_shared_featured_image()
    {
        Storage::fake('public');

        // Create a central media upload entry
        $mediaUpload = MediaUpload::create([
            'title' => 'Sample Image',
            'alt_text' => 'Sample alt text',
        ]);
        
        $file = UploadedFile::fake()->image('sample.jpg');
        $mediaUpload->addMedia($file)->toMediaCollection('gallery');

        // Create a post linked to the central media upload
        $post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test Content',
            'status' => 'published',
            'author_id' => $this->adminUser->id,
            'featured_image_id' => $mediaUpload->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonPath('posts.data.0.featured_image_id', $mediaUpload->id)
            ->assertJsonStructure([
                'posts' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'slug',
                            'featured_image_id',
                            'featured_image_url',
                            'thumbnail_url',
                        ]
                    ]
                ]
            ]);

        $this->assertNotEmpty($response->json('posts.data.0.featured_image_url'));
        $this->assertNotEmpty($response->json('posts.data.0.thumbnail_url'));
    }

    public function test_can_create_post_with_direct_image_upload_and_centralize_it()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('featured.png');

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/posts', [
                'title' => 'New Direct Post',
                'slug' => 'new-direct-post',
                'content' => 'Content with direct file upload',
                'status' => 'published',
                'featured_image' => $file,
            ]);

        $response->assertStatus(201);
        
        $postId = $response->json('post.id');
        $post = Post::with('featuredImage')->find($postId);

        $this->assertNotNull($post->featured_image_id);
        $this->assertEquals('featured', $post->featuredImage->title);
        $this->assertNotEmpty($response->json('post.featured_image_url'));
        
        // Ensure MediaUpload model was created
        $this->assertDatabaseHas('media_uploads', [
            'id' => $post->featured_image_id,
            'title' => 'featured',
        ]);
    }
}

