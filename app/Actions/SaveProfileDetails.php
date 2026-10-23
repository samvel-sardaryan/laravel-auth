<?php

namespace App\Actions;

use App\Models\User;
use App\Repositories\Contracts\ProfileRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SaveProfileDetails
{
    public function __construct(private readonly ProfileRepository $profiles) {}

    public function handle(User $user, array $data, ?UploadedFile $avatar): void
    {
        $newPath = $avatar?->store('avatars', 'public');
        $oldPath = $this->profiles->forUser($user)->avatar;
        $details = Arr::except($data, 'avatar');
        if ($newPath) {
            $details['avatar'] = $newPath;
        }
        try {
            $this->profiles->saveDetails($user, $details);
        } catch (Throwable $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            throw $e;
        }
        if ($newPath && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }
    }
}
