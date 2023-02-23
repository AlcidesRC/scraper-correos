<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Correos\Scraper as Correos;

set_time_limit(0);

$provinceId          = $argv[1] ?? Correos::RANGE_PROVINCES_MIN;
$postalCodesRangeMin = $argv[2] ?? Correos::RANGE_POSTAL_CODES_MIN;
$postalCodesRangeMax = $argv[3] ?? Correos::RANGE_POSTAL_CODES_MAX;

echo "Processing [ {$provinceId} ] ... ";

(new Correos((int) $provinceId))->process((int) $postalCodesRangeMin, (int) $postalCodesRangeMax);

echo PHP_EOL;
