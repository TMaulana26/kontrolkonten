<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { appearance, updateAppearance } = useAppearance();

const tabs = computed(() => [
    { value: 'light', Icon: Sun, label: t('components.theme.light') },
    { value: 'dark', Icon: Moon, label: t('components.theme.dark') },
    { value: 'system', Icon: Monitor, label: t('components.theme.system') },
] as const);
</script>

<template>
    <div class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                appearance === value
                    ? 'bg-indigo-500 text-neutral-200 shadow-xs dark:bg-neutral-700 dark:text-amber-300'
                    : 'text-indigo-300 hover:bg-neutral-200/60 hover:text-indigo-800 dark:text-amber-400 dark:hover:bg-neutral-700/60',
            ]"
        >
            <component :is="Icon" class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div>
</template>
