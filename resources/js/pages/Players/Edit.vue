<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';

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
    player: Player;
}>();

const form = useForm({
    name: props.player.name,
    games: props.player.games,
    at_bat: props.player.at_bat,
    runs: props.player.runs,
    hits: props.player.hits,
    doubles: props.player.doubles,
    triples: props.player.triples,
    home_runs: props.player.home_runs,
    rbi: props.player.rbi,
    walks: props.player.walks,
    strikeouts: props.player.strikeouts,
    stolen_bases: props.player.stolen_bases,
    caught_stealing: props.player.caught_stealing,
    batting_average: props.player.batting_average,
    on_base_percentage: props.player.on_base_percentage,
    slugging_percentage: props.player.slugging_percentage,
    on_base_plus_slugging: props.player.on_base_plus_slugging,
});

function submit() {
    form.put(`/players/${props.player.id}`);
}

const statFields = [
    { key: 'games', label: 'Games' },
    { key: 'at_bat', label: 'At Bats' },
    { key: 'runs', label: 'Runs' },
    { key: 'hits', label: 'Hits' },
    { key: 'doubles', label: 'Doubles' },
    { key: 'triples', label: 'Triples' },
    { key: 'home_runs', label: 'Home Runs' },
    { key: 'rbi', label: 'RBI' },
    { key: 'walks', label: 'Walks' },
    { key: 'strikeouts', label: 'Strikeouts' },
    { key: 'stolen_bases', label: 'Stolen Bases' },
    { key: 'caught_stealing', label: 'Caught Stealing' },
];

const advancedFields = [
    { key: 'batting_average', label: 'Batting Average', step: '0.001' },
    { key: 'on_base_percentage', label: 'On-Base %', step: '0.001' },
    { key: 'slugging_percentage', label: 'Slugging %', step: '0.001' },
    { key: 'on_base_plus_slugging', label: 'OPS', step: '0.001' },
];
</script>

<template>
    <Head :title="`Edit ${player.name}`" />

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
                <Link :href="`/players/${player.id}`">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back to Player
                    </Button>
                </Link>
            </div>

            <form @submit.prevent="submit">
                <!-- Player Name Card -->
                <Card class="mb-4">
                    <CardHeader>
                        <CardTitle>Edit Player</CardTitle>
                        <CardDescription>
                            Update player information and statistics
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="max-w-md">
                            <Label for="name">Player Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1"
                                required
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Basic Stats Card -->
                <Card class="mb-4">
                    <CardHeader>
                        <CardTitle>Career Statistics</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                            <div
                                v-for="field in statFields"
                                :key="field.key"
                                class="space-y-1"
                            >
                                <Label :for="field.key">{{
                                    field.label
                                }}</Label>
                                <Input
                                    :id="field.key"
                                    v-model.number="
                                        form[field.key as keyof typeof form]
                                    "
                                    type="number"
                                    min="0"
                                    class="mt-1"
                                />
                                <InputError
                                    :message="
                                        form.errors[
                                            field.key as keyof typeof form.errors
                                        ]
                                    "
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Advanced Stats Card -->
                <Card class="mb-4">
                    <CardHeader>
                        <CardTitle>Advanced Metrics</CardTitle>
                        <CardDescription>
                            These are typically calculated values
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                            <div
                                v-for="field in advancedFields"
                                :key="field.key"
                                class="space-y-1"
                            >
                                <Label :for="field.key">{{
                                    field.label
                                }}</Label>
                                <Input
                                    :id="field.key"
                                    v-model.number="
                                        form[field.key as keyof typeof form]
                                    "
                                    type="number"
                                    :step="field.step"
                                    min="0"
                                    max="2"
                                    class="mt-1"
                                />
                                <InputError
                                    :message="
                                        form.errors[
                                            field.key as keyof typeof form.errors
                                        ]
                                    "
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Submit Button -->
                <div class="flex justify-end gap-4">
                    <Link :href="`/players/${player.id}`">
                        <Button variant="outline" type="button">Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="mr-2 h-4 w-4" />
                        Save Changes
                    </Button>
                </div>
            </form>
        </main>
    </div>
</template>
