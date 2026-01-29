<?php

declare(strict_types=1);

namespace TableForge\Columns;

/**
 * Image column for displaying images/avatars
 */
class ImageColumn extends Column
{
    protected string $type = 'image';
    protected string $size = '40';
    protected bool $circular = false;
    protected ?string $defaultImage = null;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->html = true;
        $this->align = 'center';
    }

    public function size(string $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function circular(bool $circular = true): static
    {
        $this->circular = $circular;
        return $this;
    }

    public function defaultImage(string $url): static
    {
        $this->defaultImage = $url;
        return $this;
    }

    public function getValue(array $row): mixed
    {
        $value = $row[$this->name] ?? $this->default ?? $this->defaultImage;

        if ($this->stateUsing) {
            $value = ($this->stateUsing)($row);
        }

        if (!$value) {
            return '-';
        }

        $roundedClass = $this->circular ? 'rounded-full' : 'rounded';
        $sizeStyle = "width: {$this->size}px; height: {$this->size}px;";

        return '<img src="' . htmlspecialchars($value) . '" alt="" class="object-cover ' . $roundedClass . '" style="' . $sizeStyle . '">';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'size' => $this->size,
            'circular' => $this->circular,
            'defaultImage' => $this->defaultImage,
        ]);
    }
}
