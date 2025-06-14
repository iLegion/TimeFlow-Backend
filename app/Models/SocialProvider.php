<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $provider_id
 * @property string $provider_name
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 *
 * @property-read User $user
 */
class SocialProvider extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}