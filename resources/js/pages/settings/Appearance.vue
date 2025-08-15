<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import LanguageTabs from '@/components/LanguageTabs.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: '/settings/appearance',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="$t('setting.appearance.title')" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall :title="$t('setting.appearance.title')" :description="$t('setting.appearance.desc')" />
                <AppearanceTabs />
                <LanguageTabs class="ml-3" />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
