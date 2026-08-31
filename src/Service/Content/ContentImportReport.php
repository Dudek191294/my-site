<?php

namespace App\Service\Content;

final class ContentImportReport
{
    public int $stacksCreated = 0;
    public int $stacksUpdated = 0;
    public int $projectsCreated = 0;
    public int $projectsUpdated = 0;
    public int $experiencesCreated = 0;
    public int $experiencesUpdated = 0;
    public int $socialLinksCreated = 0;
    public int $socialLinksUpdated = 0;
    public bool $siteSettingWritten = false;

    /**
     * @var list<string>
     */
    public array $warnings = [];
}
