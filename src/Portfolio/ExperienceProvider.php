<?php

namespace App\Portfolio;

final class ExperienceProvider
{
    /**
     * @return list<ExperienceEntry>
     */
    public function all(): array
    {
        return [
            new ExperienceEntry(
                period: '[YEAR] — [YEAR]',
                role: '[ROLE TITLE]',
                company: '[COMPANY NAME]',
                summary: '[Short description of scope and impact — not a duty list.]',
                bullets: [
                    '[Achievement / impact]',
                    '[Responsibility with outcome]',
                    '[Technical challenge and approach]',
                    '[Result — qualitative only; no invented KPIs]',
                ],
                technologies: ['[TECH]', '[TECH]', '[TECH]'],
            ),
            new ExperienceEntry(
                period: '[YEAR] — [YEAR]',
                role: '[ROLE TITLE]',
                company: '[COMPANY NAME]',
                summary: '[Short description of scope and impact — not a duty list.]',
                bullets: [
                    '[Achievement / impact]',
                    '[Responsibility with outcome]',
                    '[Technical challenge and approach]',
                    '[Result — qualitative only; no invented KPIs]',
                ],
                technologies: ['[TECH]', '[TECH]', '[TECH]'],
            ),
        ];
    }
}
