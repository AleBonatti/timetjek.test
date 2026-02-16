<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <nav class="bg-white dark:bg-gray-800 shadow">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex shrink-0 items-center">
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                                Timetjek
                            </h1>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-700 dark:text-gray-300 mr-4">
                            {{ user?.name }}
                        </span>
                        <BaseButton
                            variant="secondary"
                            @click="handleLogout"
                            :loading="loggingOut"
                        >
                            Logout
                        </BaseButton>
                    </div>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    Welcome, {{ user?.name }}!
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    Dashboard content will go here.
                </p>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import BaseButton from '@/components/BaseButton.vue';

const router = useRouter();
const authStore = useAuthStore();

const user = computed(() => authStore.user);
const loggingOut = ref(false);

const handleLogout = async () => {
    loggingOut.value = true;
    await authStore.logout();
    router.push({ name: 'login' });
};
</script>
