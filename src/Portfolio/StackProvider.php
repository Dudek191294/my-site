<?php

namespace App\Portfolio;

final class StackProvider
{
    /**
     * @return array<string, list<string>>
     */
    public function categories(): array
    {
        return [
            'Frontend' => ['[TECH]', '[TECH]', '[TECH]'],
            'Backend' => ['[TECH]', '[TECH]', '[TECH]'],
            'Database' => ['[TECH]', '[TECH]'],
            'Infrastructure' => ['[TECH]', '[TECH]', '[TECH]'],
            'Tools' => ['[TECH]', '[TECH]', '[TECH]'],
        ];
    }
}
