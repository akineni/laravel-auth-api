<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\{Arr, Str};

class UsernameService
{
    protected array $wordBank = [
        'echo', 'nova', 'pixel', 'grid', 'orbit', 'mint',
        'spark', 'wave', 'node', 'zen', 'prime', 'core',
        'flux', 'nest', 'lumen', 'path', 'shift', 'vibe',
    ];

    public function normalize(string $username): string
    {
        return $this->sanitize($username);
    }

    public function exists(string $username, Model|string $model, ?string $ignoreId = null): bool
    {
        $username = $this->sanitize($username);
        $modelClass = is_string($model) ? $model : $model::class;

        if ($username === '') {
            return false;
        }

        return $modelClass::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('username', $username)
            ->exists();
    }

    public function isAvailable(string $username, Model|string $model, ?string $ignoreId = null): bool
    {
        return !$this->exists($username, $model, $ignoreId);
    }

    public function generateUnique(?string $firstname, ?string $lastname, Model|string $model): string
    {
        $first = $this->sanitize($firstname ?? '');
        $last = $this->sanitize($lastname ?? '');
        $year = now()->format('y');

        $families = [
            $this->shuffleCandidates($this->classicFamily($first, $last, $year)),
            $this->shuffleCandidates($this->compactFamily($first, $last, $year)),
            $this->shuffleCandidates($this->brandedFamily($first, $last)),
            $this->shuffleCandidates($this->wordBankFamily($first, $last)),
        ];

        foreach ($families as $family) {
            foreach ($family as $candidate) {
                if ($this->isAvailable($candidate, $model)) {
                    return $candidate;
                }
            }
        }

        return $this->randomFallback($first, $last, $model);
    }

    public function suggest(string $desiredUsername, Model|string $model, int $limit = 5): array
    {
        $base = $this->sanitize($desiredUsername);

        if ($base === '') {
            $base = 'user';
        }

        $suggestions = [];
        $templates = [
            fn () => "{$base}_" . random_int(10, 99),
            fn () => "{$base}." . random_int(10, 99),
            fn () => "{$base}" . random_int(100, 9999),
            fn () => "{$base}_" . $this->randomWord(),
            fn () => "{$base}." . $this->randomWord(),
            fn () => $this->randomWord() . "_{$base}",
            fn () => 'real' . $base,
            fn () => 'the' . $base,
            fn () => "{$base}_" . now()->format('y'),
            fn () => "{$base}.hq",
        ];

        while (count($suggestions) < $limit) {
            $template = Arr::random($templates);
            $candidate = $this->sanitize($template());

            if (
                $candidate !== ''
                && !in_array($candidate, $suggestions, true)
                && $this->isAvailable($candidate, $model)
            ) {
                $suggestions[] = $candidate;
            }
        }

        return $suggestions;
    }

    protected function classicFamily(string $first, string $last, string $year): array
    {
        $firstInitial = $first !== '' ? Str::substr($first, 0, 1) : '';
        $lastInitial = $last !== '' ? Str::substr($last, 0, 1) : '';

        return $this->filterCandidates([
            "{$first}{$last}",
            "{$first}.{$last}",
            "{$first}_{$last}",
            "{$last}{$first}",
            "{$last}.{$first}",
            "{$last}_{$first}",
            "{$first}{$year}",
            "{$first}_{$year}",
            "{$first}.{$year}",
            "{$first}{$lastInitial}",
            "{$firstInitial}{$last}",
            "{$first}_{$last}" . random_int(10, 99),
            "{$first}.{$last}" . random_int(10, 99),
        ]);
    }

    protected function compactFamily(string $first, string $last, string $year): array
    {
        $firstInitial = $first !== '' ? Str::substr($first, 0, 1) : '';
        $lastInitial = $last !== '' ? Str::substr($last, 0, 1) : '';

        return $this->filterCandidates([
            "{$first}" . random_int(10, 99),
            "{$last}" . random_int(10, 99),
            "{$firstInitial}{$last}",
            "{$first}{$lastInitial}",
            "{$firstInitial}_{$last}",
            "{$first}.{$lastInitial}",
            "{$first}{$year}" . random_int(1, 9),
            "{$last}{$year}",
        ]);
    }

    protected function brandedFamily(string $first, string $last): array
    {
        return $this->filterCandidates([
            "real{$first}",
            "the{$first}",
            "its{$first}",
            "hey{$first}",
            "{$first}_official",
            "{$first}.hq",
            "{$last}.hq",
            "team{$first}",
        ]);
    }

    protected function wordBankFamily(string $first, string $last): array
    {
        $word1 = $this->randomWord();
        $word2 = $this->randomWord();

        return $this->filterCandidates([
            "{$first}.{$word1}",
            "{$first}_{$word1}",
            "{$word1}_{$first}",
            "{$word1}.{$first}",
            "{$last}.{$word1}",
            "{$first}{$word1}",
            "{$first}_{$word2}",
            "{$word2}_{$last}",
        ]);
    }

    protected function randomFallback(string $first, string $last, Model|string $model): string
    {
        $baseFirst = $first !== '' ? $first : 'user';
        $baseLast = $last !== '' ? $last : 'member';

        $templates = [
            fn () => "{$baseFirst}_{$baseLast}" . random_int(10, 99),
            fn () => "{$baseFirst}.{$baseLast}" . random_int(10, 99),
            fn () => "{$baseFirst}_" . $this->randomWord(),
            fn () => "{$baseFirst}." . $this->randomWord(),
            fn () => $this->randomWord() . "_{$baseFirst}",
            fn () => "{$baseFirst}{$baseLast}" . random_int(100, 9999),
            fn () => "{$baseFirst}_" . $this->randomWord() . random_int(10, 99),
            fn () => Str::substr($baseFirst, 0, 1) . "{$baseLast}_" . $this->randomWord(),
        ];

        do {
            $template = Arr::random($templates);
            $candidate = $this->sanitize($template());
        } while ($candidate === '' || !$this->isAvailable($candidate, $model));

        return $candidate;
    }

    protected function randomWord(): string
    {
        return Arr::random($this->wordBank);
    }

    protected function shuffleCandidates(array $candidates): array
    {
        shuffle($candidates);

        return $candidates;
    }

    protected function filterCandidates(array $candidates): array
    {
        $sanitized = array_map(fn ($candidate) => $this->sanitize($candidate), $candidates);
        $filtered = array_filter($sanitized, fn ($candidate) => $candidate !== '');

        return array_values(array_unique($filtered));
    }

    protected function sanitize(string $username): string
    {
        return Str::of($username)
            ->lower()
            ->replaceMatches('/[^a-z0-9._]/', '')
            ->trim('._')
            ->limit(24, '')
            ->value();
    }
}