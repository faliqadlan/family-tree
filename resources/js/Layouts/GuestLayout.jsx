import ApplicationLogo from '@/Components/ApplicationLogo';
import ThemeToggle from '@/Components/ThemeToggle';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="flex min-h-screen flex-col items-center bg-gray-100 pt-6 dark:bg-gray-900 sm:justify-center sm:pt-0">
            <div className="mb-4 w-full max-w-md px-2 text-right">
                <ThemeToggle />
            </div>

            <div>
                <Link href="/">
                    <ApplicationLogo className="h-20 w-20 fill-current text-gray-700 dark:text-gray-200" />
                </Link>
            </div>

            <div className="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md dark:bg-gray-950 dark:shadow-none sm:max-w-md sm:rounded-lg">
                {children}
            </div>
        </div>
    );
}
