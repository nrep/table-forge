<?php

declare(strict_types=1);

namespace TableForge\Columns;

use TableForge\Contracts\ColumnInterface;
use Closure;

/**
 * Base column class for all table columns
 */
class Column implements ColumnInterface
{
    protected string $name;
    protected string $type = 'text';
    protected ?string $label = null;
    protected ?string $tooltip = null;
    protected mixed $default = null;
    protected bool $sortable = false;
    protected bool $searchable = false;
    protected bool $visible = true;
    protected bool $toggleable = false;
    protected ?string $width = null;
    protected string $align = 'left';
    protected array $class = [];
    protected ?Closure $formatUsing = null;
    protected ?Closure $stateUsing = null;
    protected ?string $prefix = null;
    protected ?string $suffix = null;
    protected ?int $limit = null;
    protected bool $copyable = false;
    protected bool $html = false;
    protected bool $wrap = false;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = ucfirst(str_replace('_', ' ', $name));
    }

    // Factory methods
    public static function make(string $name): static
    {
        return new static($name);
    }

    public static function text(string $name): TextColumn
    {
        return new TextColumn($name);
    }

    public static function numeric(string $name): NumericColumn
    {
        return new NumericColumn($name);
    }

    public static function money(string $name): MoneyColumn
    {
        return new MoneyColumn($name);
    }

    public static function date(string $name): DateColumn
    {
        return new DateColumn($name);
    }

    public static function dateTime(string $name): DateTimeColumn
    {
        return new DateTimeColumn($name);
    }

    public static function boolean(string $name): BooleanColumn
    {
        return new BooleanColumn($name);
    }

    public static function badge(string $name): BadgeColumn
    {
        return new BadgeColumn($name);
    }

    public static function image(string $name): ImageColumn
    {
        return new ImageColumn($name);
    }

    // Label & Content
    public function label(string|Closure $label): static
    {
        $this->label = $label instanceof Closure ? $label() : $label;
        return $this;
    }

    public function tooltip(string $tooltip): static
    {
        $this->tooltip = $tooltip;
        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;
        return $this;
    }

    public function state(Closure $callback): static
    {
        $this->stateUsing = $callback;
        return $this;
    }

    // Formatting
    public function formatStateUsing(Closure $callback): static
    {
        $this->formatUsing = $callback;
        return $this;
    }

    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function suffix(string $suffix): static
    {
        $this->suffix = $suffix;
        return $this;
    }

    public function limit(int $length): static
    {
        $this->limit = $length;
        return $this;
    }

    public function html(bool $html = true): static
    {
        $this->html = $html;
        return $this;
    }

    public function copyable(bool $copyable = true): static
    {
        $this->copyable = $copyable;
        return $this;
    }

    public function wrap(bool $wrap = true): static
    {
        $this->wrap = $wrap;
        return $this;
    }

    // Sorting & Searching
    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    // Visibility
    public function visible(bool $visible = true): static
    {
        $this->visible = $visible;
        return $this;
    }

    public function hidden(bool $hidden = true): static
    {
        $this->visible = !$hidden;
        return $this;
    }

    public function toggleable(bool $toggleable = true): static
    {
        $this->toggleable = $toggleable;
        return $this;
    }

    // Styling
    public function width(string $width): static
    {
        $this->width = $width;
        return $this;
    }

    public function alignLeft(): static
    {
        $this->align = 'left';
        return $this;
    }

    public function alignCenter(): static
    {
        $this->align = 'center';
        return $this;
    }

    public function alignRight(): static
    {
        $this->align = 'right';
        return $this;
    }

    public function class(string|array $classes): static
    {
        $this->class = is_array($classes) ? $classes : [$classes];
        return $this;
    }

    // Getters
    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTooltip(): ?string
    {
        return $this->tooltip;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function isToggleable(): bool
    {
        return $this->toggleable;
    }

    public function isCopyable(): bool
    {
        return $this->copyable;
    }

    public function isHtml(): bool
    {
        return $this->html;
    }

    public function getAlign(): string
    {
        return $this->align;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function getClass(): array
    {
        return $this->class;
    }

    // Get formatted value for a row
    public function getValue(array $row): mixed
    {
        $value = $row[$this->name] ?? $this->default;

        if ($this->stateUsing) {
            $value = ($this->stateUsing)($row);
        }

        if ($this->formatUsing) {
            $value = ($this->formatUsing)($value, $row);
        }

        if ($value !== null && $value !== '') {
            if ($this->prefix) {
                $value = $this->prefix . $value;
            }

            if ($this->suffix) {
                $value = $value . $this->suffix;
            }

            if ($this->limit && is_string($value) && mb_strlen($value) > $this->limit) {
                $value = mb_substr($value, 0, $this->limit) . '...';
            }
        }

        return $value;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'label' => $this->label,
            'tooltip' => $this->tooltip,
            'default' => $this->default,
            'sortable' => $this->sortable,
            'searchable' => $this->searchable,
            'visible' => $this->visible,
            'toggleable' => $this->toggleable,
            'width' => $this->width,
            'align' => $this->align,
            'class' => $this->class,
            'html' => $this->html,
            'copyable' => $this->copyable,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
