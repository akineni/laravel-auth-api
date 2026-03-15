<?php

namespace App\Services\Auth\TwoFactor;

use App\Services\Auth\TwoFactor\Contracts\TwoFactorDriverInterface;
use InvalidArgumentException;

class TwoFactorDriverManager
{
    /**
     * @param iterable<TwoFactorDriverInterface> $drivers
     */
    public function __construct(
        private readonly iterable $drivers
    ) {}

    public function driver(string $method): TwoFactorDriverInterface
    {
        foreach ($this->drivers as $driver) {
            if ($driver->supports($method)) {
                return $driver;
            }
        }

        throw new InvalidArgumentException("Unsupported two-factor method [{$method}].");
    }
}