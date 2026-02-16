<template>
    <div class="min-h-full">
        <!-- Mobile sidebar dialog -->
        <div v-if="sidebarOpen" class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900/80 transition-opacity duration-300 ease-linear" @click="sidebarOpen = false" />

            <div class="fixed inset-0 flex">
                <!-- Sidebar panel -->
                <div class="relative mr-16 flex w-full max-w-xs flex-1 transform transition duration-300 ease-in-out">
                    <!-- Close button -->
                    <div class="absolute top-0 left-full flex w-16 justify-center pt-5">
                        <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                            <span class="sr-only">Close sidebar</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" class="size-6 text-white">
                                <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>

                    <!-- Sidebar content -->
                    <div
                        :class="[
                            'relative flex grow flex-col gap-y-5 overflow-y-auto px-6 pb-2',
                            theme === 'dark' ? 'bg-gray-900 ring ring-white/10 before:pointer-events-none before:absolute before:inset-0 before:bg-black/10' : 'bg-white',
                        ]"
                    >
                        <Sidebar :theme="theme" @logout="handleLogout" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Static sidebar for desktop -->
        <div :class="['hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col', theme === 'dark' ? 'bg-gray-900' : '']">
            <div :class="['flex grow flex-col gap-y-5 overflow-y-auto border-r px-6', theme === 'dark' ? 'border-white/10 bg-black/10' : 'border-gray-200 bg-white']">
                <Sidebar :theme="theme" @logout="handleLogout" />
                <div class="-mx-6 mt-auto">
                    <!-- Theme toggle button -->
                    <button
                        :class="[
                            'flex w-full items-center gap-x-4 px-6 py-3 text-sm/6 font-semibold',
                            theme === 'dark' ? 'text-gray-400 hover:bg-white/5 hover:text-white' : 'text-gray-700 hover:bg-gray-50',
                        ]"
                        @click="toggleTheme"
                    >
                        <!-- Sun icon (show in dark mode) -->
                        <svg v-if="theme === 'dark'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-6 shrink-0">
                            <path
                                d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        <!-- Moon icon (show in light mode) -->
                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-6 shrink-0">
                            <path
                                d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        <span aria-hidden="true">{{ theme === 'dark' ? 'Light mode' : 'Dark mode' }}</span>
                    </button>
                    <!-- User profile -->
                    <div :class="['flex w-full items-center gap-x-4 px-6 py-3 text-sm/6 font-semibold', theme === 'dark' ? 'text-white' : 'text-gray-900']">
                        <img
                            v-if="user?.avatar"
                            :src="user.avatar"
                            alt=""
                            :class="['size-8 rounded-full outline -outline-offset-1', theme === 'dark' ? 'bg-gray-800 outline-white/10' : 'bg-gray-50 outline-black/5']"
                        />
                        <div v-else class="size-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-medium text-sm outline -outline-offset-1 outline-black/5">
                            {{ userInitials }}
                        </div>
                        <span aria-hidden="true">{{ user?.name }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile header -->
        <div
            :class="[
                'sticky top-0 z-40 flex items-center gap-x-6 px-4 py-4 sm:px-6 lg:hidden',
                theme === 'dark' ? 'bg-gray-900 after:pointer-events-none after:absolute after:inset-0 after:border-b after:border-white/10 after:bg-black/10' : 'bg-white shadow-xs',
            ]"
        >
            <button type="button" :class="['-m-2.5 p-2.5', theme === 'dark' ? 'text-gray-400 hover:text-white' : 'text-gray-700 hover:text-gray-900']" @click="sidebarOpen = true">
                <span class="sr-only">Open sidebar</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" class="size-6">
                    <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
            <div :class="['flex-1 text-sm/6 font-semibold', theme === 'dark' ? 'text-white' : 'text-gray-900']">
                {{ currentPageTitle }}
            </div>
            <div>
                <img
                    v-if="user?.avatar"
                    :src="user.avatar"
                    alt=""
                    :class="['size-8 rounded-full outline -outline-offset-1', theme === 'dark' ? 'bg-gray-800 outline-white/10' : 'bg-gray-50 outline-black/5']"
                />
                <div v-else class="size-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-medium text-sm outline -outline-offset-1 outline-black/5">
                    {{ userInitials }}
                </div>
            </div>
        </div>

        <!-- Main content -->
        <main class="py-10 lg:pl-72">
            <div class="px-4 sm:px-6 lg:px-8">
                <router-view />
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useTheme } from '@/composables/useTheme';
import Sidebar from '@/components/Sidebar.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const { theme, toggleTheme } = useTheme();

const sidebarOpen = ref(false);
const user = computed(() => authStore.user);

const userInitials = computed(() => {
    if (!user.value?.name) return '?';
    const parts = user.value.name.split(' ');
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[parts.length - 1][0]}`.toUpperCase();
    }
    return user.value.name[0].toUpperCase();
});

const currentPageTitle = computed(() => {
    const routeName = route.name as string;
    const titleMap: Record<string, string> = {
        dashboard: 'Dashboard',
        clockings: 'Clockings',
        settings: 'Settings',
    };
    return titleMap[routeName] || 'Dashboard';
});

const handleLogout = async () => {
    await authStore.logout();
    router.push({ name: 'login' });
};
</script>
