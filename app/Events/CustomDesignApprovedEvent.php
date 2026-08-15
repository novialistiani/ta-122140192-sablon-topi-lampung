<?php

namespace App\Events;

use App\Models\CustomDesignOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomDesignApprovedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CustomDesignOrder $customDesignOrder;

    /**
     * Create a new event instance.
     */
    public function __construct(CustomDesignOrder $customDesignOrder)
    {
        $this->customDesignOrder = $customDesignOrder;
    }
}
