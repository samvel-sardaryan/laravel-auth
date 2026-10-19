<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['first_name', 'last_name', 'phone', 'location', 'date_of_birth', 'bio', 'avatar'])]
class Profile extends Model
{
    public const int NAME_MAX_LENGTH = 100;

    public const int PHONE_MAX_LENGTH = 30;

    public const int LOCATION_MAX_LENGTH = 100;

    public const int PHONE_MIN_DIGITS = 7;

    public const int PHONE_MAX_DIGITS = 15;

    public const int BIO_MAX_LENGTH = 1000;

    public const int AVATAR_MAX_KILOBYTES = 2048;

    protected function casts(): array
    {
        return ['date_of_birth' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
