<?php

namespace App\Models\Concerns;

trait HasFullName
{
    /**
     * Get the user's full name.
     */
    public function getFullnameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->firstname,
            $this->lastname,
            // You can add middlename if available
        ])));
    }
}
