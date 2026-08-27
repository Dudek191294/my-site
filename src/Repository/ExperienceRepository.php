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
}
