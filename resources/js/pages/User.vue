<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Column, type Paginator, type User } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { MoreHorizontal, UserRoundPen, UserRoundPlus, UserRoundX } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    users: Paginator<User>;
    filters: {
        search: string;
        sort: string;
        direction: string;
        per_page: string;
    };
}>();

const { t } = useI18n();

const columns: Column<User>[] = [
    { key: 'id', label: t('pages.user.table.id'), sortable: true },
    { key: 'name', label: t('pages.user.table.name'), sortable: true },
    { key: 'email', label: t('pages.user.table.email'), sortable: true },
    { key: 'created_at', label: t('pages.user.table.created_at'), sortable: true },
    { key: 'updated_at', label: t('pages.user.table.updated_at'), sortable: true },
    { key: 'actions', label: t('pages.user.table.actions.title') },
];

const isDialogOpen = ref(false);
const isEditing = ref(false);
const editingUserId = ref<number | null>(null);

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

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('pages.user.title'),
        href: route('user.index'),
    },
]);

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    return format(new Date(dateString), 'PPpp');
};

const openCreateDialog = () => {
    isEditing.value = false;
    form.reset();
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
    <Head :title="t('pages.user.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-4">
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

            <DataTable :data="users" :columns="columns" :filters="filters">
                <template #cell-created_at="{ item }">
                    {{ formatDate(item.created_at) }}
                </template>
                <template #cell-updated_at="{ item }">
                    {{ formatDate(item.updated_at) }}
                </template>
                <template #cell-actions="{ item }">
                    <div class="text-right">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" class="h-8 w-8 p-0">
                                    <span class="sr-only">{{ t('pages.user.table.actions.open') }}</span>
                                    <MoreHorizontal class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="openEditDialog(item)">
                                    <UserRoundPen class="mr-2 h-4 w-4" />
                                    {{ t('pages.user.table.actions.edit') }}
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="deleteUser(item.id)">
                                    <UserRoundX class="mr-2 h-4 w-4" />
                                    {{ t('pages.user.table.actions.delete') }}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </template>
            </DataTable>
        </div>

        <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ isEditing ? t('pages.user.dialog.edit_title') : t('pages.user.dialog.create_title') }}</DialogTitle>
                    <DialogDescription>
                        {{ isEditing ? t('pages.user.dialog.edit_desc') : t('pages.user.dialog.create_desc') }}
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
