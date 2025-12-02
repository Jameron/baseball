<?php

namespace App\Console\Commands;

use App\Models\Player;
use App\Models\PlayerStatistic;
use App\Models\Position;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImportBaseballData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'baseball:import {--fresh : Clear existing data before importing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import baseball player data from the HireFraction API';

    /**
     * Position abbreviation to full name mapping.
     */
    private array $positionNames = [
        'LF' => 'Left Field',
        'RF' => 'Right Field',
        'CF' => 'Center Field',
        '1B' => 'First Base',
        '2B' => 'Second Base',
        '3B' => 'Third Base',
        'SS' => 'Shortstop',
        'C' => 'Catcher',
        'P' => 'Pitcher',
        'DH' => 'Designated Hitter',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Fetching baseball data from API...');

        try {
            $response = Http::timeout(30)->get('https://api.hirefraction.com/api/test/baseball');

            if (!$response->successful()) {
                $this->error('Failed to fetch data from API. Status: ' . $response->status());
                return Command::FAILURE;
            }

            $players = $response->json();

            if (empty($players)) {
                $this->error('No player data received from API.');
                return Command::FAILURE;
            }

            $this->info('Received ' . count($players) . ' players from API.');

            if ($this->option('fresh')) {
                $this->warn('Clearing existing data...');
                DB::table('player_statistics')->delete();
                DB::table('players')->delete();
                DB::table('positions')->delete();
            }

            DB::beginTransaction();

            try {
                $this->importPlayers($players);
                DB::commit();
                $this->info('Successfully imported ' . count($players) . ' players!');
                return Command::SUCCESS;
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            $this->error('Error importing data: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Import players from the API response.
     */
    private function importPlayers(array $players): void
    {
        $bar = $this->output->createProgressBar(count($players));
        $bar->start();

        foreach ($players as $playerData) {
            // Get or create the position
            $positionAbbrev = $playerData['position'] ?? 'DH';
            $position = Position::firstOrCreate(
                ['abbreviation' => $positionAbbrev],
                ['name' => $this->positionNames[$positionAbbrev] ?? $positionAbbrev]
            );

            // Create or update the player
            $player = Player::updateOrCreate(
                ['name' => $playerData['Player name']],
                ['name' => $playerData['Player name']]
            );

            // Create or update player statistics
            PlayerStatistic::updateOrCreate(
                ['player_id' => $player->id],
                [
                    'position_id' => $position->id,
                    'games' => $this->parseInteger($playerData['Games'] ?? 0),
                    'at_bat' => $this->parseInteger($playerData['At-bat'] ?? 0),
                    'runs' => $this->parseInteger($playerData['Runs'] ?? 0),
                    'hits' => $this->parseInteger($playerData['Hits'] ?? 0),
                    'doubles' => $this->parseInteger($playerData['Double (2B)'] ?? 0),
                    'triples' => $this->parseInteger($playerData['third baseman'] ?? 0), // API has wrong field name
                    'home_runs' => $this->parseInteger($playerData['home run'] ?? 0),
                    'rbi' => $this->parseInteger($playerData['run batted in'] ?? 0),
                    'walks' => $this->parseInteger($playerData['a walk'] ?? 0),
                    'strikeouts' => $this->parseInteger($playerData['Strikeouts'] ?? 0),
                    'stolen_bases' => $this->parseInteger($playerData['stolen base'] ?? 0),
                    'caught_stealing' => $this->parseInteger($playerData['Caught stealing'] ?? 0),
                    'batting_average' => $this->parseDecimal($playerData['AVG'] ?? null),
                    'on_base_percentage' => $this->parseDecimal($playerData['On-base Percentage'] ?? null),
                    'slugging_percentage' => $this->parseDecimal($playerData['Slugging Percentage'] ?? null),
                    'on_base_plus_slugging' => $this->parseDecimal($playerData['On-base Plus Slugging'] ?? null),
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Parse a value as an integer, returning 0 for invalid values.
     */
    private function parseInteger(mixed $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        return 0;
    }

    /**
     * Parse a value as a decimal, returning null for invalid values.
     */
    private function parseDecimal(mixed $value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        return null;
    }
}

