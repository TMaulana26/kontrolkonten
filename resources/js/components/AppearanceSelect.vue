<script setup lang="ts">
import { Select, SelectContent, SelectItem, SelectTrigger } from '@/components/ui/select';
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { appearance, updateAppearance } = useAppearance();

const options = computed(() => [
    { value: 'light', Icon: Sun, label: t('components.theme.light') },
    { value: 'dark', Icon: Moon, label: t('components.theme.dark') },
    { value: 'system', Icon: Monitor, label: t('components.theme.system') },
] as const);

const activeOption = computed(() => {
    return options.value.filter((option: { value: string; }) => option.value === appearance.value)[0] || options.value.find((option: { value: string; }) => option.value === t('components.theme.system'));
});

const selectedAppearance = computed({
    get: () => appearance.value,
    set: (value) => {
        if (value) {
            updateAppearance(value as 'light' | 'dark' | 'system');
        }
    },
});
</script>

<template>
    <div class="inline-flex">
        <Select v-model="selectedAppearance">
            <SelectTrigger class="h-10 w-16 border-indigo-400 text-indigo-600 dark:border-neutral-700 dark:text-amber-300">
                <component v-if="activeOption" :is="activeOption.Icon" class="h-5 w-5 text-indigo-600 dark:text-amber-300" />
            </SelectTrigger>
            <SelectContent class="min-w-[8rem]">
                <SelectItem v-for="{ value, Icon, label } in options" :key="value" :value="value">
                    <div class="flex items-center text-indigo-400 dark:text-amber-300">
                        <component :is="Icon" class="h-4 w-4 text-indigo-600 dark:text-amber-300" />
                        <span class="ml-2 text-sm">{{ label }}</span>
                    </div>
                </SelectItem>
            </SelectContent>
        </Select>
    </div>
</template>
