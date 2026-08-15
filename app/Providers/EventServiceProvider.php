<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \App\Events\OrderCreatedEvent::class => [
            \App\Listeners\SendOrderCreatedNotification::class,
        ],
        \App\Events\OrderApprovedEvent::class => [
            \App\Listeners\SendOrderApprovedNotification::class,
        ],
        \App\Events\OrderRejectedEvent::class => [
            \App\Listeners\SendOrderRejectedNotification::class,
        ],
        \App\Events\OrderCompletedEvent::class => [
            \App\Listeners\SendOrderCompletedNotification::class,
        ],
        \App\Events\PaymentReceivedEvent::class => [
            \App\Listeners\SendPaymentReceivedNotification::class,
        ],
        \App\Events\CustomDesignUploadedEvent::class => [
            \App\Listeners\SendCustomDesignUploadedNotification::class,
        ],
        \App\Events\CustomDesignApprovedEvent::class => [
            \App\Listeners\SendCustomDesignApprovedNotification::class,
        ],
        \App\Events\CustomDesignRejectedEvent::class => [
            \App\Listeners\SendCustomDesignRejectedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     * We extend ServiceProvider directly (not EventServiceProvider) 
     * to avoid auto-discovery from parent class
     */
    public function boot(): void
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    /**
     * Get the events and handlers.
     */
    public function listens(): array
    {
        return $this->listen;
    }
}
