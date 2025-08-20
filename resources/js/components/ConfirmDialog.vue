<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { AlertTriangle, Info, ShieldX } from 'lucide-vue-next';
import { computed, onUnmounted, watch } from 'vue';

// Define the types for the component's props and emits
type ConfirmIconType = 'info' | 'warning' | 'error';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        description: string;
        iconType: ConfirmIconType;
        timer?: number | null;
        showCancel?: boolean;
        showConfirm?: boolean;
    }>(),
    {
        timer: null,
        showCancel: true,
        showConfirm: true,
    },
);

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'confirm'): void;
    (e: 'cancel'): void;
}>();

let timerId: number | null = null;

// Watch for the dialog opening to start the timer
watch(
    () => [props.open, props.timer],
    ([isOpen, timerValue]) => {
        if (timerId) {
            clearTimeout(timerId);
            timerId = null;
        }
        if (isOpen && timerValue) {
            timerId = setTimeout(() => {
                handleCancel(); // Close the dialog after the timer runs out
            }, Number(timerValue));
        }
    },
);

// Ensure the timer is cleared if the component is unmounted
onUnmounted(() => {
    if (timerId) {
        clearTimeout(timerId);
    }
});

const dialogIcon = computed(() => {
    switch (props.iconType) {
        case 'warning':
            return AlertTriangle;
        case 'error':
            return ShieldX;
        default:
            return Info;
    }
});

const dialogIconClass = computed(() => {
    switch (props.iconType) {
        case 'warning':
            return 'bg-amber-100 text-amber-600 dark:bg-amber-900/50';
        case 'error':
            return 'bg-red-100 text-red-600 dark:bg-red-900/50';
        default:
            return 'bg-blue-100 text-blue-600 dark:bg-blue-900/50';
    }
});

const handleConfirm = () => {
    emit('confirm');
    emit('update:open', false);
};

const handleCancel = () => {
    emit('cancel');
    emit('update:open', false);
};
</script>

<template>
    <AlertDialog :open="props.open" @update:open="$emit('update:open', $event)">
        <AlertDialogContent>
            <div class="flex flex-col items-center gap-4 text-center sm:flex-row sm:items-start sm:text-left">
                <div class="mb-4 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mb-0" :class="dialogIconClass">
                    <component :is="dialogIcon" class="h-6 w-6" />
                </div>

                <div class="flex-1">
                    <AlertDialogHeader>
                        <AlertDialogTitle>{{ props.title }}</AlertDialogTitle>
                        <AlertDialogDescription>
                            {{ props.description }}
                        </AlertDialogDescription>
                    </AlertDialogHeader>

                    <AlertDialogFooter class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <AlertDialogCancel v-if="props.showCancel" @click="handleCancel" class="w-full sm:w-auto"> Cancel </AlertDialogCancel>
                        <AlertDialogAction v-if="props.showConfirm" @click="handleConfirm" class="w-full sm:w-auto"> Continue </AlertDialogAction>
                    </AlertDialogFooter>
                </div>
            </div>
        </AlertDialogContent>
    </AlertDialog>
</template>
