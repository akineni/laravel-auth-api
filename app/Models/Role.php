<?php

namespace App\Models;

use App\Models\Concerns\{HasCommonFilterScopes, HasSearchScope};
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property string $id
 * @property string $name
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role applyFilters(array $filters = [], ?array $searchFields = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role createdBetween(?string $startDate = null, ?string $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role filterStatus(?mixed $status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role forActionModule(?string $actionModule)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role search(?string $term, ?array $fields = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role withoutPermission($permissions)
 * @mixin \Eloquent
 */
class Role extends SpatieRole
{
    use HasUuids, HasSearchScope, HasCommonFilterScopes;

    public function getSearchableColumns(): array
    {
        return [
            'name',
            'guard_name',
        ];
    }

    public function getDefaultSearchFields(): array
    {
        return [
            'name',
        ];
    }
}