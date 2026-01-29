<?php

declare(strict_types=1);

namespace TableForge\Contracts;

/**
 * Interface for table action components
 */
interface ActionInterface
{
    public function getName(): string;
    public function getLabel(): ?string;
    public function getIcon(): ?string;
    public function getColor(): string;
    public function getUrl(array $row): ?string;
    public function isVisible(array $row): bool;
    public function render(array $row): string;
    public function toArray(): array;
}
