<?php

namespace App\Support;

use Symfony\Component\Yaml\Yaml;

class StubProfileImportParser
{
    /**
     * Parse a CSV, JSON, or YAML file and return valid rows and skipped count.
     *
     * @return array{rows: array<int, array<string, mixed>>, skipped: int}
     */
    public static function parse(string $path, string $filename): array
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($ext === 'json') {
            $content = file_get_contents($path);
            $decoded = json_decode($content ?: '', true);

            return [
                'rows' => is_array($decoded) ? $decoded : [],
                'skipped' => 0,
            ];
        }

        if (in_array($ext, ['yaml', 'yml'], true)) {
            $content = file_get_contents($path);
            $decoded = Yaml::parse($content ?: '');

            return [
                'rows' => is_array($decoded) ? $decoded : [],
                'skipped' => 0,
            ];
        }

        $rows = [];
        $skipped = 0;

        if (($handle = fopen($path, 'r')) === false) {
            return ['rows' => [], 'skipped' => 0];
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);

            return ['rows' => [], 'skipped' => 0];
        }

        $headers = array_map('trim', $headers);
        $columnCount = count($headers);

        while (($line = fgetcsv($handle)) !== false) {
            if (count($line) !== $columnCount) {
                $skipped++;
                continue;
            }

            $combined = array_combine($headers, array_map('trim', $line));
            if ($combined === false) {
                $skipped++;
                continue;
            }

            $rows[] = $combined;
        }

        fclose($handle);

        return ['rows' => $rows, 'skipped' => $skipped];
    }
}
