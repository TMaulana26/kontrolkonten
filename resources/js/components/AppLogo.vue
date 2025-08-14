<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { SquareStack } from 'lucide-vue-next';
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
        <AppLogoIcon class="size-6 fill-current text-white dark:text-amber-600" />
    </div>
    <div class="ml-1 flex items-center text-left text-sm">
        <span class="mr-1 truncate leading-tight font-semibold">Kontrol Konten</span>
        <SquareStack class="size-4" />
    </div>
</template>
