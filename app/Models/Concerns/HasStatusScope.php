<?php

namespace App\Models\Concerns;

use App\Enums\UserStatusEnum;
use Illuminate\Database\Eloquent\Builder;

trait HasStatusScope
{
    /**
     * Filter records by status column.
     */
    public function scopeStatus(Builder $query, string|int|null $status): Builder
    {
        if (is_null($status)) {
            return $query;
        }

        $column = method_exists($this, 'getStatusColumn')
            ? $this->getStatusColumn()
            : 'status';

        $value = method_exists($this, 'normalizeStatusValue')
            ? $this->normalizeStatusValue($status)
            : $status;

        return $query->where($column, $value);
    }

    protected function getStatusColumn(): string
    {
        return 'status';
    }

    protected function normalizeStatusValue(string|int $status): string|int|bool
    {
        return $status;
    }
}