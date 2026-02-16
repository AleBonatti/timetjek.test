<template>
    <div>
        <PageHeading title="Settings" description="Manage your account settings" />

        <!-- Profile Information Section -->
        <div class="mt-8">
            <div class="max-w-2xl">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Profile Information</h3>

                <!-- Success Message -->
                <div v-if="profileSuccessMessage" class="mb-6 rounded-md bg-green-50 p-4 dark:bg-green-900/20">
                    <div class="flex">
                        <div class="shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-800 dark:text-green-200">
                                {{ profileSuccessMessage }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Error Messages -->
                <div v-if="profileErrors.apiErrors.length > 0" class="mb-6 rounded-md bg-red-50 p-4 dark:bg-red-900/20">
                    <div class="flex">
                        <div class="shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm text-red-800 dark:text-red-200">
                                <ul class="space-y-1">
                                    <li v-for="(error, index) in profileErrors.apiErrors" :key="index">
                                        {{ error }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <form class="bg-white outline -outline-offset-1 outline-black/5 dark:bg-gray-800/50 dark:outline-white/10 sm:rounded-xl" @submit.prevent="handleProfileSubmit">
                    <div class="px-4 py-6 sm:p-8 space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-900 dark:text-white"> Name </label>
                            <div class="mt-2">
                                <input
                                    id="name"
                                    v-model="profileForm.name"
                                    type="text"
                                    autocomplete="name"
                                    required
                                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-primary-500 sm:text-sm/6"
                                />
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Must be at least 3 characters.</p>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-900 dark:text-white"> Email </label>
                            <div class="mt-2">
                                <input
                                    id="email"
                                    v-model="profileForm.email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-primary-500 sm:text-sm/6"
                                />
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Must be at least 3 characters.</p>
                        </div>

                        <div class="flex gap-4">
                            <BaseButton type="submit" variant="primary" :loading="isSubmittingProfile" :disabled="isSubmittingProfile"> Update Profile </BaseButton>
                            <BaseButton type="button" variant="secondary" :disabled="isSubmittingProfile" @click="resetProfileForm"> Cancel </BaseButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password Section -->
        <div class="mt-12">
            <div class="max-w-2xl">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Update Password</h3>

                <!-- Success Message -->
                <div v-if="successMessage" class="mb-6 rounded-md bg-green-50 p-4 dark:bg-green-900/20">
                    <div class="flex">
                        <div class="shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-800 dark:text-green-200">
                                {{ successMessage }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Error Messages -->
                <div v-if="errors.apiErrors.length > 0" class="mb-6 rounded-md bg-red-50 p-4 dark:bg-red-900/20">
                    <div class="flex">
                        <div class="shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm text-red-800 dark:text-red-200">
                                <ul class="space-y-1">
                                    <li v-for="(error, index) in errors.apiErrors" :key="index">
                                        {{ error }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <form class="bg-white outline -outline-offset-1 outline-black/5 dark:bg-gray-800/50 dark:outline-white/10 sm:rounded-xl" @submit.prevent="handleSubmit">
                    <div class="px-4 py-6 sm:p-8 space-y-6">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-900 dark:text-white"> Current Password </label>
                            <div class="mt-2">
                                <input
                                    id="current_password"
                                    v-model="form.current_password"
                                    type="password"
                                    autocomplete="current-password"
                                    required
                                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-primary-500 sm:text-sm/6"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-900 dark:text-white"> New Password </label>
                            <div class="mt-2">
                                <input
                                    id="new_password"
                                    v-model="form.new_password"
                                    type="password"
                                    autocomplete="new-password"
                                    required
                                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-primary-500 sm:text-sm/6"
                                />
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Must be at least 8 characters.</p>
                        </div>

                        <div>
                            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-900 dark:text-white"> Confirm New Password </label>
                            <div class="mt-2">
                                <input
                                    id="new_password_confirmation"
                                    v-model="form.new_password_confirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    required
                                    class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-primary-500 sm:text-sm/6"
                                />
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <BaseButton type="submit" variant="primary" :loading="isSubmitting" :disabled="isSubmitting"> Update Password </BaseButton>
                            <BaseButton type="button" variant="secondary" :disabled="isSubmitting" @click="resetForm"> Cancel </BaseButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import PageHeading from '@/components/PageHeading.vue';
import BaseButton from '@/components/BaseButton.vue';
import axios from '@/utils/axios';
import { isAxiosError } from 'axios';

const authStore = useAuthStore();

// Profile form
const profileForm = ref({
    name: '',
    email: '',
});

const profileErrors = ref({
    apiErrors: [] as string[],
});

const profileSuccessMessage = ref<string | null>(null);
const isSubmittingProfile = ref(false);

// Password form
const form = ref({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const errors = ref({
    apiErrors: [] as string[],
});

const successMessage = ref<string | null>(null);
const isSubmitting = ref(false);

const resetProfileForm = () => {
    if (authStore.user) {
        profileForm.value = {
            name: authStore.user.name,
            email: authStore.user.email,
        };
    }
    profileErrors.value.apiErrors = [];
    profileSuccessMessage.value = null;
};

const resetForm = () => {
    form.value = {
        current_password: '',
        new_password: '',
        new_password_confirmation: '',
    };
    errors.value.apiErrors = [];
    successMessage.value = null;
};

const handleProfileSubmit = async () => {
    profileErrors.value.apiErrors = [];
    profileSuccessMessage.value = null;
    isSubmittingProfile.value = true;
    const startTime = Date.now();

    let hadError = false;
    let errorMessages: string[] = [];
    let successMsg: string | null = null;

    try {
        const response = await axios.put('/api/user/profile', profileForm.value);
        successMsg = response.data.message;

        // Update the user in auth store
        await authStore.fetchUser();
    } catch (error) {
        hadError = true;
        if (isAxiosError(error) && error.response) {
            const responseData = error.response.data;

            if (error.response.status === 422) {
                // Validation errors
                const validationErrors = responseData?.errors as Record<string, string[]> | undefined;
                if (validationErrors) {
                    Object.keys(validationErrors).forEach((key) => {
                        validationErrors[key].forEach((msg: string) => {
                            errorMessages.push(msg);
                        });
                    });
                }
            } else {
                // Other errors
                errorMessages.push(responseData?.message || 'An error occurred while updating your profile.');
            }
        } else {
            errorMessages.push('An unexpected error occurred. Please try again.');
        }
    } finally {
        // Ensure loading state is visible for at least 500ms
        const elapsedTime = Date.now() - startTime;
        const remainingTime = Math.max(0, 500 - elapsedTime);

        setTimeout(() => {
            isSubmittingProfile.value = false;

            // Set messages after timeout
            if (hadError) {
                profileErrors.value.apiErrors = errorMessages;
            } else {
                profileSuccessMessage.value = successMsg;
            }
        }, remainingTime);
    }
};

const handleSubmit = async () => {
    errors.value.apiErrors = [];
    successMessage.value = null;
    isSubmitting.value = true;
    const startTime = Date.now();

    let hadError = false;
    let errorMessages: string[] = [];
    let successMsg: string | null = null;

    try {
        const response = await axios.put('/api/user/password', form.value);
        successMsg = response.data.message;

        // Reset form after successful update
        form.value = {
            current_password: '',
            new_password: '',
            new_password_confirmation: '',
        };
    } catch (error) {
        hadError = true;
        if (isAxiosError(error) && error.response) {
            const responseData = error.response.data;

            if (error.response.status === 422) {
                // Validation errors
                const validationErrors = responseData?.errors as Record<string, string[]> | undefined;
                if (validationErrors) {
                    Object.keys(validationErrors).forEach((key) => {
                        validationErrors[key].forEach((msg: string) => {
                            errorMessages.push(msg);
                        });
                    });
                }
            } else {
                // Other errors
                errorMessages.push(responseData?.message || 'An error occurred while updating your password.');
            }
        } else {
            errorMessages.push('An unexpected error occurred. Please try again.');
        }
    } finally {
        // Ensure loading state is visible for at least 500ms
        const elapsedTime = Date.now() - startTime;
        const remainingTime = Math.max(0, 500 - elapsedTime);

        setTimeout(() => {
            isSubmitting.value = false;

            // Set messages after timeout
            if (hadError) {
                errors.value.apiErrors = errorMessages;
            } else {
                successMessage.value = successMsg;
            }
        }, remainingTime);
    }
};

onMounted(() => {
    // Initialize profile form with current user data
    if (authStore.user) {
        profileForm.value = {
            name: authStore.user.name,
            email: authStore.user.email,
        };
    }
});
</script>
