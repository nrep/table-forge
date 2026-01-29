<?php

declare(strict_types=1);

namespace TableForge\Actions;

use Closure;

/**
 * Bulk action for multiple selected rows
 */
class BulkAction extends Action
{
    protected ?string $actionUrl = null;
    protected string $method = 'POST';

    public static function make(string $name): static
    {
        return new static($name);
    }

    public static function deleteSelected(): static
    {
        return static::make('delete_selected')
            ->label('Delete Selected')
            ->icon('fas fa-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->confirmationHeading('Delete Selected Records')
            ->confirmationDescription('Are you sure you want to delete the selected records? This action cannot be undone.');
    }

    public static function exportSelected(): static
    {
        return static::make('export_selected')
            ->label('Export Selected')
            ->icon('fas fa-download')
            ->color('secondary');
    }

    public function actionUrl(string $url): static
    {
        $this->actionUrl = $url;
        return $this;
    }

    public function method(string $method): static
    {
        $this->method = strtoupper($method);
        return $this;
    }

    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(array $row): ?string
    {
        return $this->actionUrl;
    }

    public function render(array $row = []): string
    {
        $colorClass = match ($this->color) {
            'danger', 'red' => 'bg-red-600 hover:bg-red-700 text-white',
            'warning', 'yellow' => 'bg-yellow-600 hover:bg-yellow-700 text-white',
            'success', 'green' => 'bg-green-600 hover:bg-green-700 text-white',
            'secondary', 'gray' => 'bg-gray-600 hover:bg-gray-700 text-white',
            default => 'bg-blue-600 hover:bg-blue-700 text-white',
        };

        $confirmAttr = '';
        if ($this->requiresConfirmation) {
            $confirmAttr = ' onclick="return confirm(\'' . htmlspecialchars($this->confirmationDescription ?? 'Are you sure?') . '\')"';
        }

        $html = '<button type="submit" ';
        $html .= 'formaction="' . htmlspecialchars($this->actionUrl ?? '') . '" ';
        $html .= 'formmethod="' . $this->method . '" ';
        $html .= 'class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded ' . $colorClass . '"';
        $html .= $confirmAttr . '>';

        if ($this->icon) {
            $html .= '<i class="' . htmlspecialchars($this->icon) . ' mr-2"></i>';
        }

        $html .= htmlspecialchars($this->label ?? '');
        $html .= '</button>';

        return $html;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'actionUrl' => $this->actionUrl,
            'method' => $this->method,
        ]);
    }
}
