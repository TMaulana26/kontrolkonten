<script setup lang="ts" generic="T">
import { Button } from '@/components/ui/button';
import type { Column } from '@/types';
import { ArrowUpDown } from 'lucide-vue-next';

defineProps<{
    column: Column<T>;
}>();

defineEmits<{
    (e: 'sort', field: string): void;
}>();
</script>

<template>
    <div v-if="column.sortable">
        <Button variant="ghost" @click="$emit('sort', String(column.key))">
            {{ column.label }}
            <ArrowUpDown class="ml-2 h-4 w-4" />
        </Button>
    </div>
    <span v-else>{{ column.label }}</span>
</template>
