<template>
    <div>
        <PageHeading title="Dashboard" :description="`Hej ${user?.name}!`" />

        <!-- Location Warning -->
        <div v-if="locationWarning" class="mt-6 rounded-md bg-yellow-50 p-4 dark:bg-yellow-900/20">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                            fill-rule="evenodd"
                            d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        {{ locationWarning }}
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <div class="flex flex-col sm:flex-row gap-4">
                <BaseButton variant="success" size="lg" :disabled="!canClockIn" :loading="isClockingIn" :full-width="true" @click="handleClockIn"> Clock In </BaseButton>
                <BaseButton variant="warning" size="lg" :disabled="!canClockOut" :loading="isClockingOut" :full-width="true" @click="handleClockOut"> Clock Out </BaseButton>
            </div>
        </div>

        <div class="mt-8">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Today's Time Entries</h3>

            <div v-if="isLoading" class="text-center py-12">
                <svg class="animate-spin h-12 w-12 mx-auto text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Loading time entries...</p>
            </div>

            <div v-else-if="todayTimeEntries.length === 0" class="text-sm text-gray-500 dark:text-gray-400">No time entries for today yet.</div>

            <div v-else class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Clock In</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Clock Out</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Duration</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
                        <tr v-for="entry in todayTimeEntries" :key="entry.id">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ formatTime(entry.clock_in) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ entry.clock_out ? formatTime(entry.clock_out) : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                                <span v-if="entry.clock_out">
                                    {{ calculateDuration(entry.clock_in, entry.clock_out) }}
                                </span>
                                <span v-else class="font-mono">
                                    {{ getLiveDuration(entry.clock_in) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span
                                    :class="[
                                        'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                        entry.clock_out ? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                    ]"
                                >
                                    {{ entry.clock_out ? 'Closed' : 'Running...' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import PageHeading from '@/components/PageHeading.vue';
import BaseButton from '@/components/BaseButton.vue';
import axios from '@/utils/axios';
import type { TimeEntry } from '@/types';

const authStore = useAuthStore();
const user = computed(() => authStore.user);

const todayTimeEntries = ref<TimeEntry[]>([]);
const isLoading = ref(false);
const isClockingIn = ref(false);
const isClockingOut = ref(false);
const currentTime = ref(new Date());
const locationWarning = ref<string | null>(null);
let timerInterval: ReturnType<typeof setInterval> | null = null;

const canClockIn = computed(() => {
    if (todayTimeEntries.value.length === 0) return true;
    const latestEntry = todayTimeEntries.value[0];
    // Can only clock in if the latest entry is closed (has a clock_out)
    return latestEntry.clock_out != null; // != checks for both null and undefined
});

const canClockOut = computed(() => {
    if (todayTimeEntries.value.length === 0) return false;
    const latestEntry = todayTimeEntries.value[0];
    return latestEntry.clock_out == null; // == checks for both null and undefined
});

const getCurrentPosition = (): Promise<{ latitude: number; longitude: number } | null> => {
    return new Promise((resolve) => {
        if (!navigator.geolocation) {
            locationWarning.value = 'Geolocation is not supported by your browser. Time entries will be recorded without location data.';
            resolve(null);
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                locationWarning.value = null;
                resolve({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                });
            },
            (error) => {
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        locationWarning.value = 'Location access denied. Please enable location permissions in your browser settings to record your location with time entries.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        locationWarning.value = 'Location information is unavailable. Time entries will be recorded without location data.';
                        break;
                    case error.TIMEOUT:
                        locationWarning.value = 'Location request timed out. Time entries will be recorded without location data.';
                        break;
                    default:
                        locationWarning.value = 'An unknown error occurred while getting your location. Time entries will be recorded without location data.';
                        break;
                }
                resolve(null);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0,
            }
        );
    });
};

const fetchTodayTimeEntries = async () => {
    isLoading.value = true;
    const startTime = Date.now();

    try {
        const response = await axios.get('/api/time-entries/today');
        todayTimeEntries.value = response.data.time_entries;
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

const handleClockIn = async () => {
    // Prevent clocking in if there's already an open entry
    if (!canClockIn.value) return;

    isClockingIn.value = true;
    try {
        const position = await getCurrentPosition();
        const response = await axios.post('/api/time-entries/clock-in', {
            latitude: position?.latitude || null,
            longitude: position?.longitude || null,
        });
        // Add the new entry to the beginning of the list
        todayTimeEntries.value.unshift(response.data.time_entry);
    } catch (error) {
        console.error('Error clocking in:', error);
    } finally {
        isClockingIn.value = false;
    }
};

const handleClockOut = async () => {
    isClockingOut.value = true;
    try {
        const position = await getCurrentPosition();
        const response = await axios.post('/api/time-entries/clock-out', {
            latitude: position?.latitude || null,
            longitude: position?.longitude || null,
        });
        // Update the first entry (latest open entry) with clock_out data
        const updatedEntry = response.data.time_entry;
        const index = todayTimeEntries.value.findIndex((entry) => entry.id === updatedEntry.id);
        if (index !== -1) {
            todayTimeEntries.value[index] = updatedEntry;
        }
    } catch (error) {
        console.error('Error clocking out:', error);
    } finally {
        isClockingOut.value = false;
    }
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

const getLiveDuration = (clockIn: string) => {
    const start = new Date(clockIn);
    const now = currentTime.value;
    const diff = Math.max(0, now.getTime() - start.getTime());
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
    return `${hours}h ${minutes}m ${seconds}s`;
};

onMounted(async () => {
    fetchTodayTimeEntries();

    // Check geolocation permission
    if (navigator.permissions) {
        try {
            const permissionStatus = await navigator.permissions.query({ name: 'geolocation' });
            if (permissionStatus.state === 'denied') {
                locationWarning.value = 'Location access denied. Please enable location permissions in your browser settings to record your location with time entries.';
            }
        } catch (error) {
            // Permissions API not supported, geolocation will be checked on first use
        }
    }

    // Update current time every second for live duration
    timerInterval = setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
});
</script>
