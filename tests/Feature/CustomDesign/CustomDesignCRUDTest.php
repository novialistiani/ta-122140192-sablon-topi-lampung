<?php

namespace Tests\Feature\CustomDesign;

use App\Models\CustomDesignOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomDesignCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function custom_design_can_be_created()
    {
        $design = CustomDesignOrder::factory()->create([
            "user_id" => $this->user->id,
            "product_name" => "Custom Design",
        ]);

        $this->assertDatabaseHas("custom_design_orders", [
            "product_name" => "Custom Design",
            "user_id" => $this->user->id,
        ]);
    }

    /** @test */
    public function custom_design_can_be_retrieved()
    {
        $design = CustomDesignOrder::factory()->create(["user_id" => $this->user->id]);

        $this->actingAs($this->user);

        // Test that the design exists in the database
        $this->assertDatabaseHas("custom_design_orders", [
            "id" => $design->id,
            "user_id" => $this->user->id,
        ]);
    }

    /** @test */
    public function custom_design_status_can_be_updated()
    {
        $design = CustomDesignOrder::factory()->create([
            "user_id" => $this->user->id,
            "status" => "pending",
        ]);

        // Update the status directly
        $design->update([
            "status" => "processing",
        ]);

        $this->assertDatabaseHas("custom_design_orders", [
            "id" => $design->id,
            "status" => "processing",
        ]);
    }

    /** @test */
    public function custom_design_can_be_deleted()
    {
        $design = CustomDesignOrder::factory()->create(["user_id" => $this->user->id]);

        $design->delete();

        $this->assertDatabaseMissing("custom_design_orders", [
            "id" => $design->id,
        ]);
    }

    /** @test */
    public function custom_design_quantity_can_be_updated()
    {
        $design = CustomDesignOrder::factory()->create([
            "user_id" => $this->user->id,
            "quantity" => 5,
        ]);

        $design->update([
            "quantity" => 20,
        ]);

        $this->assertDatabaseHas("custom_design_orders", [
            "id" => $design->id,
            "quantity" => 20,
        ]);
    }
}
