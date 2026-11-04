<?php

namespace Tests\Feature\Stock;

use Tests\TestCase;

class AutoCancelScheduleTest extends TestCase
{
    public function test_cancel_expired_command_is_scheduled(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('orders:cancel-expired')
            ->assertExitCode(0);
    }
}