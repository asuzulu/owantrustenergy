<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ProfileImageUploading
{
    use Dispatchable, SerializesModels;

    public $user;
    public $filename;

    public function __construct(User $user, string $filename)
    {
        $this->user = $user;
        $this->filename = $filename;
    }
}
