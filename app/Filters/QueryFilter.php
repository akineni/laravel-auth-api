<?php

namespace App\Filters;

use App\Enums\UserStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class QueryFilter
{
    protected Builder $query;
    protected array $filters = [];

    public function __construct(Builder $query, array $filters = [])
    {
        $this->query = $query;
        $this->filters = $filters;
    }

    public static function apply(Builder $query, array $filters = []): Builder
    {
        return (new static($query, $filters))->handle();
    }

    public function handle(): Builder
    {
        $this->applySearch();
        $this->applyStatus();
        $this->applyDateRange();
        $this->applyActionModule();

        return $this->query;
    }

    protected function applySearch(): void
    {
        if (empty($this->filters['search']) || empty($this->filters['searchable'])) {
            return;
        }

        $term = $this->filters['search'];
        $model = $this->query->getModel();

        $this->query->where(function ($query) use ($term, $model) {
            foreach ($this->filters['searchable'] as $column) {
                $query->orWhere(function ($q) use ($column, $term, $model) {
                    $this->applySearchColumn($q, $model, $column, $term);
                });
            }
        });
    }

    /**
     * Recursively apply search for a column or nested relationship.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $column
     * @param string $term
     */
    protected function applySearchColumn($query, $model, string $column, string $term): void
    {
        // Relationship (causer.fullname, causer.email, etc.)
        if (str_contains($column, '.')) {
            [$relation, $nested] = explode('.', $column, 2);

            $query->whereHas($relation, function ($relQuery) use ($nested, $term) {
                $relatedModel = $relQuery->getModel();
                $expressions = method_exists($relatedModel, 'getSearchableExpressions')
                    ? $relatedModel->getSearchableExpressions()
                    : [];

                // Virtual column on related model (fullname)
                if (isset($expressions[$nested])) {
                    $relQuery->where(function ($r) use ($expressions, $nested, $term) {
                        foreach ((array) $expressions[$nested] as $expr) {
                            $r->orWhereRaw("{$expr} LIKE ?", ["%{$term}%"]);
                        }
                    });
                } else {
                    $relQuery->where($nested, 'LIKE', "%{$term}%");
                }
            });

            return;
        }

        // Virtual column on root model
        $expressions = method_exists($model, 'getSearchableExpressions')
            ? $model->getSearchableExpressions()
            : [];

        if (isset($expressions[$column])) {
            $query->where(function ($q) use ($expressions, $column, $term) {
                foreach ((array) $expressions[$column] as $expr) {
                    $q->orWhereRaw("{$expr} LIKE ?", ["%{$term}%"]);
                }
            });
            return;
        }

        // Normal column
        $query->where($column, 'LIKE', "%{$term}%");
    }

    protected function applyStatus(): void
    {
        $status = $this->filters['status'] ?? null;
        if (is_null($status)) return;

        $this->query->when(
            $this->query->getModel()->isFillable('status'),
            fn($q) => $q->where('status', $status),
            fn($q) => $q->where('is_active', $status === UserStatusEnum::ACTIVE->value)
        );
    }

    protected function applyDateRange(): void
    {
        $start = $this->filters['start_date'] ?? null;
        $end = $this->filters['end_date'] ?? null;

        if (!$start && !$end) return;

        $start = $start ? Carbon::parse($start)->startOfDay() : null;
        $end = $end ? Carbon::parse($end)->endOfDay() : null;

        if ($start && $end) {
            $this->query->whereBetween('created_at', [$start, $end]);
        } elseif ($start) {
            $this->query->where('created_at', '>=', $start);
        } elseif ($end) {
            $this->query->where('created_at', '<=', $end);
        }
    }

    protected function applyActionModule(): void
    {
        if (empty($this->filters['action_module'] ?? null)) {
            return;
        }

        $this->query->where('action_module', $this->filters['action_module']);
    }
}
