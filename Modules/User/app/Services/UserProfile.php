<?php

namespace Modules\User\Services;

use Illuminate\Support\Str;

final class UserProfile
{
    /**
     * @param  iterable<int, string>  $skills
     * @return list<string>
     */
    public function normalizeSkills(iterable $skills): array
    {
        return collect($skills)
            ->map(fn (string $name): string => Str::squish($name))
            ->filter()
            ->unique(fn (string $name): string => mb_strtolower($name))
            ->take(20)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string|null>  $links
     * @return array<string, string>
     */
    public function normalizeSocialLinks(array $links): array
    {
        return collect($links)
            ->map(fn (?string $url): ?string => filled($url) ? trim($url) : null)
            ->filter()
            ->all();
    }
}
