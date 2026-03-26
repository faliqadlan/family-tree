import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useState } from 'react';

function displayMasked(value) {
    if (value === '***') {
        return '***';
    }

    if (value === null || value === undefined || value === '') {
        return 'Private';
    }

    return value;
}

function TreeNode({ node }) {
    const children = Array.isArray(node?.children) ? node.children : [];

    return (
        <li>
            <div className="rounded-md border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <p className="font-semibold text-gray-900 dark:text-gray-100">
                    {node.full_name || 'Unknown Member'}
                </p>
                <p className="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    Gender: {node.gender || 'n/a'}
                </p>
                <p className="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    Phone: {displayMasked(node.phone)}
                </p>
                <p className="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    Birth Date: {displayMasked(node.date_of_birth)}
                </p>
                <p className="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    Address: {displayMasked(node.address)}
                </p>
            </div>

            {children.length > 0 && (
                <ul className="ms-6 mt-3 space-y-3 border-s-2 border-gray-200 ps-4 dark:border-gray-700">
                    {children.map((child) => (
                        <TreeNode
                            key={child.id || `${node.id}-child-${child.full_name}`}
                            node={child}
                        />
                    ))}
                </ul>
            )}
        </li>
    );
}

export default function FamilyTree() {
    const [treeNodes, setTreeNodes] = useState([]);
    const [totalNodes, setTotalNodes] = useState(0);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState('');

    const loadTree = () => {
        setError('');
        setIsLoading(true);

        axios
            .get('/api/family-tree')
            .then((response) => {
                const payload = response?.data?.data ?? {};
                const nodes = Array.isArray(payload?.nodes) ? payload.nodes : [];

                setTreeNodes(nodes);
                setTotalNodes(Number(payload?.total_nodes ?? 0));
            })
            .catch((requestError) => {
                const message =
                    requestError?.response?.data?.message ||
                    'Unable to load family tree data.';
                setError(message);
                setTreeNodes([]);
                setTotalNodes(0);
            })
            .finally(() => {
                setIsLoading(false);
            });
    };

    useEffect(() => {
        loadTree();
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
                        <div className="flex items-center justify-between">
                            <p className="text-sm text-gray-600 dark:text-gray-300">
                                Hierarchical family tree loaded automatically.
                            </p>
                            <button
                                type="button"
                                onClick={loadTree}
                                className="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                            >
                                Reload Tree
                            </button>
                        </div>

                        {error && (
                            <p className="mt-4 text-sm text-red-600">{error}</p>
                        )}
                    </div>

                    <div className="rounded-lg bg-white p-6 shadow-sm">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Family Hierarchy
                            </h3>
                            <span className="text-sm text-gray-500">
                                {totalNodes} member(s)
                            </span>
                        </div>

                        {isLoading ? (
                            <p className="text-sm text-gray-500">Loading family tree...</p>
                        ) : treeNodes.length === 0 ? (
                            <p className="text-sm text-gray-500">
                                No family tree data found.
                            </p>
                        ) : (
                            <ul className="space-y-4">
                                {treeNodes.map((node) => (
                                    <TreeNode
                                        key={node.id || `root-${node.full_name}`}
                                        node={node}
                                    />
                                ))}
                            </ul>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
