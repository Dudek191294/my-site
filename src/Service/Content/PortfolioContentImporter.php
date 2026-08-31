<?php

namespace App\Service\Content;

use App\Entity\Experience;
use App\Entity\Project;
use App\Entity\SiteSetting;
use App\Entity\SocialLink;
use App\Entity\Stack;
use App\Entity\StackCategory;
use App\Repository\ExperienceRepository;
use App\Repository\ProjectRepository;
use App\Repository\SiteSettingRepository;
use App\Repository\SocialLinkRepository;
use App\Repository\StackRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Idempotent CMS upsert. Never touches User. Never deletes rows missing from the file.
 */
final class PortfolioContentImporter
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SiteSettingRepository $siteSettings,
        private readonly StackRepository $stacks,
        private readonly ProjectRepository $projects,
        private readonly ExperienceRepository $experiences,
        private readonly SocialLinkRepository $socialLinks,
    ) {
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    public function import(array $snapshot, bool $dryRun = false): ContentImportReport
    {
        $report = new ContentImportReport();

        $this->entityManager->beginTransaction();
        try {
            $this->importSiteSetting($snapshot['site_setting'] ?? null, $report);
            $this->importStacks($this->listOfMaps($snapshot['stacks'] ?? []), $report);
            $this->entityManager->flush();

            $this->importProjects($this->listOfMaps($snapshot['projects'] ?? []), $report);
            $this->importExperiences($this->listOfMaps($snapshot['experiences'] ?? []), $report);
            $this->importSocialLinks($this->listOfMaps($snapshot['social_links'] ?? []), $report);
            $this->entityManager->flush();

            if ($dryRun) {
                $this->entityManager->rollback();
            } else {
                $this->entityManager->commit();
            }
        } catch (\Throwable $exception) {
            $this->entityManager->rollback();
            throw $exception;
        }

        return $report;
    }

    private function importSiteSetting(mixed $row, ContentImportReport $report): void
    {
        if ($row === null) {
            return;
        }

        $data = $this->map($row, 'site_setting');
        $site = $this->siteSettings->findSingleton() ?? new SiteSetting();

        $site->setSiteName($this->string($data, 'site_name'));
        $site->setRoleTitle($this->string($data, 'role_title'));
        $site->setHeadline($this->string($data, 'headline'));
        $site->setPositioning($this->string($data, 'positioning'));
        $site->setLocation($this->nullableString($data, 'location'));
        $site->setAvailability($this->nullableString($data, 'availability'));
        $site->setWorkMode($this->nullableString($data, 'work_mode'));
        $site->setAboutBody($this->string($data, 'about_body'));
        $site->setPrinciples($this->stringList($data['principles'] ?? []));
        $site->setContactIntro($this->nullableString($data, 'contact_intro'));
        $site->setContactEmail($this->nullableString($data, 'contact_email'));
        $site->setProjectsIntro($this->nullableString($data, 'projects_intro'));
        $site->setExperienceIntro($this->nullableString($data, 'experience_intro'));
        $site->setStackIntro($this->nullableString($data, 'stack_intro'));
        $site->setGithubIntro($this->nullableString($data, 'github_intro'));
        $site->setMetaDescription($this->nullableString($data, 'meta_description'));

        $this->entityManager->persist($site);
        $report->siteSettingWritten = true;
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function importStacks(array $rows, ContentImportReport $report): void
    {
        foreach ($rows as $index => $data) {
            $name = $this->string($data, 'name');
            if ($name === '') {
                throw new \InvalidArgumentException(sprintf('stacks[%d].name is required.', $index));
            }

            $stack = $this->stacks->findOneByName($name);
            if ($stack instanceof Stack) {
                ++$report->stacksUpdated;
            } else {
                $stack = new Stack();
                $stack->setName($name);
                $this->entityManager->persist($stack);
                ++$report->stacksCreated;
            }

            $category = StackCategory::tryFrom($this->string($data, 'category'));
            if (!$category instanceof StackCategory) {
                throw new \InvalidArgumentException(sprintf('Unknown stack category "%s" for "%s".', $this->string($data, 'category'), $name));
            }

            $stack->setCategory($category);
            $stack->setIcon($this->nullableString($data, 'icon'));
            $stack->setFeatured($this->bool($data, 'featured'));
            $stack->setPublished($this->bool($data, 'published'));
            $stack->setSortOrder($this->int($data, 'sort_order'));
        }
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function importProjects(array $rows, ContentImportReport $report): void
    {
        foreach ($rows as $index => $data) {
            $slug = $this->string($data, 'slug');
            if ($slug === '') {
                throw new \InvalidArgumentException(sprintf('projects[%d].slug is required.', $index));
            }

            $project = $this->projects->findOneBySlug($slug);
            if ($project instanceof Project) {
                ++$report->projectsUpdated;
            } else {
                $project = new Project();
                $project->setSlug($slug);
                $this->entityManager->persist($project);
                ++$report->projectsCreated;
            }

            $project->setTitle($this->string($data, 'title'));
            $project->setShortDescription($this->string($data, 'short_description'));
            $project->setDescription($this->string($data, 'description'));
            $project->setRole($this->nullableString($data, 'role'));
            $project->setChallenge($this->nullableString($data, 'challenge'));
            $project->setSolution($this->nullableString($data, 'solution'));
            $project->setDemoUrl($this->nullableString($data, 'demo_url'));
            $project->setGithubUrl($this->nullableString($data, 'github_url'));
            $project->setImage($this->nullableString($data, 'image'));
            $project->setImageAlt($this->nullableString($data, 'image_alt'));
            $project->setFeatured($this->bool($data, 'featured'));
            $project->setPublished($this->bool($data, 'published'));
            $project->setSortOrder($this->int($data, 'sort_order'));
            $this->replaceStacks($project, $this->stringList($data['stacks'] ?? []), sprintf('projects[%d] (%s)', $index, $slug));
        }
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function importExperiences(array $rows, ContentImportReport $report): void
    {
        foreach ($rows as $index => $data) {
            $company = $this->string($data, 'company');
            $role = $this->string($data, 'role');
            $startDate = $this->date($data, 'start_date');
            if ($company === '' || $role === '') {
                throw new \InvalidArgumentException(sprintf('experiences[%d] needs company and role.', $index));
            }

            $experience = $this->experiences->findOneByIdentity($company, $role, $startDate);
            if ($experience instanceof Experience) {
                ++$report->experiencesUpdated;
            } else {
                $experience = new Experience();
                $experience->setCompany($company);
                $experience->setRole($role);
                $experience->setStartDate($startDate);
                $this->entityManager->persist($experience);
                ++$report->experiencesCreated;
            }

            $experience->setDescription($this->string($data, 'description'));
            $experience->setBullets($this->stringList($data['bullets'] ?? []));
            $experience->setEndDate($this->nullableDate($data, 'end_date'));
            $experience->setCurrent($this->bool($data, 'current'));
            $experience->setPublished($this->bool($data, 'published'));
            $experience->setSortOrder($this->int($data, 'sort_order'));
            $this->replaceExperienceStacks($experience, $this->stringList($data['stacks'] ?? []), sprintf('experiences[%d] (%s)', $index, $company));
        }
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function importSocialLinks(array $rows, ContentImportReport $report): void
    {
        foreach ($rows as $index => $data) {
            $platform = $this->string($data, 'platform');
            if ($platform === '') {
                throw new \InvalidArgumentException(sprintf('social_links[%d].platform is required.', $index));
            }

            $link = $this->socialLinks->findOneByPlatform($platform);
            if ($link instanceof SocialLink) {
                ++$report->socialLinksUpdated;
            } else {
                $link = new SocialLink();
                $link->setPlatform($platform);
                $this->entityManager->persist($link);
                ++$report->socialLinksCreated;
            }

            $link->setLabel($this->string($data, 'label'));
            $link->setUrl($this->string($data, 'url'));
            $link->setIcon($this->nullableString($data, 'icon'));
            $link->setSortOrder($this->int($data, 'sort_order'));
            $link->setPublished($this->bool($data, 'published'));
        }
    }

    /**
     * @param list<string> $names
     */
    private function replaceStacks(Project $project, array $names, string $context): void
    {
        foreach ($project->getStacks()->toArray() as $stack) {
            $project->removeStack($stack);
        }
        foreach ($names as $name) {
            $project->addStack($this->requireStack($name, $context));
        }
    }

    /**
     * @param list<string> $names
     */
    private function replaceExperienceStacks(Experience $experience, array $names, string $context): void
    {
        foreach ($experience->getStacks()->toArray() as $stack) {
            $experience->removeStack($stack);
        }
        foreach ($names as $name) {
            $experience->addStack($this->requireStack($name, $context));
        }
    }

    private function requireStack(string $name, string $context): Stack
    {
        $stack = $this->stacks->findOneByName($name);
        if (!$stack instanceof Stack) {
            throw new \InvalidArgumentException(sprintf('%s references unknown stack "%s". Import stacks first or add it to the file.', $context, $name));
        }

        return $stack;
    }

    /**
     * @param list<mixed> $rows
     *
     * @return list<array<string, mixed>>
     */
    private function listOfMaps(mixed $rows): array
    {
        if (!\is_array($rows)) {
            return [];
        }

        $maps = [];
        foreach ($rows as $row) {
            if (\is_array($row)) {
                $maps[] = $row;
            }
        }

        return $maps;
    }

    /**
     * @return array<string, mixed>
     */
    private function map(mixed $row, string $label): array
    {
        if (!\is_array($row)) {
            throw new \InvalidArgumentException(sprintf('%s must be a mapping.', $label));
        }

        /** @var array<string, mixed> $row */
        return $row;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function string(array $data, string $key): string
    {
        $value = $data[$key] ?? '';

        return \is_scalar($value) ? trim((string) $value) : '';
    }

    /**
     * @param array<string, mixed> $data
     */
    private function nullableString(array $data, string $key): ?string
    {
        $value = $this->string($data, $key);

        return $value === '' ? null : $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function bool(array $data, string $key): bool
    {
        return (bool) ($data[$key] ?? false);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function int(array $data, string $key): int
    {
        return (int) ($data[$key] ?? 0);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function date(array $data, string $key): \DateTimeImmutable
    {
        $value = $this->string($data, $key);
        if ($value === '') {
            throw new \InvalidArgumentException(sprintf('Missing date "%s".', $key));
        }

        return new \DateTimeImmutable($value);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function nullableDate(array $data, string $key): ?\DateTimeImmutable
    {
        $value = $this->nullableString($data, $key);

        return $value === null ? null : new \DateTimeImmutable($value);
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $values): array
    {
        if (!\is_array($values)) {
            return [];
        }

        $list = [];
        foreach ($values as $value) {
            if (\is_scalar($value)) {
                $trimmed = trim((string) $value);
                if ($trimmed !== '') {
                    $list[] = $trimmed;
                }
            }
        }

        return $list;
    }
}
