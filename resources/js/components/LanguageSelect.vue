<script setup lang="ts">
import { Select, SelectContent, SelectItem, SelectTrigger } from '@/components/ui/select';
import { useLanguage } from '@/composables/useLanguage';
import { computed, h, type FunctionalComponent, type HTMLAttributes, type VNodeProps } from 'vue';

const { language, updateLanguage } = useLanguage();

const EnIcon: FunctionalComponent<HTMLAttributes & VNodeProps> = (props) =>
    h('svg', { ...props, xmlns: 'http://www.w3.org/2000/svg', width: '1em', height: '1em', viewBox: '0 0 24 24' }, [
        h(
            'text',
            {
                x: '50%',
                y: '50%',
                'dominant-baseline': 'central',
                'text-anchor': 'middle',
                'font-size': '14',
                'font-weight': 'bold',
                fill: 'currentColor',
            },
            'EN',
        ),
    ]);

const IdIcon: FunctionalComponent<HTMLAttributes & VNodeProps> = (props) =>
    h('svg', { ...props, xmlns: 'http://www.w3.org/2000/svg', width: '1em', height: '1em', viewBox: '0 0 24 24' }, [
        h(
            'text',
            {
                x: '50%',
                y: '50%',
                'dominant-baseline': 'central',
                'text-anchor': 'middle',
                'font-size': '14',
                'font-weight': 'bold',
                fill: 'currentColor',
            },
            'ID',
        ),
    ]);

const options = [
    { value: 'en', Icon: EnIcon, label: 'English' },
    { value: 'id', Icon: IdIcon, label: 'Indonesia' },
] as const;

const activeOption = computed(() => {
    return options.find((option) => option.value === language.value) || options[0];
});

const selectedLanguage = computed({
    get: () => language.value,
    set: (value) => {
        if (value) {
            updateLanguage(value as 'en' | 'id');
        }
    },
});
</script>

<template>
    <div class="inline-flex">
        <Select v-model="selectedLanguage">
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
