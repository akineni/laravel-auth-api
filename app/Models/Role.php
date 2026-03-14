<?php

namespace App\Models;

use App\Models\Concerns\{HasCommonFilterScopes, HasSearchScope};
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Models\Role as SpatieRole;

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