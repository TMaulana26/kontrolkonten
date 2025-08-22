<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import DataTable from '@/components/DataTable.vue';
import DynamicIcon from '@/components/DynamicIcon.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Column, type Menu, type Paginator } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CheckCircle, MoreHorizontal, Pencil, PlusCircle, Power, Trash, Trash2, Undo, XCircle } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const props = defineProps<{
    menus: Paginator<Menu>;
    trashedMenus: Menu[];
    filters: {
        search: string;
        sort: string;
        direction: string;
        per_page: string;
    };
}>();

const { t, locale, availableLocales } = useI18n();

const columns = computed<Column<Menu>[]>(() => [
    { key: 'icon', label: t('pages.menu.table.icon') },
    { key: 'name', label: t('pages.menu.table.name'), sortable: true },
    { key: 'route', label: t('pages.menu.table.route'), sortable: true },
    { key: 'order', label: t('pages.menu.table.order'), sortable: true },
    { key: 'status', label: t('pages.menu.table.status'), sortable: true },
    { key: 'actions', label: t('pages.menu.table.actions') },
]);

const isDialogOpen = ref(false);
const isEditing = ref(false);
const editingMenuId = ref<number | null>(null);

const form = useForm({
    name: { en: '', id: '' },
    description: { en: '', id: '' },
    route: '',
    icon: '',
    order: 0,
    status: false,
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
        title: t('pages.menu.title'),
        href: route('menu.index'),
    },
]);

const openCreateDialog = () => {
    isEditing.value = false;
    form.reset();
    isDialogOpen.value = true;
};

const openEditDialog = (menu: Menu) => {
    isEditing.value = true;
    editingMenuId.value = menu.id;
    form.name = menu.name;
    form.description = menu.description;
    form.route = menu.route;
    form.icon = menu.icon;
    form.order = menu.order;
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
            toast.success(`Menu ${isEditing.value ? 'updated' : 'created'} successfully.`);
        },
        onError: (errors: Record<string, string>) => {
            const firstError = Object.values(errors)[0];
            if (firstError) toast.error(firstError);
        },
    };
    if (isEditing.value) {
        form.put(route('menu.update', { menu: editingMenuId.value }), options);
    } else {
        form.post(route('menu.store'), options);
    }
};

const toggleStatus = (menu: Menu) => {
    confirmState.title = t('pages.menu.confirm.toggle_title');
    confirmState.description = menu.status ? t('pages.menu.confirm.toggle_deactivate') : t('pages.menu.confirm.toggle_activate');
    confirmState.iconType = 'info';
    confirmState.onConfirm = () => {
        router.patch(
            route('menu.toggleStatus', menu),
            {},
            {
                preserveState: true,
                onSuccess: () => toast.success(t('pages.menu.confirm.toggle_success')),
            },
        );
    };
    confirmState.open = true;
};

const deleteMenu = (menuId: number) => {
    confirmState.title = t('pages.menu.confirm.delete_title');
    confirmState.description = t('pages.menu.confirm.delete_desc');
    confirmState.iconType = 'warning';
    confirmState.onConfirm = () => {
        router.delete(route('menu.destroy', { menu: menuId }), {
            preserveState: true,
            onSuccess: () => toast.info(t('pages.menu.confirm.delete_success')),
        });
    };
    confirmState.open = true;
};

const restoreMenu = (menuId: number) => {
    confirmState.title = t('pages.menu.confirm.restore_title');
    confirmState.description = t('pages.menu.confirm.restore_desc');
    confirmState.iconType = 'info';
    confirmState.onConfirm = () => {
        router.post(
            route('menu.restore', { id: menuId }),
            {},
            {
                preserveState: true,
                onSuccess: () => toast.success(t('pages.menu.confirm.restore_success')),
            },
        );
    };
    confirmState.open = true;
};

const forceDeleteMenu = (menuId: number) => {
    confirmState.title = t('pages.menu.confirm.force_delete_title');
    confirmState.description = t('pages.menu.confirm.force_delete_desc');
    confirmState.iconType = 'error';
    confirmState.onConfirm = () => {
        router.delete(route('menu.forceDelete', { id: menuId }), {
            preserveState: true,
            onSuccess: () => toast.error(t('pages.menu.confirm.force_delete_success')),
        });
    };
    confirmState.open = true;
};
</script>

