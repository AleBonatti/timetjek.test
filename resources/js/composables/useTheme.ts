import { ref, onMounted, watch } from 'vue';

export type Theme = 'light' | 'dark';

export function useTheme() {
    const theme = ref<Theme>('dark');

    const setTheme = (newTheme: Theme) => {
        theme.value = newTheme;
        localStorage.setItem('theme', newTheme);
        updateHtmlClass(newTheme);
    };

    const toggleTheme = () => {
        setTheme(theme.value === 'dark' ? 'light' : 'dark');
    };

    const updateHtmlClass = (currentTheme: Theme) => {
        const html = document.documentElement;
        if (currentTheme === 'dark') {
            html.classList.remove('light');
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
            html.classList.add('light');
        }
    };

    const initTheme = () => {
        // Check localStorage first
        const savedTheme = localStorage.getItem('theme') as Theme | null;
        if (savedTheme) {
            theme.value = savedTheme;
        } else {
            // Check system preference
            const prefersDark = window.matchMedia(
                '(prefers-color-scheme: dark)'
            ).matches;
            theme.value = prefersDark ? 'dark' : 'light';
        }
        updateHtmlClass(theme.value);
    };

    onMounted(() => {
        initTheme();

        // Listen for system theme changes
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        const handleChange = (e: MediaQueryListEvent) => {
            if (!localStorage.getItem('theme')) {
                theme.value = e.matches ? 'dark' : 'light';
                updateHtmlClass(theme.value);
            }
        };

        mediaQuery.addEventListener('change', handleChange);

        return () => {
            mediaQuery.removeEventListener('change', handleChange);
        };
    });

    return {
        theme,
        setTheme,
        toggleTheme,
    };
}
