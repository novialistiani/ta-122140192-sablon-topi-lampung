<?php

namespace App\Events;

use App\Models\CustomDesignOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomDesignRejectedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CustomDesignOrder $customDesignOrder;
    public string $reason;

    /**
     * Create a new event instance.
     */
    public function __construct(CustomDesignOrder $customDesignOrder, string $reason = '')
    {
        $this->customDesignOrder = $customDesignOrder;
        $this->reason = $reason;
    }
}
