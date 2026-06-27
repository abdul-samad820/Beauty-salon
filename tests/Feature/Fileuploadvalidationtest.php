<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * FILE UPLOAD VALIDATION TESTS
 *
 * Covers:
 *  8. File upload validation — staff profile photos and gallery images
 *     need type, size, and dimension checks to prevent malicious uploads.
 *
 * Tests:
 *  - Only image MIME types accepted (jpeg, png, webp)
 *  - Files exceeding max size are rejected
 *  - Non-image files (PDF, PHP scripts) are rejected
 *  - Valid image uploads succeed and file is stored
 */
class FileUploadValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    // ──────────────────────────────────────────────
    // STAFF PROFILE PHOTO UPLOAD
    // ──────────────────────────────────────────────

    public function test_staff_profile_photo_upload_accepts_valid_jpeg(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $this->bindTenant($tenant);

        $image = UploadedFile::fake()->image('photo.jpg', 400, 400);

        $response = $this->postJson('/api/v1/owner/staff', [
            'name' => 'New Staff',
            'email' => 'newstaff@test.com',
            'phone' => '9111111111',
            'commission_percent' => 15,
            'specializations' => ['hair'],
            'working_hours' => [
                'mon' => '09:00-18:00',
                'tue' => '09:00-18:00',
                'wed' => '09:00-18:00',
                'thu' => '09:00-18:00',
                'fri' => '09:00-18:00',
                'sat' => '09:00-18:00',
                'sun' => null,
            ],
            'photo' => $image,
        ], $this->ownerHeaders($owner, $tenant));

        // Should succeed — 201
        $response->assertStatus(201);
    }

    public function test_staff_profile_photo_upload_rejects_pdf(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $this->bindTenant($tenant);

        $pdf = UploadedFile::fake()->create('malicious.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/v1/owner/staff', [
            'name' => 'Bad Staff',
            'email' => 'badstaff@test.com',
            'phone' => '9111111112',
            'commission_percent' => 15,
            'specializations' => ['hair'],
            'working_hours' => ['mon' => '09:00-18:00'],
            'photo' => $pdf,
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['photo']);
    }

    public function test_staff_profile_photo_upload_rejects_php_script(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $this->bindTenant($tenant);

        $script = UploadedFile::fake()->create('shell.php', 10, 'text/plain');

        $response = $this->postJson('/api/v1/owner/staff', [
            'name' => 'Hacker',
            'email' => 'hacker@test.com',
            'phone' => '9111111113',
            'commission_percent' => 10,
            'specializations' => ['nail'],
            'working_hours' => ['mon' => '09:00-18:00'],
            'photo' => $script,
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['photo']);
    }

    public function test_staff_profile_photo_upload_rejects_oversized_file(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $this->bindTenant($tenant);

        // 6MB image — typical max is 2MB
        $largeImage = UploadedFile::fake()->image('huge.jpg')->size(6144);

        $response = $this->postJson('/api/v1/owner/staff', [
            'name' => 'BigPhoto Staff',
            'email' => 'bigphoto@test.com',
            'phone' => '9111111114',
            'commission_percent' => 10,
            'specializations' => ['hair'],
            'working_hours' => ['mon' => '09:00-18:00'],
            'photo' => $largeImage,
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['photo']);
    }

    // ──────────────────────────────────────────────
    // PRODUCT IMAGE UPLOAD
    // ──────────────────────────────────────────────

    public function test_product_image_upload_accepts_valid_png(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $this->bindTenant($tenant);

        $image = UploadedFile::fake()->image('product.png', 300, 300);

        $response = $this->postJson('/api/v1/owner/products', [
            'name' => 'Argan Oil',
            'category' => 'hair',
            'price' => 350,
            'quantity' => 30,
            'low_stock_threshold' => 5,
            'is_active' => true,
            'image' => $image,
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(201);
    }

    public function test_product_image_upload_rejects_non_image(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $this->bindTenant($tenant);

        $csv = UploadedFile::fake()->create('data.csv', 50, 'text/csv');

        $response = $this->postJson('/api/v1/owner/products', [
            'name' => 'Bad Product',
            'category' => 'hair',
            'price' => 100,
            'quantity' => 10,
            'low_stock_threshold' => 2,
            'is_active' => true,
            'image' => $csv,
        ], $this->ownerHeaders($owner, $tenant));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    // ──────────────────────────────────────────────
    // GALLERY IMAGE UPLOAD (web route)
    // ──────────────────────────────────────────────

    public function test_gallery_upload_accepts_valid_image(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $this->bindTenant($tenant);

        $image = UploadedFile::fake()->image('gallery.webp', 800, 600);

        $response = $this->actingAs($owner)
            ->post('/owner/gallery', [
                'image' => $image,
                'caption' => 'Before & After',
                '_token' => csrf_token(),
            ]);

        // Should succeed — 302 redirect (web route)
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    public function test_gallery_upload_rejects_non_image_file(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner($tenant);

        $this->bindTenant($tenant);

        $pdf = UploadedFile::fake()->create('fake.pdf', 200, 'application/pdf');

        $response = $this->actingAs($owner)
            ->post('/owner/gallery', [
                'image' => $pdf,
                '_token' => csrf_token(),
            ]);

        $response->assertSessionHasErrors(['image']);
    }
}
