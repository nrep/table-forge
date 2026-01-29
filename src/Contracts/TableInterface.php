<?php

declare(strict_types=1);

namespace TableForge\Contracts;

/**
 * Interface for table components
 */
interface TableInterface
{
    public static function make(): static;
    public static function fromSchema(string $schemaClass): static;
    public function columns(array $columns): static;
    public function data(array $data): static;
    public function getColumns(): array;
    public function getData(): array;
    public function render(): string;
    public function toArray(): array;
}
