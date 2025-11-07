<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class QrCodeGenerated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public readonly array $payload)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('qr.codes');
    }

    public function broadcastAs(): string
    {
        return 'QrCodeGenerated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
