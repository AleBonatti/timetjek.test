<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div class="border-b border-gray-200 pb-5 dark:border-white/10">
        <h3 class="text-2xl font-semibold text-gray-900 dark:text-white">
            {{ title }}
        </h3>
        <p v-if="description" class="mt-2 max-w-4xl text-sm text-gray-500 dark:text-gray-400">
            {{ description }}
        </p>
    </div>
</template>
