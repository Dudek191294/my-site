<?php

namespace App\Portfolio;

final class ProjectProvider
{
    /**
     * @return list<Project>
     */
    public function all(): array
    {
        return [
            $this->placeholder(
                slug: 'project-one',
                name: '[PROJECT NAME 1]',
            ),
            $this->placeholder(
                slug: 'project-two',
                name: '[PROJECT NAME 2]',
            ),
            $this->placeholder(
                slug: 'project-three',
                name: '[PROJECT NAME 3]',
            ),
        ];
    }

    public function findBySlug(string $slug): ?Project
    {
        foreach ($this->all() as $project) {
            if ($project->slug === $slug) {
                return $project;
            }
        }

        return null;
    }

    private function placeholder(string $slug, string $name): Project
    {
        return new Project(
            slug: $slug,
            name: $name,
            summary: '[Short summary of what this project does and the problem it addresses.]',
            stack: ['[TECH]', '[TECH]', '[TECH]'],
            demoUrl: '[DEMO_URL]',
            githubUrl: '[GITHUB_URL]',
            image: '',
            imageAlt: $name.' screenshot placeholder',
            overview: '[Overview — context and goal of the project.]',
            problem: '[Problem — what was broken, missing, or inefficient.]',
            solution: '[Solution — how the product or system addressed the problem.]',
            role: '[Role — your responsibilities on this project.]',
            architecture: [
                'frontend' => '[Frontend layer description]',
                'api' => '[API layer description]',
                'backend' => '[Backend layer description]',
                'database' => '[Database layer description]',
                'infrastructure' => '[Infrastructure layer description]',
            ],
            technicalDecisions: [
                '[Technical decision 1 and why]',
                '[Technical decision 2 and why]',
            ],
            challenges: [
                '[Challenge 1 and how it was handled]',
                '[Challenge 2 and how it was handled]',
            ],
            result: '[Result — qualitative outcome only; no invented KPIs.]',
            lessonsLearned: [
                '[Lesson learned 1]',
                '[Lesson learned 2]',
            ],
        );
    }
}
