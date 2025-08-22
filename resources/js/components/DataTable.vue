<script setup lang="ts" generic="T extends Record<string, any>">
import DataTableColumnHeader from '@/components/DataTableColumnHeader.vue';
import { buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { type Column, type Paginator } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import throttle from 'lodash/throttle';
import { ref, watch } from 'vue';

const props = withDefaults(defineProps<{
    data: Paginator<T>;
    columns: Column<T>[];
    filters?: {
        search?: string;
        sort?: string;
        direction?: string;
        per_page?: string;
    };
}>(), {
    filters: () => ({
        search: '',
        sort: '',
        direction: '',
        per_page: '10',
    }),
});

const search = ref(props.filters.search);
const perPage = ref(props.filters.per_page || '10');
const openDetailsId = ref<number | null>(null);

const sort = (field: string) => {
    let direction = 'asc';
    if (props.filters.sort === field && props.filters.direction === 'asc') {
        direction = 'desc';
    }
    router.get(
        window.location.pathname,
        { ...props.filters, search: search.value, per_page: perPage.value, sort: field, direction },
        { preserveState: true },
    );
};

const toggleDetails = (itemId: number) => {
    openDetailsId.value = openDetailsId.value === itemId ? null : itemId;
};

watch(
    [search, perPage],
    throttle(() => {
        router.get(
            window.location.pathname,
            {
                ...props.filters,
                search: search.value,
                per_page: perPage.value,
            },
            { preserveState: true, replace: true },
        );
    }, 300),
);
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-col items-center gap-4 sm:flex-row">
            <Input v-model="search" :placeholder="$t('components.datatable.search')" class="w-full max-w-sm" />
            <slot name="filters" />
        </div>
        <div class="rounded-xl border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead v-for="column in columns" :key="String(column.key)">
                            <DataTableColumnHeader :column="column" @sort="sort" />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="data.data.length > 0">
                        <template v-for="item in data.data" :key="item.id">
                            <TableRow>
                                <TableCell v-for="column in columns" :key="`cell-${String(column.key)}`">
                                    <!-- Default cell slot -->
                                    <slot :name="`cell-${String(column.key)}`" :item="item" :toggleDetails="toggleDetails">
                                        {{ item[column.key as keyof T] }}
                                    </slot>
                                </TableCell>
                            </TableRow>
                            <tr v-if="openDetailsId === item.id">
                                <td :colspan="columns.length" class="p-0">
                                    <slot name="details-row" :item="item" />
                                </td>
                            </tr>
                        </template>
                    </template>
                    <TableRow v-else>
                        <TableCell :colspan="columns.length" class="h-24 text-center"> {{ $t('components.datatable.empty') }} </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <div class="text-sm text-muted-foreground">
                {{ $t('components.datatable.show', { from: data.from, to: data.to, total: data.total }) }}
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center space-x-2">
                    <p class="text-sm font-medium">{{ $t('components.datatable.per_page') }}</p>
                    <Select v-model="perPage">
                        <SelectTrigger class="h-8 w-[70px]">
                            <SelectValue :placeholder="perPage" />
                        </SelectTrigger>
                        <SelectContent side="top">
                            <SelectItem v-for="val in [10, 20, 30, 40, 50]" :key="val" :value="String(val)">{{ val }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="flex items-center space-x-2">
                    <Link
                        v-for="link in data.links"
                        :key="link.label"
                        :href="link.url || ''"
                        :class="[
                            buttonVariants({
                                variant: link.active ? 'default' : 'outline',
                                size: 'sm',
                            }),
                            !link.url && 'cursor-not-allowed opacity-50',
                        ]"
                        v-html="link.label"
                        preserve-scroll
                    />
                </div>
            </div>
        </div>
    </div>
</template>
