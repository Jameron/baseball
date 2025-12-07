<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp } from 'lucide-vue-next';

interface Player {
    id: number;
    name: string;
    position: string;
    position_name: string;
    games: number;
    at_bat: number;
    runs: number;
    hits: number;
    doubles: number;
    triples: number;
    home_runs: number;
    rbi: number;
    walks: number;
    strikeouts: number;
    stolen_bases: number;
    caught_stealing: number;
    batting_average: number | string | null;
    on_base_percentage: number | string | null;
    slugging_percentage: number | string | null;
    on_base_plus_slugging: number | string | null;
}

const props = defineProps<{
    players: Player[];
    sort: string;
    direction: string;
}>();

function sortBy(field: string) {
    const newDirection =
        props.sort === field && props.direction === 'desc' ? 'asc' : 'desc';
    router.get(
        '/players',
        { sort: field, direction: newDirection },
        { preserveState: true },
    );
}

function formatDecimal(value: number | string | null): string {
    if (value === null || value === undefined) return '---';
    const num = typeof value === 'string' ? parseFloat(value) : value;
    return isNaN(num) ? '---' : num.toFixed(3);
}

function calculateHitsPerGame(player: Player): number | null {
    if (!player.games || player.games === 0) return null;
    return player.hits / player.games;
}
</script>

<template>
    <Head title="Baseball Players" />

    <div class="min-h-screen bg-background">
        <!-- Header -->
        <header class="border-b border-border bg-card">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight">
                    ⚾ Baseball Stats
                </h1>
            </div>
        </header>

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <Card>
                <CardHeader>
                    <CardTitle>Player Statistics</CardTitle>
                    <CardDescription>
                        Click a player name to view details. Sort by Hits, Home Runs, or Hits/Game.
                    </CardDescription>
                    
                    <!-- Sort Controls -->
                    <div class="flex items-center gap-2 pt-4">
                        <span class="text-sm text-muted-foreground">Sort by:</span>
                        <Button
                            :variant="sort === 'hits' ? 'default' : 'outline'"
                            size="sm"
                            @click="sortBy('hits')"
                        >
                            Hits
                            <ArrowDown v-if="sort === 'hits' && direction === 'desc'" class="ml-1 h-4 w-4" />
                            <ArrowUp v-if="sort === 'hits' && direction === 'asc'" class="ml-1 h-4 w-4" />
                        </Button>
                        <Button
                            :variant="sort === 'home_runs' ? 'default' : 'outline'"
                            size="sm"
                            @click="sortBy('home_runs')"
                        >
                            Home Runs
                            <ArrowDown v-if="sort === 'home_runs' && direction === 'desc'" class="ml-1 h-4 w-4" />
                            <ArrowUp v-if="sort === 'home_runs' && direction === 'asc'" class="ml-1 h-4 w-4" />
                        </Button>
                        <Button
                            :variant="sort === 'hits_per_game' ? 'default' : 'outline'"
                            size="sm"
                            @click="sortBy('hits_per_game')"
                        >
                            Hits/Game
                            <ArrowDown v-if="sort === 'hits_per_game' && direction === 'desc'" class="ml-1 h-4 w-4" />
                            <ArrowUp v-if="sort === 'hits_per_game' && direction === 'asc'" class="ml-1 h-4 w-4" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left">
                                    <th class="p-3 font-medium">Name</th>
                                    <th class="p-3 font-medium">Pos</th>
                                    <th class="p-3 font-medium">G</th>
                                    <th class="p-3 font-medium" :class="{ 'text-primary': sort === 'hits' }">
                                        H
                                    </th>
                                    <th class="p-3 font-medium" :class="{ 'text-primary': sort === 'hits_per_game' }">
                                        H/G
                                    </th>
                                    <th class="p-3 font-medium" :class="{ 'text-primary': sort === 'home_runs' }">
                                        HR
                                    </th>
                                    <th class="p-3 font-medium">RBI</th>
                                    <th class="p-3 font-medium">AVG</th>
                                    <th class="p-3 font-medium">OPS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="player in players"
                                    :key="player.id"
                                    class="border-b transition-colors hover:bg-muted/50"
                                >
                                    <td class="p-3">
                                        <Link
                                            :href="`/players/${player.id}`"
                                            class="font-medium text-primary hover:underline"
                                        >
                                            {{ player.name }}
                                        </Link>
                                    </td>
                                    <td class="p-3 text-muted-foreground">
                                        {{ player.position }}
                                    </td>
                                    <td class="p-3">{{ player.games }}</td>
                                    <td class="p-3 font-semibold" :class="{ 'text-primary': sort === 'hits' }">
                                        {{ player.hits }}
                                    </td>
                                    <td class="p-3 font-semibold" :class="{ 'text-primary': sort === 'hits_per_game' }">
                                        {{ formatDecimal(calculateHitsPerGame(player)) }}
                                    </td>
                                    <td class="p-3 font-semibold" :class="{ 'text-primary': sort === 'home_runs' }">
                                        {{ player.home_runs }}
                                    </td>
                                    <td class="p-3">{{ player.rbi }}</td>
                                    <td class="p-3">
                                        {{ formatDecimal(player.batting_average) }}
                                    </td>
                                    <td class="p-3">
                                        {{ formatDecimal(player.on_base_plus_slugging) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </main>
    </div>
</template>
