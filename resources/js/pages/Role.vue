<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DataTable from '@/components/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Column, type Paginator, type Permission, type Role } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CheckCircle, KeyRound, MoreHorizontal, Pencil, PlusCircle, Power, Trash, Trash2, Undo, XCircle } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    roles: Paginator<Role>;
    permissions: Record<string, Permission[]>;
    trashedRoles: Role[];
    filters: {
        search: string;
        sort: string;
        direction: string;
        per_page: string;
    };
}>();

const { t } = useI18n();

const columns: Column<Role>[] = [
    { key: 'name', label: 'Role', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'assign', label: 'Assign Permissions' },
    { key: 'actions', label: 'Actions' },
];

const isDialogOpen = ref(false);
const isEditing = ref(false);
const editingRoleId = ref<number | null>(null);

const form = useForm({
    name: '',
    description: '',
});

const permissionsForm = useForm({
    permissions: [] as number[],
});

const confirmState = reactive({
    open: false,
    title: '',
    description: '',
    iconType: 'info' as 'info' | 'warning' | 'error',
    onConfirm: () => {},
});

const breadcrumbs = computed<BreadcrumbItem[]>(() => [{ title: 'Roles', href: route('role.index') }]);

const openCreateDialog = () => {
    isEditing.value = false;
    form.reset();
    isDialogOpen.value = true;
};

const openEditDialog = (role: Role) => {
    isEditing.value = true;
    editingRoleId.value = role.id;
    form.name = role.name;
    form.description = role.description;
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
            toast.success(`Role ${isEditing.value ? 'updated' : 'created'} successfully.`);
        },
        onError: (errors: Record<string, string>) => {
            const firstError = Object.values(errors)[0];
            if (firstError) toast.error(firstError);
        },
    };
    if (isEditing.value) {
        form.put(route('role.update', { role: editingRoleId.value }), options);
    } else {
        form.post(route('role.store'), options);
    }
};

const toggleStatus = (role: Role) => {
    confirmState.title = 'Toggle Role Status';
    confirmState.description = `Are you sure you want to ${role.status ? 'deactivate' : 'activate'} this role?`;
    confirmState.iconType = 'info';
    confirmState.onConfirm = () => {
        router.patch(
            route('role.toggle-status', { role: role.id }),
            {},
            {
                preserveState: true,
                onSuccess: () => toast.success('Role status updated.'),
            },
        );
    };
    confirmState.open = true;
};

const deleteRole = (roleId: number) => {
    confirmState.title = 'Delete Role';
    confirmState.description = 'Are you sure you want to move this role to trash?';
    confirmState.iconType = 'warning';
    confirmState.onConfirm = () => {
        router.delete(route('role.destroy', { role: roleId }), {
            preserveState: true,
            onSuccess: () => toast.info('Role moved to trash.'),
        });
    };
    confirmState.open = true;
};

const assignPermissions = (role: Role) => {
    permissionsForm.permissions = role.permissions.map((p) => p.id);
    permissionsForm.post(route('role.assign-permissions', { role: role.id }), {
        preserveState: true,
        onSuccess: () => toast.success('Permissions updated successfully.'),
    });
};

const restoreRole = (roleId: number) => {
    confirmState.title = 'Restore Role';
    confirmState.description = 'Are you sure you want to restore this role?';
    confirmState.iconType = 'info';
    confirmState.onConfirm = () => {
        router.post(
            route('role.restore', { id: roleId }),
            {},
            {
                preserveState: true,
                onSuccess: () => toast.success('Role restored successfully.'),
            },
        );
    };
    confirmState.open = true;
};

const forceDeleteRole = (roleId: number) => {
    confirmState.title = 'Permanently Delete Role';
    confirmState.description = 'This action cannot be undone. Are you sure you want to permanently delete this role?';
    confirmState.iconType = 'error';
    confirmState.onConfirm = () => {
        router.delete(route('role.forceDelete', { id: roleId }), {
            preserveState: true,
            onSuccess: () => toast.error('Role permanently deleted.'),
        });
    };
    confirmState.open = true;
};
</script>

