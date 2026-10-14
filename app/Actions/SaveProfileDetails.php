<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SaveProfileDetails
{
    public function handle(User $user, array $data, ?UploadedFile $avatar): void
    {
        $newPath = $avatar?->store('avatars', 'public');
        $oldPath = $user->profile?->avatar;
        $details = Arr::except($data, 'avatar');
        if ($newPath) {
            $details['avatar'] = $newPath;
        }
        try {
            DB::transaction(fn () => $user->profile()->updateOrCreate([], $details));
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
