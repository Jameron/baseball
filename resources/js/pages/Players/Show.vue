<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Head, Link } from '@inertiajs/vue3';
import { Edit, ArrowLeft } from 'lucide-vue-next';

interface Player {
    id: number;
    name: string;
    description: string | null;
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
    player: Player;
}>();

function formatDecimal(value: number | string | null): string {
    if (value === null || value === undefined) return '---';
    const num = typeof value === 'string' ? parseFloat(value) : value;
    return isNaN(num) ? '---' : num.toFixed(3);
}

const stats = [
    { label: 'Games', value: props.player.games, key: 'games' },
    { label: 'At Bats', value: props.player.at_bat, key: 'at_bat' },
    { label: 'Runs', value: props.player.runs, key: 'runs' },
    { label: 'Hits', value: props.player.hits, key: 'hits', highlight: true },
    { label: 'Doubles', value: props.player.doubles, key: 'doubles' },
    { label: 'Triples', value: props.player.triples, key: 'triples' },
    {
        label: 'Home Runs',
        value: props.player.home_runs,
        key: 'home_runs',
        highlight: true,
    },
    { label: 'RBI', value: props.player.rbi, key: 'rbi' },
    { label: 'Walks', value: props.player.walks, key: 'walks' },
    { label: 'Strikeouts', value: props.player.strikeouts, key: 'strikeouts' },
    {
        label: 'Stolen Bases',
        value: props.player.stolen_bases,
        key: 'stolen_bases',
    },
    {
        label: 'Caught Stealing',
        value: props.player.caught_stealing,
        key: 'caught_stealing',
    },
];

const advancedStats = [
    {
        label: 'Batting Average',
        value: formatDecimal(props.player.batting_average),
        key: 'batting_average',
    },
    {
        label: 'On-Base %',
        value: formatDecimal(props.player.on_base_percentage),
        key: 'on_base_percentage',
    },
    {
        label: 'Slugging %',
        value: formatDecimal(props.player.slugging_percentage),
        key: 'slugging_percentage',
    },
    {
        label: 'OPS',
        value: formatDecimal(props.player.on_base_plus_slugging),
        key: 'on_base_plus_slugging',
    },
];
</script>

<template>
    <Head :title="player.name" />

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
            <!-- Header with actions -->
            <div class="mb-6 flex items-center justify-between">
                <Link href="/players">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back to Players
                    </Button>
                </Link>
                <Link :href="`/players/${player.id}/edit`">
                    <Button>
                        <Edit class="mr-2 h-4 w-4" />
                        Edit Player
                    </Button>
                </Link>
            </div>

            <!-- Player Info Card -->
            <Card class="mb-6">
                <CardHeader>
                    <div class="flex items-center gap-3">
                        <CardTitle class="text-2xl">{{
                            player.name
                        }}</CardTitle>
                        <Badge variant="secondary">{{ player.position }}</Badge>
                    </div>
                    <CardDescription>
                        {{ player.position_name }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <!-- Basic Stats -->
                    <h3 class="mb-4 text-lg font-semibold text-muted-foreground">
                        Career Statistics
                    </h3>
                    <div class="mb-8 grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div
                            v-for="stat in stats"
                            :key="stat.key"
                            class="rounded-lg border p-4"
                            :class="{
                                'border-primary/20 bg-primary/5': stat.highlight,
                            }"
                        >
                            <div class="text-sm text-muted-foreground">
                                {{ stat.label }}
                            </div>
                            <div
                                class="text-2xl font-bold"
                                :class="{
                                    'text-primary': stat.highlight,
                                }"
                            >
                                {{ stat.value }}
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Stats -->
                    <h3 class="mb-4 text-lg font-semibold text-muted-foreground">
                        Advanced Metrics
                    </h3>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div
                            v-for="stat in advancedStats"
                            :key="stat.key"
                            class="rounded-lg border bg-muted/30 p-4"
                        >
                            <div class="text-sm text-muted-foreground">
                                {{ stat.label }}
                            </div>
                            <div class="text-2xl font-bold">
                                {{ stat.value }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Player Description Card -->
            <Card>
                <CardHeader>
                    <CardTitle>About {{ player.name }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="player.description" class="prose dark:prose-invert max-w-none">
                        <p
                            v-for="(paragraph, index) in player.description.split('\n\n')"
                            :key="index"
                            class="mb-4 leading-relaxed last:mb-0"
                        >
                            {{ paragraph }}
                        </p>
                    </div>
                    <p v-else class="italic text-muted-foreground">
                        No description available for this player.
                    </p>
                </CardContent>
            </Card>
        </main>
    </div>
</template>
