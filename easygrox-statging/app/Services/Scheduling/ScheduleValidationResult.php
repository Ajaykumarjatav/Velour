<?php

namespace App\Services\Scheduling;

/**
 * Result of availability validation for a proposed appointment window.
 *
 * @phpstan-type Reason array{code: string, message: string}
 */
final class ScheduleValidationResult
{
    /** @param list<Reason> $reasons */
    public function __construct(
        public readonly bool $ok,
        public readonly array $reasons = [],
    ) {}

    public static function success(): self
    {
        return new self(true, []);
    }

    /** @param list<Reason> $reasons */
    public static function failure(array $reasons): self
    {
        return new self(false, $reasons);
    }

    public function firstMessage(): string
    {
        return $this->reasons[0]['message'] ?? 'This time is not available.';
    }

    /** @return list<string> */
    public function codes(): array
    {
        return array_values(array_map(fn (array $r) => $r['code'], $this->reasons));
    }
}
