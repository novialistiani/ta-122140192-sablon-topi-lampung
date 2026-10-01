<?php

namespace Tests\Feature\CustomDesign;

use App\Events\CustomDesignUploadedEvent;
use App\Models\CustomDesignOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomDesignSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function payload(Product $product, array $override = []): array
    {
        return array_merge([
            'product_id' => $product->id,
            'quantity' => 2,
            'cutting_type' => 'Standar',
            'additional_description' => 'Tolong warna logo dibuat lebih terang',
            'uploads' => [
                [
                    'section_name' => 'A (Dada depan)',
                    'file' => UploadedFile::fake()->image('desain.jpg'),
                ],
            ],
        ], $override);
    }

    protected function makeProduct(): Product
    {
        return Product::factory()->create([
            'price' => 100000,
            'custom_design_allowed' => true,
        ]);
    }

    public function test_guest_cannot_submit_custom_design(): void
    {
        $product = $this->makeProduct();

        $response = $this->post(route('custom-design.store'), $this->payload($product));

        $this->assertContains($response->status(), [302, 401]);
        $this->assertDatabaseCount('custom_design_orders', 0);
    }

    public function test_customer_can_submit_custom_design_with_image(): void
    {
        Storage::fake('public');
        Event::fake();

        $user = User::factory()->create();
        $product = $this->makeProduct();

        $response = $this->actingAs($user)
            ->post(route('custom-design.store'), $this->payload($product));

        $response->assertOk()->assertJson(['success' => true]);

        $order = CustomDesignOrder::first();
        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals(1, $order->uploads()->count());
    }

    public function test_custom_design_accepts_pdf_file(): void
    {
        Storage::fake('public');
        Event::fake();

        $user = User::factory()->create();
        $product = $this->makeProduct();

        $response = $this->actingAs($user)->post(route('custom-design.store'), $this->payload($product, [
            'uploads' => [
                [
                    'section_name' => 'A (Dada depan)',
                    'file' => UploadedFile::fake()->create('desain.pdf', 500, 'application/pdf'),
                ],
            ],
        ]));

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(1, CustomDesignOrder::first()->uploads()->count());
    }

    public function test_custom_design_can_have_multiple_upload_sections(): void
    {
        Storage::fake('public');
        Event::fake();

        $user = User::factory()->create();
        $product = $this->makeProduct();

        $response = $this->actingAs($user)->post(route('custom-design.store'), $this->payload($product, [
            'uploads' => [
                ['section_name' => 'A (Dada depan)', 'file' => UploadedFile::fake()->image('depan.jpg')],
                ['section_name' => 'B (Punggung)', 'file' => UploadedFile::fake()->image('belakang.png')],
            ],
        ]));

        $response->assertOk();
        $this->assertEquals(2, CustomDesignOrder::first()->uploads()->count());
    }

    public function test_custom_design_rejects_invalid_file_type(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $product = $this->makeProduct();

        $response = $this->actingAs($user)->post(route('custom-design.store'), $this->payload($product, [
            'uploads' => [
                [
                    'section_name' => 'A (Dada depan)',
                    'file' => UploadedFile::fake()->create('virus.exe', 100),
                ],
            ],
        ]));

        $response->assertStatus(422);
        $this->assertDatabaseCount('custom_design_orders', 0);
    }

    public function test_custom_design_rejects_file_larger_than_10mb(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $product = $this->makeProduct();

        $response = $this->actingAs($user)->post(route('custom-design.store'), $this->payload($product, [
            'uploads' => [
                [
                    'section_name' => 'A (Dada depan)',
                    'file' => UploadedFile::fake()->create('besar.jpg', 11000),
                ],
            ],
        ]));

        $response->assertStatus(422);
        $this->assertDatabaseCount('custom_design_orders', 0);
    }

    public function test_custom_design_requires_at_least_one_upload(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $response = $this->actingAs($user)->post(route('custom-design.store'), $this->payload($product, [
            'uploads' => [],
        ]));

        $response->assertStatus(422);
        $this->assertDatabaseCount('custom_design_orders', 0);
    }

    public function test_custom_design_rejects_zero_quantity(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $product = $this->makeProduct();

        $response = $this->actingAs($user)->post(route('custom-design.store'), $this->payload($product, [
            'quantity' => 0,
        ]));

        $response->assertStatus(422);
        $this->assertDatabaseCount('custom_design_orders', 0);
    }

    public function test_custom_design_total_price_is_base_price_times_quantity(): void
    {
        Storage::fake('public');
        Event::fake();

        $user = User::factory()->create();
        $product = $this->makeProduct();

        $this->actingAs($user)->post(route('custom-design.store'), $this->payload($product, [
            'quantity' => 2,
        ]));

        $order = CustomDesignOrder::first();
        $this->assertEquals(200000, (float) $order->total_price);
    }

    public function test_custom_design_dispatches_uploaded_event(): void
    {
        Storage::fake('public');
        Event::fake([CustomDesignUploadedEvent::class]);

        $user = User::factory()->create();
        $product = $this->makeProduct();

        $this->actingAs($user)->post(route('custom-design.store'), $this->payload($product));

        Event::assertDispatched(CustomDesignUploadedEvent::class);
    }
}