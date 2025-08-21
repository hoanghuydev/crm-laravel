<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLoggedIn
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public string $ipAddress;
    public string $userAgent;
    public \DateTime $loginTime;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->ipAddress = request()->ip() ?? 'unknown';
        $this->userAgent = request()->userAgent() ?? 'unknown';
        $this->loginTime = now()->toDateTime();
    }
}
