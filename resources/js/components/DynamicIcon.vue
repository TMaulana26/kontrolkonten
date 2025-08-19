<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue';

// This component accepts the icon name as a string prop.
const props = defineProps<{
    name: string;
}>();

// This is the magic part. We create a computed property that returns
// an asynchronous component.
const iconComponent = computed(() => {
    return defineAsyncComponent(async () => {
        try {
            // We dynamically import the entire lucide-vue-next library.
            // Vite is smart and will handle this efficiently.
            const icons = await import('lucide-vue-next');

            // We then access the specific icon from the imported module
            // using the name prop. If it's not found, we default to 'HelpCircle'.
            // The 'as any' is used to tell TypeScript to trust us here.
            return (icons as any)[props.name] || (icons as any)['HelpCircle'];
        } catch (error) {
            console.error(`Icon "${props.name}" not found.`, error);
            // Fallback to a default icon in case of any error
            const icons = await import('lucide-vue-next');
            return (icons as any)['HelpCircle'];
        }
    });
});
</script>

<template>
    <!-- The <component> tag renders our dynamically loaded icon. -->
    <component :is="iconComponent" />
</template>
