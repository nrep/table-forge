<?php

declare(strict_types=1);

namespace TableForge\Examples;

use TableForge\Table;
use TableForge\Columns\Column;
use TableForge\Filters\SelectFilter;
use TableForge\Actions\Action;
use TableForge\Actions\BulkAction;

/**
 * Example TableForge schema for Products table
 * 
 * Demonstrates how to create a product listing table with:
 * - Multiple column types (text, money, badge, boolean)
 * - Sorting and searching
 * - Filters
 * - Row actions (view, edit, delete)
 * - Bulk actions
 * - Pagination
 */
class ProductTableSchema
{
    /**
     * Helper to get translation or fallback
     */
    private static function t(string $key, string $fallback): string
    {
        return function_exists('__') ? (__($key) ?? $fallback) : $fallback;
    }

    /**
     * Create a products table with all features
     */
    public static function make(array $products, array $options = []): Table
    {
        return Table::make()
            ->columns(self::columns())
            ->filters(self::filters($options['categories'] ?? []))
            ->actions(self::actions())
            ->bulkActions(self::bulkActions())
            ->data($products)
            ->searchable()
            ->striped()
            ->hoverable()
            ->selectable()
            ->paginated($options['perPage'] ?? 25)
            ->emptyState(self::t('products.no_products_found', 'No products found'), 'box');
    }

    /**
     * Define table columns
     */
    public static function columns(): array
    {
        return [
            Column::text('name')
                ->label(self::t('products.name', 'Name'))
                ->sortable()
                ->searchable()
                ->limit(50),

            Column::text('sku')
                ->label(self::t('products.sku', 'SKU'))
                ->sortable()
                ->searchable()
                ->copyable()
                ->width('120px'),

            Column::text('category_name')
                ->label(self::t('products.category', 'Category'))
                ->sortable(),

            Column::money('selling_price')
                ->label(self::t('products.price', 'Price'))
                ->currency('RWF')
                ->decimals(0)
                ->sortable(),

            Column::numeric('quantity')
                ->label(self::t('products.stock', 'Stock'))
                ->sortable()
                ->alignRight(),

            Column::badge('status')
                ->label(self::t('common.status', 'Status'))
                ->colors([
                    'green' => 'active',
                    'red' => 'inactive',
                    'yellow' => 'low_stock',
                ])
                ->labels([
                    'active' => self::t('common.active', 'Active'),
                    'inactive' => self::t('common.inactive', 'Inactive'),
                    'low_stock' => self::t('products.low_stock', 'Low Stock'),
                ]),

            Column::boolean('is_featured')
                ->label(self::t('products.featured', 'Featured'))
                ->trueIcon('fas fa-star')
                ->trueColor('text-yellow-500')
                ->falseIcon('fas fa-star')
                ->falseColor('text-gray-300'),

            Column::date('created_at')
                ->label(self::t('common.created_at', 'Created'))
                ->format('M d, Y')
                ->sortable()
                ->toggleable(),
        ];
    }

    /**
     * Define table filters
     */
    public static function filters(array $categories = []): array
    {
        $categoryOptions = [];
        foreach ($categories as $cat) {
            $categoryOptions[$cat['id']] = $cat['name'];
        }

        return [
            SelectFilter::make('status')
                ->label(self::t('common.status', 'Status'))
                ->options([
                    'active' => self::t('common.active', 'Active'),
                    'inactive' => self::t('common.inactive', 'Inactive'),
                    'low_stock' => self::t('products.low_stock', 'Low Stock'),
                ])
                ->placeholder(self::t('common.all', '-- All --')),

            SelectFilter::make('category_id')
                ->label(self::t('products.category', 'Category'))
                ->options($categoryOptions)
                ->placeholder(self::t('common.all_categories', '-- All Categories --')),
        ];
    }

    /**
     * Define row actions
     */
    public static function actions(): array
    {
        return [
            Action::view()
                ->url('index.php?page=inventory&action=view&id={id}'),

            Action::edit()
                ->url('index.php?page=inventory&action=edit&id={id}'),

            Action::delete()
                ->url('index.php?page=inventory&action=delete&id={id}'),
        ];
    }

    /**
     * Define bulk actions
     */
    public static function bulkActions(): array
    {
        return [
            BulkAction::make('activate')
                ->label(self::t('products.activate_selected', 'Activate Selected'))
                ->icon('fas fa-check')
                ->color('success')
                ->actionUrl('index.php?page=inventory&action=bulk_activate'),

            BulkAction::make('deactivate')
                ->label(self::t('products.deactivate_selected', 'Deactivate Selected'))
                ->icon('fas fa-ban')
                ->color('warning')
                ->actionUrl('index.php?page=inventory&action=bulk_deactivate'),

            BulkAction::deleteSelected()
                ->actionUrl('index.php?page=inventory&action=bulk_delete'),
        ];
    }
}
