<?php

namespace App\Models\Concerns;

trait HasFullName
{
    /**
     * Get the user's full name.
     */
    public function getFullnameAttribute(): string
    {
        return trim("{$this->firstname} {$this->lastname}");
    }
}
