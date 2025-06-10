<?php

namespace App\Models;

use Database\Factories\TrackFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property ?Carbon $email_verified_at
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 *
 * @property-read Collection<int, Project> $projects
 */
class User extends Authenticatable
{
    /** @use HasFactory<TrackFactory> */
    use HasFactory, HasApiTokens;

    public function casts(): array
    {
        return [
            'email_verified_at' => 'datetime'
        ];
    }

    public function email(): Attribute
    {
        return new Attribute(
            set: fn($value) => Str::lower($value),
        );
    }

    public function generateNewToken(): NewAccessToken
    {
        return $this->createToken(Str::random(60));
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
