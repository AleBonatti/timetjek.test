<template>
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <img
                src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500"
                alt="Timetjek"
                class="mx-auto h-10 w-auto"
            />
            <h2
                class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900 dark:text-white"
            >
                Sign in to your account
            </h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <BaseInput
                    id="personnummer"
                    v-model="form.personnummer"
                    label="Personnummer"
                    type="text"
                    placeholder="YYYYMMDD-XXXX"
                    required
                    autocomplete="username"
                    :error="errors.personnummer"
                />

                <div>
                    <div class="flex items-center justify-between">
                        <label
                            for="password"
                            class="block text-sm/6 font-medium text-gray-900 dark:text-white"
                        >
                            Password
                        </label>
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
                                    ? 'outline-red-300 text-red-900 placeholder:text-red-300 focus:outline-red-500 dark:outline-red-500 dark:text-red-400 dark:placeholder:text-red-500 dark:focus:outline-red-500'
                                    : 'bg-white text-gray-900 outline-gray-300 placeholder:text-gray-400 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500',
                            ]"
                        />
                    </div>
                    <p
                        v-if="errors.password"
                        class="mt-2 text-sm text-red-600 dark:text-red-400"
                    >
                        {{ errors.password }}
                    </p>
                </div>

                <div
                    v-if="errors.general"
                    class="rounded-md bg-red-50 p-4 dark:bg-red-900/20"
                >
                    <p class="text-sm text-red-800 dark:text-red-400">
                        {{ errors.general }}
                    </p>
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
import { useAuthStore } from '@/stores/auth';
import BaseInput from '@/components/BaseInput.vue';
import BaseButton from '@/components/BaseButton.vue';
import axios from '@/utils/axios';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
    personnummer: '',
    password: '',
});

const errors = ref<{
    personnummer?: string;
    password?: string;
    general?: string;
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
        errors.value.personnummer =
            'Please enter a valid personnummer (YYYYMMDD-XXXX)';
        return;
    }

    loading.value = true;

    try {
        // Get CSRF cookie
        await axios.get('/sanctum/csrf-cookie');

        // Attempt login
        const response = await axios.post('/api/login', {
            personnummer: form.value.personnummer,
            password: form.value.password,
        });

        // Set user in store
        authStore.setUser(response.data.user);

        // Redirect to dashboard
        router.push({ name: 'dashboard' });
    } catch (error: unknown) {
        if (axios.isAxiosError(error) && error.response) {
            if (error.response.status === 422) {
                // Validation errors
                const validationErrors = error.response.data.errors;
                if (validationErrors) {
                    errors.value = {
                        personnummer: validationErrors.personnummer?.[0],
                        password: validationErrors.password?.[0],
                    };
                }
            } else if (error.response.status === 401) {
                // Authentication failed
                errors.value.general =
                    'Invalid credentials. Please check your personnummer and password.';
            } else {
                errors.value.general =
                    'An error occurred. Please try again later.';
            }
        } else {
            errors.value.general = 'An unexpected error occurred.';
        }
    } finally {
        loading.value = false;
    }
};
</script>
