<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        // Validate sort field
        $allowedSortFields = ['hits', 'home_runs', 'batting_average', 'name', 'games', 'runs', 'rbi'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'hits';
        }

        // Validate sort direction
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $players = Player::with(['statistics.position'])
            ->join('player_statistics', 'players.id', '=', 'player_statistics.player_id')
            ->select('players.*')
            ->when($sortBy === 'name', function ($query) use ($sortDirection) {
                $query->orderBy('players.name', $sortDirection);
            }, function ($query) use ($sortBy, $sortDirection) {
                $query->orderBy("player_statistics.{$sortBy}", $sortDirection);
            })
            ->get();

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
     * Generate a description for the player based on their stats.
     */
    public function generateDescription(Player $player)
    {
        $player->load(['statistics.position']);
        $stats = $player->statistics->first();

        if (!$stats) {
            return response()->json([
                'error' => 'No statistics found for this player.',
            ], 404);
        }

        $description = $this->buildDescription($player, $stats);

        // Save the description to the database
        $player->update(['description' => $description]);

        return response()->json([
            'description' => $description,
        ]);
    }

    /**
     * Build a rich description based on player statistics.
     */
    private function buildDescription(Player $player, $stats): string
    {
        $name = $player->name;
        $position = $stats->position?->name ?? 'position player';
        $posAbbrev = $stats->position?->abbreviation ?? '';
        
        $games = $stats->games ?? 0;
        $hits = $stats->hits ?? 0;
        $homeRuns = $stats->home_runs ?? 0;
        $rbi = $stats->rbi ?? 0;
        $runs = $stats->runs ?? 0;
        $avg = $stats->batting_average ?? 0;
        $obp = $stats->on_base_percentage ?? 0;
        $slg = $stats->slugging_percentage ?? 0;
        $ops = $stats->on_base_plus_slugging ?? 0;
        $stolenBases = $stats->stolen_bases ?? 0;
        $walks = $stats->walks ?? 0;
        $strikeouts = $stats->strikeouts ?? 0;

        // Determine player tier based on stats
        $hrTier = $this->getHomRunTier($homeRuns);
        $hitsTier = $this->getHitsTier($hits);
        $avgTier = $this->getAvgTier($avg);
        $powerProfile = $this->getPowerProfile($homeRuns, $slg);
        $contactProfile = $this->getContactProfile($avg, $strikeouts, $stats->at_bat ?? 1);
        $speedProfile = $this->getSpeedProfile($stolenBases);
        $disciplineProfile = $this->getDisciplineProfile($walks, $obp);

        // Build paragraphs
        $p1 = "{$name} established themselves as {$this->getArticle($hrTier)} {$hrTier} {$position} over a career spanning {$games} games. ";
        
        if ($homeRuns >= 500) {
            $p1 .= "With {$homeRuns} career home runs, {$name} ranks among the most prolific power hitters in baseball history. ";
        } elseif ($homeRuns >= 300) {
            $p1 .= "Accumulating {$homeRuns} home runs over their career, {$name} demonstrated elite power at the plate. ";
        } else {
            $p1 .= "Recording {$homeRuns} home runs throughout their career, {$name} contributed consistent offensive production. ";
        }
        
        $p1 .= "They drove in {$rbi} runs while scoring {$runs} times, showcasing their ability to produce in crucial situations.";

        $p2 = "At the plate, {$name} was {$contactProfile}. ";
        $p2 .= "Their career batting average of " . number_format($avg, 3) . " {$avgTier}. ";
        
        if ($ops >= 0.900) {
            $p2 .= "With an OPS of " . number_format($ops, 3) . ", they ranked among the elite offensive forces of their era. ";
        } else {
            $p2 .= "They posted an OPS of " . number_format($ops, 3) . ", reflecting their overall offensive contribution. ";
        }
        
        if ($stolenBases >= 200) {
            $p2 .= "Adding {$stolenBases} stolen bases to their résumé, {$name} brought a dynamic element to the basepaths.";
        } elseif ($walks >= 1000) {
            $p2 .= "Drawing {$walks} career walks demonstrated their excellent plate discipline and ability to work counts.";
        } else {
            $p2 .= "Their {$hits} career hits stand as a testament to their consistency and longevity in the game.";
        }

        $p3 = "{$name}'s career statistics paint the picture of {$powerProfile}. ";
        if ($hits >= 3000) {
            $p3 .= "Joining the exclusive 3,000-hit club, they cemented their place among baseball's all-time greats. ";
        } elseif ($hits >= 2500) {
            $p3 .= "With over 2,500 hits, they established themselves as one of the more productive hitters of their generation. ";
        }
        
        if ($homeRuns >= 600) {
            $p3 .= "Their membership in the 600 home run club ensures their legacy as one of the greatest power hitters to ever play the game.";
        } elseif ($homeRuns >= 500) {
            $p3 .= "Reaching the 500 home run milestone places them in rarified air among baseball's power elite.";
        } else {
            $p3 .= "Their combination of {$disciplineProfile} made them a valuable contributor throughout their career.";
        }

        return "{$p1}\n\n{$p2}\n\n{$p3}";
    }

    private function getHomRunTier(int $hr): string
    {
        if ($hr >= 600) return 'legendary';
        if ($hr >= 500) return 'Hall of Fame-caliber';
        if ($hr >= 400) return 'premier';
        if ($hr >= 300) return 'standout';
        if ($hr >= 200) return 'solid';
        return 'capable';
    }

    private function getHitsTier(int $hits): string
    {
        if ($hits >= 3000) return 'historic';
        if ($hits >= 2500) return 'exceptional';
        if ($hits >= 2000) return 'accomplished';
        return 'productive';
    }

    private function getAvgTier(float $avg): string
    {
        if ($avg >= 0.320) return 'places them among the premier contact hitters in history';
        if ($avg >= 0.300) return 'reflects their elite ability to make contact';
        if ($avg >= 0.280) return 'demonstrates their solid hitting ability';
        return 'shows their contribution to the lineup';
    }

    private function getPowerProfile(int $hr, float $slg): string
    {
        if ($hr >= 500 && $slg >= 0.550) return 'a transcendent power hitter who changed games with a single swing';
        if ($hr >= 400) return 'an elite slugger who consistently delivered extra-base power';
        if ($hr >= 300) return 'a dangerous power threat throughout their career';
        return 'a well-rounded offensive player';
    }

    private function getContactProfile(float $avg, int $so, int $ab): string
    {
        $soRate = $ab > 0 ? $so / $ab : 0;
        if ($avg >= 0.300 && $soRate < 0.15) return 'an exceptional contact hitter who rarely struck out';
        if ($avg >= 0.300) return 'a pure hitter with an exceptional eye at the plate';
        if ($avg >= 0.280) return 'a reliable hitter who consistently put the ball in play';
        return 'a productive offensive contributor';
    }

    private function getSpeedProfile(int $sb): string
    {
        if ($sb >= 400) return 'one of the greatest base stealers in history';
        if ($sb >= 200) return 'a significant threat on the basepaths';
        if ($sb >= 100) return 'capable of stealing a base when needed';
        return 'not primarily known for speed';
    }

    private function getDisciplineProfile(int $walks, float $obp): string
    {
        if ($walks >= 1500 && $obp >= 0.400) return 'exceptional plate discipline and on-base ability';
        if ($walks >= 1000) return 'strong plate discipline and patience';
        if ($obp >= 0.350) return 'good on-base skills';
        return 'offensive production';
    }

    private function getArticle(string $word): string
    {
        return in_array(strtolower($word[0]), ['a', 'e', 'i', 'o', 'u']) ? 'an' : 'a';
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

