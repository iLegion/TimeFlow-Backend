<?php

namespace App\Models;

use Database\Factories\TrackFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property ?string $title
 * @property Carbon $started_at
 * @property ?Carbon $finished_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read User $user
 *
 * @method static Builder query()
 */
class Track extends Model
{
    /** @use HasFactory<TrackFactory> */
    use HasFactory;

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected $fillable = [
        'title',
        'started_at',
        'finished_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
