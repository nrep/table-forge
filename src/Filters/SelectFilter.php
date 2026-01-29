<?php

declare(strict_types=1);

namespace TableForge\Filters;

use Closure;

/**
 * Select dropdown filter
 */
class SelectFilter extends Filter
{
    protected array $options = [];
    protected ?string $placeholder = '-- All --';
    protected bool $multiple = false;
    protected bool $searchable = false;

    public function options(array|Closure $options): static
    {
        $this->options = $options instanceof Closure ? $options() : $options;
        return $this;
    }

    public function placeholder(?string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function apply(array $data, mixed $value): array
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            return $data;
        }

        if ($this->queryUsing) {
            return array_filter($data, fn($row) => ($this->queryUsing)($row, $value));
        }

        // Handle multiple values
        if ($this->multiple && is_array($value)) {
            return array_filter($data, fn($row) => in_array($row[$this->name] ?? null, $value));
        }

        return array_filter($data, fn($row) => ($row[$this->name] ?? null) === $value);
    }

    public function render(): string
    {
        $inputName = $this->queryParam ?? ('filter[' . $this->name . ']');
        $name = htmlspecialchars($inputName);
        $multiple = $this->multiple ? 'multiple' : '';
        $searchable = $this->searchable ? 'data-searchable="true"' : '';

        $html = '<select name="' . $name . ($this->multiple ? '[]' : '') . '" class="input w-full" ' . $multiple . ' ' . $searchable . '>';

        if ($this->placeholder && !$this->multiple) {
            $html .= '<option value="">' . htmlspecialchars($this->placeholder) . '</option>';
        }

        foreach ($this->options as $optValue => $optLabel) {
            $isSelected = false;
            // Check if value matches default (which should be populated from GET by the caller)
            if ($this->multiple && is_array($this->default)) {
                $isSelected = in_array((string)$optValue, array_map('strval', $this->default));
            } else {
                $isSelected = (string)$this->default === (string)$optValue;
            }
            
            $selected = $isSelected ? 'selected' : '';
            $html .= '<option value="' . htmlspecialchars((string) $optValue) . '" ' . $selected . '>' . htmlspecialchars($optLabel) . '</option>';
        }

        $html .= '</select>';

        return $html;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'type' => 'select',
            'options' => $this->options,
            'multiple' => $this->multiple,
            'searchable' => $this->searchable,
        ]);
    }
}
