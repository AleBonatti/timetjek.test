<script setup lang="ts">
import { computed } from 'vue'

interface Props {
    type?: 'button' | 'submit' | 'reset'
    variant?: 'primary' | 'secondary' | 'danger' | 'success'
    loading?: boolean
    disabled?: boolean
    fullWidth?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    type: 'button',
    variant: 'primary',
    loading: false,
    disabled: false,
    fullWidth: false,
})

const isDisabled = computed(() => props.disabled || props.loading)

const buttonClasses = computed(() => {
    const base = 'flex justify-center rounded-md px-3 py-1.5 text-sm/6 font-semibold shadow-xs focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50 disabled:cursor-not-allowed'

    const widthClass = props.fullWidth ? 'w-full' : ''

    const variantClasses = {
        primary: 'bg-indigo-600 text-white hover:bg-indigo-500 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:shadow-none dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500',
        secondary:
            'bg-white text-gray-900 outline-1 -outline-offset-1 outline-gray-300 hover:bg-gray-50 focus-visible:outline-gray-900 dark:bg-white/10 dark:text-white dark:outline-white/20 dark:hover:bg-white/20 dark:focus-visible:outline-white',
        danger: 'bg-red-600 text-white hover:bg-red-500 focus-visible:outline-red-600 dark:bg-red-500 dark:shadow-none dark:hover:bg-red-400 dark:focus-visible:outline-red-500',
        success: 'bg-green-600 text-white hover:bg-green-500 focus-visible:outline-green-600 dark:bg-green-500 dark:shadow-none dark:hover:bg-green-400 dark:focus-visible:outline-green-500',
    }

    return [base, widthClass, variantClasses[props.variant]].filter(Boolean).join(' ')
})
</script>

<template>
    <button :type="type" :disabled="isDisabled" :class="buttonClasses">
        <svg v-if="loading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span v-else>
            <slot></slot>
        </span>
    </button>
</template>
