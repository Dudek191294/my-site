<?php

namespace App\Service\Icon;

/**
 * Locates the installed simple-icons/simple-icons Composer package and exposes its catalog.
 */
final class SimpleIconsPackage
{
    private const SLUG_PATTERN = '/^[a-z0-9]{1,80}$/';

  /** @var list<string>|null */
    private ?array $slugsCache = null;

    /** @var array<string, string>|null slug => human title */
    private ?array $titleMapCache = null;

    public function isInstalled(): bool
    {
        return $this->getPackagePath() !== null;
    }

    public function getPackagePath(): ?string
    {
        $path = \Composer\InstalledVersions::getInstallPath('simple-icons/simple-icons');
        if ($path === null) {
            return null;
        }

        $resolved = realpath($path);

        return $resolved !== false ? $resolved : null;
    }

    public function getIconsSourceDir(): ?string
    {
        $packagePath = $this->getPackagePath();
        if ($packagePath === null) {
            return null;
        }

        $resolved = realpath($packagePath.'/icons');
        if ($resolved === false || !is_dir($resolved)) {
            return null;
        }

        return $resolved;
    }

    public function getMetadataPath(): ?string
    {
        $packagePath = $this->getPackagePath();
        if ($packagePath === null) {
            return null;
        }

        $candidate = $packagePath.'/data/simple-icons.json';
        if (!is_file($candidate) || !is_readable($candidate)) {
            return null;
        }

        return $candidate;
    }

    /**
     * @return list<string> sorted slugs available in the package icons/ directory
     */
    public function listSlugs(): array
    {
        if ($this->slugsCache !== null) {
            return $this->slugsCache;
        }

        $sourceDir = $this->getIconsSourceDir();
        if ($sourceDir === null) {
            return $this->slugsCache = [];
        }

        $slugs = [];
        foreach (scandir($sourceDir) ?: [] as $file) {
            if (!str_ends_with($file, '.svg')) {
                continue;
            }

            $slug = substr($file, 0, -4);
            if ($this->isValidSlug($slug) && is_file($sourceDir.\DIRECTORY_SEPARATOR.$slug.'.svg')) {
                $slugs[] = $slug;
            }
        }

        sort($slugs, \SORT_STRING);

        return $this->slugsCache = $slugs;
    }

    /**
     * @return array<string, string> slug => title
     */
    public function titleMap(): array
    {
        if ($this->titleMapCache !== null) {
            return $this->titleMapCache;
        }

        $map = [];
        $available = array_fill_keys($this->listSlugs(), true);

        $metadataPath = $this->getMetadataPath();
        if ($metadataPath !== null) {
            $json = file_get_contents($metadataPath);
            if ($json !== false) {
                /** @var list<array{title: string, slug?: string}>|null $entries */
                $entries = json_decode($json, true);
                if (\is_array($entries)) {
                    foreach ($entries as $entry) {
                        if (!isset($entry['title']) || !\is_string($entry['title'])) {
                            continue;
                        }

                        $slug = isset($entry['slug']) && \is_string($entry['slug'])
                            ? strtolower(trim($entry['slug']))
                            : self::slugFromTitle($entry['title']);

                        if (!isset($available[$slug])) {
                            continue;
                        }

                        $map[$slug] = $entry['title'];
                    }
                }
            }
        }

        foreach (array_keys($available) as $slug) {
            if (!isset($map[$slug])) {
                $map[$slug] = ucfirst($slug);
            }
        }

        uasort($map, static fn (string $a, string $b): int => strcasecmp($a, $b));

        return $this->titleMapCache = $map;
    }

    public function titleFor(string $slug): string
    {
        $slug = strtolower(trim($slug));

        return $this->titleMap()[$slug] ?? ucfirst($slug);
    }

    public function isValidSlug(string $slug): bool
    {
        $slug = strtolower(trim($slug));

        return $slug !== '' && preg_match(self::SLUG_PATTERN, $slug) === 1;
    }

    public function sourcePathFor(string $slug): ?string
    {
        if (!$this->isValidSlug($slug)) {
            return null;
        }

        $sourceDir = $this->getIconsSourceDir();
        if ($sourceDir === null) {
            return null;
        }

        $slug = strtolower(trim($slug));
        $candidate = $sourceDir.\DIRECTORY_SEPARATOR.$slug.'.svg';
        $resolved = realpath($candidate);

        if ($resolved === false || !str_starts_with($resolved, $sourceDir.\DIRECTORY_SEPARATOR) || !is_file($resolved)) {
            return null;
        }

        return $resolved;
    }

  /**
   * Derives a Simple Icons slug from a brand title (matches package naming rules).
   */
    public static function slugFromTitle(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = str_replace('.', 'dot', $slug);
        $slug = str_replace('+', 'plus', $slug);
        $slug = str_replace('#', 'sharp', $slug);
        $slug = str_replace('&', 'and', $slug);

        return (string) preg_replace('/[^a-z0-9]/', '', $slug);
    }
}
