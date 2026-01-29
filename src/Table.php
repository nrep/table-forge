<?php

declare(strict_types=1);

namespace TableForge;

use TableForge\Contracts\TableInterface;
use TableForge\Contracts\RendererInterface;
use TableForge\Columns\Column;
use TableForge\Renderers\TailwindRenderer;

/**
 * Main table class for building data tables
 */
class Table implements TableInterface
{
    protected array $columns = [];
    protected array $filters = [];
    protected array $actions = [];
    protected array $bulkActions = [];
    protected array $headerActions = [];
    protected array $data = [];
    protected bool $paginated = false;
    protected bool $alreadyPaginated = false;
    protected int $perPage = 25;
    protected int $currentPage = 1;
    protected int $totalItems = 0;
    protected bool $searchable = false;
    protected ?string $searchQuery = null;
    protected bool $striped = false;
    protected bool $hoverable = true;
    protected bool $bordered = false;
    protected bool $compact = false;
    protected bool $selectable = false;
    protected ?string $sortBy = null;
    protected string $sortDirection = 'asc';
    protected string $emptyMessage = 'No records found';
    protected string $emptyIcon = 'inbox';
    protected ?string $emptyActionUrl = null;
    protected ?string $emptyActionLabel = null;
    protected ?RendererInterface $renderer = null;
    protected array $attrs = [];

    public static function make(): static
    {
        return new static();
    }

    public static function fromSchema(string $schemaClass): static
    {
        $table = new static();
        $fields = $schemaClass::fields();

        foreach ($fields as $field) {
            if ($field->isVisibleInTable()) {
                $fieldData = $field->toArray();
                $column = Column::make($field->getName())
                    ->label($field->getLabel() ?? $field->getName());

                if ($fieldData['sortable'] ?? false) {
                    $column->sortable();
                }
                if ($fieldData['searchable'] ?? false) {
                    $column->searchable();
                }
                if ($fieldData['tableWidth'] ?? null) {
                    $column->width($fieldData['tableWidth']);
                }
                if ($fieldData['tableAlign'] ?? null) {
                    $column->{'align' . ucfirst($fieldData['tableAlign'])}();
                }

                $table->columns[] = $column;
            }
        }

        return $table;
    }

    // Configuration
    public function columns(array $columns): static
    {
        $this->columns = $columns;
        return $this;
    }

    public function filters(array $filters): static
    {
        $this->filters = $filters;
        return $this;
    }

    public function actions(array $actions): static
    {
        $this->actions = $actions;
        return $this;
    }

    public function bulkActions(array $bulkActions): static
    {
        $this->bulkActions = $bulkActions;
        return $this;
    }

    public function headerActions(array $headerActions): static
    {
        $this->headerActions = $headerActions;
        return $this;
    }

    public function data(array $data): static
    {
        $this->data = $data;
        $this->totalItems = count($data);
        return $this;
    }

    // Pagination
    public function paginated(int $perPage = 25): static
    {
        $this->paginated = true;
        $this->perPage = $perPage;
        return $this;
    }

    public function alreadyPaginated(bool $val = true): static
    {
        $this->alreadyPaginated = $val;
        return $this;
    }

    public function perPage(int $perPage): static
    {
        $this->perPage = $perPage;
        return $this;
    }

    public function currentPage(int $page): static
    {
        $this->currentPage = max(1, $page);
        return $this;
    }

    public function totalItems(int $total): static
    {
        $this->totalItems = $total;
        return $this;
    }

    // Search & Sort
    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function search(?string $query): static
    {
        $this->searchQuery = $query;
        return $this;
    }

    public function sortBy(?string $column, string $direction = 'asc'): static
    {
        $this->sortBy = $column;
        $this->sortDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        return $this;
    }

    // Styling
    public function striped(bool $striped = true): static
    {
        $this->striped = $striped;
        return $this;
    }

    public function hoverable(bool $hoverable = true): static
    {
        $this->hoverable = $hoverable;
        return $this;
    }

    public function bordered(bool $bordered = true): static
    {
        $this->bordered = $bordered;
        return $this;
    }

    public function compact(bool $compact = true): static
    {
        $this->compact = $compact;
        return $this;
    }

    public function selectable(bool $selectable = true): static
    {
        $this->selectable = $selectable;
        return $this;
    }

