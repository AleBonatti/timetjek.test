<template>
  <div class="min-h-full">
    <!-- Mobile sidebar dialog -->
    <div
      v-if="sidebarOpen"
      class="relative z-50 lg:hidden"
      role="dialog"
      aria-modal="true"
    >
      <!-- Backdrop -->
      <div
        class="fixed inset-0 bg-gray-900/80 transition-opacity duration-300 ease-linear"
        @click="sidebarOpen = false"
      />

      <div class="fixed inset-0 flex">
        <!-- Sidebar panel -->
        <div class="relative mr-16 flex w-full max-w-xs flex-1 transform transition duration-300 ease-in-out">
          <!-- Close button -->
          <div class="absolute top-0 left-full flex w-16 justify-center pt-5">
            <button
              type="button"
              class="-m-2.5 p-2.5"
              @click="sidebarOpen = false"
            >
              <span class="sr-only">Close sidebar</span>
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                aria-hidden="true"
                class="size-6 text-white"
              >
                <path
                  d="M6 18 18 6M6 6l12 12"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
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
            <div class="relative flex h-16 shrink-0 items-center">
              <img
                :src="
                  theme === 'dark'
                    ? 'https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500'
                    : 'https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600'
                "
                alt="Timetjek"
                class="h-8 w-auto"
              >
            </div>
            <nav class="relative flex flex-1 flex-col">
              <ul
                role="list"
                class="flex flex-1 flex-col gap-y-7"
              >
                <li>
                  <ul
                    role="list"
                    class="-mx-2 space-y-1"
                  >
                    <li
                      v-for="item in navigation"
                      :key="item.name"
                    >
                      <router-link
                        :to="item.to"
                        :class="[
                          isActive(item.to)
                            ? theme === 'dark'
                              ? 'bg-white/5 text-white'
                              : 'bg-gray-50 text-indigo-600'
                            : theme === 'dark'
                              ? 'text-gray-400 hover:bg-white/5 hover:text-white'
                              : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600',
                          'group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold',
                        ]"
                      >
                        <component
                          :is="item.icon"
                          :class="[
                            isActive(item.to)
                              ? theme === 'dark'
                                ? 'text-white'
                                : 'text-indigo-600'
                              : theme === 'dark'
                                ? 'text-gray-400 group-hover:text-white'
                                : 'text-gray-400 group-hover:text-indigo-600',
                            'size-6 shrink-0',
                          ]"
                        />
                        {{ item.name }}
                      </router-link>
                    </li>
                    <li>
                      <button
                        :class="[
                          theme === 'dark'
                            ? 'text-gray-400 hover:bg-white/5 hover:text-white'
                            : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600',
                          'group flex w-full gap-x-3 rounded-md p-2 text-sm/6 font-semibold',
                        ]"
                        @click="handleLogout"
                      >
                        <component
                          :is="logoutItem.icon"
                          :class="[
                            theme === 'dark'
                              ? 'text-gray-400 group-hover:text-white'
                              : 'text-gray-400 group-hover:text-indigo-600',
                            'size-6 shrink-0',
                          ]"
                        />
                        {{ logoutItem.name }}
                      </button>
                    </li>
                  </ul>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Static sidebar for desktop -->
    <div :class="['hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col', theme === 'dark' ? 'bg-gray-900' : '']">
      <div :class="['flex grow flex-col gap-y-5 overflow-y-auto border-r px-6', theme === 'dark' ? 'border-white/10 bg-black/10' : 'border-gray-200 bg-white']">
        <div class="flex h-16 shrink-0 items-center">
          <img
            :src="
              theme === 'dark'
                ? 'https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500'
                : 'https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600'
            "
            alt="Timetjek"
            class="h-8 w-auto"
          >
        </div>
        <nav class="flex flex-1 flex-col">
          <ul
            role="list"
            class="flex flex-1 flex-col gap-y-7"
          >
            <li>
              <ul
                role="list"
                class="flex flex-1 flex-col gap-y-7"
              >
                <li>
                  <ul
                    role="list"
                    class="-mx-2 space-y-1"
                  >
                    <li
                      v-for="item in navigation"
                      :key="item.name"
                    >
                      <router-link
                        :to="item.to"
                        :class="[
                          isActive(item.to)
                            ? theme === 'dark'
                              ? 'bg-white/5 text-white'
                              : 'bg-gray-50 text-indigo-600'
                            : theme === 'dark'
                              ? 'text-gray-400 hover:bg-white/5 hover:text-white'
                              : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600',
                          'group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold',
                        ]"
                      >
                        <component
                          :is="item.icon"
                          :class="[
                            isActive(item.to)
                              ? theme === 'dark'
                                ? 'text-white'
                                : 'text-indigo-600'
                              : theme === 'dark'
                                ? 'text-gray-400 group-hover:text-white'
                                : 'text-gray-400 group-hover:text-indigo-600',
                            'size-6 shrink-0',
                          ]"
                        />
                        {{ item.name }}
                      </router-link>
                    </li>
                    <li>
                      <button
                        :class="[
                          theme === 'dark'
                            ? 'text-gray-400 hover:bg-white/5 hover:text-white'
                            : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600',
                          'group flex w-full gap-x-3 rounded-md p-2 text-sm/6 font-semibold',
                        ]"
                        @click="handleLogout"
                      >
                        <component
                          :is="logoutItem.icon"
                          :class="[
                            theme === 'dark'
                              ? 'text-gray-400 group-hover:text-white'
                              : 'text-gray-400 group-hover:text-indigo-600',
                            'size-6 shrink-0',
                          ]"
                        />
                        {{ logoutItem.name }}
                      </button>
                    </li>
                  </ul>
                </li>
              </ul>
            </li>
            <li class="-mx-6 mt-auto">
              <!-- Theme toggle button -->
              <button
                :class="[
                  'flex w-full items-center gap-x-4 px-6 py-3 text-sm/6 font-semibold',
                  theme === 'dark' ? 'text-gray-400 hover:bg-white/5 hover:text-white' : 'text-gray-700 hover:bg-gray-50',
                ]"
                @click="toggleTheme"
              >
                <!-- Sun icon (show in dark mode) -->
                <svg
                  v-if="theme === 'dark'"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                  class="size-6 shrink-0"
                >
                  <path
                    d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
                <!-- Moon icon (show in light mode) -->
                <svg
                  v-else
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                  class="size-6 shrink-0"
                >
                  <path
                    d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
                <span aria-hidden="true">{{ theme === 'dark' ? 'Light mode' : 'Dark mode' }}</span>
              </button>
              <!-- User profile -->
              <div
                :class="['flex w-full items-center gap-x-4 px-6 py-3 text-sm/6 font-semibold', theme === 'dark' ? 'text-white' : 'text-gray-900']"
              >
                <img
                  v-if="user?.avatar"
                  :src="user.avatar"
                  alt=""
                  :class="['size-8 rounded-full outline -outline-offset-1', theme === 'dark' ? 'bg-gray-800 outline-white/10' : 'bg-gray-50 outline-black/5']"
                >
                <div
                  v-else
                  class="size-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-medium text-sm outline -outline-offset-1 outline-black/5"
                >
                  {{ userInitials }}
                </div>
                <span aria-hidden="true">{{ user?.name }}</span>
              </div>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- Mobile header -->
    <div
      :class="[
        'sticky top-0 z-40 flex items-center gap-x-6 px-4 py-4 sm:px-6 lg:hidden',
        theme === 'dark' ? 'bg-gray-900 after:pointer-events-none after:absolute after:inset-0 after:border-b after:border-white/10 after:bg-black/10' : 'bg-white shadow-xs',
      ]"
    >
      <button
        type="button"
        :class="['-m-2.5 p-2.5', theme === 'dark' ? 'text-gray-400 hover:text-white' : 'text-gray-700 hover:text-gray-900']"
        @click="sidebarOpen = true"
      >
        <span class="sr-only">Open sidebar</span>
        <svg
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.5"
          aria-hidden="true"
          class="size-6"
        >
          <path
            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
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
        >
        <div
          v-else
          class="size-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-medium text-sm outline -outline-offset-1 outline-black/5"
        >
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
import { ref, computed, h } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useTheme } from '@/composables/useTheme';

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

