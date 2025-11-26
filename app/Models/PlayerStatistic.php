<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerStatistic extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'player_id',
        'position_id',
        'games',
        'at_bat',
        'runs',
        'hits',
        'doubles',
        'triples',
        'home_runs',
        'rbi',
        'walks',
        'strikeouts',
        'stolen_bases',
        'caught_stealing',
        'batting_average',
        'on_base_percentage',
        'slugging_percentage',
        'on_base_plus_slugging',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'batting_average' => 'decimal:3',
            'on_base_percentage' => 'decimal:3',
            'slugging_percentage' => 'decimal:3',
            'on_base_plus_slugging' => 'decimal:3',
        ];
    }

    /**
     * Get the player that owns these statistics.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Get the position for these statistics.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
