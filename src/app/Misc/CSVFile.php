<?php

declare(strict_types=1);

namespace App\Misc;

final class CSVFile
{
    private const CSV_HEADERS = ['Postal Code', 'Text', 'Latitude', 'Longitude'];
    private const CSV_SEPARATOR = ';';

    public function __construct(
        private string $filepath
    ) {
    }

    /**
     * @param array $headers<int,string>
     */
    public function create(array $headers = self::CSV_HEADERS): void
    {
        $fp = fopen($this->filepath, 'w');

        if (count($headers)) {
            fputcsv($fp, $headers, self::CSV_SEPARATOR);
        }

        fclose($fp);
    }

    public function save(array $lines): void
    {
        if (! count($lines)) {
            return;
        }

        $fp = fopen($this->filepath, 'a+');

        array_map(static function ($line) use ($fp): void {
            fputcsv($fp, $line, self::CSV_SEPARATOR);
        }, $lines);

        fclose($fp);
    }
}
