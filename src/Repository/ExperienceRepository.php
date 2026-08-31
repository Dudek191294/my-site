<?php

namespace App\Repository;

use App\Entity\Experience;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Experience>
 */
class ExperienceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Experience::class);
    }

    /**
     * @return list<Experience>
     */
    public function findPublishedOrdered(): array
    {
        /** @var list<Experience> $entries */
        $entries = $this->createQueryBuilder('e')
            ->leftJoin('e.stacks', 's', 'WITH', 's.published = true')
            ->addSelect('s')
            ->andWhere('e.published = true')
            ->orderBy('e.sortOrder', 'ASC')
            ->addOrderBy('e.startDate', 'DESC')
            ->getQuery()
            ->getResult();

        return $entries;
    }

    public function findOneByIdentity(string $company, string $role, \DateTimeImmutable $startDate): ?Experience
    {
        /** @var Experience|null $entry */
        $entry = $this->createQueryBuilder('e')
            ->andWhere('e.company = :company')
            ->andWhere('e.role = :role')
            ->andWhere('e.startDate = :startDate')
            ->setParameter('company', $company)
            ->setParameter('role', $role)
            ->setParameter('startDate', $startDate)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $entry;
    }

    /**
     * @return list<Experience>
     */
    public function findAllWithStacks(): array
    {
        /** @var list<Experience> $entries */
        $entries = $this->createQueryBuilder('e')
            ->leftJoin('e.stacks', 's')
            ->addSelect('s')
            ->orderBy('e.sortOrder', 'ASC')
            ->addOrderBy('e.startDate', 'DESC')
            ->addOrderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $entries;
    }
}
