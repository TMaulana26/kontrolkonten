<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { RangeCalendar } from '@/components/ui/range-calendar';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import { type Activity, type BreadcrumbItem, type Column, type Paginator } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { DateFormatter, getLocalTimeZone, parseDate, type CalendarDate } from '@internationalized/date';
import { format } from 'date-fns';
import throttle from 'lodash/throttle';
import { Calendar as CalendarIcon, ChevronsUpDown, XIcon } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    logs: Paginator<Activity>;
    filters: {
        search: string;
        sort: string;
        direction: string;
        per_page: string;
        start_date: string | null;
        end_date: string | null;
    };
}>();

const { t } = useI18n();

const columns: Column<Activity>[] = [
    { key: 'event', label: t('pages.activity_log.table.event') },
    { key: 'user', label: t('pages.activity_log.table.user') },
    { key: 'created_at', label: t('pages.activity_log.table.created_at'), sortable: true },
    { key: 'details', label: t('pages.activity_log.table.details') },
];

const dateRange = ref({
    start: props.filters.start_date ? parseDate(props.filters.start_date) : null,
    end: props.filters.end_date ? parseDate(props.filters.end_date) : null,
});

const df = new DateFormatter('en-US', { dateStyle: 'medium' });

const dateRangeDisplay = computed(() => {
    if (!dateRange.value?.start) return t('pages.activity_log.date_range');
    if (dateRange.value.start && !dateRange.value.end) {
        return df.format(dateRange.value.start.toDate(getLocalTimeZone()));
    }
    if (dateRange.value.start && dateRange.value.end) {
        return `${df.format(dateRange.value.start.toDate(getLocalTimeZone()))} - ${df.format(dateRange.value.end.toDate(getLocalTimeZone()))}`;
    }
    return t('pages.activity_log.date_range');
});

const formatToUrlDate = (date: CalendarDate | null): string | undefined => {
    if (!date) return undefined;
    const month = String(date.month).padStart(2, '0');
    const day = String(date.day).padStart(2, '0');
    return `${date.year}-${month}-${day}`;
};

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('pages.activity_log.title'),
        href: route('activity-log.index'),
    },
]);

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'N/A';
    try {
        return format(new Date(dateString), 'PPpp');
    } catch (error) {
        return 'Invalid Date';
    }
};

const getModelName = (logName: string | null) => {
    if (!logName) return 'Unknown';
    return logName.charAt(0).toUpperCase() + logName.slice(1);
};

const formatPropertyValue = (value: any) => {
    if (typeof value === 'object' && value !== null) {
        if (value.en && value.id) {
            return `EN: "${value.en}" | ID: "${value.id}"`;
        }
        return JSON.stringify(value);
    }
    return value;
};

const clearDateFilter = () => {
    dateRange.value = { start: null, end: null };
};

watch(
    dateRange,
    throttle(() => {
        router.get(
            route('activity-log.index'),
            {
                ...props.filters,
                start_date: formatToUrlDate(dateRange.value.start as CalendarDate),
                end_date: formatToUrlDate(dateRange.value.end as CalendarDate),
            },
            { preserveState: true, replace: true },
        );
    }, 300),
    { deep: true },
);
</script>

<template>
    <Head title="Activity Log" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-4">
            <div>
                <h1 class="text-2xl font-semibold">{{ t('pages.activity_log.title') }}</h1>
                <p class="text-muted-foreground">{{ t('pages.activity_log.desc') }}</p>
            </div>

            <DataTable :data="logs" :columns="columns" :filters="filters">
                <template #filters>
                    <div class="flex w-full items-center gap-2 sm:w-auto">
                        <Popover>
                            <PopoverTrigger as-child>
                                <Button
                                    variant="outline"
                                    :class="
                                        cn('w-full justify-start text-left font-normal sm:w-[280px]', !dateRange.start && 'text-muted-foreground')
                                    "
                                >
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    <span>{{ dateRangeDisplay }}</span>
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0">
                                <RangeCalendar v-model="dateRange as any" />
                            </PopoverContent>
                        </Popover>
                        <Button v-if="dateRange.start" @click="clearDateFilter" variant="ghost" size="icon">
                            <XIcon class="h-4 w-4" />
                        </Button>
                    </div>
                </template>

                <template #cell-event="{ item }">
                    <div class="flex items-center gap-2">
                        <Badge variant="outline">{{ getModelName(item.log_name) }}</Badge>
                        <span>{{ item.description }}</span>
                    </div>
                </template>

                <template #cell-user="{ item }">
                    {{ item.causer?.name || 'System' }}
                </template>

                <template #cell-created_at="{ item }">
                    {{ formatDate(item.created_at) }}
                </template>

                <template #cell-details="{ item, toggleDetails }">
                    <div class="text-right">
                        <Button
                            v-if="item.properties && (item.properties.attributes || item.properties.old)"
                            variant="ghost"
                            size="sm"
                            @click="toggleDetails(item.id)"
                        >
                            {{ t('pages.activity_log.table.details_trigger') }}
                            <ChevronsUpDown class="ml-2 h-4 w-4" />
                        </Button>
                    </div>
                </template>

                <template #details-row="{ item }">
                    <div class="grid grid-cols-1 gap-x-8 gap-y-4 bg-muted/50 p-4 md:grid-cols-2">
                        <div v-if="item.properties.attributes">
                            <h4 class="mb-2 font-semibold">{{ t('pages.activity_log.table.new_value') }}</h4>
                            <div class="space-y-1 text-sm">
                                <div v-for="(value, key) in item.properties.attributes" :key="key" class="grid grid-cols-3 gap-2">
                                    <span class="col-span-1 text-muted-foreground">{{ key }}:</span>
                                    <span class="col-span-2 font-mono text-xs break-all">{{ formatPropertyValue(value) }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-if="item.properties.old">
                            <h4 class="mb-2 font-semibold">{{ t('pages.activity_log.table.old_value') }}</h4>
                            <div class="space-y-1 text-sm">
                                <div v-for="(value, key) in item.properties.old" :key="key" class="grid grid-cols-3 gap-2">
                                    <span class="col-span-1 text-muted-foreground">{{ key }}:</span>
                                    <span class="col-span-2 font-mono text-xs break-all">{{ formatPropertyValue(value) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
