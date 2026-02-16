<template>
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Dashboard
        </h1>
        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
            Welcome back, {{ user?.name }}!
        </p>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const user = computed(() => authStore.user);
</script>
