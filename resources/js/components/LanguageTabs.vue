<script setup lang="ts">
import { useLanguage } from '@/composables/useLanguage';
import { h, type FunctionalComponent, type HTMLAttributes, type VNodeProps } from 'vue';

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

const tabs = [
    { value: 'en', Icon: EnIcon, label: 'English' },
    { value: 'id', Icon: IdIcon, label: 'Indonesia' },
] as const;
</script>

<template>
    <div class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateLanguage(value)"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                language === value
                    ? 'bg-indigo-500 text-neutral-200 shadow-xs dark:bg-neutral-700 dark:text-amber-300'
                    : 'text-indigo-300 hover:bg-neutral-200/60 hover:text-indigo-800 dark:text-amber-400 dark:hover:bg-neutral-700/60',
            ]"
        >
            <component :is="Icon" class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div>
</template>
