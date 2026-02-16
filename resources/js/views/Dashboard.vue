<template>
  <PageHeading
    title="Dashboard"
    :description="`Welcome back, ${user?.name}!`"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import PageHeading from '@/components/PageHeading.vue';

const authStore = useAuthStore();
const user = computed(() => authStore.user);
</script>
