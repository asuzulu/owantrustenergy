<?php

namespace App\Listeners;

use App\Events\ProfileImageUploading;
use Illuminate\Support\Facades\Storage;

class HandleProfileImageUploading
{
    public function handle(ProfileImageUploading $event)
    {
        $disk = Storage::disk('public');
        $path = 'profile-images/' . $event->filename;

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}
