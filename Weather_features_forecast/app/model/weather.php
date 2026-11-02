<?php

require_once __DIR__ . '/wet_dry.php';

class Weather
{
    public const TYPE_SUNNY = 'nắng';
    public const TYPE_RAIN = 'mưa';
    public const TYPE_FOG = 'sương mù';
    public const TYPE_SNOW = 'tuyết rơi';
    public const TYPE_THUNDERSTORM = 'sấm sét';

    public const SNOW_MAX_TEMPERATURE_C = 0.0;
    public const SNOW_MIN_HUMIDITY_PERCENT = 80.0;
    public const SUNNY_MIN_TEMPERATURE_C = 20.0;
    public const SUNNY_MAX_HUMIDITY_PERCENT = 60.0;
    public const RAIN_MIN_HUMIDITY_PERCENT = 80.0;
    public const FOG_MIN_HUMIDITY_PERCENT = 90.0;
    public const FOG_MAX_TEMPERATURE_C = 20.0;
    public const STORM_MIN_HUMIDITY_PERCENT = 85.0;
    public const STORM_MIN_TEMPERATURE_C = 25.0;

    public const TYPES = [
        self::TYPE_SUNNY,
        self::TYPE_RAIN,
        self::TYPE_FOG,
        self::TYPE_SNOW,
        self::TYPE_THUNDERSTORM,
    ];

    public int $id;
    public string $sensor_id;
    public string $weather_type;
    public string $recorded_at;
    public string $location;

    public function __construct(
        int $id,
        string $sensor_id,
        string $weather_type,
        string $recorded_at,
        string $location
    ) {
        $this->validateRequiredText($sensor_id, 'Mã cảm biến');
        $this->validateRequiredText($recorded_at, 'Thời điểm ghi nhận');
        $this->validateRequiredText($location, 'Vị trí');

        $this->id = $id;
        $this->sensor_id = $sensor_id;
        $this->weather_type = $this->validateWeatherType($weather_type);
        $this->recorded_at = $recorded_at;
        $this->location = $location;
    }

    public function updateWeatherType(string $weather_type, string $recorded_at): void
    {
        $this->validateRequiredText($recorded_at, 'Thời điểm ghi nhận');

        $this->weather_type = $this->validateWeatherType($weather_type);
        $this->recorded_at = $recorded_at;
    }

    public function getWeatherTypeLabel(): string
    {
        return ucfirst($this->weather_type);
    }

    public static function inferFromConditions(float $humidity, float $temperature): ?string
    {
        self::validateConditions($humidity, $temperature);
        $moisture = new Wet($humidity, $temperature);

        if ($temperature <= self::SNOW_MAX_TEMPERATURE_C && $humidity >= self::SNOW_MIN_HUMIDITY_PERCENT) {
            return self::TYPE_SNOW;
        }

        if ($temperature >= self::STORM_MIN_TEMPERATURE_C
            && $moisture->isWet()
            && $humidity >= self::STORM_MIN_HUMIDITY_PERCENT
        ) {
            return self::TYPE_THUNDERSTORM;
        }

        if ($temperature <= self::FOG_MAX_TEMPERATURE_C && $humidity >= self::FOG_MIN_HUMIDITY_PERCENT) {
            return self::TYPE_FOG;
        }

        if ($temperature > self::SNOW_MAX_TEMPERATURE_C && $moisture->isWet()) {
            return self::TYPE_RAIN;
        }

        $dry = new Dry($humidity, $temperature);
        if ($temperature >= self::SUNNY_MIN_TEMPERATURE_C && $dry->isDry()) {
            return self::TYPE_SUNNY;
        }

        return null;
    }

    public function isSnowy(float $humidity, float $temperature): bool
    {
        $this->validateConditions($humidity, $temperature);

        $moisture = new Wet($humidity, $temperature);

        return $temperature <= self::SNOW_MAX_TEMPERATURE_C
            && $moisture->isWet();
    }

    public function isSunny(float $humidity, float $temperature): bool
    {
        $this->validateConditions($humidity, $temperature);

        $dry = new Dry($humidity, $temperature);

        return $temperature >= self::SUNNY_MIN_TEMPERATURE_C
            && $dry->isDry();
    }

    public function isRainy(float $humidity, float $temperature): bool
    {
        $this->validateConditions($humidity, $temperature);

        $wet = new Wet($humidity, $temperature);

        return $temperature > self::SNOW_MAX_TEMPERATURE_C
            && $wet->isWet();
    }

    public function isStormy(float $humidity, float $temperature): bool
    {
        $this->validateConditions($humidity, $temperature);

        $wet = new Wet($humidity, $temperature);

        return $temperature >= self::STORM_MIN_TEMPERATURE_C
            && $wet->isWet()
            && $humidity >= self::STORM_MIN_HUMIDITY_PERCENT;
    }

    public function isFoggy(float $humidity, float $temperature): bool
    {
        $this->validateConditions($humidity, $temperature);

        $wet = new Wet($humidity, $temperature);

        return $temperature <= self::FOG_MAX_TEMPERATURE_C
            && $wet->isWet()
            && $humidity >= self::FOG_MIN_HUMIDITY_PERCENT;
    }

    private function validateWeatherType(string $weather_type): string
    {
        if (!in_array($weather_type, self::TYPES, true)) {
            throw new InvalidArgumentException(
                'Kiểu thời tiết phải là: ' . implode(', ', self::TYPES) . '.'
            );
        }

        return $weather_type;
    }

    private function validateRequiredText(string $value, string $field): void
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException($field . ' không được để trống.');
        }
    }

    private static function validateConditions(float $humidity, float $temperature): void
    {
        if (!is_finite($humidity) || $humidity < 0 || $humidity > 100) {
            throw new InvalidArgumentException('Độ ẩm phải nằm trong khoảng 0–100%.');
        }

        if (!is_finite($temperature) || $temperature < -273.15) {
            throw new InvalidArgumentException('Nhiệt độ phải là số hữu hạn và không thấp hơn -273.15°C.');
        }
    }
}