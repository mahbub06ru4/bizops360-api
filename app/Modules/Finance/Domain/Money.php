<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain;

use InvalidArgumentException;

/**
 * An immutable money amount held as an integer number of minor units (cents),
 * so arithmetic on invoice balances and report totals never accumulates binary
 * floating-point error.
 *
 * The database stores the same values as `decimal(15, 2)` (matching CRM's
 * `estimated_value`); this value object is the in-memory calculation type.
 */
final readonly class Money
{
    private function __construct(public int $minorUnits) {}

    public static function fromMinorUnits(int $minorUnits): self
    {
        return new self($minorUnits);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    /**
     * Build from a major-unit amount ("1234.50", 1234.5, 1234). Half-up rounding
     * to the cent; rejects non-numeric input.
     */
    public static function fromDecimal(string|int|float $amount): self
    {
        if (is_string($amount) && ! is_numeric($amount)) {
            throw new InvalidArgumentException("Non-numeric money amount: [{$amount}].");
        }

        return new self((int) round((float) $amount * 100, 0, PHP_ROUND_HALF_UP));
    }

    public function add(self $other): self
    {
        return new self($this->minorUnits + $other->minorUnits);
    }

    public function subtract(self $other): self
    {
        return new self($this->minorUnits - $other->minorUnits);
    }

    /**
     * Never returns a negative amount (a floor at zero).
     */
    public function clampToZero(): self
    {
        return $this->minorUnits < 0 ? self::zero() : $this;
    }

    public function isZero(): bool
    {
        return $this->minorUnits === 0;
    }

    public function isPositive(): bool
    {
        return $this->minorUnits > 0;
    }

    public function isNegative(): bool
    {
        return $this->minorUnits < 0;
    }

    public function equals(self $other): bool
    {
        return $this->minorUnits === $other->minorUnits;
    }

    public function greaterThan(self $other): bool
    {
        return $this->minorUnits > $other->minorUnits;
    }

    public function lessThan(self $other): bool
    {
        return $this->minorUnits < $other->minorUnits;
    }

    public function toFloat(): float
    {
        return $this->minorUnits / 100;
    }

    /**
     * Canonical string form for API responses and DB writes: always two decimals,
     * no thousands separator (e.g. "1234.50", "-5.00").
     */
    public function toDecimalString(): string
    {
        return number_format($this->minorUnits / 100, 2, '.', '');
    }
}
