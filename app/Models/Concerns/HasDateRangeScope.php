<?php

namespace App\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait HasDateRangeScope
{
    /**
     * Filter records by created_at date range.
     */
    public function scopeCreatedBetween(
        Builder $query,
        ?string $startDate = null,
        ?string $endDate = null
    ): Builder {
        if (! $startDate && ! $endDate) {
            return $query;
        }

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        return $query
            ->when($start && $end, fn (Builder $q) => $q->whereBetween('created_at', [$start, $end]))
            ->when($start && ! $end, fn (Builder $q) => $q->where('created_at', '>=', $start))
            ->when(! $start && $end, fn (Builder $q) => $q->where('created_at', '<=', $end));
    }
}