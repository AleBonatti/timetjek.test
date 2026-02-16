<script setup lang="ts">
interface Props {
    id: string;
    label: string;
    modelValue: string;
    placeholder?: string;
    required?: boolean;
    rows?: number;
    error?: string;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: '',
    required: false,
    rows: 3,
    error: '',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const handleInput = (event: Event) => {
    const target = event.target as HTMLTextAreaElement;
    emit('update:modelValue', target.value);
};
</script>

<template>
  <div>
    <label
      :for="id"
      class="block text-sm/6 font-medium text-gray-900 dark:text-white"
    >
      {{ label }}
    </label>
    <div class="mt-2">
      <textarea
        :id="id"
        :name="id"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :rows="rows"
        :class="[
          'block w-full rounded-md px-3 py-1.5 text-base outline-1 -outline-offset-1 focus:outline-2 focus:-outline-offset-2 sm:text-sm/6',
          error
            ? 'outline-red-300 text-red-900 placeholder:text-red-300 focus:outline-red-500 dark:outline-red-500 dark:text-red-400 dark:placeholder:text-red-500 dark:focus:outline-red-500'
            : 'bg-white text-gray-900 outline-gray-300 placeholder:text-gray-400 focus:outline-indigo-600 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500',
        ]"
        @input="handleInput"
      />
    </div>
    <p
      v-if="error"
      class="mt-2 text-sm text-red-600 dark:text-red-400"
    >
      {{ error }}
    </p>
  </div>
</template>
