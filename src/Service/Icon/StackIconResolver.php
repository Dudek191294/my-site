<?php

namespace App\Service\Icon;

/**
 * Maps a validated icon slug to a local public SVG under public/icons/stack/.
 * Never accepts filesystem paths — only lowercase alphanumeric slugs.
 */
final class StackIconResolver
{
    private const SLUG_PATTERN = '/^[a-z0-9]{1,80}$/';

    private readonly string $iconsDirectory;

    public function __construct(string $projectDir)
    {
        $configured = $projectDir.'/public/icons/stack';
        $resolved = realpath($configured);

        $this->iconsDirectory = $resolved !== false ? $resolved : '';
    }

    /**
     * Public web path, e.g. /icons/stack/react.svg — or null when missing/invalid.
     */
    public function resolve(string $slug): ?string
    {
        if (!$this->isValidSlug($slug) || !$this->fileExists($slug)) {
            return null;
        }

        return '/icons/stack/'.strtolower(trim($slug)).'.svg';
    }

    /**
     * Inline SVG markup from the local public icons directory, or null.
     */
    public function resolveSvg(string $slug): ?string
    {
        $path = $this->absolutePath($slug);
        if ($path === null) {
            return null;
        }

        $svg = file_get_contents($path);
        if ($svg === false || !str_contains($svg, '<svg')) {
            return null;
        }

        return $svg;
    }

    public function exists(string $slug): bool
    {
        return $this->absolutePath($slug) !== null;
    }

    public function getIconsDirectory(): string
    {
        return $this->iconsDirectory;
    }

    private function isValidSlug(string $slug): bool
    {
        $slug = strtolower(trim($slug));

        return $slug !== '' && preg_match(self::SLUG_PATTERN, $slug) === 1;
    }

    private function absolutePath(string $slug): ?string
    {
        if (!$this->isValidSlug($slug) || $this->iconsDirectory === '') {
            return null;
        }

        $slug = strtolower(trim($slug));
        $candidate = $this->iconsDirectory.\DIRECTORY_SEPARATOR.$slug.'.svg';
        $realPath = realpath($candidate);

        if ($realPath === false) {
            return null;
        }

        $prefix = $this->iconsDirectory.\DIRECTORY_SEPARATOR;
        if (!str_starts_with($realPath, $prefix)) {
            return null;
        }

        if (!is_file($realPath) || !is_readable($realPath)) {
            return null;
        }

        return $realPath;
    }

    private function fileExists(string $slug): bool
    {
        return $this->absolutePath($slug) !== null;
    }
}
