import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useState } from 'react';

export default function ProfileManagement({ profileId }) {
    const [form, setForm] = useState({
        full_name: '',
        nickname: '',
        bio: '',
        phone: '',
        address: '',
    });
    const [isLoading, setIsLoading] = useState(true);
    const [isSaving, setIsSaving] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

    const applyProfileToForm = (profile) => {
        setForm({
            full_name: profile?.full_name ?? '',
            nickname: profile?.nickname ?? '',
            bio: profile?.bio ?? '',
            phone: profile?.phone ?? '',
            address: profile?.address ?? '',
        });
    };

    const loadProfile = () => {
        if (!profileId) {
            setError('No linked profile found for your account.');
            setIsLoading(false);
            return;
        }

        setError('');
        setSuccess('');
        setIsLoading(true);

        axios
            .get(`/api/profiles/${profileId}`)
            .then((response) => {
                const profile = response?.data?.data ?? response?.data;
                applyProfileToForm(profile || {});
            })
            .catch((requestError) => {
                const message =
                    requestError?.response?.data?.message ||
                    'Unable to load profile data.';
                setError(message);
            })
            .finally(() => {
                setIsLoading(false);
            });
    };

    useEffect(() => {
        loadProfile();
    }, [profileId]);

    const handleChange = (field, value) => {
        setForm((previousForm) => ({
            ...previousForm,
            [field]: value,
        }));
    };

    const saveProfile = (event) => {
        event.preventDefault();

        if (!profileId) {
            return;
        }

        setError('');
        setSuccess('');
        setIsSaving(true);

        axios
            .patch(`/api/profiles/${profileId}`, form)
            .then((response) => {
                const profile = response?.data?.data ?? response?.data;
                applyProfileToForm(profile || {});
                setSuccess('Profile updated successfully.');
            })
            .catch((requestError) => {
                const validationErrors = requestError?.response?.data?.errors;
                if (validationErrors) {
                    const firstError = Object.values(validationErrors)[0]?.[0];
                    setError(firstError || 'Unable to update profile.');
                    return;
                }

                const message =
                    requestError?.response?.data?.message ||
                    'Unable to update profile.';
                setError(message);
            })
            .finally(() => {
                setIsSaving(false);
            });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Profile Management
                </h2>
            }
        >
            <Head title="Profile Management" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-6 shadow-sm">
                        {isLoading ? (
                            <p className="text-sm text-gray-500">Loading profile...</p>
                        ) : (
                            <form onSubmit={saveProfile} className="space-y-4">
                                <div>
                                    <label
                                        htmlFor="full_name"
                                        className="block text-sm font-medium text-gray-700"
                                    >
                                        Full name
                                    </label>
                                    <input
                                        id="full_name"
                                        type="text"
                                        value={form.full_name}
                                        onChange={(event) =>
                                            handleChange(
                                                'full_name',
                                                event.target.value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label
                                        htmlFor="nickname"
                                        className="block text-sm font-medium text-gray-700"
                                    >
                                        Nickname
                                    </label>
                                    <input
                                        id="nickname"
                                        type="text"
                                        value={form.nickname}
                                        onChange={(event) =>
                                            handleChange(
                                                'nickname',
                                                event.target.value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        htmlFor="phone"
                                        className="block text-sm font-medium text-gray-700"
                                    >
                                        Phone
                                    </label>
                                    <input
                                        id="phone"
                                        type="text"
                                        value={form.phone}
                                        onChange={(event) =>
                                            handleChange(
                                                'phone',
                                                event.target.value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        htmlFor="address"
                                        className="block text-sm font-medium text-gray-700"
                                    >
                                        Address
                                    </label>
                                    <textarea
                                        id="address"
                                        value={form.address}
                                        onChange={(event) =>
                                            handleChange(
                                                'address',
                                                event.target.value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        rows={2}
                                    />
                                </div>

                                <div>
                                    <label
                                        htmlFor="bio"
                                        className="block text-sm font-medium text-gray-700"
                                    >
                                        Bio
                                    </label>
                                    <textarea
                                        id="bio"
                                        value={form.bio}
                                        onChange={(event) =>
                                            handleChange('bio', event.target.value)
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        rows={4}
                                    />
                                </div>

                                {error && (
                                    <p className="text-sm text-red-600">{error}</p>
                                )}
                                {success && (
                                    <p className="text-sm text-green-600">{success}</p>
                                )}

                                <div className="flex items-center gap-3">
                                    <button
                                        type="submit"
                                        disabled={isSaving || !profileId}
                                        className="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {isSaving ? 'Saving...' : 'Save Profile'}
                                    </button>
                                    <button
                                        type="button"
                                        onClick={loadProfile}
                                        disabled={isSaving || !profileId}
                                        className="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        Reload
                                    </button>
                                </div>
                            </form>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
