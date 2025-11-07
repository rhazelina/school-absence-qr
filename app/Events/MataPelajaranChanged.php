<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class MataPelajaranChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly string $action,
        public readonly array $payload
    ) {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('lessons');
    }

    public function broadcastAs(): string
    {
        return 'LessonChanged';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'data' => $this->payload,
        ];
    }
}
