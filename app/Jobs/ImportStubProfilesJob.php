<?php

namespace App\Jobs;

use App\Enums\GenderOptions;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportStubProfilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function __construct(protected array $rows) {}

    public function handle(): void
    {
        foreach ($this->rows as $index => $row) {
            $name = trim($row['name'] ?? '');
            if (empty($name)) {
                Log::warning('ImportStubProfilesJob: skipping row ' . ($index + 1) . ' — missing name.', ['row' => $row]);
                continue;
            }

            $user = User::create([
                'name'         => $name,
                'email'        => null,
                'password'     => null,
                'role'         => 'user',
                'is_stub'      => true,
                'is_deceased'  => filter_var($row['is_deceased'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ]);

            // UserObserver creates the base profile + Neo4j node.
            // Update the profile with additional fields from the import row.
            $profile = $user->profile()->first();

            if (! $profile) {
                Log::warning('ImportStubProfilesJob: profile missing after user creation.', [
                    'user_id' => $user->id,
                    'name' => $name,
                ]);
                continue;
            }

            $profile->fill([
                'full_name'       => $name,
                'nickname'        => $row['nickname'] ?? null,
                'gender'          => in_array($row['gender'] ?? '', GenderOptions::VALUES) ? $row['gender'] : null,
                'date_of_birth'   => $this->parseDate($row['birth_year'] ?? null),
                'date_of_death'   => $this->parseDate($row['death_year'] ?? null),
                'place_of_birth'  => $row['place_of_birth'] ?? null,
                'father_name'     => $row['father_name'] ?? null,
                'mother_name'     => $row['mother_name'] ?? null,
                'bio'             => $row['bio'] ?? null,
            ]);

            $profile->save();
        }
    }

    private function parseDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Accept full date (YYYY-MM-DD) or just a year (YYYY)
        if (preg_match('/^\d{4}$/', trim($value))) {
            return trim($value) . '-01-01';
        }

        return trim($value) ?: null;
    }
}
