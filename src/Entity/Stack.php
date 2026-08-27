<?php

namespace App\Entity;

use App\Repository\StackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StackRepository::class)]
#[ORM\Table(name: 'stack')]
#[ORM\UniqueConstraint(name: 'UNIQ_STACK_SLUG', fields: ['slug'])]
#[ORM\Index(name: 'IDX_STACK_PUBLISHED_CATEGORY', columns: ['published', 'category'])]
#[ORM\Index(name: 'IDX_STACK_CATEGORY_SORT', columns: ['category', 'sort_order'])]
#[UniqueEntity(fields: ['slug'], message: 'This stack slug is already used.')]
class Stack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    private string $name = '';

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    #[Assert\Regex(pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', message: 'Use a lowercase slug with optional hyphens (e.g. tailwindcss, react).')]
    private string $slug = '';

    /**
     * Local Simple Icons identifier only (maps to public/icons/stack/{icon}.svg).
     */
    #[ORM\Column(length: 80, nullable: true)]
    #[Assert\Regex(pattern: '/^[a-z0-9]{1,80}$/', message: 'Icon must be a Simple Icons slug (a–z, 0–9 only).')]
    private ?string $icon = null;

    #[ORM\Column(length: 50, enumType: StackCategory::class)]
    #[Assert\NotNull]
    private StackCategory $category = StackCategory::Tools;

    #[ORM\Column(length: 32, nullable: true)]
    #[Assert\Regex(pattern: '/^#([0-9A-Fa-f]{6}|[0-9A-Fa-f]{3})$/', message: 'Color must be a HEX value, e.g. #61DAFB.')]
    private ?string $color = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url]
    #[Assert\Length(max: 500)]
    private ?string $websiteUrl = null;

    #[ORM\Column]
    private bool $featured = false;

    #[ORM\Column]
    private bool $published = false;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $sortOrder = 0;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'stacks')]
    private Collection $projects;

    /**
     * @var Collection<int, Experience>
     */
    #[ORM\ManyToMany(targetEntity: Experience::class, mappedBy: 'stacks')]
    private Collection $experiences;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->experiences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = strtolower(trim($slug));

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        if ($icon !== null) {
            $icon = strtolower(trim($icon));
            if ($icon === '' || preg_match('/^[a-z0-9]{1,80}$/', $icon) !== 1) {
                $icon = null;
            }
        }

        $this->icon = $icon;

        return $this;
    }

    public function getCategory(): StackCategory
    {
        return $this->category;
    }

    public function setCategory(StackCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        if ($color !== null) {
            $color = trim($color);
            if ($color === '') {
                $color = null;
            }
        }

        $this->color = $color;

        return $this;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): static
    {
        if ($websiteUrl !== null) {
            $websiteUrl = trim($websiteUrl);
            if ($websiteUrl === '') {
                $websiteUrl = null;
            }
        }

        $this->websiteUrl = $websiteUrl;

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

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    /**
     * @return Collection<int, Experience>
     */
    public function getExperiences(): Collection
    {
        return $this->experiences;
    }

    public function __toString(): string
    {
        return $this->name !== '' ? $this->name : 'Stack';
    }
}
