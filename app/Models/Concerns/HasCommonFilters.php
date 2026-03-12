<?php

namespace App\Models\Concerns;

trait HasCommonFilters
{
    use HasSearchScope;
    use HasDateRangeScope;
}