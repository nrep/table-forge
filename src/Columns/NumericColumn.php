<?php

declare(strict_types=1);

namespace TableForge\Columns;

/**
 * Numeric column with formatting support
 */
class NumericColumn extends Column
{
    protected string $type = 'numeric';
    protected int $decimals = 0;
    protected string $decimalSeparator = '.';
    protected string $thousandsSeparator = ',';

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->align = 'right';
    }

    public function decimals(int $decimals): static
    {
        $this->decimals = $decimals;
        return $this;
    }

    public function decimalSeparator(string $separator): static
    {
        $this->decimalSeparator = $separator;
        return $this;
    }

    public function thousandsSeparator(string $separator): static
    {
        $this->thousandsSeparator = $separator;
        return $this;
    }

    public function getValue(array $row): mixed
    {
        $value = $row[$this->name] ?? $this->default ?? 0;

        if ($this->stateUsing) {
            $value = ($this->stateUsing)($row);
        }

        if (is_numeric($value)) {
            $value = number_format(
                (float) $value,
                $this->decimals,
                $this->decimalSeparator,
                $this->thousandsSeparator
            );
        }

        if ($this->formatUsing) {
            $value = ($this->formatUsing)($value, $row);
        }

        if ($this->prefix) {
            $value = $this->prefix . $value;
        }

        if ($this->suffix) {
            $value = $value . $this->suffix;
        }

        return $value;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'decimals' => $this->decimals,
        ]);
    }
}
