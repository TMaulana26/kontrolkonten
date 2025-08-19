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
import { computed } from 'vue';

// Define the types for the component's props and emits
type ConfirmIconType = 'info' | 'warning' | 'error';

const props = defineProps<{
    open: boolean;
    title: string;
    description: string;
    iconType: ConfirmIconType;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'confirm'): void;
    (e: 'cancel'): void;
}>();

// Logic to determine the icon and styling based on the iconType prop
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
                        <AlertDialogCancel @click="handleCancel" class="w-full sm:w-auto"> Cancel </AlertDialogCancel>
                        <AlertDialogAction @click="handleConfirm" class="w-full sm:w-auto"> Continue </AlertDialogAction>
                    </AlertDialogFooter>
                </div>
            </div>
        </AlertDialogContent>
    </AlertDialog>
</template>
