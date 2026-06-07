<?php

namespace App\Services\Scheduling;

use InvalidArgumentException;

/**
 * Thrown when a proposed appointment window fails unified availability checks.
 */
final class AvailabilityRejectedException extends InvalidArgumentException
{
    public function __construct(public readonly ScheduleValidationResult $result)
    {
        parent::__construct($result->firstMessage());
    }
}
