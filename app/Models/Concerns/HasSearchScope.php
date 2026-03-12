<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasSearchScope
{
    /**
     * Apply a search filter across given columns.
     */
    public function scopeSearch(Builder $query, ?string $term, array $searchable = []): Builder
    {
        if (blank($term) || empty($searchable)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term, $searchable) {
            foreach ($searchable as $column) {
                if (str_contains($column, '.')) {
                    [$relation, $relColumn] = explode('.', $column, 2);

                    $q->orWhereHas($relation, function (Builder $relQuery) use ($relColumn, $term) {
                        $relQuery->where($relColumn, 'like', "%{$term}%");
                    });
                } else {
                    $q->orWhere($column, 'like', "%{$term}%");
                }
            }
        });
    }
}