<template>
    <Head title="Roles" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-4">
            <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 class="text-2xl font-semibold">Role Management</h1>
                    <p class="text-muted-foreground">Manage user roles and their permissions.</p>
                </div>
                <Button @click="openCreateDialog" class="w-full md:w-auto">
                    <PlusCircle class="mr-2 h-4 w-4" />
                    Create Role
                </Button>
            </div>

            <DataTable :data="roles" :columns="columns" :filters="filters">
                <template #cell-name="{ item }">
                    <div class="font-medium">{{ item.name }}</div>
                    <div class="text-sm text-muted-foreground">{{ item.description }}</div>
                </template>

                <template #cell-status="{ item }">
                    <Badge :variant="item.status ? 'success' : 'destructive'">
                        <CheckCircle v-if="item.status" class="mr-1 h-4 w-4" />
                        <XCircle v-else class="mr-1 h-4 w-4" />
                        {{ item.status ? 'Active' : 'Inactive' }}
                    </Badge>
                </template>

                <template #cell-assign="{ item, toggleDetails }">
                    <Button variant="ghost" size="icon" @click="toggleDetails(item.id)">
                        <KeyRound class="h-4 w-4" />
                    </Button>
                </template>

                <template #cell-actions="{ item }">
                    <div class="text-right">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" class="h-8 w-8 p-0"><MoreHorizontal class="h-4 w-4" /></Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="toggleStatus(item)"><Power class="mr-2 h-4 w-4" /> Toggle Status</DropdownMenuItem>
                                <DropdownMenuItem @click="openEditDialog(item)"><Pencil class="mr-2 h-4 w-4" /> Edit</DropdownMenuItem>
                                <DropdownMenuItem @click="deleteRole(item.id)"><Trash2 class="mr-2 h-4 w-4" /> Delete</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </template>

                <template #details-row="{ item }">
                    <div class="bg-muted/50 p-4">
                        <h4 class="mb-4 font-semibold">Assign Permissions for "{{ item.name }}"</h4>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                            <div v-for="(permissionGroup, feature) in permissions" :key="feature">
                                <h5 class="mb-2 font-medium">{{ feature }}</h5>
                                <div class="space-y-2">
                                    <div v-for="permission in permissionGroup" :key="permission.id" class="flex items-center space-x-2">
                                        <Checkbox
                                            :id="`perm-${item.id}-${permission.id}`"
                                            :checked="item.permissions.some((p) => p.id === permission.id)"
                                            @update:checked="
                                                () => {
                                                    const permIds = new Set(item.permissions.map((p) => p.id));
                                                    if (permIds.has(permission.id)) {
                                                        permIds.delete(permission.id);
                                                    } else {
                                                        permIds.add(permission.id);
                                                    }
                                                    item.permissions = Array.from(permIds).map((id) => ({ id, name: '', feature: '' }));
                                                }
                                            "
                                        />
                                        <label :for="`perm-${item.id}-${permission.id}`" class="text-sm leading-none font-medium">
                                            {{ permission.name }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <Button @click="assignPermissions(item)">Save Permissions</Button>
                        </div>
                    </div>
                </template>
            </DataTable>

            <div v-if="props.trashedRoles && props.trashedRoles.length > 0" class="mt-6">
                <h2 class="mb-2 text-xl font-semibold">Trash</h2>
                <div class="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="role in props.trashedRoles" :key="role.id">
                                <TableCell>{{ role.name }}</TableCell>
                                <TableCell class="text-right">
                                    <Button variant="ghost" size="icon" @click="restoreRole(role.id)"><Undo class="h-4 w-4" /></Button>
                                    <Button variant="ghost" size="icon" @click="forceDeleteRole(role.id)"><Trash class="h-4 w-4" /></Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>

        <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ isEditing ? 'Edit Role' : 'Create Role' }}</DialogTitle>
                    <DialogDescription>{{ isEditing ? 'Update the details of this role.' : 'Add a new role to the system.' }}</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <Label for="name">Role Name</Label>
                        <Input id="name" v-model="form.name" />
                    </div>
                    <div>
                        <Label for="description">Description</Label>
                        <Input id="description" v-model="form.description" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="closeDialog">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">Save</Button>
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
