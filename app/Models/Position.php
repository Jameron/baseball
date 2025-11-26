<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'abbreviation',
    ];

    /**
     * Get the player statistics for this position.
     */
    public function playerStatistics(): HasMany
    {
        return $this->hasMany(PlayerStatistic::class);
    }
}