    // Empty State
    public function emptyState(string $message, string $icon = 'inbox'): static
    {
        $this->emptyMessage = $message;
        $this->emptyIcon = $icon;
        return $this;
    }

    public function emptyStateAction(string $url, string $label): static
    {
        $this->emptyActionUrl = $url;
        $this->emptyActionLabel = $label;
        return $this;
    }

    // Attributes
    public function attrs(array $attrs): static
    {
        $this->attrs = array_merge($this->attrs, $attrs);
        return $this;
    }

    // Renderer
    public function renderer(RendererInterface $renderer): static
    {
        $this->renderer = $renderer;
        return $this;
    }

    public function getRenderer(): RendererInterface
    {
        return $this->renderer ?? new TailwindRenderer();
    }

    // Getters
    public function getColumns(): array
    {
        return array_filter($this->columns, fn($c) => $c->isVisible());
    }

    public function getAllColumns(): array
    {
        return $this->columns;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function getBulkActions(): array
    {
        return $this->bulkActions;
    }

    public function getHeaderActions(): array
    {
        return $this->headerActions;
    }

    public function getData(): array
    {
        $data = $this->data;

        // Apply search filter
        if ($this->searchQuery && $this->searchable) {
            $searchColumns = array_filter($this->columns, fn($c) => $c->isSearchable());
            $query = strtolower($this->searchQuery);

            $data = array_values(array_filter($data, function ($row) use ($searchColumns, $query) {
                foreach ($searchColumns as $column) {
                    $value = strtolower((string) ($row[$column->getName()] ?? ''));
                    if (str_contains($value, $query)) {
                        return true;
                    }
                }
                return false;
            }));
        }

        // Apply sorting
        if ($this->sortBy) {
            $sortColumn = $this->sortBy;
            $direction = $this->sortDirection;

            usort($data, function ($a, $b) use ($sortColumn, $direction) {
                $aVal = $a[$sortColumn] ?? '';
                $bVal = $b[$sortColumn] ?? '';

                if (is_numeric($aVal) && is_numeric($bVal)) {
                    $result = $aVal <=> $bVal;
                } else {
                    $result = strcasecmp((string) $aVal, (string) $bVal);
                }

                return $direction === 'desc' ? -$result : $result;
            });
        }

        // Apply pagination
        if ($this->paginated && !$this->alreadyPaginated) {
            $offset = ($this->currentPage - 1) * $this->perPage;
            $data = array_slice($data, $offset, $this->perPage);
        }

        return $data;
    }

    public function isPaginated(): bool
    {
        return $this->paginated;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function getTotalPages(): int
    {
        if (!$this->paginated || $this->perPage <= 0) {
            return 1;
        }
        return (int) ceil($this->totalItems / $this->perPage);
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function getSearchQuery(): ?string
    {
        return $this->searchQuery;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    public function isStriped(): bool
    {
        return $this->striped;
    }

    public function isHoverable(): bool
    {
        return $this->hoverable;
    }

    public function isBordered(): bool
    {
        return $this->bordered;
    }

    public function isCompact(): bool
    {
        return $this->compact;
    }

    public function isSelectable(): bool
    {
        return $this->selectable;
    }

    public function getEmptyMessage(): string
    {
        return $this->emptyMessage;
    }

    public function getEmptyIcon(): string
    {
        return $this->emptyIcon;
    }

    public function getEmptyActionUrl(): ?string
    {
        return $this->emptyActionUrl;
    }

    public function getEmptyActionLabel(): ?string
    {
        return $this->emptyActionLabel;
    }

    public function getAttrs(): array
    {
        return $this->attrs;
    }

    // Rendering
    public function render(): string
    {
        return $this->getRenderer()->renderTable($this);
    }

    public function toArray(): array
    {
        return [
            'columns' => array_map(fn($c) => $c->toArray(), $this->columns),
            'filters' => array_map(fn($f) => $f->toArray(), $this->filters),
            'actions' => array_map(fn($a) => $a->toArray(), $this->actions),
            'data' => $this->getData(),
            'pagination' => [
                'enabled' => $this->paginated,
                'perPage' => $this->perPage,
                'currentPage' => $this->currentPage,
                'totalItems' => $this->totalItems,
                'totalPages' => $this->getTotalPages(),
            ],
            'searchable' => $this->searchable,
            'sortBy' => $this->sortBy,
            'sortDirection' => $this->sortDirection,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
