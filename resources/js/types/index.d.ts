import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    onClick: (event: MouseEvent) => void;
    title: string;
    href: string;
    icon?: string;
    isActive?: boolean;
    onClick?: (event: MouseEvent) => void;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Menu {
    id: number;
    name: {
        en: string;
        id: string;
    };
    description: {
        en: string;
        id: string;
    };
    route: string;
    icon: string;
    order: number;
    status: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string | null;
}

export interface Paginator<T> {
    data: T[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
}

export interface Activity {
    id: number;
    log_name: string;
    description: string;
    subject_type: string;
    subject_id: number;
    causer_type: string;
    causer_id: number;
    properties: any;
    created_at: string;
    causer: User | null;
}

export interface Column<T> {
    key: keyof T | string;
    label: string;
    sortable?: boolean;
}

export interface Role {
    id: number;
    name: string;
    description: string;
    status: boolean;
    permissions: Permission[];
}

export interface Permission {
    id: number;
    name: string;
    feature: string;
}
