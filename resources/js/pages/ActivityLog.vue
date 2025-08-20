<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { RangeCalendar } from '@/components/ui/range-calendar';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import { type Activity, type BreadcrumbItem, type Paginator } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { CalendarDate, DateFormatter, getLocalTimeZone, parseDate } from '@internationalized/date';
import { format } from 'date-fns';
import throttle from 'lodash/throttle';
import { ArrowUpDown, Calendar as CalendarIcon, ChevronsUpDown, XIcon } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    logs: Paginator<Activity>;
    filters: {
        search: string;
        sort: string;
        direction: string;
        start_date: string | null;
        end_date: string | null;
    };
}>();

const { t } = useI18n();

const search = ref(props.filters.search);
const openDetailsId = ref<number | null>(null);

// --- Date Range Picker State and Helpers ---  

// Helper to parse a 'YYYY-MM-DD' string into a CalendarDate object
const parseToCalendarDate = (dateString: string | null): CalendarDate | null => {
    if (!dateString) return null;
    try {
        return parseDate(dateString);
    } catch (error) {
        console.error('Invalid date string for parseDate:', dateString);
        return null;
    }
};

// Main reactive state for the date range picker
const dateRange = ref({
    start: parseToCalendarDate(props.filters.start_date),
    end: parseToCalendarDate(props.filters.end_date),
});

// Formatter for displaying the date in the button
const df = new DateFormatter('en-US', {
    dateStyle: 'medium',
});

// Computed property to display the selected date range text
const dateRangeDisplay = computed(() => {
    if (!dateRange.value?.start) {
        return 'Pick a date range';
    }
    if (dateRange.value.start && !dateRange.value.end) {
        return df.format(dateRange.value.start.toDate(getLocalTimeZone()));
    }
    if (dateRange.value.start && dateRange.value.end) {
        return `${df.format(dateRange.value.start.toDate(getLocalTimeZone()))} - ${df.format(dateRange.value.end.toDate(getLocalTimeZone()))}`;
    }
    return 'Pick a date range';
});

// Helper to format a CalendarDate object back to a 'YYYY-MM-DD' string for the URL
const formatToUrlDate = (date: CalendarDate | null): string | undefined => {
    if (!date) return undefined;
    const month = String(date.month).padStart(2, '0');
    const day = String(date.day).padStart(2, '0');
    return `${date.year}-${month}-${day}`;
};

// --- End of Date Range Picker Logic ---

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Activity Log',
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

const sort = (field: string) => {
    let direction = 'asc';
    if (props.filters.sort === field && props.filters.direction === 'asc') {
        direction = 'desc';
    }
    router.get(route('activity-log.index'), { ...props.filters, sort: field, direction }, { preserveState: true });
};

const toggleDetails = (logId: number) => {
    openDetailsId.value = openDetailsId.value === logId ? null : logId;
};

const clearDateFilter = () => {
    dateRange.value = { start: null, end: null };
};

// Watch for changes in search input or the date range
watch(
    [search, dateRange],
    throttle(() => {
        router.get(
            route('activity-log.index'),
            {
                search: search.value,
                start_date: formatToUrlDate(dateRange.value.start),
                end_date: formatToUrlDate(dateRange.value.end),
                sort: props.filters.sort,
                direction: props.filters.direction,
            },
            { preserveState: true, replace: true },
        );
    }, 300),
    { deep: true }, // Use deep watcher for the dateRange object
);
</script>

<template>
    <Head title="Activity Log" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-semibold">Activity Log</h1>
                <p class="text-muted-foreground">An audit trail of all activities in the application.</p>
            </div>

            <!-- DataTable -->
            <div class="space-y-4">
                <div class="flex flex-col items-center gap-4 sm:flex-row">
                    <Input v-model="search" placeholder="Search by user name..." class="w-full max-w-sm" />
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
                                <RangeCalendar v-model="dateRange" />
                            </PopoverContent>
                        </Popover>
                        <Button v-if="dateRange.start" @click="clearDateFilter" variant="ghost" size="icon">
                            <XIcon class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
                <div class="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Event</TableHead>
                                <TableHead class="hidden md:table-cell">User</TableHead>
                                <TableHead class="hidden md:table-cell">
                                    <Button variant="ghost" @click="sort('created_at')">
                                        Date & Time
                                        <ArrowUpDown class="ml-2 h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead class="w-24 text-right"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="props.logs.data.length > 0">
                                <template v-for="log in props.logs.data" :key="log.id">
                                    <TableRow class="border-b">
                                        <TableCell class="font-medium">
                                            <div class="flex items-center gap-2">
                                                <Badge variant="outline">{{ getModelName(log.log_name) }}</Badge>
                                                <span>{{ log.description }}</span>
                                            </div>
                                            <div class="mt-1 text-xs text-muted-foreground md:hidden">
                                                By {{ log.causer?.name || 'System' }} on
                                                {{ formatDate(log.created_at) }}
                                            </div>
                                        </TableCell>
                                        <TableCell class="hidden md:table-cell">{{ log.causer?.name || 'System' }}</TableCell>
                                        <TableCell class="hidden md:table-cell">{{ formatDate(log.created_at) }}</TableCell>
                                        <TableCell class="text-right">
                                            <Button
                                                v-if="log.properties && (log.properties.attributes || log.properties.old)"
                                                variant="ghost"
                                                size="sm"
                                                @click="toggleDetails(log.id)"
                                                :aria-expanded="openDetailsId === log.id"
                                                :aria-controls="`details-${log.id}`"
                                            >
                                                Details
                                                <ChevronsUpDown class="ml-2 h-4 w-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                    <tr v-if="openDetailsId === log.id" class="bg-muted/50" :id="`details-${log.id}`">
                                        <td colspan="4" class="p-0">
                                            <div class="grid grid-cols-1 gap-x-8 gap-y-4 p-4 md:grid-cols-2">
                                                <div v-if="log.properties.attributes">
                                                    <h4 class="mb-2 font-semibold">New Values</h4>
                                                    <div class="space-y-1 text-sm">
                                                        <div
                                                            v-for="(value, key) in log.properties.attributes"
                                                            :key="key"
                                                            class="grid grid-cols-3 gap-2"
                                                        >
                                                            <span class="col-span-1 text-muted-foreground">{{ key }}:</span>
                                                            <span class="col-span-2 font-mono text-xs break-all">{{
                                                                formatPropertyValue(value)
                                                            }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div v-if="log.properties.old">
                                                    <h4 class="mb-2 font-semibold">Old Values</h4>
                                                    <div class="space-y-1 text-sm">
                                                        <div v-for="(value, key) in log.properties.old" :key="key" class="grid grid-cols-3 gap-2">
                                                            <span class="col-span-1 text-muted-foreground">{{ key }}:</span>
                                                            <span class="col-span-2 font-mono text-xs break-all">{{
                                                                formatPropertyValue(value)
                                                            }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                            <TableRow v-else>
                                <TableCell colspan="4" class="h-24 text-center"> No results found. </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <!-- Pagination -->
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="text-sm text-muted-foreground">
                        Showing {{ props.logs.from }} to {{ props.logs.to }} of {{ props.logs.total }} results
                    </div>
                    <div class="flex items-center space-x-2">
                        <Button
                            v-for="link in props.logs.links"
                            :key="link.label"
                            :href="link.url"
                            :disabled="!link.url"
                            :class="{ 'bg-primary text-primary-foreground': link.active }"
                            v-html="link.label"
                            as="Link"
                            preserve-scroll
                            size="sm"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
