<?php

namespace App\Portfolio;

final class ExperienceEntry
{
    /**
     * @param list<string> $bullets
     * @param list<string> $technologies
     */
    public function __construct(
        public readonly string $period,
        public readonly string $role,
        public readonly string $company,
        public readonly string $summary,
        public readonly array $bullets,
        public readonly array $technologies,
    ) {
    }
}
