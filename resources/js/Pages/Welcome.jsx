import ApplicationLogo from '@/Components/ApplicationLogo';
import ThemeToggle from '@/Components/ThemeToggle';
import { Head, Link } from '@inertiajs/react';

export default function Welcome() {
    return (
        <>
            <Head title="Welcome" />

            <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
                <div className="mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-6 py-12">
                    <div className="mb-4 flex justify-end">
                        <ThemeToggle />
                    </div>

                    <div className="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-gray-950 dark:shadow-none">
                        <div className="grid gap-8 p-8 md:grid-cols-2 md:p-12">
                            <div className="flex flex-col justify-center">
                                <div className="mb-4 flex items-center gap-3">
                                    <ApplicationLogo className="h-10 w-10 fill-current text-gray-800 dark:text-gray-100" />
                                    <p className="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Silsilah Keluarga
                                    </p>
                                </div>

                                <h1 className="mt-3 text-3xl font-bold text-gray-900 dark:text-gray-100 md:text-4xl">
                                    Welcome to your family tree workspace
                                </h1>

                                <p className="mt-4 text-base text-gray-600 dark:text-gray-300">
                                    Manage profiles, discover descendants, and coordinate events in one place with a privacy-first workflow.
                                </p>

                                <div className="mt-8 flex flex-wrap gap-3">
                                    <Link
                                        href={route('login')}
                                        className="inline-flex items-center rounded-md bg-gray-900 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-gray-200"
                                    >
                                        Log In
                                    </Link>

                                    <Link
                                        href={route('register')}
                                        className="inline-flex items-center rounded-md border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800"
                                    >
                                        Register
                                    </Link>
                                </div>
                            </div>

                            <div className="grid content-center gap-4">
                                <div className="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900">
                                    <h2 className="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        Family Tree
                                    </h2>
                                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                        Traverse descendant relationships using integrated graph queries.
                                    </p>
                                </div>

                                <div className="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900">
                                    <h2 className="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        Profile Management
                                    </h2>
                                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                        Update personal details with granular privacy controls.
                                    </p>
                                </div>

                                <div className="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900">
                                    <h2 className="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        Event Coordination
                                    </h2>
                                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                        Organize family events with invitations, RSVPs, and contribution tracking.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
