<?php

namespace App\Twig;

use App\Service\Icon\StackIconResolver;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

final class IconExtension extends AbstractExtension
{
    public function __construct(
        private readonly StackIconResolver $iconResolver,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('stack_icon_path', $this->path(...)),
            new TwigFunction('stack_icon_svg', $this->svg(...), ['is_safe' => ['html']]),
        ];
    }

    public function path(?string $slug): ?string
    {
        if ($slug === null || $slug === '') {
            return null;
        }

        return $this->iconResolver->resolve($slug);
    }

    /**
     * Returns trusted SVG from public/icons/stack, or an empty string.
     */
    public function svg(?string $slug): string|Markup
    {
        if ($slug === null || $slug === '') {
            return '';
        }

        $svg = $this->iconResolver->resolveSvg($slug);
        if ($svg === null) {
            return '';
        }

        return new Markup($svg, 'UTF-8');
    }
}
