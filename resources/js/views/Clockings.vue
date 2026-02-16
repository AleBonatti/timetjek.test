<template>
    <div>
        <PageHeading title="Time Entries" description="View and manage your time entries" />

        <!-- View Switcher -->
        <div class="mt-6">
            <div class="sm:hidden">
                <select
                    v-model="viewMode"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:focus:outline-primary-500"
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
                                ? 'bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-400'
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
                                ? 'bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-400'
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
            <svg class="animate-spin h-12 w-12 mx-auto text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
                            <div class="overflow-hidden shadow-sm outline-1 outline-black/5 dark:outline-white/10 sm:rounded-lg">
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
                                        <tr v-for="entry in entries" :key="entry.id" @click="openEditModal(entry)" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/75 transition-colors">
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
                                            <th scope="row" colspan="3" class="py-3.5 pr-3 pl-4 text-right text-sm font-semibold text-gray-900 dark:text-gray-200 sm:pl-6"></th>
                                            <td class="py-3.5 pr-4 pl-3 text-sm text-right font-semibold whitespace-nowrap text-gray-900 dark:text-gray-200 sm:pr-6">
                                                Total: {{ getDayTotal(entries) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <BaseModal :open="isEditModalOpen" :title="modalTitle" @close="closeEditModal">
            <div class="space-y-4">
                <div v-if="editErrors.general" class="rounded-md bg-red-50 p-4 dark:bg-red-900/20">
                    <p class="text-sm text-red-800 dark:text-red-200">{{ editErrors.general }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <BaseInput id="edit-clock-in" v-model="editForm.clock_in" type="time" label="Clock In Time" required :error="editErrors.clock_in?.[0]" />

                    <BaseInput id="edit-clock-out" v-model="editForm.clock_out" type="time" label="Clock Out Time" :error="editErrors.clock_out?.[0]" />
                </div>

                <BaseTextarea id="edit-notes" v-model="editForm.notes" label="Notes" placeholder="Add any notes about this time entry..." :rows="4" :error="editErrors.notes?.[0]" />
            </div>

            <template #actions>
                <div class="flex flex-col-reverse sm:flex-row sm:justify-between gap-3">
                    <BaseButton variant="danger" @click="deleteEntry" :loading="isDeleting">
                        Delete Entry
                    </BaseButton>
                    <div class="flex gap-3">
                        <BaseButton variant="secondary" @click="closeEditModal">
                            Cancel
                        </BaseButton>
                        <BaseButton variant="primary" :loading="isSaving" @click="saveEntry">
                            Save Changes
                        </BaseButton>
                    </div>
                </div>
            </template>
        </BaseModal>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import PageHeading from '@/components/PageHeading.vue';
import BaseModal from '@/components/BaseModal.vue';
import BaseInput from '@/components/BaseInput.vue';
import BaseTextarea from '@/components/BaseTextarea.vue';
import BaseButton from '@/components/BaseButton.vue';
import axios from '@/utils/axios';
import type { TimeEntry } from '@/types';

type ViewMode = 'week' | 'month';

const viewMode = ref<ViewMode>('week');
const timeEntries = ref<TimeEntry[]>([]);
const isLoading = ref(false);
const isEditModalOpen = ref(false);
const isSaving = ref(false);
const isDeleting = ref(false);
const editingEntry = ref<TimeEntry | null>(null);
const editForm = ref({
    clock_in: '',
    clock_out: '',
    notes: '',
});
const editErrors = ref<Record<string, string>>({});

const modalTitle = computed(() => {
    if (!editingEntry.value) return 'Edit Time Entry';
    const date = new Date(editingEntry.value.clock_in);
    const formattedDate = date.toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
    return `Edit Time Entry for ${formattedDate}`;
});

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

const formatTimeForInput = (dateTime: string | null) => {
    if (!dateTime) return '';
    const date = new Date(dateTime);
    // Format as HH:mm for time input
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes}`;
};

const openEditModal = (entry: TimeEntry) => {
    // Don't allow editing open entries
    if (!entry.clock_out) {
        alert('Cannot edit an open time entry. Please clock out first.');
        return;
    }

    editingEntry.value = entry;
    editForm.value = {
        clock_in: formatTimeForInput(entry.clock_in),
        clock_out: formatTimeForInput(entry.clock_out),
        notes: entry.notes || '',
    };
    editErrors.value = {};
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    editingEntry.value = null;
    editForm.value = {
        clock_in: '',
        clock_out: '',
        notes: '',
    };
    editErrors.value = {};
};

const saveEntry = async () => {
    if (!editingEntry.value) return;

    isSaving.value = true;
    editErrors.value = {};

    try {
        // Combine the original date with the new time
        const originalClockIn = new Date(editingEntry.value.clock_in);
        const originalClockOut = editingEntry.value.clock_out ? new Date(editingEntry.value.clock_out) : null;

        // Parse time from HH:mm format and combine with original date
        const [clockInHours, clockInMinutes] = editForm.value.clock_in.split(':').map(Number);
        const newClockIn = new Date(originalClockIn);
        newClockIn.setHours(clockInHours, clockInMinutes, 0, 0);

        let newClockOut = null;
        if (editForm.value.clock_out && originalClockOut) {
            const [clockOutHours, clockOutMinutes] = editForm.value.clock_out.split(':').map(Number);
            newClockOut = new Date(originalClockOut);
            newClockOut.setHours(clockOutHours, clockOutMinutes, 0, 0);
        }

        // Format datetime in YYYY-MM-DD HH:mm:ss format (Laravel expects this format)
        const formatDateTime = (date: Date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        };

        const response = await axios.put(`/api/time-entries/${editingEntry.value.id}`, {
            clock_in: formatDateTime(newClockIn),
            clock_out: newClockOut ? formatDateTime(newClockOut) : null,
            notes: editForm.value.notes || null,
        });

        // Update the entry in the list
        const index = timeEntries.value.findIndex((e) => e.id === editingEntry.value!.id);
        if (index !== -1) {
            timeEntries.value[index] = response.data.time_entry;
        }

        closeEditModal();
    } catch (error: any) {
        if (error.response?.data?.errors) {
            editErrors.value = error.response.data.errors;
        } else if (error.response?.data?.message) {
            editErrors.value = { general: error.response.data.message };
        } else {
            editErrors.value = { general: 'An error occurred while saving the entry.' };
        }
    } finally {
        isSaving.value = false;
    }
};

const deleteEntry = async () => {
    if (!editingEntry.value) return;

    if (!confirm('Are you sure you want to delete this time entry? This action cannot be undone.')) {
        return;
    }

    isDeleting.value = true;
    editErrors.value = {};

    try {
        await axios.delete(`/api/time-entries/${editingEntry.value.id}`);

        // Remove the entry from the list
        const index = timeEntries.value.findIndex((e) => e.id === editingEntry.value!.id);
        if (index !== -1) {
            timeEntries.value.splice(index, 1);
        }

        closeEditModal();
    } catch (error: any) {
        if (error.response?.data?.message) {
            editErrors.value = { general: error.response.data.message };
        } else {
            editErrors.value = { general: 'An error occurred while deleting the entry.' };
        }
    } finally {
        isDeleting.value = false;
    }
};

// Watch for view mode changes
watch(viewMode, () => {
    fetchTimeEntries();
});

onMounted(() => {
    fetchTimeEntries();
});
</script>
