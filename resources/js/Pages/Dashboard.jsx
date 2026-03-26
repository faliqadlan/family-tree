import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useState } from 'react';

export default function Dashboard({ stats }) {
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
                    Dashboard
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
                                Pending Access Requests
                            </p>
                            <p className="mt-2 text-3xl font-semibold text-gray-900">
                                {stats?.pendingAccessRequests ?? 0}
                            </p>
                        </div>
                    </div>

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
