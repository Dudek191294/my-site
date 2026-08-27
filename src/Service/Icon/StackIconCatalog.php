<?php

namespace App\Service\Icon;

/**
 * Catalog of locally imported stack icons (public/icons/stack/*.svg).
 * Does not scan on every public page render — used by admin / importer.
 */
final class StackIconCatalog
{
    public function __construct(
        private readonly StackIconResolver $resolver,
    ) {
    }

    /**
     * @return list<string> sorted icon slugs available locally
     */
    public function listSlugs(): array
    {
        $dir = $this->resolver->getIconsDirectory();
        if ($dir === '' || !is_dir($dir)) {
            return [];
        }

        $slugs = [];
        foreach (scandir($dir) ?: [] as $file) {
            if (!str_ends_with($file, '.svg')) {
                continue;
            }

            $slug = substr($file, 0, -4);
            if ($this->resolver->exists($slug)) {
                $slugs[] = $slug;
            }
        }

        sort($slugs, \SORT_STRING);

        return $slugs;
    }

    /**
     * ChoiceField map: label => value (slug => slug, humanized label).
     *
     * @return array<string, string>
     */
    public function choices(): array
    {
        $choices = [];
        foreach ($this->listSlugs() as $slug) {
            $choices[$this->labelFor($slug)] = $slug;
        }

        return $choices;
    }

    public function labelFor(string $slug): string
    {
        $known = [
            'php' => 'PHP',
            'postgresql' => 'PostgreSQL',
            'tailwindcss' => 'Tailwind CSS',
            'github' => 'GitHub',
            'css' => 'CSS',
            'html5' => 'HTML5',
            'javascript' => 'JavaScript',
            'typescript' => 'TypeScript',
        ];

        if (isset($known[$slug])) {
            return $known[$slug];
        }

        return ucfirst($slug);
    }
}
