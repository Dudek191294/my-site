<?php

namespace App\Entity;

use App\Repository\ExperienceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExperienceRepository::class)]
#[ORM\Table(name: 'experience')]
#[ORM\Index(name: 'IDX_EXPERIENCE_PUBLISHED_SORT', columns: ['published', 'sort_order'])]
class Experience
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $company = '';

    #[ORM\Column(length: 180)]
    private string $role = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $description = '';

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $bullets = [];

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $startDate;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column]
    private bool $current = false;

    #[ORM\Column]
    private bool $published = false;

    #[ORM\Column]
    private int $sortOrder = 0;

    /**
     * @var Collection<int, Stack>
     */
    #[ORM\ManyToMany(targetEntity: Stack::class, inversedBy: 'experiences')]
    #[ORM\JoinTable(name: 'experience_stack')]
    #[ORM\OrderBy(['sortOrder' => 'ASC', 'name' => 'ASC'])]
    private Collection $stacks;

    public function __construct()
    {
        $this->startDate = new \DateTimeImmutable('today');
        $this->stacks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): static
    {
        $this->company = $company;

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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getBullets(): array
    {
        return $this->bullets;
    }

    /**
     * @param list<string> $bullets
     */
    public function setBullets(array $bullets): static
    {
        $this->bullets = $bullets;

        return $this;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function isCurrent(): bool
    {
        return $this->current;
    }

    public function setCurrent(bool $current): static
    {
        $this->current = $current;

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
     * Human-readable period for the public timeline (e.g. "2022 — Present").
     */
    public function getPeriod(): string
    {
        $start = $this->startDate->format('Y');

        if ($this->current || $this->endDate === null) {
            return sprintf('%s — Present', $start);
        }

        return sprintf('%s — %s', $start, $this->endDate->format('Y'));
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
        $label = trim($this->role.' @ '.$this->company);

        return $label !== '@' && $label !== '' ? $label : 'Experience';
    }
}
