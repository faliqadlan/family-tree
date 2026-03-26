import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function AdminTools({ templateDownloadUrl }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Admin Tools
                </h2>
            }
        >
            <Head title="Admin Tools" />

            <div className="py-12">
                <div className="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-gray-900">
                            System Manager Capabilities
                        </h3>
                        <ul className="mt-3 list-disc space-y-2 pl-5 text-sm text-gray-700">
                            <li>Manage all users and profiles across the application.</li>
                            <li>Approve and manage all access requests.</li>
                            <li>Manage all events, RSVPs, and financial contributions.</li>
                            <li>View and query any family-tree branch in Neo4j.</li>
                        </ul>
                    </div>

                    <div className="rounded-lg bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-gray-900">
                            Stub Profile Import Template
                        </h3>
                        <p className="mt-2 text-sm text-gray-600">
                            Download the CSV template for bulk import of deceased/stub family profiles.
                        </p>
                        <a
                            href={templateDownloadUrl}
                            className="mt-4 inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
                        >
                            Download CSV Template
                        </a>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
