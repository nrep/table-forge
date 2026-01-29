<?php

/**
 * TableForge Demo - Products Table
 * 
 * Run with: php core/TableForge/Examples/demo.php
 * 
 * Demonstrates TableForge integration with Bibike ERP product data.
 */

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/ProductTableSchema.php';

use TableForge\Examples\ProductTableSchema;

// Sample product data (simulating database results)
$products = [
    [
        'id' => 1,
        'name' => 'iPhone 15 Pro Max',
        'sku' => 'APL-IP15PM-256',
        'category_name' => 'Electronics',
        'category_id' => 1,
        'selling_price' => 1500000,
        'quantity' => 25,
        'status' => 'active',
        'is_featured' => true,
        'created_at' => '2024-01-15',
    ],
    [
        'id' => 2,
        'name' => 'Samsung Galaxy S24 Ultra',
        'sku' => 'SAM-S24U-512',
        'category_name' => 'Electronics',
        'category_id' => 1,
        'selling_price' => 1350000,
        'quantity' => 15,
        'status' => 'active',
        'is_featured' => true,
        'created_at' => '2024-01-20',
    ],
    [
        'id' => 3,
        'name' => 'MacBook Pro 16"',
        'sku' => 'APL-MBP16-M3',
        'category_name' => 'Computers',
        'category_id' => 2,
        'selling_price' => 3500000,
        'quantity' => 5,
        'status' => 'low_stock',
        'is_featured' => false,
        'created_at' => '2024-02-01',
    ],
    [
        'id' => 4,
        'name' => 'Sony WH-1000XM5 Headphones',
        'sku' => 'SNY-WH1000XM5',
        'category_name' => 'Audio',
        'category_id' => 3,
        'selling_price' => 450000,
        'quantity' => 0,
        'status' => 'inactive',
        'is_featured' => false,
        'created_at' => '2024-02-10',
    ],
    [
        'id' => 5,
        'name' => 'iPad Pro 12.9"',
        'sku' => 'APL-IPDP-129',
        'category_name' => 'Tablets',
        'category_id' => 4,
        'selling_price' => 1800000,
        'quantity' => 12,
        'status' => 'active',
        'is_featured' => true,
        'created_at' => '2024-02-15',
    ],
];

$categories = [
    ['id' => 1, 'name' => 'Electronics'],
    ['id' => 2, 'name' => 'Computers'],
    ['id' => 3, 'name' => 'Audio'],
    ['id' => 4, 'name' => 'Tablets'],
];

// Create table using schema
$table = ProductTableSchema::make($products, [
    'categories' => $categories,
    'perPage' => 10,
]);

// Output demo info
echo "=== TableForge Demo - Products Table ===\n\n";

// Show table configuration
echo "Table Configuration:\n";
echo "- Columns: " . count($table->getColumns()) . "\n";
echo "- Filters: " . count($table->getFilters()) . "\n";
echo "- Actions: " . count($table->getActions()) . "\n";
echo "- Bulk Actions: " . count($table->getBulkActions()) . "\n";
echo "- Searchable: " . ($table->isSearchable() ? 'Yes' : 'No') . "\n";
echo "- Paginated: " . ($table->isPaginated() ? 'Yes (' . $table->getPerPage() . ' per page)' : 'No') . "\n";
echo "- Selectable: " . ($table->isSelectable() ? 'Yes' : 'No') . "\n";

echo "\nColumn Details:\n";
foreach ($table->getColumns() as $column) {
    $features = [];
    if ($column->isSortable()) $features[] = 'sortable';
    if ($column->isSearchable()) $features[] = 'searchable';
    if ($column->isCopyable()) $features[] = 'copyable';
    
    echo "- {$column->getLabel()} ({$column->getType()})";
    if (!empty($features)) {
        echo " [" . implode(', ', $features) . "]";
    }
    echo "\n";
}

echo "\nData Preview (first 3 rows):\n";
$data = $table->getData();
foreach (array_slice($data, 0, 3) as $row) {
    echo "- {$row['name']} | {$row['sku']} | FRw " . number_format($row['selling_price']) . " | {$row['status']}\n";
}

echo "\nHTML Output Length: " . strlen($table->render()) . " bytes\n";

// Test serialization
$json = $table->toJson();
echo "JSON Output Length: " . strlen($json) . " bytes\n";

echo "\n=== Demo Complete ===\n";
