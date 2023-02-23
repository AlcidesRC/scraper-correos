<?php

declare(strict_types=1);

namespace App\Correos;

use App\Misc\CSVFile;
use App\Misc\IntRange;
use App\Misc\RandomUserAgent;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\RequestOptions;
use GuzzleRetry\GuzzleRetryMiddleware;

final class Scraper
{
    private const BASE_ENDPOINT = '/digital-services/searchengines/api/v1/suggestions?text=';
    private const BASE_URI = 'https://api1.correos.es/';

    public const RANGE_PROVINCES_MIN    = 1;
    public const RANGE_PROVINCES_MAX    = 52;

    public const RANGE_POSTAL_CODES_MIN = 1;
    public const RANGE_POSTAL_CODES_MAX = 999;

    private const CONCURRENCY = 18;

    private Client $client;

    private array $progressThesholds;

    private int $province;

    private IntRange $rangePostalCodes;
    private IntRange $rangeProvinces;

    public function __construct(int $province = self::RANGE_PROVINCES_MIN)
    {
        // Validate input

        $this->rangeProvinces = new IntRange(self::RANGE_PROVINCES_MIN, self::RANGE_PROVINCES_MAX);

        if (! $this->rangeProvinces->isInRange($province)) {
            throw new \Exception('Province is not in valid range', 1);
        }

        $this->rangePostalCodes = new IntRange(self::RANGE_POSTAL_CODES_MIN, self::RANGE_POSTAL_CODES_MAX);

        // Initialize some properties

        $this->progressThesholds = [
            0   => false,
            25  => false,
            50  => false,
            75  => false,
            100 => false,
        ];

        $this->province = $province;

        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory());

        $this->client = new Client([
            // Standard Guzzle options
            'base_uri'              => self::BASE_URI,
            'handler'               => $stack,
            RequestOptions::DELAY   => 300,
            RequestOptions::TIMEOUT => 0,
            RequestOptions::COOKIES => true,
            RequestOptions::HEADERS => [
                'User-Agent' => (new RandomUserAgent())(),
            ],

            // Retry options
            'max_retry_attempts'         => 3,
            'max_allowable_timeout_secs' => 10,
            'retry_on_status'            => [429, 503, 500],
            'default_retry_multiplier'   => 2.5,
        ]);
    }

    /**
     * Perform concurrent calls to remote endpoint to check all posible postal
     * codes in a province
     */
    public function process(int $start = self::RANGE_POSTAL_CODES_MIN, int $end = self::RANGE_POSTAL_CODES_MAX): void
    {
        if (! $this->rangePostalCodes->isInRange($start) || ! $this->rangePostalCodes->isInRange($end)) {
            throw new \Exception('Provided range is not valid', 1);
        }

        $file = new CSVFile("/output/province-{$this->normalizeProvince()}.csv");

        if ($start === self::RANGE_POSTAL_CODES_MIN) {
            $file->create();
        }

        // Split the whole list of postal codes into blocks of concurrency levels
        $chunks = array_chunk(
            $this->generatePostalCodesInRange(new IntRange($start, $end)),
            self::CONCURRENCY
        );

        $totalChuncks = count($chunks);

        foreach ($chunks as $current => $postalCodes) {
            $file->save(
                $this->prepareLines(
                    $this->collectData($postalCodes)
                )
            );

            $this->showProgress($current, $totalChuncks);
        }
    }

    /**
     * Collects data from remote endpoint
     *
     * Example
     * ["text" => "01001, Vitoria, Álava, País Vasco, ESP", "latitude" => 42.849914208, "longitude" => -2.6721449999999]
     * ["text" => "01002, Vitoria, Álava, País Vasco, ESP", "latitude" => 42.852705001, "longitude" => -2.659999182]
     */
    private function collectData(array $postalCodes): array
    {
        $promises = array_map(function (string $postalCode) {
            return $this->client->getAsync(self::BASE_ENDPOINT . $postalCode);
        }, $postalCodes);

        $responses = Utils::settle(Utils::unwrap($promises))->wait();

        return array_filter(array_map(static function (array $response) {
            if ($response['value']->getStatusCode() === 200) {
                $json = json_decode($response['value']->getBody()->getContents(), true);

                return $json['suggestions']
                    ?? null;
            }
        }, (array) $responses));
    }

    /**
     * Generates an array with possible postal code options in a province
     *
     * Example:
     * [01001..01999]
     *
     * @return array<string>
     */
    private function generatePostalCodesInRange(IntRange $range): array
    {
        $prefix = $this->normalizeProvince();

        return $range->each(static function (int $value) use ($prefix) {
            return $prefix . str_pad((string) $value, 3, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Normalize the province ID as 2-digit number
     *
     * Example:
     * 01
     * 52
     */
    private function normalizeProvince(): string
    {
        return str_pad((string) $this->province, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Convert the extracted data into a desired CSV file line
     *
     * Example:
     * [01001, "01001, Vitoria, Álava, País Vasco, ESP", 42.849914208, -2.6721449999999]
     */
    private function prepareLines(array $data): array
    {
        $lines = [];

        foreach ($data as $entry) {
            $lines = array_merge($lines, array_map(static function (array $suggestion) {
                return [
                    explode(',', $suggestion['text'])[0],
                    $suggestion['text'],
                    $suggestion['latitude'],
                    $suggestion['longitude'],
                ];
            }, $entry));
        }

        return $lines;
    }

    /**
     * Print out the progress
     *
     * Example:
     * Processing [ 52 ] ... 0% 25% 50%
     */
    private function showProgress(int $current, int $totalChuncks): void
    {
        $progress = ceil($current * 100 / $totalChuncks);

        if ($progress >= 0 && ! $this->progressThesholds[0]) {
            echo "0% ";
            $this->progressThesholds[0] = true;
        } elseif ($progress >= 25 && ! $this->progressThesholds[25]) {
            echo "{$progress}% ";
            $this->progressThesholds[25] = true;
        } elseif ($progress >= 50 && ! $this->progressThesholds[50]) {
            echo "{$progress}% ";
            $this->progressThesholds[50] = true;
        } elseif ($progress >= 75 && ! $this->progressThesholds[75]) {
            echo "{$progress}% ";
            $this->progressThesholds[75] = true;
        } elseif ($progress >= 99 && ! $this->progressThesholds[100]) {
            echo "100% ";
            $this->progressThesholds[100] = true;
        }
    }
}
