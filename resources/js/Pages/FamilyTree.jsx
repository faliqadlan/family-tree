import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useState } from 'react';

export default function FamilyTree({ initialAncestorUuid, defaultDepth }) {
    const [ancestorUuid, setAncestorUuid] = useState(initialAncestorUuid ?? '');
    const [depth, setDepth] = useState(defaultDepth ?? 4);
    const [profiles, setProfiles] = useState([]);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState('');

    const loadDescendants = () => {
        if (!ancestorUuid) {
            setError('Ancestor UUID is required.');
            setProfiles([]);
            return;
        }

        setError('');
        setIsLoading(true);

        axios
            .get('/api/family-tree/descendants', {
                params: {
                    ancestor_uuid: ancestorUuid,
                    depth,
                },
            })
            .then((response) => {
                const items = response?.data?.data ?? [];
                setProfiles(Array.isArray(items) ? items : []);
            })
            .catch((requestError) => {
                const message =
                    requestError?.response?.data?.message ||
                    'Unable to load family tree data.';
                setError(message);
                setProfiles([]);
            })
            .finally(() => {
                setIsLoading(false);
            });
    };

    useEffect(() => {
        if (initialAncestorUuid) {
            loadDescendants();
        }
    }, []);

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Family Tree
                </h2>
            }
        >
            <Head title="Family Tree" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-6 shadow-sm">
                        <div className="grid gap-4 md:grid-cols-[1fr_140px_auto]">
                            <div>
                                <label
                                    htmlFor="ancestor_uuid"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    Ancestor UUID
                                </label>
                                <input
                                    id="ancestor_uuid"
                                    type="text"
                                    value={ancestorUuid}
                                    onChange={(event) =>
                                        setAncestorUuid(event.target.value)
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                                />
                            </div>

                            <div>
                                <label
                                    htmlFor="depth"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    Depth
                                </label>
                                <input
                                    id="depth"
                                    type="number"
                                    min="1"
                                    max="10"
                                    value={depth}
                                    onChange={(event) =>
                                        setDepth(Number(event.target.value))
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <div className="flex items-end">
                                <button
                                    type="button"
                                    onClick={loadDescendants}
                                    className="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                                >
                                    Load Tree
                                </button>
                            </div>
                        </div>

                        {error && (
                            <p className="mt-4 text-sm text-red-600">{error}</p>
                        )}
                    </div>

                    <div className="rounded-lg bg-white p-6 shadow-sm">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Descendants
                            </h3>
                            <span className="text-sm text-gray-500">
                                {profiles.length} result(s)
                            </span>
                        </div>

                        {isLoading ? (
                            <p className="text-sm text-gray-500">Loading descendants...</p>
                        ) : profiles.length === 0 ? (
                            <p className="text-sm text-gray-500">
                                No descendants loaded yet.
                            </p>
                        ) : (
                            <ul className="grid gap-3 md:grid-cols-2">
                                {profiles.map((profile) => (
                                    <li
                                        key={profile.id}
                                        className="rounded-md border border-gray-200 p-4"
                                    >
                                        <p className="font-medium text-gray-900">
                                            {profile.full_name}
                                        </p>
                                        <p className="mt-1 text-sm text-gray-500">
                                            Gender: {profile.gender || 'n/a'}
                                        </p>
                                        <p className="mt-1 break-all text-xs text-gray-500">
                                            Node: {profile.graph_node_id}
                                        </p>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
