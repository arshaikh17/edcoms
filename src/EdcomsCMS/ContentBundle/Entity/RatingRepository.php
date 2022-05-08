<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\EntityRepository;
use EdcomsCMS\AuthBundle\Entity\cmsUsers;

/**
 * RatingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RatingRepository extends EntityRepository
{

    /**
     * Find a Rating object constrained by User and Structure
     *
     * @param cmsUsers $user
     * @param Structure $structure
     * @return mixed - Raiting or NULL
     */
    public function findOneByUserAndStructure(cmsUsers $user, Structure $structure)
    {
        return $this
            ->createQueryBuilder('r')
            ->where('r.user = :user')
            ->andWhere('r.structure = :structure')
            ->setParameter('user', $user)
            ->setParameter('structure', $structure)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
