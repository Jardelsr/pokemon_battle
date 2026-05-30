<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Battle extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pokemon_one_name',
        'pokemon_one_hp',
        'pokemon_two_name',
        'pokemon_two_hp',
        'winner_name',
        'result',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pokemon_one_hp' => 'integer',
        'pokemon_two_hp' => 'integer',
        'winner_name'    => 'string',
        'result'         => 'string',
    ];

    /**
     * Scope for retrieving the most recent battles.
     *
     * Usage: Battle::recent()->get()
     *
     * @param  Builder<Battle>  $query
     * @return Builder<Battle>
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest()->limit(5);
    }
}
