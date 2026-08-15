<?php

namespace Tests\Unit\VirtualAccount;

use App\Models\VirtualAccount;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VirtualAccountModelTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function virtual_account_can_be_created()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $va = VirtualAccount::create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'bank_code' => 'bca',
            'va_number' => '3572123456789',
            'amount' => 500000,
            'status' => 'pending',
            'expired_at' => now()->addDays(1),
        ]);

        $this->assertDatabaseHas('virtual_accounts', [
            'bank_code' => 'bca',
            'va_number' => '3572123456789',
        ]);
    }

    /** @test */
    public function virtual_account_belongs_to_order()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $va = VirtualAccount::create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'bank_code' => 'bni',
            'va_number' => '7654321098765',
            'amount' => 250000,
            'status' => 'pending',
            'expired_at' => now()->addDays(1),
        ]);

        $this->assertTrue($va->order->is($order));
    }

    /** @test */
    public function virtual_account_can_track_amount()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $va = VirtualAccount::create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'bank_code' => 'mandiri',
            'va_number' => '1234567890123',
            'amount' => 1500000.50,
            'status' => 'pending',
            'expired_at' => now()->addDays(1),
        ]);

        $this->assertEquals(1500000.50, $va->fresh()->amount);
    }

    /** @test */
    public function virtual_account_status_can_be_pending()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $va = VirtualAccount::create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'bank_code' => 'bca',
            'va_number' => '1111111111111',
            'amount' => 300000,
            'status' => 'pending',
            'expired_at' => now()->addDays(1),
        ]);

        $this->assertEquals('pending', $va->status);
    }

    /** @test */
    public function virtual_account_status_can_be_cancelled()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $va = VirtualAccount::create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'bank_code' => 'bca',
            'va_number' => '2222222222222',
            'amount' => 400000,
            'status' => 'cancelled',
            'expired_at' => now()->addDays(1),
        ]);

        $this->assertEquals('cancelled', $va->status);
    }

    /** @test */
    public function virtual_account_can_be_updated()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $va = VirtualAccount::create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'bank_code' => 'bca',
            'va_number' => '3333333333333',
            'amount' => 500000,
            'status' => 'pending',
            'expired_at' => now()->addDays(1),
        ]);

        $va->update(['status' => 'paid']);
        $this->assertEquals('paid', $va->fresh()->status);
    }

    /** @test */
    public function virtual_account_tracks_created_at()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $va = VirtualAccount::create([
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'bank_code' => 'bni',
            'va_number' => '9999999999999',
            'amount' => 600000,
            'status' => 'pending',
            'expired_at' => now()->addDays(1),
        ]);

        $this->assertNotNull($va->created_at);
    }

    /** @test */
    public function virtual_account_can_have_multiple_banks()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $banks = ['bca', 'bni', 'mandiri', 'permata'];

        foreach ($banks as $index => $bank) {
            VirtualAccount::create([
                'user_id' => $this->user->id,
                'order_id' => $order->id,
                'bank_code' => $bank,
                'va_number' => '1000000000000' . $index,
                'amount' => 500000,
                'status' => 'pending',
                'expired_at' => now()->addDays(1),
            ]);
        }

        $this->assertEquals(4, VirtualAccount::where('order_id', $order->id)->count());
    }
}
