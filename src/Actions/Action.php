<?php

declare(strict_types=1);

namespace TableForge\Actions;

use TableForge\Contracts\ActionInterface;
use Closure;

/**
 * Row action for table rows
 */
class Action implements ActionInterface
{
    protected string $name;
    protected ?string $label = null;
    protected ?string $icon = null;
    protected string $color = 'primary';
    protected ?Closure $urlUsing = null;
    protected ?Closure $actionUsing = null;
    protected ?Closure $visibleUsing = null;
    protected ?Closure $disabledUsing = null;
    protected bool $requiresConfirmation = false;
    protected ?string $confirmationHeading = null;
    protected ?string $confirmationDescription = null;
    protected ?string $confirmationButtonLabel = null;
    protected bool $openInNewTab = false;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = ucfirst(str_replace('_', ' ', $name));
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    // Common presets
    public static function view(string $urlPattern = ''): static
    {
        return static::make('view')
            ->label('View')
            ->icon('fas fa-eye')
            ->color('primary');
    }

    public static function edit(string $urlPattern = ''): static
    {
        return static::make('edit')
            ->label('Edit')
            ->icon('fas fa-edit')
            ->color('primary');
    }

    public static function delete(): static
    {
        return static::make('delete')
            ->label('Delete')
            ->icon('fas fa-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->confirmationHeading('Delete Record')
            ->confirmationDescription('Are you sure you want to delete this record? This action cannot be undone.');
    }

    // Configuration
    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function color(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function url(string|Closure $url): static
    {
        if (is_string($url)) {
            $this->urlUsing = fn($row) => str_replace('{id}', (string) ($row['id'] ?? ''), $url);
        } else {
            $this->urlUsing = $url;
        }
        return $this;
    }

    public function action(Closure $callback): static
    {
        $this->actionUsing = $callback;
        return $this;
    }

    public function visible(Closure $callback): static
    {
        $this->visibleUsing = $callback;
        return $this;
    }

    public function disabled(Closure $callback): static
    {
        $this->disabledUsing = $callback;
        return $this;
    }

    public function requiresConfirmation(bool $requires = true): static
    {
        $this->requiresConfirmation = $requires;
        return $this;
    }

    public function confirmationHeading(string $heading): static
    {
        $this->confirmationHeading = $heading;
        return $this;
    }

    public function confirmationDescription(string $description): static
    {
        $this->confirmationDescription = $description;
        return $this;
    }

    public function confirmationButtonLabel(string $label): static
    {
        $this->confirmationButtonLabel = $label;
        return $this;
    }

    public function openInNewTab(bool $newTab = true): static
    {
        $this->openInNewTab = $newTab;
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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getUrl(array $row): ?string
    {
        if ($this->urlUsing) {
            return ($this->urlUsing)($row);
        }
        return null;
    }

    public function isVisible(array $row): bool
    {
        if ($this->visibleUsing) {
            return (bool) ($this->visibleUsing)($row);
        }
        return true;
    }

    public function isDisabled(array $row): bool
    {
        if ($this->disabledUsing) {
            return (bool) ($this->disabledUsing)($row);
        }
        return false;
    }

    public function needsConfirmation(): bool
    {
        return $this->requiresConfirmation;
    }

    public function getConfirmationHeading(): ?string
    {
        return $this->confirmationHeading;
    }

    public function getConfirmationDescription(): ?string
    {
        return $this->confirmationDescription;
    }

    public function render(array $row): string
    {
        if (!$this->isVisible($row)) {
            return '';
        }

        $url = $this->getUrl($row);
        $disabled = $this->isDisabled($row);

        $colorClass = match ($this->color) {
            'danger', 'red' => 'text-red-600 hover:text-red-900 dark:text-red-400',
            'warning', 'yellow' => 'text-yellow-600 hover:text-yellow-900 dark:text-yellow-400',
            'success', 'green' => 'text-green-600 hover:text-green-900 dark:text-green-400',
            'secondary', 'gray' => 'text-gray-600 hover:text-gray-900 dark:text-gray-400',
            default => 'text-blue-600 hover:text-blue-900 dark:text-blue-400',
        };

        if ($disabled) {
            $colorClass = 'text-gray-400 cursor-not-allowed';
        }

        $attrs = '';
        if ($this->openInNewTab) {
            $attrs .= ' target="_blank"';
        }
        if ($this->requiresConfirmation) {
            $attrs .= ' onclick="return confirm(\'' . htmlspecialchars($this->confirmationDescription ?? 'Are you sure?') . '\')"';
        }

        $title = $this->label ? 'title="' . htmlspecialchars($this->label) . '"' : '';

        if ($url && !$disabled) {
            $html = '<a href="' . htmlspecialchars($url) . '" class="' . $colorClass . '" ' . $title . $attrs . '>';
        } else {
            $html = '<button type="button" class="' . $colorClass . '" ' . $title . ($disabled ? 'disabled' : '') . '>';
        }

        if ($this->icon) {
            $html .= '<i class="' . htmlspecialchars($this->icon) . '"></i>';
        } else {
            $html .= htmlspecialchars($this->label ?? '');
        }

        $html .= $url && !$disabled ? '</a>' : '</button>';

        return $html;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'icon' => $this->icon,
            'color' => $this->color,
            'requiresConfirmation' => $this->requiresConfirmation,
            'openInNewTab' => $this->openInNewTab,
        ];
    }
}
