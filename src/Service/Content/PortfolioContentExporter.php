<?php

namespace App\Service\Content;

use App\Entity\Experience;
use App\Entity\Project;
use App\Entity\SiteSetting;
use App\Entity\SocialLink;
use App\Entity\Stack;
use App\Repository\ExperienceRepository;
use App\Repository\ProjectRepository;
use App\Repository\SiteSettingRepository;
use App\Repository\SocialLinkRepository;
use App\Repository\StackRepository;

/**
 * Builds a portable CMS snapshot. Never includes User rows.
 *
 * @phpstan-type ContentSnapshot array{
 *     site_setting: array<string, mixed>|null,
 *     stacks: list<array<string, mixed>>,
 *     projects: list<array<string, mixed>>,
 *     experiences: list<array<string, mixed>>,
 *     social_links: list<array<string, mixed>>
 * }
 */
final class PortfolioContentExporter
{
    public function __construct(
        private readonly SiteSettingRepository $siteSettings,
        private readonly StackRepository $stacks,
        private readonly ProjectRepository $projects,
        private readonly ExperienceRepository $experiences,
        private readonly SocialLinkRepository $socialLinks,
    ) {
    }

    /**
     * @return ContentSnapshot
     */
    public function export(): array
    {
        return [
            'site_setting' => $this->exportSiteSetting($this->siteSettings->findSingleton()),
            'stacks' => array_map($this->exportStack(...), $this->stacks->findAllOrdered()),
            'projects' => array_map($this->exportProject(...), $this->projects->findAllWithStacks()),
            'experiences' => array_map($this->exportExperience(...), $this->experiences->findAllWithStacks()),
            'social_links' => array_map($this->exportSocialLink(...), $this->socialLinks->findAllOrdered()),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function exportSiteSetting(?SiteSetting $site): ?array
    {
        if (!$site instanceof SiteSetting) {
            return null;
        }

        return [
            'site_name' => $site->getSiteName(),
            'role_title' => $site->getRoleTitle(),
            'headline' => $site->getHeadline(),
            'positioning' => $site->getPositioning(),
            'location' => $site->getLocation(),
            'availability' => $site->getAvailability(),
            'work_mode' => $site->getWorkMode(),
            'about_body' => $site->getAboutBody(),
            'principles' => $site->getPrinciples(),
            'contact_intro' => $site->getContactIntro(),
            'contact_email' => $site->getContactEmail(),
            'projects_intro' => $site->getProjectsIntro(),
            'experience_intro' => $site->getExperienceIntro(),
            'stack_intro' => $site->getStackIntro(),
            'github_intro' => $site->getGithubIntro(),
            'meta_description' => $site->getMetaDescription(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function exportStack(Stack $stack): array
    {
        return [
            'name' => $stack->getName(),
            'category' => $stack->getCategory()->value,
            'icon' => $stack->getIcon(),
            'featured' => $stack->isFeatured(),
            'published' => $stack->isPublished(),
            'sort_order' => $stack->getSortOrder(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function exportProject(Project $project): array
    {
        return [
            'slug' => $project->getSlug(),
            'title' => $project->getTitle(),
            'short_description' => $project->getShortDescription(),
            'description' => $project->getDescription(),
            'role' => $project->getRole(),
            'challenge' => $project->getChallenge(),
            'solution' => $project->getSolution(),
            'demo_url' => $project->getDemoUrl(),
            'github_url' => $project->getGithubUrl(),
            'image' => $project->getImage(),
            'image_alt' => $project->getImageAlt(),
            'featured' => $project->isFeatured(),
            'published' => $project->isPublished(),
            'sort_order' => $project->getSortOrder(),
            'stacks' => $this->stackNames($project->getStacks()->toArray()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function exportExperience(Experience $experience): array
    {
        return [
            'company' => $experience->getCompany(),
            'role' => $experience->getRole(),
            'description' => $experience->getDescription(),
            'bullets' => $experience->getBullets(),
            'start_date' => $experience->getStartDate()->format('Y-m-d'),
            'end_date' => $experience->getEndDate()?->format('Y-m-d'),
            'current' => $experience->isCurrent(),
            'published' => $experience->isPublished(),
            'sort_order' => $experience->getSortOrder(),
            'stacks' => $this->stackNames($experience->getStacks()->toArray()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function exportSocialLink(SocialLink $link): array
    {
        return [
            'platform' => $link->getPlatform(),
            'label' => $link->getLabel(),
            'url' => $link->getUrl(),
            'icon' => $link->getIcon(),
            'sort_order' => $link->getSortOrder(),
            'published' => $link->isPublished(),
        ];
    }

    /**
     * @param list<Stack> $stacks
     *
     * @return list<string>
     */
    private function stackNames(array $stacks): array
    {
        $names = [];
        foreach ($stacks as $stack) {
            $names[] = $stack->getName();
        }

        return $names;
    }
}
