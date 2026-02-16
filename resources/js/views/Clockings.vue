<template>
    <div>
        <PageHeading title="Clockings" description="View and manage your time entries" />

        <!-- View Switcher -->
        <div class="mt-6">
            <div class="sm:hidden">
                <select
                    v-model="viewMode"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:focus:outline-indigo-500"
                >
                    <option value="week">Current Week</option>
                    <option value="month">Current Month</option>
                </select>
            </div>
            <div class="hidden sm:block">
                <nav class="flex space-x-4" aria-label="Tabs">
                    <button
                        @click="viewMode = 'week'"
                        :class="[
                            viewMode === 'week'
                                ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400'
                                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                            'rounded-md px-3 py-2 text-sm font-medium',
                        ]"
                    >
                        Current Week
                    </button>
                    <button
                        @click="viewMode = 'month'"
                        :class="[
                            viewMode === 'month'
                                ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400'
                                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                            'rounded-md px-3 py-2 text-sm font-medium',
                        ]"
                    >
                        Current Month
                    </button>
                </nav>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="mt-8 text-center py-12">
            <svg class="animate-spin h-12 w-12 mx-auto text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Loading time entries...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="Object.keys(groupedEntries).length === 0" class="mt-8 text-center py-12">
            <p class="text-sm text-gray-500 dark:text-gray-400">No time entries found for this period.</p>
        </div>

        <!-- Tables grouped by day -->
        <div v-else class="mt-8 space-y-8">
            <div v-for="(entries, date) in groupedEntries" :key="date">
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ formatDate(date) }}</h2>
                    </div>
                </div>

                <div class="mt-4 flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <div class="overflow-hidden outline-1 -outline-offset-1 outline-gray-300 dark:outline-white/10 sm:rounded-lg">
                                <table class="relative min-w-full divide-y divide-gray-300 dark:divide-white/15">
                                    <thead class="bg-gray-50 dark:bg-gray-800/75">
                                        <tr>
                                            <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 dark:text-gray-200 sm:pl-6">Clock In</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">Clock Out</th>
                                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900 dark:text-gray-200">Status</th>
                                            <th scope="col" class="py-3.5 pr-4 pl-3 text-right text-sm font-semibold text-gray-900 dark:text-gray-200 sm:pr-6">Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-white/10 bg-white dark:bg-gray-800/50">
                                        <tr v-for="entry in entries" :key="entry.id">
                                            <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-white sm:pl-6">
                                                {{ formatTime(entry.clock_in) }}
                                            </td>
                                            <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                {{ entry.clock_out ? formatTime(entry.clock_out) : '-' }}
                                            </td>
                                            <td class="px-3 py-4 text-sm text-right whitespace-nowrap">
                                                <span
                                                    :class="[
                                                        'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                                        entry.clock_out
                                                            ? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
                                                            : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                    ]"
                                                >
                                                    {{ entry.clock_out ? 'Closed' : 'Open' }}
                                                </span>
                                            </td>
                                            <td class="py-4 pr-4 pl-3 text-sm text-right whitespace-nowrap text-gray-500 dark:text-gray-400 sm:pr-6">
                                                {{ entry.clock_out ? calculateDuration(entry.clock_in, entry.clock_out) : '-' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50 dark:bg-gray-800/75">
                                        <tr>
                                            <th scope="row" colspan="3" class="py-3.5 pr-3 pl-4 text-right text-sm font-semibold text-gray-900 dark:text-gray-200 sm:pl-6">Total:</th>
                                            <td class="py-3.5 pr-4 pl-3 text-sm text-right font-semibold whitespace-nowrap text-gray-900 dark:text-gray-200 sm:pr-6">{{ getDayTotal(entries) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import PageHeading from '@/components/PageHeading.vue';
import axios from '@/utils/axios';
import type { TimeEntry } from '@/types';

type ViewMode = 'week' | 'month';

const viewMode = ref<ViewMode>('week');
const timeEntries = ref<TimeEntry[]>([]);
const isLoading = ref(false);

const groupedEntries = computed(() => {
    const groups: Record<string, TimeEntry[]> = {};

    timeEntries.value.forEach((entry) => {
        const date = new Date(entry.clock_in).toISOString().split('T')[0];
        if (!groups[date]) {
            groups[date] = [];
        }
        groups[date].push(entry);
    });

    return groups;
});

const fetchTimeEntries = async () => {
    isLoading.value = true;
    const startTime = Date.now();

    try {
        const endpoint = viewMode.value === 'week' ? '/api/time-entries/current-week' : '/api/time-entries/current-month';
        const response = await axios.get(endpoint);
        timeEntries.value = response.data.time_entries;
    } catch (error) {
        console.error('Error fetching time entries:', error);
    } finally {
        // Ensure loading state is visible for at least 500ms
        const elapsedTime = Date.now() - startTime;
        const remainingTime = Math.max(0, 500 - elapsedTime);

        setTimeout(() => {
            isLoading.value = false;
        }, remainingTime);
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    // Check if it's today
    if (date.toDateString() === today.toDateString()) {
        return `Today, ${date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}`;
    }

    // Check if it's yesterday
    if (date.toDateString() === yesterday.toDateString()) {
        return `Yesterday, ${date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}`;
    }

    return date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
};

const formatTime = (dateTime: string) => {
    const date = new Date(dateTime);
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });
};

const calculateDuration = (clockIn: string, clockOut: string) => {
    const start = new Date(clockIn);
    const end = new Date(clockOut);
    const diff = end.getTime() - start.getTime();
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    return `${hours}h ${minutes}m`;
};

const getDayTotal = (entries: TimeEntry[]) => {
    let totalMinutes = 0;

    entries.forEach((entry) => {
        if (entry.clock_out) {
            const start = new Date(entry.clock_in);
            const end = new Date(entry.clock_out);
            const diff = end.getTime() - start.getTime();
            totalMinutes += Math.floor(diff / (1000 * 60));
        }
    });

    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    return `${hours}h ${minutes}m`;
};

// Watch for view mode changes
watch(viewMode, () => {
    fetchTimeEntries();
});

onMounted(() => {
    fetchTimeEntries();
});
</script>
