<?php

namespace Tests\Feature\Stock;

use App\Models\Admin;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class StockReportTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
    }

    private function movement(array $attrs = []): StockMovement
    {
        $createdAt = $attrs['created_at'] ?? null;
        unset($attrs['created_at']);

        $movement = StockMovement::create(array_merge([
            'product_id' => 1,
            'product_name' => 'Kaos Uji',
            'type' => 'out',
            'quantity_change' => -2,
            'stock_before' => 10,
            'stock_after' => 8,
            'reason' => 'order_approved',
        ], $attrs));

        if ($createdAt) {
            $movement->forceFill(['created_at' => $createdAt])->save();
        }

        return $movement;
    }

    private function report(array $query = [])
    {
        return $this->actingAs($this->admin, 'admin')->get(route('admin.stock-report', $query));
    }

    public function test_guest_cannot_view_stock_report(): void
    {
        $this->get(route('admin.stock-report'))->assertRedirect();
    }

    public function test_admin_can_view_stock_report_page(): void
    {
        $this->report()->assertOk()->assertSee('Laporan Stok');
    }

    public function test_totals_sum_incoming_and_outgoing_stock(): void
    {
        $this->movement(['type' => 'in', 'quantity_change' => 10, 'reason' => 'initial_stock']);
        $this->movement(['quantity_change' => -3]);
        $this->movement(['quantity_change' => -2]);

        $this->report()->assertViewHas('totals', function ($totals) {
            return (int) $totals->total_in === 10
                && (int) $totals->total_out === 5
                && (int) $totals->movements === 3;
        });
    }

    public function test_default_period_excludes_movements_older_than_30_days(): void
    {
        $this->movement();
        $this->movement(['created_at' => now()->subDays(60)]);

        $this->report()->assertViewHas('totals', fn ($totals) => (int) $totals->movements === 1);
    }

    public function test_custom_date_range_includes_older_movements(): void
    {
        $this->movement();
        $this->movement(['created_at' => now()->subDays(60)]);

        $this->report([
            'start_date' => now()->subDays(70)->toDateString(),
            'end_date' => now()->toDateString(),
        ])->assertViewHas('totals', fn ($totals) => (int) $totals->movements === 2);
    }

    public function test_search_filters_by_product_name(): void
    {
        $this->movement(['product_id' => 1, 'product_name' => 'Kaos Uji']);
        $this->movement(['product_id' => 2, 'product_name' => 'Topi Uji']);

        $this->report(['search' => 'Topi'])
            ->assertViewHas('totals', fn ($totals) => (int) $totals->movements === 1);
    }

    public function test_type_filter_separates_incoming_and_outgoing_stock(): void
    {
        $this->movement(['type' => 'in', 'quantity_change' => 10, 'reason' => 'initial_stock']);
        $this->movement(['quantity_change' => -3]);

        $this->report(['type' => 'out'])
            ->assertViewHas('totals', fn ($totals) => (int) $totals->movements === 1 && (int) $totals->total_in === 0);
    }

    public function test_per_product_summary_groups_movements_by_product(): void
    {
        $this->movement(['quantity_change' => -3]);
        $this->movement(['quantity_change' => -2]);
        $this->movement(['type' => 'in', 'quantity_change' => 10, 'reason' => 'initial_stock']);

        $this->report()->assertViewHas('perProduct', function ($rows) {
            return $rows->count() === 1
                && (int) $rows->first()->total_in === 10
                && (int) $rows->first()->total_out === 5;
        });
    }

    public function test_stock_report_can_be_exported_to_excel(): void
    {
        Excel::fake();
        $this->movement();

        $this->actingAs($this->admin, 'admin')->get(route('admin.stock-report.export'));

        Excel::assertDownloaded('laporan-stok-' . now()->format('Y-m-d') . '.xlsx');
    }
}