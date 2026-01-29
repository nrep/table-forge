# TableForge

Schema-driven table/data grid builder for PHP. Framework agnostic with sorting, filtering, and pagination support.

## Installation

```bash
composer require nrep/table-forge
```

## Quick Start

```php
<?php

use TableForge\Columns\Column;
use TableForge\Table;
use TableForge\Renderers\TailwindRenderer;

// Define table schema
$table = Table::make()
    ->schema([
        Column::text('name')->label('Name')->sortable(),
        Column::text('email')->label('Email')->sortable(),
        Column::badge('status')
            ->label('Status')
            ->colors([
                'active' => 'green',
                'inactive' => 'gray',
                'pending' => 'yellow',
            ]),
        Column::money('balance')->label('Balance')->sortable(),
        Column::date('created_at')->label('Created')->format('M d, Y'),
        Column::actions()
            ->label('Actions')
            ->actions([
                ['label' => 'Edit', 'url' => '/edit/{id}', 'icon' => 'edit'],
                ['label' => 'Delete', 'url' => '/delete/{id}', 'icon' => 'trash', 'confirm' => true],
            ]),
    ])
    ->data($customers)
    ->paginate(20);

// Render with Tailwind CSS
$renderer = new TailwindRenderer();
echo $table->render($renderer);
```

## Available Column Types

| Column Type | Method | Description |
|-------------|--------|-------------|
| Text | `Column::text()` | Plain text display |
| Badge | `Column::badge()` | Colored status badges |
| Boolean | `Column::boolean()` | Yes/No or checkmark display |
| Date | `Column::date()` | Formatted date |
| DateTime | `Column::dateTime()` | Formatted date and time |
| Money | `Column::money()` | Currency formatted |
| Number | `Column::number()` | Numeric with formatting |
| Image | `Column::image()` | Image thumbnail |
| Link | `Column::link()` | Clickable link |
| Actions | `Column::actions()` | Action buttons |
| Custom | `Column::custom()` | Custom callback renderer |

## Column Configuration

```php
Column::text('username')
    ->label('Username')
    ->sortable()
    ->searchable()
    ->width('200px')
    ->class('font-bold')
    ->hidden(false)
    ->tooltip('User login name');
```

## Sorting

```php
$table = Table::make()
    ->schema([
        Column::text('name')->sortable(),
        Column::date('created_at')->sortable()->defaultSort('desc'),
    ])
    ->sortable(true);
```

## Filtering

```php
use TableForge\Filters\Filter;

$table = Table::make()
    ->schema([...])
    ->filters([
        Filter::select('status')
            ->label('Status')
            ->options(['active' => 'Active', 'inactive' => 'Inactive']),
        Filter::dateRange('created_at')
            ->label('Date Range'),
        Filter::search('name', 'email')
            ->placeholder('Search...'),
    ]);
```

## Pagination

```php
$table = Table::make()
    ->schema([...])
    ->data($allRecords)
    ->paginate(25)
    ->currentPage($page);
```

## Bulk Actions

```php
$table = Table::make()
    ->schema([...])
    ->selectable()
    ->bulkActions([
        ['label' => 'Delete Selected', 'action' => 'delete', 'confirm' => true],
        ['label' => 'Export Selected', 'action' => 'export'],
    ]);
```

## Renderers

### Tailwind CSS (Default)

```php
use TableForge\Renderers\TailwindRenderer;

$renderer = new TailwindRenderer([
    'tableClass' => 'min-w-full',
    'headerClass' => 'bg-gray-50',
    'rowClass' => 'hover:bg-gray-100',
    'stripedRows' => true,
]);
```

## License

MIT License - see [LICENSE](LICENSE) file.
