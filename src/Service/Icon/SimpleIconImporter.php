<?php

namespace App\Service\Icon;

/**
 * Copies SVG files from the Simple Icons Composer package into public/icons/stack/.
 */
final class SimpleIconImporter
{
    public function __construct(
        private readonly string $projectDir,
        private readonly SimpleIconsPackage $package,
    ) {
    }

    public function import(string $slug, bool $force = false): SimpleIconImportReport
    {
        return $this->importMany([$slug], $force);
    }

    /**
     * @param list<string> $slugs
     */
    public function importMany(array $slugs, bool $force = false): SimpleIconImportReport
    {
        if (!$this->package->isInstalled()) {
            return new SimpleIconImportReport(errors: ['Package simple-icons/simple-icons is not installed.']);
        }

        $targetDir = $this->ensureTargetDirectory();
        if ($targetDir === null) {
            return new SimpleIconImportReport(errors: [sprintf('Unable to create or resolve target directory: %s/public/icons/stack', $this->projectDir)]);
        }

        $added = [];
        $skipped = [];
        $errors = [];

        foreach (array_values(array_unique(array_map(
            static fn (string $slug): string => strtolower(trim($slug)),
            $slugs,
        ))) as $slug) {
            if ($slug === '') {
                continue;
            }

            if (!$this->package->isValidSlug($slug)) {
                $errors[] = sprintf('%s — invalid slug (expected a–z / 0–9 only)', $slug);

                continue;
            }

            $sourceReal = $this->package->sourcePathFor($slug);
            if ($sourceReal === null) {
                $errors[] = sprintf('%s — not found in Simple Icons package', $slug);

                continue;
            }

            $destination = $targetDir.\DIRECTORY_SEPARATOR.$slug.'.svg';
            if (is_file($destination) && !$force) {
                $skipped[] = sprintf('%s — already exists (use --force to overwrite)', $slug);

                continue;
            }

            $svg = file_get_contents($sourceReal);
            if ($svg === false || !str_contains($svg, '<svg')) {
                $errors[] = sprintf('%s — source file is not a readable SVG', $slug);

                continue;
            }

            if (file_put_contents($destination, $svg) === false) {
                $errors[] = sprintf('%s — failed to write %s', $slug, $destination);

                continue;
            }

            $added[] = $slug;
        }

        return new SimpleIconImportReport($added, $skipped, $errors);
    }

    private function ensureTargetDirectory(): ?string
    {
        $targetDir = $this->projectDir.'/public/icons/stack';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            return null;
        }

        $resolved = realpath($targetDir);

        return $resolved !== false ? $resolved : null;
    }
}
