<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Doctrine\ORM\EntityRepository;

/**
 * UserGeneratedContentEntryRepository
 *
 */
class UserGeneratedContentEntryRepository extends EntityRepository
{
    public function findAllValuesByEntry($entryID)
    {
        $entry = $this->getEntityManager('edcoms_cms')
                ->createQuery("SELECT ugce, u, ugcv "
                            . "FROM EdcomsCMSContentBundle:UserGeneratedContentEntry ugce "
                            . "LEFT JOIN ugce.userGeneratedContentValues ugcv "
                            . "LEFT JOIN ugce.user u "
                            . "WHERE ugce.id=:id")
                ->setParameter('id', $entryID)
                ->getOneOrNullResult();

        if (null === $entry) {
            $message = sprintf(
                'Unable to find an entry EdcomsCMSContentBundle:UserGeneratedContentEntry object identified by "%s".',
                $entryID
            );
            throw new NotFoundResourceException($message);
        }
        return $entry;
    }
    public function findByForm($formID)
    {
        $entries = $this->getEntityManager('edcoms_cms')
                ->createQuery("SELECT ugce, u, ugcv, ugcf "
                            . "FROM EdcomsCMSContentBundle:UserGeneratedContentEntry ugce "
                            . "LEFT JOIN ugce.userGeneratedContentValues ugcv "
                            . "LEFT JOIN ugce.user u "
                            . "LEFT JOIN ugce.userGeneratedContentForm ugcf "
                            . "WHERE ugcf.id=:id")
                ->setParameter('id', $formID)
                ->getResult();

        if (null === $entries) {
            $message = sprintf(
                'Unable to find entries of EdcomsCMSContentBundle:UserGeneratedContentEntry object identified by "%s".',
                $formID
            );
            throw new NotFoundResourceException($message);
        }
        return $entries;
    }
}