<template>
    <Head :title="$t('pages.menu.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-4">
            <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 class="text-2xl font-semibold">{{ $t('pages.menu.title') }}</h1>
                    <p class="text-muted-foreground">{{ $t('pages.menu.desc') }}</p>
                </div>
                <Button @click="openCreateDialog" class="w-full md:w-auto">
                    <PlusCircle class="mr-2 h-4 w-4" />
                    {{ $t('pages.menu.create_btn') }}
                </Button>
            </div>

            <DataTable :data="menus" :columns="columns" :filters="filters">
                <template #cell-icon="{ item }">
                    <DynamicIcon :name="item.icon" class="h-5 w-5" />
                </template>
                <template #cell-name="{ item }">
                    <div class="font-medium">{{ item.name[locale as 'en' | 'id'] }}</div>
                    <div class="text-sm text-muted-foreground">{{ item.description[locale as 'en' | 'id'] }}</div>
                </template>
                <template #cell-status="{ item }">
                    <Badge :variant="item.status ? 'success' : 'destructive'">
                        <CheckCircle v-if="item.status" class="mr-1 h-4 w-4" />
                        <XCircle v-else class="mr-1 h-4 w-4" />
                        {{ item.status ? 'Active' : 'Inactive' }}
                    </Badge>
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
                                <DropdownMenuItem @click="deleteMenu(item.id)"><Trash2 class="mr-2 h-4 w-4" /> Delete</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </template>
            </DataTable>

            <div v-if="props.trashedMenus.length > 0" class="mt-6">
                <h2 class="mb-2 text-xl font-semibold">{{ $t('pages.menu.trash') }}</h2>
                <div class="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Route</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="menu in props.trashedMenus" :key="menu.id">
                                <TableCell>{{ menu.name[locale as 'en' | 'id'] }}</TableCell>
                                <TableCell>{{ menu.route }}</TableCell>
                                <TableCell class="text-right">
                                    <Button variant="ghost" size="icon" @click="restoreMenu(menu.id)"><Undo class="h-4 w-4" /></Button>
                                    <Button variant="ghost" size="icon" @click="forceDeleteMenu(menu.id)"><Trash class="h-4 w-4" /></Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>

        <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
            <DialogContent class="sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle>{{ isEditing ? 'Edit Menu' : 'Create Menu' }}</DialogTitle>
                    <DialogDescription>{{
                        isEditing ? 'Update the details of this menu item.' : 'Add a new menu item to the navigation.'
                    }}</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <Tabs default-value="en">
                        <TabsList>
                            <TabsTrigger v-for="lang in availableLocales" :key="lang" :value="lang">{{ lang.toUpperCase() }}</TabsTrigger>
                        </TabsList>
                        <TabsContent v-for="lang in availableLocales" :key="`content-${lang}`" :value="lang" class="space-y-4 pt-2">
                            <div class="grid grid-cols-1 items-start gap-2 md:grid-cols-4 md:items-center md:gap-4">
                                <Label :for="`name_${lang}`" class="md:text-right"
                                    >{{ $t('pages.menu.dialog.name') }}
                                    <span class="text-xs text-muted-foreground">({{ lang.toUpperCase() }})</span></Label
                                >
                                <Input :id="`name_${lang}`" v-model="form.name[lang as 'en' | 'id']" class="md:col-span-3" />
                            </div>
                            <div class="grid grid-cols-1 items-start gap-2 md:grid-cols-4 md:items-center md:gap-4">
                                <Label :for="`description_${lang}`" class="md:text-right">{{ $t('pages.menu.dialog.description') }}</Label>
                                <Input :id="`description_${lang}`" v-model="form.description[lang as 'en' | 'id']" class="md:col-span-3" />
                            </div>
                        </TabsContent>
                    </Tabs>
                    <div class="space-y-4 border-t pt-4">
                        <div class="grid grid-cols-1 items-start gap-2 md:grid-cols-4 md:items-center md:gap-4">
                            <Label for="route" class="md:text-right">{{ $t('pages.menu.dialog.route') }}</Label>
                            <Input id="route" v-model="form.route" class="md:col-span-3" />
                        </div>
                        <div class="grid grid-cols-1 items-start gap-2 md:grid-cols-4 md:items-center md:gap-4">
                            <Label for="icon" class="md:text-right">{{ $t('pages.menu.dialog.icon') }}</Label>
                            <Input id="icon" v-model="form.icon" class="md:col-span-3" />
                        </div>
                        <div class="grid grid-cols-1 items-start gap-2 md:grid-cols-4 md:items-center md:gap-4">
                            <Label for="order" class="md:text-right">{{ $t('pages.menu.dialog.order') }}</Label>
                            <Input id="order" type="number" v-model="form.order" class="md:col-span-3" />
                        </div>
                    </div>
                    <DialogFooter class="pt-2">
                        <Button type="button" variant="secondary" @click="closeDialog">{{ $t('pages.menu.dialog.cancel') }}</Button>
                        <Button type="submit" :disabled="form.processing">{{ $t('pages.menu.dialog.save') }}</Button>
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
