<template>
  <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8 bg-white dark:bg-gray-900">
    <!-- Theme Toggle Button -->
    <button
      type="button"
      class="fixed top-4 right-4 rounded-md p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-gray-500 dark:hover:text-gray-400"
      aria-label="Toggle theme"
      @click="toggleTheme"
    >
      <!-- Sun icon (show in dark mode) -->
      <svg
        v-if="theme === 'dark'"
        class="h-6 w-6"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"
        />
      </svg>
      <!-- Moon icon (show in light mode) -->
      <svg
        v-else
        class="h-6 w-6"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"
        />
      </svg>
    </button>

    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
      <img
        src="/images/blue.svg"
        alt="Timecheck"
        class="mx-auto h-10 w-auto dark:hidden"
      >
      <img
        src="/images/logga-vit.svg"
        alt="Timecheck"
        class="mx-auto h-10 w-auto hidden dark:block"
      >
      <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900 dark:text-white">
        Sign in to your account
      </h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
      <form
        class="space-y-6"
        @submit.prevent="handleSubmit"
      >
        <div>
          <label
            for="personnummer"
            class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100"
          > Personnummer </label>
          <div class="mt-2">
            <input
              id="personnummer"
              v-model="form.personnummer"
              type="text"
              name="personnummer"
              required
              autocomplete="username"
              placeholder="YYYYMMDD-XXXX"
              :class="[
                'block w-full rounded-md px-3 py-1.5 text-base outline-1 -outline-offset-1 focus:outline-2 focus:-outline-offset-2 sm:text-sm/6',
                errors.personnummer
                  ? 'outline-red-300 text-red-900 placeholder:text-red-300 focus:outline-red-500'
                  : 'bg-white text-gray-900 outline-gray-300 placeholder:text-gray-400 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500',
              ]"
            >
          </div>
          <p
            v-if="errors.personnummer"
            class="mt-2 text-sm text-red-600 dark:text-red-400"
          >
            {{ errors.personnummer }}
          </p>
        </div>

        <div>
          <div class="flex items-center justify-between">
            <label
              for="password"
              class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100"
            > Password </label>
          </div>
          <div class="mt-2">
            <input
              id="password"
              v-model="form.password"
              type="password"
              name="password"
              required
              autocomplete="current-password"
              :class="[
                'block w-full rounded-md px-3 py-1.5 text-base outline-1 -outline-offset-1 focus:outline-2 focus:-outline-offset-2 sm:text-sm/6',
                errors.password
                  ? 'outline-red-300 text-red-900 placeholder:text-red-300 focus:outline-red-500'
                  : 'bg-white text-gray-900 outline-gray-300 placeholder:text-gray-400 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500',
              ]"
            >
          </div>
          <p
            v-if="errors.password"
            class="mt-2 text-sm text-red-600 dark:text-red-400"
          >
            {{ errors.password }}
          </p>
        </div>

        <div
          v-if="errors.general || (errors.apiErrors && errors.apiErrors.length > 0)"
          class="rounded-md bg-red-50 p-4 dark:bg-red-900/20"
        >
          <p
            v-if="errors.general"
            class="text-sm text-red-800 dark:text-red-400"
          >
            {{ errors.general }}
          </p>
          <div
            v-if="errors.apiErrors && errors.apiErrors.length > 0"
            class="space-y-1"
            :class="{ 'mt-2': errors.general }"
          >
            <p
              v-for="(error, index) in errors.apiErrors"
              :key="index"
              class="text-sm text-red-800 dark:text-red-400"
            >
              {{ error }}
            </p>
          </div>
        </div>

        <BaseButton
          type="submit"
          :loading="loading"
          :disabled="loading"
          full-width
        >
          Sign in
        </BaseButton>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { isAxiosError, type AxiosError } from 'axios';
import { useAuthStore } from '@/stores/auth';
import { useTheme } from '@/composables/useTheme';
import BaseButton from '@/components/BaseButton.vue';
import axios from '@/utils/axios';

const router = useRouter();
const authStore = useAuthStore();
const { theme, toggleTheme } = useTheme();

const form = ref({
    personnummer: '',
    password: '',
});

const errors = ref<{
    personnummer?: string;
    password?: string;
    general?: string;
    apiErrors?: string[];
}>({});

const loading = ref(false);

const validatePersonnummer = (personnummer: string): boolean => {
    // Remove any spaces or dashes
    const cleaned = personnummer.replace(/[\s-]/g, '');

    // Check if it's 10 or 12 digits
    if (cleaned.length !== 10 && cleaned.length !== 12) {
        return false;
    }

    // Basic format validation
    return /^\d{10}(\d{2})?$/.test(cleaned);
};

const handleSubmit = async () => {
    // Clear previous errors
    errors.value = {};

    // Validate personnummer
    if (!validatePersonnummer(form.value.personnummer)) {
        errors.value.personnummer = 'Please enter a valid personnummer (YYYYMMDD-XXXX)';
        return;
    }

    loading.value = true;

    try {
        // Get CSRF cookie
        await axios.get('/sanctum/csrf-cookie');

        // Simulate network latency
        await new Promise((resolve) => setTimeout(resolve, 500));

        // Attempt login
        const response = await axios.post('/api/login', {
            personnummer: form.value.personnummer,
            password: form.value.password,
        });

        // Set user in store
        authStore.setUser(response.data.user);

        // Redirect to dashboard
        router.push({ name: 'dashboard' });
    } catch (err: unknown) {
        if (isAxiosError(err)) {
            const error = err as AxiosError<Record<string, unknown>>;
            const apiErrors: string[] = [];
            const responseData = error.response?.data;

            if (error.response?.status === 422) {
                // Validation errors
                const validationErrors = responseData?.errors as Record<string, string[]> | undefined;

                if (validationErrors) {
                    // Collect all validation error messages
                    Object.keys(validationErrors).forEach((key) => {
                        validationErrors[key].forEach((msg: string) => {
                            apiErrors.push(msg);
                        });
                    });
                }
            } else if (error.response?.status === 401) {
                // Authentication failed
                apiErrors.push('Invalid credentials. Please check your personnummer and password.');
            } else {
                apiErrors.push('An error occurred. Please try again later.');
            }

            // Add any general error message from the response if not already added
            if (apiErrors.length === 0) {
                if (responseData?.message && typeof responseData.message === 'string') {
                    apiErrors.push(responseData.message);
                } else if (responseData?.error && typeof responseData.error === 'string') {
                    apiErrors.push(responseData.error);
                }
            }

            // Store all API errors
            if (apiErrors.length > 0) {
                errors.value.apiErrors = apiErrors;
            }
        } else {
            errors.value.apiErrors = ['An unexpected error occurred.'];
        }
    } finally {
        loading.value = false;
    }
};
</script>
