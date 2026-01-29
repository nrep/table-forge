<?php

declare(strict_types=1);

namespace TableForge\Contracts;

/**
 * Interface for table renderers
 */
interface RendererInterface
{
    public function renderTable(TableInterface $table): string;
    public function renderHeader(array $columns, array $options = []): string;
    public function renderBody(array $columns, array $data, array $options = []): string;
    public function renderEmpty(string $message, string $icon): string;
}
