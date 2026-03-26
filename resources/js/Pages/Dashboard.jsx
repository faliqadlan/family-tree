import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useState } from 'react';

export default function Dashboard({ stats, isSuperAdmin }) {
    const [events, setEvents] = useState([]);
    const [loadingEvents, setLoadingEvents] = useState(true);
    const [eventsError, setEventsError] = useState('');

    useEffect(() => {
        let isActive = true;

        axios
            .get('/api/events')
            .then((response) => {
                if (!isActive) {
                    return;
                }

                const items = response?.data?.data ?? [];
                setEvents(Array.isArray(items) ? items.slice(0, 5) : []);
            })
            .catch(() => {
                if (!isActive) {
                    return;
                }

                setEventsError('Unable to load events from API right now.');
            })
            .finally(() => {
                if (isActive) {
                    setLoadingEvents(false);
                }
            });

        return () => {
            isActive = false;
        };
    }, []);

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {isSuperAdmin ? 'Admin Dashboard' : 'Member Dashboard'}
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 grid gap-4 md:grid-cols-3">
                        <div className="rounded-lg bg-white p-6 shadow-sm">
                            <p className="text-sm text-gray-500">Total Profiles</p>
                            <p className="mt-2 text-3xl font-semibold text-gray-900">
                                {stats?.totalProfiles ?? 0}
                            </p>
                        </div>
                        <div className="rounded-lg bg-white p-6 shadow-sm">
                            <p className="text-sm text-gray-500">Total Events</p>
                            <p className="mt-2 text-3xl font-semibold text-gray-900">
                                {stats?.totalEvents ?? 0}
                            </p>
                        </div>
                        <div className="rounded-lg bg-white p-6 shadow-sm">
                            <p className="text-sm text-gray-500">
                                {isSuperAdmin
                                    ? 'All Access Requests'
                                    : 'Pending Access Requests'}
                            </p>
                            <p className="mt-2 text-3xl font-semibold text-gray-900">
                                {isSuperAdmin
                                    ? (stats?.allAccessRequests ?? 0)
                                    : (stats?.pendingAccessRequests ?? 0)}
                            </p>
                        </div>
                    </div>

                    {isSuperAdmin && (
                        <div className="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-6">
                            <h3 className="text-lg font-semibold text-emerald-900">
                                Super Admin Access
                            </h3>
                            <p className="mt-2 text-sm text-emerald-800">
                                You have full control of users, profiles, access requests, events, RSVPs, contributions, and family-tree branches.
                            </p>
                            <p className="mt-3 text-sm text-emerald-900">
                                Total Users: <span className="font-semibold">{stats?.totalUsers ?? 0}</span>
                            </p>
                            <a
                                href={route('admin.tools')}
                                className="mt-4 inline-flex items-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600"
                            >
                                Open Admin Tools
                            </a>
                        </div>
                    )}

                    {!isSuperAdmin && (
                        <div className="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-6">
                            <h3 className="text-lg font-semibold text-blue-900">
                                Member Access Scope
                            </h3>
                            <p className="mt-2 text-sm text-blue-800">
                                You can manage your own profile, view your permitted family branch, RSVP to events, contribute funds, and submit access requests.
                            </p>
                        </div>
                    )}

                    <div className="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div className="border-b border-gray-200 p-6">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Recent Events (API)
                            </h3>
                        </div>

                        <div className="p-6">
                            {loadingEvents ? (
                                <p className="text-sm text-gray-500">
                                    Loading events...
                                </p>
                            ) : eventsError ? (
                                <p className="text-sm text-red-600">{eventsError}</p>
                            ) : events.length === 0 ? (
                                <p className="text-sm text-gray-500">
                                    No events found.
                                </p>
                            ) : (
                                <ul className="space-y-3">
                                    {events.map((event) => (
                                        <li
                                            key={event.id}
                                            className="rounded-md border border-gray-200 p-4"
                                        >
                                            <p className="font-medium text-gray-900">
                                                {event.name}
                                            </p>
                                            <p className="mt-1 text-sm text-gray-500">
                                                Status: {event.status}
                                            </p>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
