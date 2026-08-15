<?php

namespace Tests\Unit\Category;

use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubcategoryModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function subcategory_can_be_created()
    {
        $subcategory = Subcategory::create([
            'name' => 'Topi Baseball',
            'description' => 'Kategori topi baseball berkualitas',
        ]);

        $this->assertDatabaseHas('subcategories', [
            'name' => 'Topi Baseball',
        ]);
    }

    /** @test */
    public function subcategory_has_name()
    {
        $subcategory = Subcategory::create([
            'name' => 'Topi Snapback',
            'description' => 'Topi snapback dengan kualitas premium',
        ]);

        $this->assertEquals('Topi Snapback', $subcategory->name);
    }

    /** @test */
    public function subcategory_can_have_description()
    {
        $description = 'Kategori topi dengan desain modern dan stylish';
        $subcategory = Subcategory::create([
            'name' => 'Topi Modern',
            'description' => $description,
        ]);

        $this->assertEquals($description, $subcategory->fresh()->description);
    }

    /** @test */
    public function subcategory_can_be_updated()
    {
        $subcategory = Subcategory::create([
            'name' => 'Original Name',
            'description' => 'Original description',
        ]);

        $subcategory->update([
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);

        $this->assertEquals('Updated Name', $subcategory->fresh()->name);
    }

    /** @test */
    public function subcategory_can_be_deleted()
    {
        $subcategory = Subcategory::create([
            'name' => 'Delete Test',
            'description' => 'This will be deleted',
        ]);

        $id = $subcategory->id;
        $subcategory->delete();

        $this->assertDatabaseMissing('subcategories', ['id' => $id]);
    }

    /** @test */
    public function subcategory_can_have_products()
    {
        $subcategory = Subcategory::create([
            'name' => 'Topi Kasual',
            'description' => 'Topi untuk penggunaan kasual',
        ]);

        $this->assertTrue(method_exists($subcategory, 'products'));
    }

    /** @test */
    public function multiple_subcategories_can_exist()
    {
        $names = ['Topi Baseball', 'Topi Snapback', 'Topi Bucket', 'Topi Trucker'];

        foreach ($names as $name) {
            Subcategory::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => 'Kategori ' . $name,
            ]);
        }

        $this->assertEquals(4, Subcategory::count());
    }

    /** @test */
    public function subcategory_query_returns_correct_count()
    {
        Subcategory::create(['name' => 'Type 1', 'slug' => 'type-1', 'description' => 'Desc 1']);
        Subcategory::create(['name' => 'Type 2', 'slug' => 'type-2', 'description' => 'Desc 2']);
        Subcategory::create(['name' => 'Type 3', 'slug' => 'type-3', 'description' => 'Desc 3']);

        $count = Subcategory::count();
        $this->assertEquals(3, $count);
    }

    /** @test */
    public function subcategory_can_be_found_by_name()
    {
        $subcategory = Subcategory::create([
            'name' => 'Unique Name',
            'description' => 'Unique desc',
        ]);

        $found = Subcategory::where('name', 'Unique Name')->first();
        $this->assertTrue($found->is($subcategory));
    }
}
