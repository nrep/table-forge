<?php

declare(strict_types=1);

namespace TableForge\Columns;

/**
 * DateTime column with time display
 */
class DateTimeColumn extends DateColumn
{
    protected string $type = 'datetime';
    protected string $format = 'M d, Y H:i';
}
