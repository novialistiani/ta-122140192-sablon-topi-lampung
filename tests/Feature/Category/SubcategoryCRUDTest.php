<?php

namespace Tests\Feature\Category;

use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubcategoryCRUDTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function create_subcategory_with_valid_data()
    {
        $data = [
            'name' => 'Topi Olahraga',
            'slug' => 'topi-olahraga',
            'description' => 'Koleksi topi untuk olahraga berbagai jenis',
        ];

        $subcategory = Subcategory::create($data);

        $this->assertDatabaseHas('subcategories', [
            'name' => 'Topi Olahraga',
        ]);
    }

    /** @test */
    public function read_subcategory()
    {
        $subcategory = Subcategory::factory()->create();
        $retrieved = Subcategory::find($subcategory->id);

        $this->assertEquals($subcategory->id, $retrieved->id);
    }

    /** @test */
    public function update_subcategory()
    {
        $subcategory = Subcategory::factory()->create([
            'name' => 'Original',
        ]);

        $subcategory->update(['name' => 'Updated']);

        $this->assertEquals('Updated', $subcategory->fresh()->name);
    }

    /** @test */
    public function delete_subcategory()
    {
        $subcategory = Subcategory::factory()->create();
        $categoryId = $subcategory->id;

        $subcategory->delete();

        $this->assertDatabaseMissing('subcategories', ['id' => $categoryId]);
    }

    /** @test */
    public function list_all_subcategories()
    {
        Subcategory::factory()->count(5)->create();

        $categories = Subcategory::all();

        $this->assertGreaterThanOrEqual(5, $categories->count());
    }

    /** @test */
    public function subcategory_slug_generation()
    {
        $subcategory = Subcategory::factory()->create();

        $this->assertNotNull($subcategory->slug);
        $this->assertIsString($subcategory->slug);
    }
}
