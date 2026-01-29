<?php

declare(strict_types=1);

namespace TableForge\Columns;

use DateTime;
use DateTimeInterface;

/**
 * Date column with formatting support
 */
class DateColumn extends Column
{
    protected string $type = 'date';
    protected string $format = 'M d, Y';
    protected ?string $timezone = null;

    public function format(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    public function timezone(string $timezone): static
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function getValue(array $row): mixed
    {
        $value = $row[$this->name] ?? $this->default;

        if ($this->stateUsing) {
            $value = ($this->stateUsing)($row);
        }

        if ($value === null || $value === '') {
            return $this->default ?? '-';
        }

        try {
            if ($value instanceof DateTimeInterface) {
                $date = $value;
            } else {
                $date = new DateTime($value);
            }

            if ($this->timezone) {
                $date->setTimezone(new \DateTimeZone($this->timezone));
            }

            $formatted = $date->format($this->format);
        } catch (\Exception $e) {
            $formatted = (string) $value;
        }

        if ($this->formatUsing) {
            $formatted = ($this->formatUsing)($formatted, $row);
        }

        return $formatted;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'format' => $this->format,
            'timezone' => $this->timezone,
        ]);
    }
}
