<?php

declare(strict_types=1);

namespace TableForge\Contracts;

/**
 * Interface for table filter components
 */
interface FilterInterface
{
    public function getName(): string;
    public function getLabel(): ?string;
    public function apply(array $data, mixed $value): array;
    public function render(): string;
    public function toArray(): array;
}
