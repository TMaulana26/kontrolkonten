<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Toaster } from '@/components/ui/sonner';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { provide, reactive } from 'vue';
import 'vue-sonner/style.css';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

const infoDialogState = reactive({
    open: false,
    title: '',
    description: '',
    iconType: 'info' as 'info' | 'warning' | 'error',
    showCancel: false,
    showConfirm: true,
});

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

function showInfoDialog(title: string, description: string) {
    infoDialogState.title = title;
    infoDialogState.description = description;
    infoDialogState.iconType = 'info';
    infoDialogState.showCancel = false;
    infoDialogState.showConfirm = true;
    infoDialogState.open = true;
}

provide('showInfoDialog', showInfoDialog);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
    <Toaster rich-colors position="top-center" />
    <ConfirmDialog
        :open="infoDialogState.open"
        :title="infoDialogState.title"
        :description="infoDialogState.description"
        :icon-type="infoDialogState.iconType"
        :show-cancel="infoDialogState.showCancel"
        :show-confirm="infoDialogState.showConfirm"
        @update:open="infoDialogState.open = $event"
    />
</template>
