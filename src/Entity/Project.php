<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table(name: 'project')]
#[ORM\UniqueConstraint(name: 'UNIQ_PROJECT_SLUG', fields: ['slug'])]
#[ORM\Index(name: 'IDX_PROJECT_PUBLISHED_SORT', columns: ['published', 'sort_order'])]
#[ORM\Index(name: 'IDX_PROJECT_PUBLISHED_FEATURED', columns: ['published', 'featured'])]
#[ORM\HasLifecycleCallbacks]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $title = '';

    #[ORM\Column(length: 180)]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $shortDescription = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $description = '';

    #[ORM\Column(length: 180)]
    private string $role = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $overview = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $problem = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $solution = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $result = '';

    /**
     * @var array{
     *     frontend?: string,
     *     api?: string,
     *     backend?: string,
     *     database?: string,
     *     infrastructure?: string
     * }
     */
    #[ORM\Column(type: Types::JSON)]
    private array $architecture = [
        'frontend' => '',
        'api' => '',
        'backend' => '',
        'database' => '',
        'infrastructure' => '',
    ];

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $technicalDecisions = [];

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $challenges = [];

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $lessonsLearned = [];

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $demoUrl = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $githubUrl = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageAlt = null;

    #[ORM\Column]
    private bool $featured = false;

    #[ORM\Column]
    private bool $published = false;

    #[ORM\Column]
    private int $sortOrder = 0;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, Stack>
     */
    #[ORM\ManyToMany(targetEntity: Stack::class, inversedBy: 'projects')]
    #[ORM\JoinTable(name: 'project_stack')]
    #[ORM\OrderBy(['sortOrder' => 'ASC', 'name' => 'ASC'])]
    private Collection $stacks;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->stacks = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getOverview(): string
    {
        return $this->overview;
    }

    public function setOverview(string $overview): static
    {
        $this->overview = $overview;

        return $this;
    }

    public function getProblem(): string
    {
        return $this->problem;
    }

    public function setProblem(string $problem): static
    {
        $this->problem = $problem;

        return $this;
    }

    public function getSolution(): string
    {
        return $this->solution;
    }

    public function setSolution(string $solution): static
    {
        $this->solution = $solution;

        return $this;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function setResult(string $result): static
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return array{
     *     frontend?: string,
     *     api?: string,
     *     backend?: string,
     *     database?: string,
     *     infrastructure?: string
     * }
     */
    public function getArchitecture(): array
    {
        return $this->architecture;
    }

    /**
     * @param array{
     *     frontend?: string,
     *     api?: string,
     *     backend?: string,
     *     database?: string,
     *     infrastructure?: string
     * } $architecture
     */
    public function setArchitecture(array $architecture): static
    {
        $this->architecture = $architecture;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getTechnicalDecisions(): array
    {
        return $this->technicalDecisions;
    }

    /**
     * @param list<string> $technicalDecisions
     */
    public function setTechnicalDecisions(array $technicalDecisions): static
    {
        $this->technicalDecisions = $technicalDecisions;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getChallenges(): array
    {
        return $this->challenges;
    }

    /**
     * @param list<string> $challenges
     */
    public function setChallenges(array $challenges): static
    {
        $this->challenges = $challenges;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getLessonsLearned(): array
    {
        return $this->lessonsLearned;
    }

    /**
     * @param list<string> $lessonsLearned
     */
    public function setLessonsLearned(array $lessonsLearned): static
    {
        $this->lessonsLearned = $lessonsLearned;

        return $this;
    }

    public function getDemoUrl(): ?string
    {
        return $this->demoUrl;
    }

    public function setDemoUrl(?string $demoUrl): static
    {
        $this->demoUrl = $demoUrl;

        return $this;
    }

    public function getGithubUrl(): ?string
    {
        return $this->githubUrl;
    }

    public function setGithubUrl(?string $githubUrl): static
    {
        $this->githubUrl = $githubUrl;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getImageAlt(): ?string
    {
        return $this->imageAlt;
    }

    public function setImageAlt(?string $imageAlt): static
    {
        $this->imageAlt = $imageAlt;

        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): static
    {
        $this->featured = $featured;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): static
    {
        $this->published = $published;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, Stack>
     */
    public function getStacks(): Collection
    {
        return $this->stacks;
    }

    public function addStack(Stack $stack): static
    {
        if (!$this->stacks->contains($stack)) {
            $this->stacks->add($stack);
        }

        return $this;
    }

    public function removeStack(Stack $stack): static
    {
        $this->stacks->removeElement($stack);

        return $this;
    }

    public function getArchitectureFrontend(): string
    {
        return $this->architecture['frontend'] ?? '';
    }

    public function setArchitectureFrontend(string $value): static
    {
        $this->architecture['frontend'] = $value;

        return $this;
    }

    public function getArchitectureApi(): string
    {
        return $this->architecture['api'] ?? '';
    }

    public function setArchitectureApi(string $value): static
    {
        $this->architecture['api'] = $value;

        return $this;
    }

    public function getArchitectureBackend(): string
    {
        return $this->architecture['backend'] ?? '';
    }

    public function setArchitectureBackend(string $value): static
    {
        $this->architecture['backend'] = $value;

        return $this;
    }

    public function getArchitectureDatabase(): string
    {
        return $this->architecture['database'] ?? '';
    }

    public function setArchitectureDatabase(string $value): static
    {
        $this->architecture['database'] = $value;

        return $this;
    }

    public function getArchitectureInfrastructure(): string
    {
        return $this->architecture['infrastructure'] ?? '';
    }

    public function setArchitectureInfrastructure(string $value): static
    {
        $this->architecture['infrastructure'] = $value;

        return $this;
    }

    public function __toString(): string
    {
        return $this->title !== '' ? $this->title : 'Project';
    }
}
