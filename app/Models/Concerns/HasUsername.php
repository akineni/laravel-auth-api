<?php

namespace App\Models\Concerns;

use App\Services\UsernameService;

trait HasUsername
{
    public static function bootHasUsername(): void
    {
        static::creating(function ($model) {
            /** @var UsernameService $usernameService */
            $usernameService = app(UsernameService::class);

            if (blank($model->username)) {
                [$first, $last] = $model->usernameSourceAttributes();

                $model->username = $usernameService->generateUnique(
                    $first,
                    $last,
                    $model
                );

                return;
            }

            $model->username = $usernameService->normalize($model->username);
        });
    }

    protected function usernameSourceAttributes(): array
    {
        return [
            $this->firstname ?? null,
            $this->lastname ?? null,
        ];
    }
}