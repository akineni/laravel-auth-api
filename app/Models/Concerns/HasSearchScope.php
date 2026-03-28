<?php

namespace App\Models\Concerns;

use Closure;
use Illuminate\Database\Eloquent\{Builder, Model};

trait HasSearchScope
{
    public function scopeSearch(Builder $query, ?string $term, ?array $fields = null): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $fields = $fields ?: $this->getDefaultSearchFields();
        $fields = array_values(array_unique(array_filter($fields)));

        $allowedFields = array_values(array_filter(
            $fields,
            fn (string $field) => $this->isAllowedSearchField($this, $field)
        ));

        if ($allowedFields === []) {
            return $query;
        }

        return $query->where(function (Builder $searchQuery) use ($allowedFields, $term) {
            foreach ($allowedFields as $field) {
                $this->applySearchField($searchQuery, $this, $field, $term);
            }
        });
    }

    /**
     * Real DB columns on this model that can be searched directly.
     */
    public function getSearchableColumns(): array
    {
        return [];
    }

    /**
     * Virtual searchable aliases.
     *
     * Supported values:
     * - string SQL expression:
     *   'fullname' => "CONCAT(firstname, ' ', lastname)"
     *
     * - list of columns:
     *   'fullname' => ['firstname', 'lastname']
     *
     * - structured array:
     *   'fullname' => ['expression' => "CONCAT(firstname, ' ', lastname)"]
     *   'name' => ['columns' => ['firstname', 'lastname']]
     *
     * - closure:
     *   'fullname' => function (Builder $query, string $term, Model $model) { ... }
     */
    public function getSearchableAliases(): array
    {
        return [];
    }

    /**
     * Relations that are allowed to be searched.
     *
     * Example:
     * ['causer', 'role']
     */
    public function getSearchableRelations(): array
    {
        return [];
    }

    /**
     * Default fields used when no explicit $fields are passed to scopeSearch().
     *
     * Keep this cheap by default.
     * Add relationship fields here only if you want them included in default search.
     */
    public function getDefaultSearchFields(): array
    {
        return array_merge(
            $this->getSearchableColumns(),
            array_keys($this->getSearchableAliases())
        );
    }

    protected function isAllowedSearchField(Model $model, string $field): bool
    {
        if (!str_contains($field, '.')) {
            return $this->isAllowedDirectSearchField($model, $field);
        }

        [$relation, $nestedField] = explode('.', $field, 2);

        if (!in_array($relation, $model->getSearchableRelations(), true)) {
            return false;
        }

        if (!method_exists($model, $relation)) {
            return false;
        }

        $relatedModel = $model->{$relation}()->getRelated();

        if (!method_exists($relatedModel, 'getSearchableColumns')) {
            return false;
        }

        return $this->isAllowedSearchField($relatedModel, $nestedField);
    }

    protected function isAllowedDirectSearchField(Model $model, string $field): bool
    {
        return in_array($field, $model->getSearchableColumns(), true)
            || array_key_exists($field, $model->getSearchableAliases());
    }

    protected function applySearchField(
        Builder $query,
        Model $model,
        string $field,
        string $term
    ): void {
        $query->orWhere(function (Builder $branch) use ($model, $field, $term) {
            if (str_contains($field, '.')) {
                $this->applyRelationSearch($branch, $model, $field, $term);
                return;
            }

            if (in_array($field, $model->getSearchableColumns(), true)) {
                $branch->where($field, 'LIKE', "%{$term}%");
                return;
            }

            $aliases = $model->getSearchableAliases();

            if (!array_key_exists($field, $aliases)) {
                return;
            }

            $this->applyAliasSearch($branch, $aliases[$field], $term, $model);
        });
    }

    protected function applyRelationSearch(
        Builder $query,
        Model $model,
        string $field,
        string $term
    ): void {
        [$relation, $nestedField] = explode('.', $field, 2);

        if (!in_array($relation, $model->getSearchableRelations(), true)) {
            return;
        }

        if (!method_exists($model, $relation)) {
            return;
        }

        $relatedModel = $model->{$relation}()->getRelated();

        if (!method_exists($relatedModel, 'getSearchableColumns')) {
            return;
        }

        if (!$this->isAllowedSearchField($relatedModel, $nestedField)) {
            return;
        }

        $query->whereHas($relation, function (Builder $relatedQuery) use ($relatedModel, $nestedField, $term) {
            $relatedQuery->where(function (Builder $nestedQuery) use ($relatedModel, $nestedField, $term) {
                $this->applySearchField($nestedQuery, $relatedModel, $nestedField, $term);
            });
        });
    }

    protected function applyAliasSearch(
        Builder $query,
        mixed $definition,
        string $term,
        Model $model
    ): void {
        if ($definition instanceof Closure) {
            $definition($query, $term, $model);
            return;
        }

        if (is_string($definition)) {
            $query->whereRaw("{$definition} LIKE ?", ["%{$term}%"]);
            return;
        }

        if (is_array($definition) && array_is_list($definition)) {
            $query->where(function (Builder $aliasQuery) use ($definition, $term) {
                foreach ($definition as $column) {
                    $aliasQuery->orWhere($column, 'LIKE', "%{$term}%");
                }
            });
            return;
        }

        if (is_array($definition) && isset($definition['expression'])) {
            $query->whereRaw("{$definition['expression']} LIKE ?", ["%{$term}%"]);
            return;
        }

        if (is_array($definition) && isset($definition['columns'])) {
            $query->where(function (Builder $aliasQuery) use ($definition, $term) {
                foreach ($definition['columns'] as $column) {
                    $aliasQuery->orWhere($column, 'LIKE', "%{$term}%");
                }
            });
        }
    }
}