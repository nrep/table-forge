<?php

declare(strict_types=1);

namespace TableForge\Columns;

/**
 * Text column for plain text display
 */
class TextColumn extends Column
{
    protected string $type = 'text';
}
