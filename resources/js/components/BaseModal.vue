<script setup lang="ts">
interface Props {
    open: boolean;
    title: string;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl';
}

const props = withDefaults(defineProps<Props>(), {
    maxWidth: 'lg',
});

const emit = defineEmits<{
    close: [];
}>();

const maxWidthClasses = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
};
</script>

<template>
  <Transition
    name="modal"
    appear
  >
    <div
      v-if="open"
      class="relative z-50"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <!-- Backdrop -->
      <Transition
        name="backdrop"
        appear
      >
        <div
          v-if="open"
          class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75"
          @click="emit('close')"
        />
      </Transition>

      <!-- Modal -->
      <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <Transition
            name="modal-content"
            appear
          >
            <div
              v-if="open"
              :class="['relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl sm:my-8 sm:w-full sm:p-6 dark:bg-gray-800', maxWidthClasses[maxWidth]]"
              @click.stop
            >
              <div>
                <div>
                  <h3
                    id="modal-title"
                    class="text-base font-semibold text-gray-900 dark:text-white text-center sm:text-left"
                  >
                    {{ title }}
                  </h3>
                  <div class="mt-6">
                    <slot />
                  </div>
                </div>
              </div>
              <div
                v-if="$slots.actions"
                class="mt-5 sm:mt-6"
              >
                <slot name="actions" />
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
/* Backdrop transitions */
.backdrop-enter-active,
.backdrop-leave-active {
    transition: opacity 300ms ease;
}

.backdrop-enter-from,
.backdrop-leave-to {
    opacity: 0;
}

/* Modal content transitions */
.modal-content-enter-active {
    transition: all 300ms ease-out;
}

.modal-content-leave-active {
    transition: all 200ms ease-in;
}

.modal-content-enter-from {
    opacity: 0;
    transform: translate(0, 1rem) scale(0.95);
}

.modal-content-leave-to {
    opacity: 0;
    transform: translate(0, 0.5rem) scale(0.95);
}

/* Main modal wrapper transition */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 300ms ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
