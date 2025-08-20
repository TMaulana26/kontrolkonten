<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Paginator, type User } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import throttle from 'lodash/throttle';
import { ArrowUpDown, MoreHorizontal, UserRoundPen, UserRoundPlus, UserRoundX } from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';
import { format } from "date-fns";

// 1. PROPS & SETUP
// -----------------------------------------------------------------------------
const props = defineProps<{
    users: Paginator<User>;
    filters: {
        search: string;
        sort: string;
        direction: string;
    };
}>();

const { t } = useI18n();

const isDialogOpen = ref(false);
const isEditing = ref(false);
const editingUserId = ref<number | null>(null);
const search = ref(props.filters.search);

const form = useForm({
    name: '',
    email: '',
});

const confirmState = reactive({
    open: false,
    title: '',
    description: '',
    iconType: 'info' as 'info' | 'warning' | 'error',
    onConfirm: () => {},
});

// 2. BREADCRUMBS & PAGE INFO
// -----------------------------------------------------------------------------
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('pages.user.title'),
        href: route('user.index'),
    },
]);

// 3. DATATABLE & FILTERING LOGIC
// -----------------------------------------------------------------------------
const sort = (field: string) => {
    let direction = 'asc';
    if (props.filters.sort === field && props.filters.direction === 'asc') {
        direction = 'desc';
    }
    router.get(route('user.index'), { search: search.value, sort: field, direction }, { preserveState: true });
};

watch(
    search,
    throttle((value: any) => {
        router.get(route('user.index'), { search: value }, { preserveState: true, replace: true });
    }, 300),
);

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    return format(new Date(dateString), 'PPpp');
};

// 4. DIALOG & FORM HANDLING
// -----------------------------------------------------------------------------
const openCreateDialog = () => {
    isEditing.value = false;
    form.reset();
    form.name = '';
    form.email = '';
    isDialogOpen.value = true;
};

const openEditDialog = (user: User) => {
    isEditing.value = true;
    editingUserId.value = user.id;
    form.name = user.name;
    form.email = user.email;
    isDialogOpen.value = true;
};

const closeDialog = () => {
    isDialogOpen.value = false;
};

const submitForm = () => {
    const options = {
        preserveState: true,
        onSuccess: () => {
            closeDialog();
            toast.success(`User ${isEditing.value ? 'updated' : 'created'} successfully.`);
        },
        onError: (errors: Record<string, string>) => {
            const firstError = Object.values(errors)[0];
            if (firstError) {
                toast.error(firstError);
            }
        },
    };

    if (isEditing.value) {
        form.put(route('user.update', { user: editingUserId.value }), options);
    } else {
        form.post(route('user.store'), options);
    }
};

