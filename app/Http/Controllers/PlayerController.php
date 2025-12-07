<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlayerController extends Controller
{
    /**
     * Display a listing of players with their statistics.
     */
    public function index(Request $request): Response
    {
        $sortBy = $request->get('sort', 'hits');
        $sortDirection = $request->get('direction', 'desc');

        // Validate sort field - hits, home_runs, and hits_per_game allowed per AC
        $allowedSortFields = ['hits', 'home_runs', 'hits_per_game'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'hits';
        }

        // Validate sort direction
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $query = Player::with(['statistics.position'])
            ->join('player_statistics', 'players.id', '=', 'player_statistics.player_id')
            ->select('players.*');

        // Handle sorting - hits_per_game is a derived field
        if ($sortBy === 'hits_per_game') {
            $query->orderByRaw("CASE WHEN player_statistics.games > 0 THEN player_statistics.hits / player_statistics.games ELSE 0 END {$sortDirection}");
        } else {
            $query->orderBy("player_statistics.{$sortBy}", $sortDirection);
        }

        $players = $query->get();

        return Inertia::render('Players/Index', [
            'players' => $players->map(function ($player) {
                $stats = $player->statistics->first();
                return [
                    'id' => $player->id,
                    'name' => $player->name,
                    'position' => $stats?->position?->abbreviation,
                    'position_name' => $stats?->position?->name,
                    'games' => $stats?->games,
                    'at_bat' => $stats?->at_bat,
                    'runs' => $stats?->runs,
                    'hits' => $stats?->hits,
                    'doubles' => $stats?->doubles,
                    'triples' => $stats?->triples,
                    'home_runs' => $stats?->home_runs,
                    'rbi' => $stats?->rbi,
                    'walks' => $stats?->walks,
                    'strikeouts' => $stats?->strikeouts,
                    'stolen_bases' => $stats?->stolen_bases,
                    'caught_stealing' => $stats?->caught_stealing,
                    'batting_average' => $stats?->batting_average,
                    'on_base_percentage' => $stats?->on_base_percentage,
                    'slugging_percentage' => $stats?->slugging_percentage,
                    'on_base_plus_slugging' => $stats?->on_base_plus_slugging,
                ];
            }),
            'sort' => $sortBy,
            'direction' => $sortDirection,
        ]);
    }

    /**
     * Display the specified player.
     */
    public function show(Player $player): Response
    {
        $player->load(['statistics.position']);
        $stats = $player->statistics->first();

        return Inertia::render('Players/Show', [
            'player' => [
                'id' => $player->id,
                'name' => $player->name,
                'description' => $player->description,
                'position' => $stats?->position?->abbreviation,
                'position_name' => $stats?->position?->name,
                'games' => $stats?->games,
                'at_bat' => $stats?->at_bat,
                'runs' => $stats?->runs,
                'hits' => $stats?->hits,
                'doubles' => $stats?->doubles,
                'triples' => $stats?->triples,
                'home_runs' => $stats?->home_runs,
                'rbi' => $stats?->rbi,
                'walks' => $stats?->walks,
                'strikeouts' => $stats?->strikeouts,
                'stolen_bases' => $stats?->stolen_bases,
                'caught_stealing' => $stats?->caught_stealing,
                'batting_average' => $stats?->batting_average,
                'on_base_percentage' => $stats?->on_base_percentage,
                'slugging_percentage' => $stats?->slugging_percentage,
                'on_base_plus_slugging' => $stats?->on_base_plus_slugging,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified player.
     */
    public function edit(Player $player): Response
    {
        $player->load(['statistics.position']);
        $stats = $player->statistics->first();

        return Inertia::render('Players/Edit', [
            'player' => [
                'id' => $player->id,
                'name' => $player->name,
                'position' => $stats?->position?->abbreviation,
                'position_name' => $stats?->position?->name,
                'games' => $stats?->games,
                'at_bat' => $stats?->at_bat,
                'runs' => $stats?->runs,
                'hits' => $stats?->hits,
                'doubles' => $stats?->doubles,
                'triples' => $stats?->triples,
                'home_runs' => $stats?->home_runs,
                'rbi' => $stats?->rbi,
                'walks' => $stats?->walks,
                'strikeouts' => $stats?->strikeouts,
                'stolen_bases' => $stats?->stolen_bases,
                'caught_stealing' => $stats?->caught_stealing,
                'batting_average' => $stats?->batting_average,
                'on_base_percentage' => $stats?->on_base_percentage,
                'slugging_percentage' => $stats?->slugging_percentage,
                'on_base_plus_slugging' => $stats?->on_base_plus_slugging,
            ],
        ]);
    }

    /**
     * Update the specified player in storage.
     */
    public function update(Request $request, Player $player)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'games' => 'required|integer|min:0',
            'at_bat' => 'required|integer|min:0',
            'runs' => 'required|integer|min:0',
            'hits' => 'required|integer|min:0',
            'doubles' => 'required|integer|min:0',
            'triples' => 'required|integer|min:0',
            'home_runs' => 'required|integer|min:0',
            'rbi' => 'required|integer|min:0',
            'walks' => 'required|integer|min:0',
            'strikeouts' => 'required|integer|min:0',
            'stolen_bases' => 'required|integer|min:0',
            'caught_stealing' => 'required|integer|min:0',
            'batting_average' => 'nullable|numeric|min:0|max:1',
            'on_base_percentage' => 'nullable|numeric|min:0|max:1',
            'slugging_percentage' => 'nullable|numeric|min:0|max:2',
            'on_base_plus_slugging' => 'nullable|numeric|min:0|max:3',
        ]);

        // Update player name
        $player->update(['name' => $validated['name']]);

        // Update statistics
        $stats = $player->statistics()->first();
        if ($stats) {
            $stats->update([
                'games' => $validated['games'],
                'at_bat' => $validated['at_bat'],
                'runs' => $validated['runs'],
                'hits' => $validated['hits'],
                'doubles' => $validated['doubles'],
                'triples' => $validated['triples'],
                'home_runs' => $validated['home_runs'],
                'rbi' => $validated['rbi'],
                'walks' => $validated['walks'],
                'strikeouts' => $validated['strikeouts'],
                'stolen_bases' => $validated['stolen_bases'],
                'caught_stealing' => $validated['caught_stealing'],
                'batting_average' => $validated['batting_average'],
                'on_base_percentage' => $validated['on_base_percentage'],
                'slugging_percentage' => $validated['slugging_percentage'],
                'on_base_plus_slugging' => $validated['on_base_plus_slugging'],
            ]);
        }

        return redirect()->route('players.show', $player)
            ->with('success', 'Player updated successfully.');
    }
}
