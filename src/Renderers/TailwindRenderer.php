<?php

declare(strict_types=1);

namespace TableForge\Renderers;

use TableForge\Contracts\RendererInterface;
use TableForge\Contracts\TableInterface;

/**
 * Tailwind CSS renderer for TableForge tables
 */
class TailwindRenderer implements RendererInterface
{
    protected array $config = [
        'tableClass' => 'min-w-full divide-y divide-gray-200 dark:divide-gray-700',
        'headerClass' => 'bg-gray-50 dark:bg-gray-800',
        'headerCellClass' => 'px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider',
        'bodyClass' => 'bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700',
        'rowClass' => '',
        'cellClass' => 'px-6 py-4 text-sm text-gray-900 dark:text-gray-100',
        'stripedClass' => 'bg-gray-50 dark:bg-gray-800/50',
        'hoverClass' => 'hover:bg-gray-50 dark:hover:bg-gray-800',
        'compactCellClass' => 'px-4 py-2 text-sm',
    ];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    public function renderTable(TableInterface $table): string
    {
        $html = '';

        // Filter toolbar (includes search if searchable)
        $filters = $table->getFilters();
        if (!empty($filters) || $table->isSearchable()) {
            $html .= $this->renderFilterToolbar($table, $filters);
        }

        // Table container
        $html .= '<div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">';
        $html .= '<table class="' . $this->config['tableClass'] . '">';

        // Header
        $html .= $this->renderHeader($table->getColumns(), [
            'sortBy' => $table->getSortBy(),
            'sortDirection' => $table->getSortDirection(),
            'selectable' => $table->isSelectable(),
            'hasActions' => !empty($table->getActions()),
        ]);

        // Body
        $html .= $this->renderBody($table->getColumns(), $table->getData(), [
            'striped' => $table->isStriped(),
            'hoverable' => $table->isHoverable(),
            'compact' => $table->isCompact(),
            'selectable' => $table->isSelectable(),
            'actions' => $table->getActions(),
            'emptyMessage' => $table->getEmptyMessage(),
            'emptyIcon' => $table->getEmptyIcon(),
            'emptyActionUrl' => $table->getEmptyActionUrl(),
            'emptyActionLabel' => $table->getEmptyActionLabel(),
        ]);

        $html .= '</table>';
        $html .= '</div>';

        // Pagination
        if ($table->isPaginated() && $table->getTotalPages() > 1) {
            $html .= $this->renderPagination($table);
        }

        return $html;
    }

    protected function renderSearch(TableInterface $table): string
    {
        $value = htmlspecialchars($table->getSearchQuery() ?? '');

        return <<<HTML
        <div class="mb-4">
            <div class="relative">
                <input type="text" 
                       name="search" 
                       value="{$value}" 
                       placeholder="Search..." 
                       class="input w-full pl-10"
                       x-on:input.debounce.300ms="\$dispatch('table-search', \$el.value)">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
            </div>
        </div>
        HTML;
    }

