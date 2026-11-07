<?php
require_once __DIR__ . '/common/functions.php';
require_once __DIR__ . '/common/weather_queue.php';
require_once __DIR__ . '/common/administrative_locations.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$locations = require __DIR__ . '/common/weather_locations.php';
$locationList = [];

foreach ($locations as $key => $location) {
    $locationList[] = [
        'key' => $key,
        'name' => $location['name'],
        'latitude' => $location['latitude'],
        'longitude' => $location['longitude'],
    ];
}

if (($_GET['action'] ?? '') === 'locations') {
    $preferredLocationKey = 'ha-noi';
    $userAddress = $_SESSION['user']['address'] ?? null;
    if (is_string($userAddress)) {
        $normalizedUserAddress = normalizeLocationName($userAddress);
        if (strpos($userAddress, ',') !== false) {
            foreach (getAdministrativeWards() as $wardKey => $ward) {
                if (normalizeLocationName($ward['address']) === $normalizedUserAddress) {
                    $preferredLocationKey = $wardKey;
                    break;
                }
            }
        }

        $legacyAddressToCurrentProvince = [
            'bà rịa - vũng tàu' => 'Hồ Chí Minh',
            'bạc liêu' => 'Cà Mau',
            'bắc giang' => 'Bắc Ninh',
            'bắc kạn' => 'Thái Nguyên',
            'bến tre' => 'Vĩnh Long',
            'bình định' => 'Gia Lai',
            'bình dương' => 'Hồ Chí Minh',
            'bình phước' => 'Đồng Nai',
            'bình thuận' => 'Lâm Đồng',
            'đắk nông' => 'Lâm Đồng',
            'hà giang' => 'Tuyên Quang',
            'hà nam' => 'Ninh Bình',
            'hải dương' => 'Hải Phòng',
            'hậu giang' => 'Cần Thơ',
            'hòa bình' => 'Phú Thọ',
            'kiên giang' => 'An Giang',
            'kon tum' => 'Quảng Ngãi',
            'long an' => 'Tây Ninh',
            'nam định' => 'Ninh Bình',
            'ninh thuận' => 'Khánh Hòa',
            'phú yên' => 'Đắk Lắk',
            'quảng bình' => 'Quảng Trị',
            'quảng nam' => 'Đà Nẵng',
            'sóc trăng' => 'Cần Thơ',
            'thái bình' => 'Hưng Yên',
            'tiền giang' => 'Đồng Tháp',
            'tp. hồ chí minh' => 'Hồ Chí Minh',
            'trà vinh' => 'Vĩnh Long',
            'vĩnh phúc' => 'Phú Thọ',
            'yên bái' => 'Lào Cai',
        ];
        if ($preferredLocationKey === 'ha-noi') {
            $normalizedUserAddress = normalizeLocationName(
                $legacyAddressToCurrentProvince[$normalizedUserAddress] ?? $userAddress
            );

            foreach ($locations as $key => $location) {
                if (normalizeLocationName($location['name']) === $normalizedUserAddress) {
                    $preferredLocationKey = $key;
                    break;
                }
            }
        }
    }

    echo json_encode([
        'locations' => $locationList,
        'preferred_location_key' => $preferredLocationKey,
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

if (($_GET['action'] ?? '') === 'wards') {
    $provinceKeysByName = [];
    foreach ($locations as $provinceKey => $location) {
        $provinceKeysByName[normalizeLocationName($location['name'])] = $provinceKey;
    }

    $wardList = [];
    foreach (getAdministrativeWards() as $ward) {
        $provinceKey = $provinceKeysByName[normalizeLocationName($ward['province'])] ?? null;
        if ($provinceKey === null) {
            throw new RuntimeException('Không tìm thấy tỉnh/thành phố của một xã, phường.');
        }

        $wardList[] = [
            'key' => $ward['key'],
            'name' => $ward['name'],
            'address' => $ward['address'],
            'province_key' => $provinceKey,
        ];
    }

    echo json_encode(['wards' => $wardList], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

$locationKey = $_GET['location'] ?? 'ha-noi';
if (!is_string($locationKey)) {
    http_response_code(400);
    echo json_encode(['error' => 'Vị trí không hợp lệ.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$ward = strpos($locationKey, 'ward-') === 0
    ? (getAdministrativeWards()[$locationKey] ?? null)
    : null;
if (!isset($locations[$locationKey]) && $ward === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Vị trí không hợp lệ.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = getDatabaseConnection();
    $sourceStatus = 'online';
    try {
        ensureWeatherQueues($db, $locations);
    } catch (WeatherProviderException $exception) {
        error_log($exception->getMessage());
        $sourceStatus = 'stale';
    }

    if ($ward === null) {
        $location = $locations[$locationKey];
        $sourceKey = $locationKey;
        $sourceLocation = $location;
        $fallback = null;
    } else {
        $location = $ward;
        $nearest = findNearestWeatherLocation(
            $ward['latitude'],
            $ward['longitude'],
            $locations
        );
        $sourceKey = $nearest['key'];
        $sourceLocation = $nearest['location'];
        $fallback = [
            'name' => $sourceLocation['name'],
            'distance_km' => $nearest['distance_km'],
        ];
    }

    $readingsStatement = $db->prepare(
        'SELECT recorded_at, temperature, humidity, weather_type
         FROM weather_minute_queue
         WHERE location = :location
         ORDER BY recorded_at DESC
         LIMIT 720'
    );
    $readingsStatement->execute([':location' => $sourceLocation['name']]);
    $readings = array_reverse($readingsStatement->fetchAll());
    $latestReading = $readings[count($readings) - 1] ?? null;

    if ($latestReading === null) {
        throw new RuntimeException('Hàng đợi chưa có dữ liệu thời tiết.');
    }

    echo json_encode([
        'location' => [
            'key' => $locationKey,
            'name' => ($ward === null ? $location['name'] : $ward['address']) . ', Việt Nam',
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
            'source_key' => $sourceKey,
            'province_key' => $ward['province_key'] ?? $sourceKey,
            'fallback' => $fallback,
        ],
        'temperature' => (float) $latestReading['temperature'],
        'humidity' => (float) $latestReading['humidity'],
        'weather_type' => $latestReading['weather_type'],
        'recorded_at' => $latestReading['recorded_at'],
        'source_status' => $sourceStatus,
        'readings' => array_map(static function (array $reading): array {
            return [
                'temperature' => (float) $reading['temperature'],
                'humidity' => (float) $reading['humidity'],
                'recorded_at' => $reading['recorded_at'],
            ];
        }, $readings),
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Không thể cập nhật hàng đợi dữ liệu thời tiết.'], JSON_UNESCAPED_UNICODE);
}
