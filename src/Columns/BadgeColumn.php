<?php

declare(strict_types=1);

namespace TableForge\Columns;

/**
 * Badge column for status display
 */
class BadgeColumn extends Column
{
    protected string $type = 'badge';
    protected array $colors = [];
    protected array $icons = [];
    protected array $labels = [];

    protected static array $colorClasses = [
        'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        'orange' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'indigo' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
        'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        'pink' => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300',
    ];

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->html = true;
    }

    public function colors(array $colors): static
    {
        $this->colors = $colors;
        return $this;
    }

    public function icons(array $icons): static
    {
        $this->icons = $icons;
        return $this;
    }

    public function labels(array $labels): static
    {
        $this->labels = $labels;
        return $this;
    }

    public function getColor(mixed $value): string
    {
        foreach ($this->colors as $color => $match) {
            if ($match === $value || (is_array($match) && in_array($value, $match))) {
                return $color;
            }
        }
        return 'gray';
    }

    public function getIcon(mixed $value): ?string
    {
        foreach ($this->icons as $icon => $match) {
            if ($match === $value || (is_array($match) && in_array($value, $match))) {
                return $icon;
            }
        }
        return null;
    }

    public function getLabelText(mixed $value): string
    {
        return $this->labels[$value] ?? ucfirst(str_replace('_', ' ', (string) $value));
    }

    public function getColorClass(string $color): string
    {
        return self::$colorClasses[$color] ?? self::$colorClasses['gray'];
    }

    public function getValue(array $row): mixed
    {
        $value = $row[$this->name] ?? $this->default;

        if ($this->stateUsing) {
            $value = ($this->stateUsing)($row);
        }

        if ($value === null || $value === '') {
            return '-';
        }

        $color = $this->getColor($value);
        $colorClass = $this->getColorClass($color);
        $icon = $this->getIcon($value);
        $label = $this->getLabelText($value);

        $html = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $colorClass . '">';

        if ($icon) {
            $html .= '<i class="' . htmlspecialchars($icon) . ' mr-1"></i>';
        }

        $html .= htmlspecialchars($label);
        $html .= '</span>';

        return $html;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'colors' => $this->colors,
            'icons' => $this->icons,
            'labels' => $this->labels,
        ]);
    }
}
