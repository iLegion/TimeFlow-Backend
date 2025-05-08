<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property ?string $title
 * @property Carbon $started_at
 * @property ?Carbon $finished_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static Builder query()
 */
class Track extends Model
{
    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected $fillable = [
        'title',
        'started_at',
        'finished_at',
    ];
}
