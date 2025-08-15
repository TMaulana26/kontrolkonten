import { onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

type Language = 'en' | 'id';

export function useLanguage() {
    const { locale } = useI18n();
    const language = ref<Language>(locale.value as Language);
    onMounted(() => {
        const savedLanguage = localStorage.getItem('language') as Language | null;
        if (savedLanguage) {
            language.value = savedLanguage;
        }
    });

    watch(language, (newLanguage) => {
        locale.value = newLanguage;
    });

    function updateLanguage(value: Language) {
        language.value = value;
        localStorage.setItem('language', value);
    }

    return {
        language,
        updateLanguage,
    };
}
