<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public string $ipAddress;
    public string $userAgent;
    public \DateTime $registrationTime;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->ipAddress = request()->ip() ?? 'unknown';
        $this->userAgent = request()->userAgent() ?? 'unknown';
        $this->registrationTime = now()->toDateTime();
    }
}
