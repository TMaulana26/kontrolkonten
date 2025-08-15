import { ref, watch } from 'vue';
import type { I18n } from 'vue-i18n';

// Define the possible language types
type Language = 'en' | 'id';

// 1. Create the language state variable outside the composable function.
// This single ref will be shared across the entire app.
const language = ref<Language>('en'); // Default to English

/**
 * Updates the application's language and persists the choice.
 * This function is also defined at the module level.
 * @param {Language} newLanguage - The new language to set ('en' or 'id').
 */
function updateLanguage(newLanguage: Language) {
    language.value = newLanguage;
    localStorage.setItem('language', newLanguage);
}

/**
 * Initializes the language state.
 * This should be called once when the application starts.
 * @param {I18n} i18nInstance - The global i18n instance from app.ts.
 */
export function initializeLanguage(i18nInstance: any) {
    // Load saved language from localStorage
    const savedLanguage = localStorage.getItem('language') as Language | null;
    if (savedLanguage) {
        language.value = savedLanguage;
    }

    // Set the initial locale for vue-i18n
    i18nInstance.global.locale.value = language.value;

    // Create a watcher that syncs our language ref with the vue-i18n locale
    watch(language, (newLanguage) => {
        i18nInstance.global.locale.value = newLanguage;
    });
}


/**
 * A Vue composable for accessing and updating the application's language.
 */
export function useLanguage() {
    // 2. The composable now simply returns the shared state and update function.
    return {
        language,
        updateLanguage,
    };
}
