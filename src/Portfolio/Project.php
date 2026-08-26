<?php

namespace App\Portfolio;

final class Project
{
    /**
     * @param list<string>                $stack
     * @param array{
     *     frontend: string,
     *     api: string,
     *     backend: string,
     *     database: string,
     *     infrastructure: string
     * }                                  $architecture
     * @param list<string>                $technicalDecisions
     * @param list<string>                $challenges
     * @param list<string>                $lessonsLearned
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly string $summary,
        public readonly array $stack,
        public readonly string $demoUrl,
        public readonly string $githubUrl,
        public readonly string $image,
        public readonly string $imageAlt,
        public readonly string $overview,
        public readonly string $problem,
        public readonly string $solution,
        public readonly string $role,
        public readonly array $architecture,
        public readonly array $technicalDecisions,
        public readonly array $challenges,
        public readonly string $result,
        public readonly array $lessonsLearned,
    ) {
    }
}
