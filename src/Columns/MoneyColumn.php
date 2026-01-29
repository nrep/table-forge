<?php

declare(strict_types=1);

namespace TableForge\Columns;

/**
 * Money column with currency formatting
 */
class MoneyColumn extends Column
{
    protected string $type = 'money';
    protected string $currency = 'USD';
    protected int $decimals = 2;
    protected bool $showSymbol = true;

    protected static array $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'RWF' => 'FRw',
        'KES' => 'KSh',
        'TZS' => 'TSh',
        'UGX' => 'USh',
    ];

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->align = 'right';
    }

    public function currency(string $currency): static
    {
        $this->currency = strtoupper($currency);
        return $this;
    }

    public function decimals(int $decimals): static
    {
        $this->decimals = $decimals;
        return $this;
    }

    public function showSymbol(bool $show = true): static
    {
        $this->showSymbol = $show;
        return $this;
    }

    public function getCurrencySymbol(): string
    {
        return self::$symbols[$this->currency] ?? $this->currency;
    }

    public function getValue(array $row): mixed
    {
        $value = $row[$this->name] ?? $this->default ?? 0;

        if ($this->stateUsing) {
            $value = ($this->stateUsing)($row);
        }

        $formatted = number_format((float) $value, $this->decimals);

        if ($this->formatUsing) {
            $formatted = ($this->formatUsing)($formatted, $row);
        }

        if ($this->showSymbol) {
            $formatted = $this->getCurrencySymbol() . ' ' . $formatted;
        }

        return $formatted;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'currency' => $this->currency,
            'decimals' => $this->decimals,
            'showSymbol' => $this->showSymbol,
        ]);
    }
}
