<?php

namespace App\Twig;

use App\Repository\SiteSettingRepository;
use App\Repository\SocialLinkRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class SiteGlobalsExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly SiteSettingRepository $siteSettings,
        private readonly SocialLinkRepository $socialLinks,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'site' => $this->siteSettings->findSingleton(),
            'socialLinks' => $this->socialLinks->findPublishedOrdered(),
        ];
    }
}
