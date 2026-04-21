<?php

namespace App\Events;

use App\Models\Patrol;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PatrolQrScanned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Patrol $patrol
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('patrols'),
        ];
    }
}