const HomeIcon = () =>
    h(
        'svg',
        {
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            strokeWidth: '1.5',
        },
        [
            h('path', {
                d: 'm2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
            }),
        ]
    );

const ClockIcon = () =>
    h(
        'svg',
        {
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            strokeWidth: '1.5',
        },
        [
            h('path', {
                d: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
            }),
        ]
    );

const CogIcon = () =>
    h(
        'svg',
        {
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            strokeWidth: '1.5',
        },
        [
            h('path', {
                d: 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z',
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
            }),
            h('path', {
                d: 'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
            }),
        ]
    );

const ArrowRightStartOnRectangleIcon = () =>
    h(
        'svg',
        {
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            strokeWidth: '1.5',
        },
        [
            h('path', {
                d: 'M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15',
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
            }),
        ]
    );

const navigation = [
    { name: 'Dashboard', to: { name: 'dashboard' }, icon: HomeIcon },
    { name: 'Clockings', to: { name: 'clockings' }, icon: ClockIcon },
    { name: 'Settings', to: { name: 'settings' }, icon: CogIcon },
];

const logoutItem = { name: 'Logout', icon: ArrowRightStartOnRectangleIcon };

const currentPageTitle = computed(() => {
    const item = navigation.find((item) => isActive(item.to));
    return item?.name || 'Dashboard';
});

const isActive = (to: { name: string }) => {
    return route.name === to.name;
};

const handleLogout = async () => {
    await authStore.logout();
    router.push({ name: 'login' });
};
</script>
