import { applyTheme, getStoredTheme, THEME_KEY } from '@/lib/theme';
import { useEffect, useState } from 'react';

const OPTIONS = [
    { label: 'System', value: 'system' },
    { label: 'Light', value: 'light' },
    { label: 'Dark', value: 'dark' },
];

export default function ThemeToggle() {
    const [theme, setTheme] = useState('system');

    useEffect(() => {
        const storedTheme = getStoredTheme();
        setTheme(storedTheme);
        applyTheme(storedTheme);
    }, []);

    useEffect(() => {
        const media = window.matchMedia('(prefers-color-scheme: dark)');

        const onSystemChange = () => {
            if (theme === 'system') {
                applyTheme('system');
            }
        };

        media.addEventListener('change', onSystemChange);

        return () => media.removeEventListener('change', onSystemChange);
    }, [theme]);

    const setThemeMode = (nextTheme) => {
        localStorage.setItem(THEME_KEY, nextTheme);
        setTheme(nextTheme);
        applyTheme(nextTheme);
    };

    return (
        <div className="inline-flex items-center rounded-md border border-gray-300 bg-white p-1 text-xs shadow-sm dark:border-gray-700 dark:bg-gray-800">
            {OPTIONS.map((option) => {
                const active = theme === option.value;

                return (
                    <button
                        key={option.value}
                        type="button"
                        onClick={() => setThemeMode(option.value)}
                        className={
                            `rounded px-2 py-1 font-medium transition ${
                                active
                                    ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900'
                                    : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
                            }`
                        }
                    >
                        {option.label}
                    </button>
                );
            })}
        </div>
    );
}
