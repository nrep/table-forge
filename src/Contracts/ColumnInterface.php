<?php

declare(strict_types=1);

namespace TableForge\Contracts;

/**
 * Interface for table column components
 */
interface ColumnInterface
{
    public function getName(): string;
    public function getType(): string;
    public function getLabel(): ?string;
    public function isSortable(): bool;
    public function isSearchable(): bool;
    public function isVisible(): bool;
    public function getAlign(): string;
    public function getWidth(): ?string;
    public function getValue(array $row): mixed;
    public function toArray(): array;
}