// 5. CRUD ACTIONS
// -----------------------------------------------------------------------------
const deleteUser = (userId: number) => {
    confirmState.title = t('pages.user.delete.title');
    confirmState.description = t('pages.user.delete.desc');
    confirmState.iconType = 'warning';
    confirmState.onConfirm = () => {
        router.delete(route('user.destroy', { user: userId }), {
            preserveState: true,
            onSuccess: () => toast.info(t('pages.user.delete.success')),
        });
    };
    confirmState.open = true;
};
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Header -->
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-semibold">{{ t('pages.user.title') }}</h1>
                    <p class="text-muted-foreground">{{ t('pages.user.desc') }}</p>
                </div>
                <Button @click="openCreateDialog">
                    <UserRoundPlus class="mr-2 h-4 w-4" />
                    {{ t('pages.user.create_btn') }}
                </Button>
            </div>

            <!-- DataTable -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <Input v-model="search" placeholder="Filter by name or email..." class="w-full max-w-sm" />
                </div>
                <div class="rounded-xl border">
                    <Table>
                        <TableHeader class="hidden md:table-header-group">
                            <TableRow>
                                <TableHead>
                                    <Button variant="ghost" @click="sort('id')">
                                        {{ t('pages.user.table.id') }}
                                        <ArrowUpDown class="ml-2 h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button variant="ghost" @click="sort('name')">
                                        {{ t('pages.user.table.name') }}
                                        <ArrowUpDown class="ml-2 h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button variant="ghost" @click="sort('email')">
                                        {{ t('pages.user.table.email') }}
                                        <ArrowUpDown class="ml-2 h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button variant="ghost" @click="sort('created_at')">
                                        {{ t('pages.user.table.created_at') }}
                                        <ArrowUpDown class="ml-2 h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button variant="ghost" @click="sort('updated_at')">
                                        {{ t('pages.user.table.updated_at') }}
                                        <ArrowUpDown class="ml-2 h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead class="text-right">{{ t('pages.user.table.actions.title') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in props.users.data" :key="user.id" class="block border-b p-4 md:table-row md:border-0 md:p-0">
                                <TableCell
                                    data-label="ID"
                                    class="block text-right font-medium before:float-left before:font-semibold before:content-[attr(data-label)] md:table-cell md:text-left md:before:content-['']"
                                >
                                    {{ user.id }}
                                </TableCell>
                                <TableCell
                                    data-label="Name"
                                    class="block text-right font-medium before:float-left before:font-semibold before:content-[attr(data-label)] md:table-cell md:text-left md:before:content-['']"
                                >
                                    {{ user.name }}
                                </TableCell>
                                <TableCell
                                    data-label="Email"
                                    class="block text-right font-medium before:float-left before:font-semibold before:content-[attr(data-label)] md:table-cell md:text-left md:before:content-['']"
                                >
                                    {{ user.email }}
                                </TableCell>
                                <TableCell
                                    data-label="Created At"
                                    class="block text-right font-medium before:float-left before:font-semibold before:content-[attr(data-label)] md:table-cell md:text-left md:before:content-['']"
                                >
                                    {{ formatDate(user.created_at) }}
                                </TableCell>
                                <TableCell
                                    data-label="Updated At"
                                    class="block text-right font-medium before:float-left before:font-semibold before:content-[attr(data-label)] md:table-cell md:text-left md:before:content-['']"
                                >
                                    {{ formatDate(user.updated_at) }}
                                </TableCell>
                                <TableCell class="block text-right md:table-cell">
                                    <span class="float-left font-semibold md:hidden">{{ t('pages.user.table.actions.title') }}</span>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="h-8 w-8 p-0">
                                                <span class="sr-only">{{ t('pages.user.table.actions.open') }}</span>
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem @click="openEditDialog(user)">
                                                <UserRoundPen class="mr-2 h-4 w-4" />
                                                {{ t('pages.user.table.actions.edit') }}
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="deleteUser(user.id)">
                                                <UserRoundX class="mr-2 h-4 w-4" />
                                                {{ t('pages.user.table.actions.delete') }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <!-- Pagination -->
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="text-sm text-muted-foreground">
                        {{ t('pages.user.pagination_info', { from: props.users.from, to: props.users.to, total: props.users.total }) }}
                    </div>
                    <div class="flex items-center space-x-2">
                        <Button
                            v-for="link in props.users.links"
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

        <!-- Create/Edit Dialog -->
        <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ isEditing ? 'Edit User' : 'Create User' }}</DialogTitle>
                    <DialogDescription>
                        {{ isEditing ? 'Update the details for this user.' : 'Add a new user to your system.' }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitForm">
                    <div class="grid gap-4 py-4">
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="name" class="text-right">{{ t('pages.user.dialog.name') }}</Label>
                            <Input id="name" v-model="form.name" class="col-span-3" />
                        </div>
                        <div class="grid grid-cols-4 items-center gap-4">
                            <Label for="email" class="text-right">{{ t('pages.user.dialog.email') }}</Label>
                            <Input id="email" type="email" v-model="form.email" class="col-span-3" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="closeDialog">{{ t('pages.user.dialog.cancel') }}</Button>
                        <Button type="submit" :disabled="form.processing">{{ t('pages.user.dialog.save') }}</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Confirmation Dialog -->
        <ConfirmDialog
            :open="confirmState.open"
            :title="confirmState.title"
            :description="confirmState.description"
            :icon-type="confirmState.iconType"
            @update:open="confirmState.open = $event"
            @confirm="confirmState.onConfirm"
        />
    </AppLayout>
</template>
