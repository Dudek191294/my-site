<?php

namespace App\Service\Icon;

/**
 * Catalog of Simple Icons available from the Composer package (for admin picker).
 */
final class StackIconCatalog
{
    public function __construct(
        private readonly SimpleIconsPackage $package,
        private readonly StackIconResolver $resolver,
    ) {
    }

    /**
     * @return list<string> sorted icon slugs available locally in public/icons/stack/
     */
    public function listLocalSlugs(): array
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
     * ChoiceField map: label => value (human title + slug => slug).
     *
     * @return array<string, string>
     */
    public function choices(): array
    {
        if (!$this->package->isInstalled()) {
            return $this->localChoices();
        }

        $choices = [];
        foreach ($this->package->titleMap() as $slug => $title) {
            $choices[sprintf('%s (%s)', $title, $slug)] = $slug;
        }

        return $choices;
    }

    public function labelFor(string $slug): string
    {
        if ($this->package->isInstalled()) {
            return $this->package->titleFor($slug);
        }

        return ucfirst($slug);
    }

    /**
     * @return array<string, string>
     */
    private function localChoices(): array
    {
        $choices = [];
        foreach ($this->listLocalSlugs() as $slug) {
            $choices[$this->labelFor($slug).' ('.$slug.')'] = $slug;
        }

        return $choices;
    }
}
