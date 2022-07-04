<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CategoriesUpdated
{
    use InteractsWithSockets, SerializesModels;
    public $updatedCategoriesId;
    public $deletedCategoriesId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($updatedCategoriesId,$deletedCategoriesId)
    {
        $this->updatedCategoriesId = $updatedCategoriesId;
        $this->deletedCategoriesId = $deletedCategoriesId;
    }

}
