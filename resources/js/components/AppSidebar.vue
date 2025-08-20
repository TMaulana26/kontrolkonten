<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type Menu as MenuType, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, inject } from 'vue';
import { useI18n } from 'vue-i18n';
import AppLogo from './AppLogo.vue';

const page = usePage();
const { t, locale } = useI18n();
const showInfoDialog = inject<(title: string, description: string) => void>('showInfoDialog');
const navMenus = computed(() => page.props.navMenus as MenuType[]);const mainNavItems = computed<NavItem[]>(() =>
    navMenus.value.map(menu => {
        const item: NavItem = {
            title: menu.name[locale.value as 'en' | 'id'],
            href: '#',
            icon: menu.icon,
        };

        try {
            item.href = route(menu.route);
        } catch (error) {
            console.warn(`Route "${menu.route}" does not exist.`);
            item.onClick = (event: MouseEvent) => {
                event.preventDefault();
                if (showInfoDialog) {
                    showInfoDialog(
                        'Under Development',
                        `The page for "${item.title}" is not available yet.`
                    );
                }
            };
        }
        return item;
    })
);

const footerNavItems = computed<NavItem[]>(() => [
    {
        title: t('nav.repository.title'),
        href: t('nav.repository.link'),
        icon: 'Folder',
    },
    {
        title: t('nav.documentation.title'),
        href: t('nav.documentation.link'),
        icon: 'BookOpen',
    },
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
