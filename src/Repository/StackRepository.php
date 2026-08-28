<?php

namespace App\Repository;

use App\Entity\Stack;
use App\Entity\StackCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stack>
 */
class StackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stack::class);
    }

    /**
     * @return list<Stack>
     */
    public function findPublished(): array
    {
        /** @var list<Stack> $stacks */
        $stacks = $this->createQueryBuilder('s')
            ->andWhere('s.published = true')
            ->orderBy('s.category', 'ASC')
            ->addOrderBy('s.sortOrder', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $stacks;
    }

    /**
     * @return list<Stack>
     */
    public function findFeatured(): array
    {
        /** @var list<Stack> $stacks */
        $stacks = $this->createQueryBuilder('s')
            ->andWhere('s.published = true')
            ->andWhere('s.featured = true')
            ->orderBy('s.category', 'ASC')
            ->addOrderBy('s.sortOrder', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $stacks;
    }

    /**
     * @return list<Stack>
     */
    public function findPublishedByCategory(StackCategory $category): array
    {
        /** @var list<Stack> $stacks */
        $stacks = $this->createQueryBuilder('s')
            ->andWhere('s.published = true')
            ->andWhere('s.category = :category')
            ->setParameter('category', $category)
            ->orderBy('s.sortOrder', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $stacks;
    }

    /**
     * Published stacks grouped for the public stack section.
     *
     * @return array<string, list<Stack>> keyed by category label
     */
    public function findPublishedGroupedByCategory(): array
    {
        $grouped = [];

        foreach (StackCategory::cases() as $category) {
            $grouped[$category->label()] = [];
        }

        foreach ($this->findPublished() as $stack) {
            $grouped[$stack->getCategory()->label()][] = $stack;
        }

        return array_filter($grouped, static fn (array $items): bool => $items !== []);
    }

    /**
     * Distinct icon slugs referenced by Stack rows (for import --from-database).
     *
     * @return list<string>
     */
    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        return $this->findOneByName($name, $excludeId) !== null;
    }

    public function findOneByName(string $name, ?int $excludeId = null): ?Stack
    {
        $normalized = mb_strtolower(trim($name));
        if ($normalized === '') {
            return null;
        }

        $qb = $this->createQueryBuilder('s')
            ->andWhere('LOWER(s.name) = :name')
            ->setParameter('name', $normalized)
            ->setMaxResults(1);

        if ($excludeId !== null) {
            $qb->andWhere('s.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findDistinctIconSlugs(): array
    {
        /** @var list<array{icon: string}> $rows */
        $rows = $this->createQueryBuilder('s')
            ->select('DISTINCT s.icon AS icon')
            ->andWhere('s.icon IS NOT NULL')
            ->andWhere("s.icon <> ''")
            ->getQuery()
            ->getArrayResult();

        $slugs = [];
        foreach ($rows as $row) {
            $slugs[] = $row['icon'];
        }

        return $slugs;
    }
}
