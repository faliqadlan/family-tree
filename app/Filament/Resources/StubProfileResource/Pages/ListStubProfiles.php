<?php

namespace App\Filament\Resources\StubProfileResource\Pages;

use App\Filament\Resources\StubProfileResource;
use App\Jobs\ImportStubProfilesJob;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

class ListStubProfiles extends ListRecords
{
    protected static string $resource = StubProfileResource::class;

    /** Maximum accepted file size for imports (5 MB). */
    private const MAX_IMPORT_BYTES = 5 * 1024 * 1024;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('Download CSV Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
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

                    $exampleRow = [
                        'Ahmad bin Abdullah',
                        'Ahmad',
                        'male',
                        '1920',
                        '1985',
                        'Kuala Lumpur',
                        'Abdullah bin Ibrahim',
                        'Fatimah binti Ismail',
                        'true',
                        'Paternal grandfather',
                    ];

                    $csv = implode(',', $headers) . "\n" . implode(',', $exampleRow) . "\n";

                    return response()->streamDownload(
                        fn() => print($csv),
                        'family_import_template.csv',
                        ['Content-Type' => 'text/csv']
                    );
                }),

            Action::make('importCsv')
                ->label('Import CSV / JSON')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('file')
                        ->label('Upload File (CSV, JSON, or YAML)')
                        ->acceptedFileTypes(['text/csv', 'application/json', 'application/x-yaml', 'text/yaml', 'text/plain'])
                        ->maxSize(5120) // 5 MB in KB
                        ->required()
                        ->disk('local')
                        ->directory('stub-imports'),
                ])
                ->action(function (array $data): void {
                    $relativePath = $data['file'];
                    $path         = Storage::disk('local')->path($relativePath);

                    if (! file_exists($path)) {
                        Notification::make()
                            ->title('Uploaded file could not be found.')
                            ->danger()
                            ->send();

                        return;
                    }

                    if (filesize($path) > self::MAX_IMPORT_BYTES) {
                        Notification::make()
                            ->title('File is too large. Maximum allowed size is 5 MB.')
                            ->danger()
                            ->send();

                        return;
                    }

                    ['rows' => $rows, 'skipped' => $skipped] = static::parseImportFile($path, $relativePath);

                    if ($skipped > 0) {
                        Log::warning("ImportStubProfiles: {$skipped} malformed row(s) skipped during file parse.", [
                            'file' => $relativePath,
                        ]);
                    }

                    if (empty($rows)) {
                        Notification::make()
                            ->title('No valid rows found in the uploaded file.')
                            ->warning()
                            ->send();

                        return;
                    }

                    ImportStubProfilesJob::dispatch($rows);

                    $body = count($rows) . ' record(s) will be processed in the background.';
                    if ($skipped > 0) {
                        $body .= " ({$skipped} malformed row(s) were skipped.)";
                    }

                    Notification::make()
                        ->title('Import queued successfully!')
                        ->body($body)
                        ->success()
                        ->send();
                }),

            \Filament\Actions\CreateAction::make(),
        ];
    }

    /**
     * Parse a CSV, JSON, or YAML file and return valid rows and skipped count.
     *
     * @return array{rows: array<int, array<string, mixed>>, skipped: int}
     */
    public static function parseImportFile(string $path, string $filename): array
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($ext === 'json') {
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            return [
                'rows'    => is_array($decoded) ? $decoded : [],
                'skipped' => 0,
            ];
        }

        if (in_array($ext, ['yaml', 'yml'], true)) {
            $content = file_get_contents($path);
            $decoded = Yaml::parse($content);

            return [
                'rows'    => is_array($decoded) ? $decoded : [],
                'skipped' => 0,
            ];
        }

        // Default: treat as CSV
        $rows    = [];
        $skipped = 0;

        if (($handle = fopen($path, 'r')) === false) {
            return ['rows' => [], 'skipped' => 0];
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);

            return ['rows' => [], 'skipped' => 0];
        }

        $headers     = array_map('trim', $headers);
        $columnCount = count($headers);

        while (($line = fgetcsv($handle)) !== false) {
            if (count($line) !== $columnCount) {
                $skipped++;
                continue;
            }
            $rows[] = array_combine($headers, array_map('trim', $line));
        }

        fclose($handle);

        return ['rows' => $rows, 'skipped' => $skipped];
    }
}