    protected function renderFilterToolbar(TableInterface $table, array $filters): string
    {
        $html = '<div class="mb-4 flex flex-wrap items-center gap-4">';

        // Search input
        if ($table->isSearchable()) {
            $value = htmlspecialchars($table->getSearchQuery() ?? '');
            $html .= '<div class="flex-1 min-w-[200px]"><div class="relative">';
            $html .= '<input type="text" name="search" value="' . $value . '" placeholder="Search..." class="input w-full pl-10">';
            $html .= '<span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fas fa-search"></i></span>';
            $html .= '</div></div>';
        }

        // Filter dropdowns
        foreach ($filters as $filter) {
            if (!$filter->isVisible()) {
                continue;
            }
            $html .= '<div class="min-w-[150px]">';
            $html .= '<label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">' . htmlspecialchars($filter->getLabel() ?? '') . '</label>';
            $html .= $filter->render();
            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    public function renderHeader(array $columns, array $options = []): string
    {
        $sortBy = $options['sortBy'] ?? null;
        $sortDirection = $options['sortDirection'] ?? 'asc';
        $selectable = $options['selectable'] ?? false;
        $hasActions = $options['hasActions'] ?? false;

        $html = '<thead class="' . $this->config['headerClass'] . '">';
        $html .= '<tr>';

        // Checkbox column
        if ($selectable) {
            $html .= '<th class="px-4 py-3 w-4">';
            $html .= '<input type="checkbox" class="rounded border-gray-300 dark:border-gray-600" x-on:change="toggleAll($el.checked)">';
            $html .= '</th>';
        }

        // Data columns
        foreach ($columns as $column) {
            $align = $column->getAlign();
            $alignClass = match ($align) {
                'right' => 'text-right',
                'center' => 'text-center',
                default => 'text-left',
            };

            $widthStyle = $column->getWidth() ? 'width: ' . $column->getWidth() . ';' : '';

            $html .= '<th class="' . $this->config['headerCellClass'] . ' ' . $alignClass . '" style="' . $widthStyle . '">';

            if ($column->isSortable()) {
                $isActive = $sortBy === $column->getName();
                $nextDirection = $isActive && $sortDirection === 'asc' ? 'desc' : 'asc';
                $iconClass = $isActive
                    ? ($sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down')
                    : 'fa-sort opacity-30';

                $html .= '<a href="?sort=' . $column->getName() . '&dir=' . $nextDirection . '" ';
                $html .= 'class="inline-flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200">';
                $html .= htmlspecialchars($column->getLabel() ?? '');
                $html .= '<i class="fas ' . $iconClass . '"></i>';
                $html .= '</a>';
            } else {
                $html .= htmlspecialchars($column->getLabel() ?? '');
            }

            if ($column->getTooltip()) {
                $html .= '<span class="ml-1 text-gray-400 cursor-help" title="' . htmlspecialchars($column->getTooltip()) . '">';
                $html .= '<i class="fas fa-info-circle"></i>';
                $html .= '</span>';
            }

            $html .= '</th>';
        }

        // Actions column
        if ($hasActions) {
            $html .= '<th class="' . $this->config['headerCellClass'] . ' text-right">Actions</th>';
        }

        $html .= '</tr>';
        $html .= '</thead>';

        return $html;
    }

    public function renderBody(array $columns, array $data, array $options = []): string
    {
        $striped = $options['striped'] ?? false;
        $hoverable = $options['hoverable'] ?? true;
        $compact = $options['compact'] ?? false;
        $selectable = $options['selectable'] ?? false;
        $actions = $options['actions'] ?? [];

        $html = '<tbody class="' . $this->config['bodyClass'] . '">';

        if (empty($data)) {
            $colspan = count($columns) + ($selectable ? 1 : 0) + (!empty($actions) ? 1 : 0);
            $html .= '<tr><td colspan="' . $colspan . '">';
            $html .= $this->renderEmpty(
                $options['emptyMessage'] ?? 'No records found',
                $options['emptyIcon'] ?? 'inbox'
            );
            $html .= '</td></tr>';
        } else {
            foreach ($data as $index => $row) {
                $rowClasses = [];

                if ($striped && $index % 2 === 1) {
                    $rowClasses[] = $this->config['stripedClass'];
                }
                if ($hoverable) {
                    $rowClasses[] = $this->config['hoverClass'];
                }

                $html .= '<tr class="' . implode(' ', $rowClasses) . '">';

                // Checkbox
                if ($selectable) {
                    $rowId = $row['id'] ?? $index;
                    $html .= '<td class="px-4 py-3">';
                    $html .= '<input type="checkbox" name="selected[]" value="' . htmlspecialchars((string) $rowId) . '" ';
                    $html .= 'class="rounded border-gray-300 dark:border-gray-600">';
                    $html .= '</td>';
                }

                // Data cells
                $cellClass = $compact ? $this->config['compactCellClass'] : $this->config['cellClass'];

                foreach ($columns as $column) {
                    $align = $column->getAlign();
                    $alignClass = match ($align) {
                        'right' => 'text-right',
                        'center' => 'text-center',
                        default => 'text-left',
                    };

                    $value = $column->getValue($row);
                    $extraClasses = implode(' ', $column->getClass());

                    $html .= '<td class="' . $cellClass . ' ' . $alignClass . ' ' . $extraClasses . '">';

                    if ($column->isHtml()) {
                        $html .= $value;
                    } else {
                        $html .= htmlspecialchars((string) ($value ?? ''));
                    }

                    if ($column->isCopyable() && $value) {
                        $escapedValue = htmlspecialchars(json_encode((string) $value), ENT_QUOTES, 'UTF-8');
                        $html .= '<button type="button" class="ml-2 text-gray-400 hover:text-gray-600" ';
                        $html .= 'onclick="navigator.clipboard.writeText(' . $escapedValue . ')">';
                        $html .= '<i class="fas fa-copy"></i>';
                        $html .= '</button>';
                    }

                    $html .= '</td>';
                }

                // Actions
                if (!empty($actions)) {
                    $html .= '<td class="' . $cellClass . ' text-right">';
                    $html .= $this->renderActions($actions, $row);
                    $html .= '</td>';
                }

                $html .= '</tr>';
            }
        }

        $html .= '</tbody>';

        return $html;
    }

    protected function renderActions(array $actions, array $row): string
    {
        $html = '<div class="flex items-center justify-end gap-2">';

        foreach ($actions as $action) {
            if (!$action->isVisible($row)) {
                continue;
            }

            $url = $action->getUrl($row);
            $label = $action->getLabel();
            $icon = $action->getIcon();
            $color = $action->getColor();

            $colorClass = match ($color) {
                'danger', 'red' => 'text-red-600 hover:text-red-900',
                'warning', 'yellow' => 'text-yellow-600 hover:text-yellow-900',
                'success', 'green' => 'text-green-600 hover:text-green-900',
                default => 'text-blue-600 hover:text-blue-900',
            };

            if ($url) {
                $html .= '<a href="' . htmlspecialchars($url) . '" class="' . $colorClass . '" title="' . htmlspecialchars($label ?? '') . '">';
            } else {
                $html .= '<button type="button" class="' . $colorClass . '" title="' . htmlspecialchars($label ?? '') . '">';
            }

            if ($icon) {
                $html .= '<i class="' . htmlspecialchars($icon) . '"></i>';
            } else {
                $html .= htmlspecialchars($label ?? '');
            }

            $html .= $url ? '</a>' : '</button>';
        }

        $html .= '</div>';

        return $html;
    }

    public function renderEmpty(string $message, string $icon): string
    {
        $escapedIcon = htmlspecialchars($icon, ENT_QUOTES, 'UTF-8');
        $escapedMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        
        return <<<HTML
        <div class="px-6 py-12 text-center">
            <div class="text-gray-400 dark:text-gray-500">
                <i class="fas fa-{$escapedIcon} text-4xl mb-3"></i>
                <p class="text-sm">{$escapedMessage}</p>
            </div>
        </div>
        HTML;
    }

    protected function renderPagination(TableInterface $table): string
    {
        $currentPage = $table->getCurrentPage();
        $totalPages = $table->getTotalPages();
        $totalItems = $table->getTotalItems();
        $perPage = $table->getPerPage();

        $from = (($currentPage - 1) * $perPage) + 1;
        $to = min($currentPage * $perPage, $totalItems);

        $html = '<div class="flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">';

        // Info
        $html .= '<div class="text-sm text-gray-700 dark:text-gray-300">';
        $html .= "Showing <span class=\"font-medium\">{$from}</span> to <span class=\"font-medium\">{$to}</span> of <span class=\"font-medium\">{$totalItems}</span> results";
        $html .= '</div>';

        // Navigation
        $html .= '<nav class="flex items-center gap-1">';

        // Previous
        if ($currentPage > 1) {
            $html .= '<a href="?page=' . ($currentPage - 1) . '" class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">';
            $html .= '<i class="fas fa-chevron-left"></i>';
            $html .= '</a>';
        }

        // Page numbers
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);

        for ($i = $start; $i <= $end; $i++) {
            $isActive = $i === $currentPage;
            $activeClass = $isActive
                ? 'bg-blue-600 text-white border-blue-600'
                : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800';

            $html .= '<a href="?page=' . $i . '" class="px-3 py-1 rounded border text-sm ' . $activeClass . '">';
            $html .= $i;
            $html .= '</a>';
        }

        // Next
        if ($currentPage < $totalPages) {
            $html .= '<a href="?page=' . ($currentPage + 1) . '" class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">';
            $html .= '<i class="fas fa-chevron-right"></i>';
            $html .= '</a>';
        }

        $html .= '</nav>';
        $html .= '</div>';

        return $html;
    }
}
