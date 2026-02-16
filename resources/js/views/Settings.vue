<template>
  <div>
    <PageHeading
      title="Settings"
      description="Manage your account settings"
    />

    <div class="mt-8">
      <div class="max-w-2xl">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
          Update Password
        </h3>

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
                <ul class="list-disc pl-5 space-y-1">
                  <li v-for="(error, index) in errors.apiErrors" :key="index">
                    {{ error }}
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div>
            <label for="current_password" class="block text-sm font-medium text-gray-900 dark:text-white">
              Current Password
            </label>
            <div class="mt-2">
              <input
                id="current_password"
                v-model="form.current_password"
                type="password"
                autocomplete="current-password"
                required
                class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500 sm:text-sm/6"
              >
            </div>
          </div>

          <div>
            <label for="new_password" class="block text-sm font-medium text-gray-900 dark:text-white">
              New Password
            </label>
            <div class="mt-2">
              <input
                id="new_password"
                v-model="form.new_password"
                type="password"
                autocomplete="new-password"
                required
                class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500 sm:text-sm/6"
              >
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Must be at least 8 characters.
            </p>
          </div>

          <div>
            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-900 dark:text-white">
              Confirm New Password
            </label>
            <div class="mt-2">
              <input
                id="new_password_confirmation"
                v-model="form.new_password_confirmation"
                type="password"
                autocomplete="new-password"
                required
                class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500 sm:text-sm/6"
              >
            </div>
          </div>

          <div class="flex gap-4">
            <BaseButton
              type="submit"
              variant="primary"
              :loading="isSubmitting"
              :disabled="isSubmitting"
            >
              Update Password
            </BaseButton>
            <BaseButton
              type="button"
              variant="secondary"
              :disabled="isSubmitting"
              @click="resetForm"
            >
              Cancel
            </BaseButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import PageHeading from '@/components/PageHeading.vue';
import BaseButton from '@/components/BaseButton.vue';
import axios from '@/utils/axios';
import { isAxiosError } from 'axios';

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

const resetForm = () => {
  form.value = {
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
  };
  errors.value.apiErrors = [];
  successMessage.value = null;
};

const handleSubmit = async () => {
  errors.value.apiErrors = [];
  successMessage.value = null;
  isSubmitting.value = true;

  try {
    const response = await axios.put('/api/user/password', form.value);
    successMessage.value = response.data.message;

    // Reset form after successful update
    form.value = {
      current_password: '',
      new_password: '',
      new_password_confirmation: '',
    };
  } catch (error) {
    if (isAxiosError(error) && error.response) {
      const responseData = error.response.data;

      if (error.response.status === 422) {
        // Validation errors
        const validationErrors = responseData?.errors as Record<string, string[]> | undefined;
        if (validationErrors) {
          Object.keys(validationErrors).forEach((key) => {
            validationErrors[key].forEach((msg: string) => {
              errors.value.apiErrors.push(msg);
            });
          });
        }
      } else {
        // Other errors
        errors.value.apiErrors.push(
          responseData?.message || 'An error occurred while updating your password.'
        );
      }
    } else {
      errors.value.apiErrors.push('An unexpected error occurred. Please try again.');
    }
  } finally {
    isSubmitting.value = false;
  }
};
</script>
