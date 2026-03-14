<?php

namespace App\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait HasCommonFilterScopes
{
    public function scopeFilterStatus(Builder $query, mixed $status): Builder
    {
        if ($status === null || $status === '') {
            return $query;
        }

        $column = $this->getStatusFilterColumn();

        if (!$column) {
            return $query;
        }

        return $query->where(
            $column,
            $this->normalizeStatusFilterValue($status, $column)
        );
    }

    public function scopeCreatedBetween(
        Builder $query,
        ?string $startDate = null,
        ?string $endDate = null,
        string $column = 'created_at'
    ): Builder {
        if (!$startDate && !$endDate) {
            return $query;
        }

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        if ($start && $end) {
            return $query->whereBetween($column, [$start, $end]);
        }

        if ($start) {
            return $query->where($column, '>=', $start);
        }

        return $query->where($column, '<=', $end);
    }

    public function scopeForActionModule(Builder $query, ?string $actionModule): Builder
    {
        if (!$actionModule) {
            return $query;
        }

        return $query->where('action_module', $actionModule);
    }

    /**
     * Optional convenience scope to apply all filters in one place,
     * while still keeping each concern in its own dedicated scope.
     */
    public function scopeApplyFilters(
        Builder $query,
        array $filters = [],
        ?array $searchFields = null
    ): Builder {
        return $query
            ->search($filters['search'] ?? null, $searchFields)
            ->filterStatus($filters['status'] ?? null)
            ->createdBetween(
                $filters['start_date'] ?? null,
                $filters['end_date'] ?? null
            )
            ->forActionModule($filters['action_module'] ?? null);
    }

    /**
     * Override this on each model that supports status filtering.
     *
     * Example return values:
     * - 'status'
     * - 'is_active'
     */
    protected function getStatusFilterColumn(): ?string
    {
        return null;
    }

    /**
     * Override when status needs transformation.
     * Example: map "active" => true for boolean columns.
     */
    protected function normalizeStatusFilterValue(mixed $status, string $column): mixed
    {
        return $status;
    }
}