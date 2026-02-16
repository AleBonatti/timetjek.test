import { ref, onMounted, onUnmounted } from 'vue';

export type Theme = 'light' | 'dark';

export function useTheme() {
    const theme = ref<Theme>('dark');

    const setTheme = (newTheme: Theme): void => {
        theme.value = newTheme;
        if (typeof window !== 'undefined') {
            window.localStorage.setItem('theme', newTheme);
        }
        updateHtmlClass(newTheme);
    };

    const toggleTheme = (): void => {
        setTheme(theme.value === 'dark' ? 'light' : 'dark');
    };

    const updateHtmlClass = (currentTheme: Theme): void => {
        if (typeof window !== 'undefined' && typeof document !== 'undefined') {
            const html = document.documentElement;
            if (currentTheme === 'dark') {
                html.classList.remove('light');
                html.classList.add('dark');
            } else {
                html.classList.remove('dark');
                html.classList.add('light');
            }
        }
    };

    const initTheme = (): void => {
        if (typeof window === 'undefined') return;

        // Check localStorage first
        const savedTheme = window.localStorage.getItem('theme') as Theme | null;
        if (savedTheme && (savedTheme === 'light' || savedTheme === 'dark')) {
            theme.value = savedTheme;
        } else {
            // Check system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            theme.value = prefersDark ? 'dark' : 'light';
        }
        updateHtmlClass(theme.value);
    };

    // Media query for system theme changes
    let mediaQuery: MediaQueryList | null = null;

    const handleChange = (e: MediaQueryListEvent): void => {
        if (typeof window !== 'undefined' && !window.localStorage.getItem('theme')) {
            theme.value = e.matches ? 'dark' : 'light';
            updateHtmlClass(theme.value);
        }
    };

    onMounted(() => {
        if (typeof window === 'undefined') return;

        initTheme();

        // Listen for system theme changes
        mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        if (mediaQuery && 'addEventListener' in mediaQuery) {
            mediaQuery.addEventListener('change', handleChange);
        }
    });

    onUnmounted(() => {
        // Clean up event listener
        if (mediaQuery && 'removeEventListener' in mediaQuery) {
            mediaQuery.removeEventListener('change', handleChange);
        }
    });

    return {
        theme,
        setTheme,
        toggleTheme,
    };
}
