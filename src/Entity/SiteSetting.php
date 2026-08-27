<?php

namespace App\Entity;

use App\Repository\SiteSettingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteSettingRepository::class)]
#[ORM\Table(name: 'site_setting')]
#[ORM\HasLifecycleCallbacks]
class SiteSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $siteName = '';

    #[ORM\Column(length: 180)]
    private string $roleTitle = '';

    #[ORM\Column(length: 255)]
    private string $headline = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $positioning = '';

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $availability = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $workMode = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $aboutBody = '';

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $principles = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contactIntro = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $contactEmail = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $projectsIntro = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $experienceIntro = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $stackIntro = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $githubIntro = null;

    #[ORM\Column(length: 300, nullable: true)]
    private ?string $metaDescription = null;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function touchUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSiteName(): string
    {
        return $this->siteName;
    }

    public function setSiteName(string $siteName): static
    {
        $this->siteName = $siteName;

        return $this;
    }

    public function getRoleTitle(): string
    {
        return $this->roleTitle;
    }

    public function setRoleTitle(string $roleTitle): static
    {
        $this->roleTitle = $roleTitle;

        return $this;
    }

    public function getHeadline(): string
    {
        return $this->headline;
    }

    public function setHeadline(string $headline): static
    {
        $this->headline = $headline;

        return $this;
    }

    public function getPositioning(): string
    {
        return $this->positioning;
    }

    public function setPositioning(string $positioning): static
    {
        $this->positioning = $positioning;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getAvailability(): ?string
    {
        return $this->availability;
    }

    public function setAvailability(?string $availability): static
    {
        $this->availability = $availability;

        return $this;
    }

    public function getWorkMode(): ?string
    {
        return $this->workMode;
    }

    public function setWorkMode(?string $workMode): static
    {
        $this->workMode = $workMode;

        return $this;
    }

    public function getAboutBody(): string
    {
        return $this->aboutBody;
    }

    public function setAboutBody(string $aboutBody): static
    {
        $this->aboutBody = $aboutBody;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getPrinciples(): array
    {
        return $this->principles ?? [];
    }

    /**
     * @param list<string>|null $principles
     */
    public function setPrinciples(?array $principles): static
    {
        $this->principles = $principles;

        return $this;
    }

    public function getContactIntro(): ?string
    {
        return $this->contactIntro;
    }

    public function setContactIntro(?string $contactIntro): static
    {
        $this->contactIntro = $contactIntro;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getProjectsIntro(): ?string
    {
        return $this->projectsIntro;
    }

    public function setProjectsIntro(?string $projectsIntro): static
    {
        $projectsIntro = $projectsIntro !== null ? trim($projectsIntro) : null;
        $this->projectsIntro = $projectsIntro === '' ? null : $projectsIntro;

        return $this;
    }

    public function getExperienceIntro(): ?string
    {
        return $this->experienceIntro;
    }

    public function setExperienceIntro(?string $experienceIntro): static
    {
        $experienceIntro = $experienceIntro !== null ? trim($experienceIntro) : null;
        $this->experienceIntro = $experienceIntro === '' ? null : $experienceIntro;

        return $this;
    }

    public function getStackIntro(): ?string
    {
        return $this->stackIntro;
    }

    public function setStackIntro(?string $stackIntro): static
    {
        $stackIntro = $stackIntro !== null ? trim($stackIntro) : null;
        $this->stackIntro = $stackIntro === '' ? null : $stackIntro;

        return $this;
    }

    public function getGithubIntro(): ?string
    {
        return $this->githubIntro;
    }

    public function setGithubIntro(?string $githubIntro): static
    {
        $githubIntro = $githubIntro !== null ? trim($githubIntro) : null;
        $this->githubIntro = $githubIntro === '' ? null : $githubIntro;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): static
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function __toString(): string
    {
        return $this->siteName !== '' ? $this->siteName : 'Ustawienia strony';
    }
}
