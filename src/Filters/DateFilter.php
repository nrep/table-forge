<?php

declare(strict_types=1);

namespace TableForge\Filters;

use DateTime;

/**
 * Date filter with optional range support
 */
class DateFilter extends Filter
{
    protected bool $range = false;
    protected ?string $minDate = null;
    protected ?string $maxDate = null;
    protected string $format = 'Y-m-d';

    public function range(bool $range = true): static
    {
        $this->range = $range;
        return $this;
    }

    public function minDate(string $date): static
    {
        $this->minDate = $date;
        return $this;
    }

    public function maxDate(string $date): static
    {
        $this->maxDate = $date;
        return $this;
    }

    public function format(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    public function apply(array $data, mixed $value): array
    {
        if ($value === null || $value === '') {
            return $data;
        }

        if ($this->queryUsing) {
            return array_filter($data, fn($row) => ($this->queryUsing)($row, $value));
        }

        // Handle range filter
        if ($this->range && is_array($value)) {
            $from = $value['from'] ?? null;
            $to = $value['to'] ?? null;

            return array_filter($data, function ($row) use ($from, $to) {
                $rowDate = $row[$this->name] ?? null;
                if (!$rowDate) {
                    return false;
                }

                try {
                    $date = new DateTime($rowDate);
                    $dateStr = $date->format('Y-m-d');

                    if ($from && $dateStr < $from) {
                        return false;
                    }
                    if ($to && $dateStr > $to) {
                        return false;
                    }

                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            });
        }

        // Single date filter
        return array_filter($data, function ($row) use ($value) {
            $rowDate = $row[$this->name] ?? null;
            if (!$rowDate) {
                return false;
            }

            try {
                $date = new DateTime($rowDate);
                return $date->format('Y-m-d') === $value;
            } catch (\Exception $e) {
                return false;
            }
        });
    }

    public function render(): string
    {
        $name = htmlspecialchars($this->name);
        $minAttr = $this->minDate ? 'min="' . htmlspecialchars($this->minDate) . '"' : '';
        $maxAttr = $this->maxDate ? 'max="' . htmlspecialchars($this->maxDate) . '"' : '';

        if ($this->range) {
            return <<<HTML
            <div class="flex items-center gap-2">
                <input type="date" name="filter[{$name}][from]" class="input" {$minAttr} {$maxAttr} placeholder="From">
                <span class="text-gray-400">-</span>
                <input type="date" name="filter[{$name}][to]" class="input" {$minAttr} {$maxAttr} placeholder="To">
            </div>
            HTML;
        }

        return '<input type="date" name="filter[' . $name . ']" class="input" ' . $minAttr . ' ' . $maxAttr . '>';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'type' => 'date',
            'range' => $this->range,
            'minDate' => $this->minDate,
            'maxDate' => $this->maxDate,
        ]);
    }
}
