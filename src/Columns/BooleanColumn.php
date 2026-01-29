<?php

declare(strict_types=1);

namespace TableForge\Columns;

/**
 * Boolean column with icon display
 */
class BooleanColumn extends Column
{
    protected string $type = 'boolean';
    protected string $trueIcon = 'fas fa-check';
    protected string $falseIcon = 'fas fa-times';
    protected string $trueColor = 'text-green-500';
    protected string $falseColor = 'text-red-500';
    protected ?string $trueLabel = null;
    protected ?string $falseLabel = null;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->align = 'center';
        $this->html = true;
    }

    public function trueIcon(string $icon): static
    {
        $this->trueIcon = $icon;
        return $this;
    }

    public function falseIcon(string $icon): static
    {
        $this->falseIcon = $icon;
        return $this;
    }

    public function trueColor(string $color): static
    {
        $this->trueColor = $color;
        return $this;
    }

    public function falseColor(string $color): static
    {
        $this->falseColor = $color;
        return $this;
    }

    public function trueLabel(string $label): static
    {
        $this->trueLabel = $label;
        return $this;
    }

    public function falseLabel(string $label): static
    {
        $this->falseLabel = $label;
        return $this;
    }

    public function getValue(array $row): mixed
    {
        $value = $row[$this->name] ?? $this->default ?? false;

        if ($this->stateUsing) {
            $value = ($this->stateUsing)($row);
        }

        $isTrue = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        if ($isTrue) {
            $icon = $this->trueIcon;
            $color = $this->trueColor;
            $label = $this->trueLabel;
        } else {
            $icon = $this->falseIcon;
            $color = $this->falseColor;
            $label = $this->falseLabel;
        }

        $html = '<i class="' . htmlspecialchars($icon) . ' ' . htmlspecialchars($color) . '"></i>';

        if ($label) {
            $html .= ' <span class="ml-1">' . htmlspecialchars($label) . '</span>';
        }

        return $html;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'trueIcon' => $this->trueIcon,
            'falseIcon' => $this->falseIcon,
            'trueColor' => $this->trueColor,
            'falseColor' => $this->falseColor,
        ]);
    }
}
