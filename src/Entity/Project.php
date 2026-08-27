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

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $role = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $challenge = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $solution = null;

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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $role = $role !== null ? trim($role) : null;
        $this->role = $role === '' ? null : $role;

        return $this;
    }

    public function getChallenge(): ?string
    {
        return $this->challenge;
    }

    public function setChallenge(?string $challenge): static
    {
        $challenge = $challenge !== null ? trim($challenge) : null;
        $this->challenge = $challenge === '' ? null : $challenge;

        return $this;
    }

    public function getSolution(): ?string
    {
        return $this->solution;
    }

    public function setSolution(?string $solution): static
    {
        $solution = $solution !== null ? trim($solution) : null;
        $this->solution = $solution === '' ? null : $solution;

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
        $image = $image !== null ? trim($image) : null;
        $this->image = $image === '' ? null : $image;

        return $this;
    }

    /**
     * Public URL for the cover image (uploaded file, stored path, or absolute URL).
     */
    public function getImageUrl(): ?string
    {
        if ($this->image === null || $this->image === '') {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $this->image) === 1) {
            return $this->image;
        }

        $path = str_replace('\\', '/', $this->image);
        if (str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'uploads/')) {
            return '/'.$path;
        }

        return '/uploads/projects/'.$path;
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

    public function __toString(): string
    {
        return $this->title !== '' ? $this->title : 'Projekt';
    }
}
