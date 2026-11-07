<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/common/functions.php';
require_once __DIR__ . '/common/weather_queue.php';

try {
    $locations = require __DIR__ . '/common/weather_locations.php';
    ensureWeatherQueues(getDatabaseConnection(), $locations);
    fwrite(STDOUT, sprintf(
        "Weather queues updated for %d provinces at %s\n",
        count($locations),
        (new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
    ));
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    fwrite(STDERR, "Weather queue update failed: {$exception->getMessage()}\n");
    exit(1);
}
