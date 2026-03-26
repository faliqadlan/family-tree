<?php

namespace Tests\Unit;

use App\Filament\Resources\StubProfileResource\Pages\ListStubProfiles;
use App\Jobs\ImportStubProfilesJob;
use App\Models\Profile;
use App\Models\User;
use App\Repositories\Contracts\GraphRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StubProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Prevent real Neo4j calls during tests
        $this->mock(GraphRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('ensurePersonNode')->andReturn([]);
            $mock->shouldReceive('linkPersons')->andReturn(null);
            $mock->shouldReceive('getDescendantUuids')->andReturn([]);
            $mock->shouldReceive('removePersonNode')->andReturn(null);
        });
    }

    public function test_stub_user_is_created_with_correct_flags(): void
    {
        $user = User::create([
            'name'        => 'Ahmad bin Abdullah',
            'email'       => null,
            'password'    => null,
            'role'        => 'user',
            'is_stub'     => true,
            'is_deceased' => true,
        ]);

        $this->assertTrue($user->is_stub);
        $this->assertTrue($user->is_deceased);
        $this->assertNull($user->email);
    }

    public function test_stub_user_observer_creates_neo4j_node_and_profile(): void
    {
        $user = User::create([
            'name'        => 'Fatimah binti Ismail',
            'email'       => null,
            'password'    => null,
            'role'        => 'user',
            'is_stub'     => true,
            'is_deceased' => false,
        ]);

        $this->assertNotNull($user->profile);
        $this->assertNotNull($user->profile->graph_node_id);
        $this->assertEquals('Fatimah binti Ismail', $user->profile->full_name);
    }

    public function test_import_stub_profiles_job_creates_users_and_profiles(): void
    {
        $rows = [
            [
                'name'           => 'Ismail bin Yusof',
                'nickname'       => 'Pak Mail',
                'gender'         => 'male',
                'birth_year'     => '1910',
                'death_year'     => '1975',
                'place_of_birth' => 'Johor Bahru',
                'father_name'    => '',
                'mother_name'    => '',
                'is_deceased'    => 'true',
                'bio'            => 'Grandfather',
            ],
            [
                'name'        => 'Zainab binti Ahmad',
                'gender'      => 'female',
                'birth_year'  => '1915',
                'is_deceased' => 'true',
            ],
        ];

        (new ImportStubProfilesJob($rows))->handle();

        $this->assertDatabaseHas('users', [
            'name'     => 'Ismail bin Yusof',
            'is_stub'  => true,
            'is_deceased' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'name'    => 'Zainab binti Ahmad',
            'is_stub' => true,
        ]);

        $ismail = User::where('name', 'Ismail bin Yusof')->first();
        $this->assertNotNull($ismail->profile);
        $this->assertEquals('1910-01-01', $ismail->profile->date_of_birth->toDateString());
        $this->assertEquals('1975-01-01', $ismail->profile->date_of_death->toDateString());
        $this->assertEquals('Johor Bahru', $ismail->profile->place_of_birth);
        $this->assertEquals('male', $ismail->profile->gender);
    }

    public function test_import_job_skips_rows_with_empty_name(): void
    {
        $rows = [
            ['name' => '', 'birth_year' => '1900'],
            ['name' => '   ', 'gender' => 'male'],
        ];

        (new ImportStubProfilesJob($rows))->handle();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_import_stub_profiles_job_is_dispatched(): void
    {
        Queue::fake();

        $rows = [['name' => 'Test Ancestor', 'is_deceased' => 'true']];

        ImportStubProfilesJob::dispatch($rows);

        Queue::assertPushed(ImportStubProfilesJob::class);
    }

    public function test_parse_import_file_csv(): void
    {
        $csvContent = "name,gender,birth_year\nAhmad,male,1920\nFatimah,female,1925\n";
        $tmpFile    = tempnam(sys_get_temp_dir(), 'test_csv_') . '.csv';
        file_put_contents($tmpFile, $csvContent);

        $result = ListStubProfiles::parseImportFile($tmpFile, 'family.csv');
        $rows   = $result['rows'];

        $this->assertCount(2, $rows);
        $this->assertEquals('Ahmad', $rows[0]['name']);
        $this->assertEquals('male', $rows[0]['gender']);
        $this->assertEquals('female', $rows[1]['gender']);
        $this->assertEquals(0, $result['skipped']);

        unlink($tmpFile);
    }

    public function test_parse_import_file_json(): void
    {
        $data    = [
            ['name' => 'Yusof', 'gender' => 'male', 'birth_year' => '1900'],
        ];
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_json_') . '.json';
        file_put_contents($tmpFile, json_encode($data));

        $result = ListStubProfiles::parseImportFile($tmpFile, 'family.json');
        $rows   = $result['rows'];

        $this->assertCount(1, $rows);
        $this->assertEquals('Yusof', $rows[0]['name']);

        unlink($tmpFile);
    }

    public function test_parse_import_file_reports_skipped_rows(): void
    {
        // Row 2 has fewer columns than headers — should be counted as skipped
        $csvContent = "name,gender,birth_year\nAhmad,male,1920\nFatimah\n";
        $tmpFile    = tempnam(sys_get_temp_dir(), 'test_csv_skip_') . '.csv';
        file_put_contents($tmpFile, $csvContent);

        $result = ListStubProfiles::parseImportFile($tmpFile, 'family.csv');

        $this->assertCount(1, $result['rows']);
        $this->assertEquals(1, $result['skipped']);

        unlink($tmpFile);
    }

    public function test_non_stub_users_are_excluded_from_stub_query(): void
    {
        User::factory()->create(['is_stub' => false]);
        User::create([
            'name'     => 'Stub Person',
            'email'    => null,
            'password' => null,
            'role'     => 'user',
            'is_stub'  => true,
        ]);

        $stubs = User::where('is_stub', true)->get();
        $this->assertCount(1, $stubs);
        $this->assertEquals('Stub Person', $stubs->first()->name);
    }
}
