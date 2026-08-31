<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * @return list<Project>
     */
    public function findPublished(): array
    {
        /** @var list<Project> $projects */
        $projects = $this->createQueryBuilder('p')
            ->leftJoin('p.stacks', 's', 'WITH', 's.published = true')
            ->addSelect('s')
            ->andWhere('p.published = true')
            ->orderBy('p.featured', 'DESC')
            ->addOrderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();

        return $projects;
    }

    /**
     * @return list<Project>
     */
    public function findFeatured(): array
    {
        /** @var list<Project> $projects */
        $projects = $this->createQueryBuilder('p')
            ->leftJoin('p.stacks', 's', 'WITH', 's.published = true')
            ->addSelect('s')
            ->andWhere('p.published = true')
            ->andWhere('p.featured = true')
            ->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();

        return $projects;
    }

    public function findPublishedBySlug(string $slug): ?Project
    {
        /** @var Project|null $project */
        $project = $this->createQueryBuilder('p')
            ->leftJoin('p.stacks', 's', 'WITH', 's.published = true')
            ->addSelect('s')
            ->andWhere('p.published = true')
            ->andWhere('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();

        return $project;
    }

    public function findOneBySlug(string $slug): ?Project
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * @return list<Project>
     */
    public function findAllWithStacks(): array
    {
        /** @var list<Project> $projects */
        $projects = $this->createQueryBuilder('p')
            ->leftJoin('p.stacks', 's')
            ->addSelect('s')
            ->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $projects;
    }
}
