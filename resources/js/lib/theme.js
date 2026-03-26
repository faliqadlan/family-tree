export const THEME_KEY = 'theme';

export function getStoredTheme() {
    const theme = localStorage.getItem(THEME_KEY);

    if (theme === 'light' || theme === 'dark' || theme === 'system') {
        return theme;
    }

    return 'system';
}

export function applyTheme(theme) {
    const prefersDark = window.matchMedia(
        '(prefers-color-scheme: dark)',
    ).matches;
    const useDark = theme === 'dark' || (theme === 'system' && prefersDark);

    document.documentElement.classList.toggle('dark', useDark);
}

export function initTheme() {
    const theme = getStoredTheme();
    applyTheme(theme);
}
