<?php

namespace Database\Seeders;

use App\Jobs\ImportStubProfilesJob;
use App\Models\Event;
use App\Models\EventCommittee;
use App\Models\FinancialContribution;
use App\Models\Rsvp;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DemoFamilySeeder extends Seeder
{
    public function run(): void
    {
        $adminName = (string) env('ADMIN_NAME', 'admin');
        $adminEmail = (string) config('auth.super_admin_email', 'admin@example.com');
        $adminPassword = (string) env('ADMIN_PASSWORD', 'admin');

        $admin = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
                'is_stub' => false,
                'is_deceased' => false,
                'email_verified_at' => now(),
            ]
        );

        $users = [
            'fatimah' => User::updateOrCreate(
                ['email' => 'fatimah@silsilah.local'],
                [
                    'name' => 'Fatimah Yusuf',
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'is_stub' => false,
                    'is_deceased' => false,
                    'email_verified_at' => now(),
                ]
            ),
            'rahman' => User::updateOrCreate(
                ['email' => 'rahman@silsilah.local'],
                [
                    'name' => 'Rahman Yusuf',
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'is_stub' => false,
                    'is_deceased' => false,
                    'email_verified_at' => now(),
                ]
            ),
            'ali' => User::updateOrCreate(
                ['email' => 'ali@silsilah.local'],
                [
                    'name' => 'Ali Rahman',
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'is_stub' => false,
                    'is_deceased' => false,
                    'email_verified_at' => now(),
                ]
            ),
            'nurul' => User::updateOrCreate(
                ['email' => 'nurul@silsilah.local'],
                [
                    'name' => 'Nurul Rahman',
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'is_stub' => false,
                    'is_deceased' => false,
                    'email_verified_at' => now(),
                ]
            ),
            'zaid' => User::updateOrCreate(
                ['email' => 'zaid@silsilah.local'],
                [
                    'name' => 'Zaid Ahmad',
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'is_stub' => false,
                    'is_deceased' => false,
                    'email_verified_at' => now(),
                ]
            ),
            'faliq' => User::updateOrCreate(
                ['email' => 'faliqadlan67@gmail.com'],
                [
                    'name' => 'Faliq Adlan',
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'is_stub' => false,
                    'is_deceased' => false,
                    'email_verified_at' => now(),
                ]
            ),
        ];

        $this->syncProfile($admin, [
            'full_name' => 'Ahmad Yusuf',
            'gender' => 'male',
            'date_of_birth' => '1982-03-20',
            'place_of_birth' => 'Bandung',
            'father_name' => 'Haji Yusuf Karim',
            'mother_name' => 'Hajjah Maryam Saleh',
            'bio' => 'Koordinator silsilah keluarga besar.',
        ]);

        $this->syncProfile($users['fatimah'], [
            'full_name' => 'Fatimah Yusuf',
            'gender' => 'female',
            'date_of_birth' => '1986-06-12',
            'place_of_birth' => 'Bandung',
            'father_name' => 'Haji Yusuf Karim',
            'mother_name' => 'Hajjah Maryam Saleh',
        ]);

        $this->syncProfile($users['rahman'], [
            'full_name' => 'Rahman Yusuf',
            'gender' => 'male',
            'date_of_birth' => '1988-11-01',
            'place_of_birth' => 'Bandung',
            'father_name' => 'Haji Yusuf Karim',
            'mother_name' => 'Hajjah Maryam Saleh',
        ]);

        $this->syncProfile($users['ali'], [
            'full_name' => 'Ali Rahman',
            'gender' => 'male',
            'date_of_birth' => '2010-01-10',
            'place_of_birth' => 'Jakarta',
            'father_name' => 'Rahman Yusuf',
            'mother_name' => 'Nur Aisyah',
        ]);

        $this->syncProfile($users['nurul'], [
            'full_name' => 'Nurul Rahman',
            'gender' => 'female',
            'date_of_birth' => '2013-05-04',
            'place_of_birth' => 'Jakarta',
            'father_name' => 'Rahman Yusuf',
            'mother_name' => 'Nur Aisyah',
        ]);

        $this->syncProfile($users['zaid'], [
            'full_name' => 'Zaid Ahmad',
            'gender' => 'male',
            'date_of_birth' => '2016-09-08',
            'place_of_birth' => 'Depok',
            'father_name' => 'Ahmad Yusuf',
            'mother_name' => 'Lina Kartika',
        ]);

        $this->syncProfile($users['faliq'], [
            'full_name' => 'Faliq Adlan',
            'gender' => 'male',
            'date_of_birth' => '2014-02-17',
            'place_of_birth' => 'Jakarta',
            'father_name' => 'Rahman Yusuf',
            'mother_name' => 'Nur Aisyah',
            'bio' => 'Akun demo member untuk pengujian akses cabang keluarga.',
        ]);

        $importRows = [
            [
                'name' => 'Abdul Karim',
                'nickname' => 'Karim',
                'gender' => 'male',
                'birth_year' => '1915',
                'death_year' => '1980',
                'place_of_birth' => 'Cirebon',
                'father_name' => 'Ibrahim Karim',
                'mother_name' => 'Siti Rahmah',
                'is_deceased' => 'true',
                'bio' => 'Buyut dari garis ayah.',
            ],
            [
                'name' => 'Siti Aminah',
                'nickname' => 'Aminah',
                'gender' => 'female',
                'birth_year' => '1920',
                'death_year' => '1990',
                'place_of_birth' => 'Cirebon',
                'father_name' => 'Saleh Iskandar',
                'mother_name' => 'Zubaidah',
                'is_deceased' => 'true',
                'bio' => 'Buyut dari garis ibu.',
            ],
            [
                'name' => 'Haji Yusuf Karim',
                'nickname' => 'Pak Yusuf',
                'gender' => 'male',
                'birth_year' => '1948',
                'death_year' => '2020',
                'place_of_birth' => 'Bandung',
                'father_name' => 'Abdul Karim',
                'mother_name' => 'Siti Aminah',
                'is_deceased' => 'true',
                'bio' => 'Ayah dari Ahmad, Fatimah, dan Rahman.',
            ],
            [
                'name' => 'Hajjah Maryam Saleh',
                'nickname' => 'Bu Maryam',
                'gender' => 'female',
                'birth_year' => '1951',
                'death_year' => '2022',
                'place_of_birth' => 'Bandung',
                'father_name' => 'Saleh Iskandar',
                'mother_name' => 'Zubaidah',
                'is_deceased' => 'true',
                'bio' => 'Ibu dari Ahmad, Fatimah, dan Rahman.',
            ],
            [
                'name' => 'Nur Aisyah',
                'nickname' => 'Aisyah',
                'gender' => 'female',
                'birth_year' => '1990',
                'death_year' => '',
                'place_of_birth' => 'Bogor',
                'father_name' => 'Hadi Pranoto',
                'mother_name' => 'Rini Mardiyah',
                'is_deceased' => 'false',
                'bio' => 'Istri Rahman Yusuf.',
            ],
            [
                'name' => 'Lina Kartika',
                'nickname' => 'Lina',
                'gender' => 'female',
                'birth_year' => '1987',
                'death_year' => '',
                'place_of_birth' => 'Depok',
                'father_name' => 'Hadi Santoso',
                'mother_name' => 'Nani Sulastri',
                'is_deceased' => 'false',
                'bio' => 'Istri Ahmad Yusuf.',
            ],
        ];

        $this->storeImportTemplate($importRows);
        (new ImportStubProfilesJob($importRows))->handle();

        $event = Event::query()->updateOrCreate(
            ['name' => 'Halal bi Halal Keluarga Besar'],
            [
                'creator_id' => $admin->id,
                'description' => 'Silaturahmi keluarga lintas generasi setelah Idul Fitri.',
                'location' => 'Gedung Serbaguna Al-Ikhlas, Bandung',
                'starts_at' => Carbon::now()->addDays(14)->setHour(10)->setMinute(0),
                'ends_at' => Carbon::now()->addDays(14)->setHour(14)->setMinute(0),
                'status' => 'published',
                'ancestor_node_id' => $admin->profile?->graph_node_id,
                'invitation_depth' => 4,
            ]
        );

        EventCommittee::query()->updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $admin->id],
            ['role' => 'coordinator']
        );

        EventCommittee::query()->updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $users['fatimah']->id],
            ['role' => 'secretary']
        );

        EventCommittee::query()->updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $users['rahman']->id],
            ['role' => 'treasurer']
        );

        $rsvpPayloads = [
            ['user_id' => $admin->id, 'status' => 'attending', 'pax' => 4, 'note' => 'Bawa konsumsi ringan.'],
            ['user_id' => $users['fatimah']->id, 'status' => 'attending', 'pax' => 3, 'note' => null],
            ['user_id' => $users['rahman']->id, 'status' => 'attending', 'pax' => 4, 'note' => 'Datang dari Jakarta.'],
            ['user_id' => $users['ali']->id, 'status' => 'maybe', 'pax' => 1, 'note' => 'Menunggu jadwal sekolah.'],
            ['user_id' => $users['nurul']->id, 'status' => 'pending', 'pax' => 1, 'note' => null],
            ['user_id' => $users['zaid']->id, 'status' => 'not_attending', 'pax' => 1, 'note' => 'Sedang ujian.'],
            ['user_id' => $users['faliq']->id, 'status' => 'attending', 'pax' => 2, 'note' => 'Datang bersama orang tua.'],
        ];

        foreach ($rsvpPayloads as $payload) {
            Rsvp::query()->updateOrCreate(
                ['event_id' => $event->id, 'user_id' => $payload['user_id']],
                [
                    'status' => $payload['status'],
                    'pax' => $payload['pax'],
                    'note' => $payload['note'],
                ]
            );
        }

        FinancialContribution::query()->updateOrCreate(
            ['event_id' => $event->id, 'contributor_id' => $admin->id],
            [
                'amount' => 750000,
                'currency' => 'IDR',
                'payment_method' => 'transfer',
                'status' => 'confirmed',
                'reference_number' => 'HBH-ADM-001',
                'note' => 'Donasi konsumsi dan sewa tempat.',
                'confirmed_by' => $users['rahman']->id,
                'confirmed_at' => now(),
            ]
        );

        FinancialContribution::query()->updateOrCreate(
            ['event_id' => $event->id, 'contributor_id' => $users['fatimah']->id],
            [
                'amount' => 300000,
                'currency' => 'IDR',
                'payment_method' => 'cash',
                'status' => 'pending',
                'reference_number' => 'HBH-FAT-002',
                'note' => 'Konfirmasi menyusul.',
                'confirmed_by' => null,
                'confirmed_at' => null,
            ]
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function storeImportTemplate(array $rows): void
    {
        $headers = [
            'name',
            'nickname',
            'gender',
            'birth_year',
            'death_year',
            'place_of_birth',
            'father_name',
            'mother_name',
            'is_deceased',
            'bio',
        ];

        $csv = implode(',', $headers) . "\n";

        foreach ($rows as $row) {
            $csv .= implode(',', array_map(
                static fn($header): string => '"' . str_replace('"', '""', (string) ($row[$header] ?? '')) . '"',
                $headers
            )) . "\n";
        }

        Storage::disk('local')->put('stub-imports/family_import_template.csv', $csv);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function syncProfile(User $user, array $payload): void
    {
        $profile = $user->profile()->first();

        if (! $profile) {
            return;
        }

        $profile->fill($payload);
        $profile->save();
    }
}
