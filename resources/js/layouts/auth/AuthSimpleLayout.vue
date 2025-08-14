<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import AppearanceSelect from '@/components/AppearanceSelect.vue';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
        <div class="hidden md:flex md:absolute md:right-6 md:top-6">
            <AppearanceSelect />
        </div>
        <div class="md:hidden absolute bottom-2 left-1/2 -translate-x-1/2 -translate-y-1/2">
            <AppearanceTabs />
        </div>
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link :href="route('home')" class="flex flex-col items-center gap-2 font-medium">
                        <div class="mb-1 flex h-9 w-9 items-center justify-center rounded-md">
                            <AppLogoIcon class="size-12 fill-current text-[var(--foreground)] dark:text-amber-600" />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
