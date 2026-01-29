<?php

declare(strict_types=1);

namespace TableForge\Filters;

use TableForge\Contracts\FilterInterface;
use Closure;

/**
 * Base filter class for table filtering
 */
class Filter implements FilterInterface
{
    protected string $name;
    protected ?string $label = null;
    protected mixed $default = null;
    protected ?Closure $queryUsing = null;
    protected ?Closure $indicateUsing = null;
    protected bool $visible = true;
    protected ?string $queryParam = null;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = ucfirst(str_replace('_', ' ', $name));
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function queryParam(string $param): static
    {
        $this->queryParam = $param;
        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;
        return $this;
    }

    public function query(Closure $callback): static
    {
        $this->queryUsing = $callback;
        return $this;
    }

    public function indicateUsing(Closure $callback): static
    {
        $this->indicateUsing = $callback;
        return $this;
    }

    public function visible(bool $visible = true): static
    {
        $this->visible = $visible;
        return $this;
    }

    public function getQueryParam(): ?string
    {
        return $this->queryParam;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function apply(array $data, mixed $value): array
    {
        if ($value === null || $value === '') {
            return $data;
        }

        if ($this->queryUsing) {
            return array_filter($data, fn($row) => ($this->queryUsing)($row, $value));
        }

        // Default: exact match on column
        return array_filter($data, fn($row) => ($row[$this->name] ?? null) === $value);
    }

    public function getIndicator(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($this->indicateUsing) {
            return ($this->indicateUsing)($value);
        }

        return $this->label . ': ' . $value;
    }

    public function render(): string
    {
        $inputName = $this->queryParam ?? ('filter[' . $this->name . ']');
        return '<input type="text" name="' . htmlspecialchars($inputName) . '" class="input" placeholder="' . htmlspecialchars($this->label ?? '') . '">';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'default' => $this->default,
            'visible' => $this->visible,
        ];
    }
}
