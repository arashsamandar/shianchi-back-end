<?php

namespace App\Events;

use App\OutsideOrder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NonExistingProductOrderSubmitted
{
    use InteractsWithSockets, SerializesModels;
    public $outsideOrder;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OutsideOrder $outsideOrder)
    {
        $this->outsideOrder = $outsideOrder;
    }
}
