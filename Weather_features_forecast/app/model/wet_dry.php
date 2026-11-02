<?php

abstract class MoistureCondition
{
    public const MIN_TEMPERATURE_C = -273.15;

    public float $relative_humidity;
    public float $temperature_celsius;
    public ?int $humidity_id;
    public ?int $temperature_id;
    public ?string $recorded_at;

    public function __construct(
        float $relative_humidity,
        float $temperature_celsius,
        ?int $humidity_id = null,
        ?int $temperature_id = null,
        ?string $recorded_at = null
    ) {
        if (!is_finite($relative_humidity) || $relative_humidity < 0 || $relative_humidity > 100) {
            throw new InvalidArgumentException('Độ ẩm tương đối phải nằm trong khoảng 0–100%.');
        }

        if (!is_finite($temperature_celsius) || $temperature_celsius < self::MIN_TEMPERATURE_C) {
            throw new InvalidArgumentException('Nhiệt độ phải là số hữu hạn và không thấp hơn -273.15°C.');
        }

        if (($humidity_id !== null && $humidity_id <= 0) || ($temperature_id !== null && $temperature_id <= 0)) {
            throw new InvalidArgumentException('Mã bản ghi độ ẩm và nhiệt độ phải lớn hơn 0.');
        }

        $this->relative_humidity = $relative_humidity;
        $this->temperature_celsius = $temperature_celsius;
        $this->humidity_id = $humidity_id;
        $this->temperature_id = $temperature_id;
        $this->recorded_at = $recorded_at;
    }
}

class Wet extends MoistureCondition
{
    public const MIN_RELATIVE_HUMIDITY_PERCENT = 80.0;

    public function isWet(): bool
    {
        return $this->relative_humidity >= self::MIN_RELATIVE_HUMIDITY_PERCENT;
    }
}

class Dry extends MoistureCondition
{
    public const MAX_RELATIVE_HUMIDITY_PERCENT = 60.0;

    public function isDry(): bool
    {
        return $this->relative_humidity < self::MAX_RELATIVE_HUMIDITY_PERCENT;
    }
}
