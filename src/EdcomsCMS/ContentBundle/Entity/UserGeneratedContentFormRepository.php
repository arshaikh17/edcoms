<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserGeneratedContentFormRepository
 *
 */
class UserGeneratedContentFormRepository extends EntityRepository
{
    public function findOneByContent($content)
    {
        $form = $this->getEntityManager('edcoms_cms')
                ->createQuery("SELECT ugc, c, ct, g, fbe "
                            . "FROM EdcomsCMSContentBundle:UserGeneratedContentForm ugc "
                            . "LEFT JOIN ugc.content c "
                            . "LEFT JOIN ugc.entryContentType ct "
                            . "LEFT JOIN ugc.groups g "
                            . "LEFT JOIN ugc.formBuilderElements fbe "
                            . "WHERE :content MEMBER OF ugc.content")
                ->setParameter('content', $content)
                ->getOneOrNullResult();
        return $form;
    }
    
    /**
     * Returns a summary of the form with the ID of '$formID', along with all of the child entries.
     * @param   integer     $formID     ID of the form to search for.
     * @param   string      $status     If not null, only return child entries with the status set as '$status'.
     * @return  array                   Summary of the found form with child entries.
     */
    public function findFormWithEntriesList($formID, $status = null)
    {
        $statement = '' . 
            'SELECT ' .
                'ugce.id, ' .
                'ugce.status, ' .
                'ugce.title, ' .
                'c.contentid ' .
            'FROM ' .
                'usergeneratedcontententry AS ugce ' .
            'LEFT JOIN ' .
                '(' .
                    'SELECT ' .
                        'NULLIF(cfd.value, \'\')::int AS contentid ' .
                    'FROM ' .
                        'customfields AS cf ' .
                    'JOIN ' .
                        'customfielddata AS cfd ' .
                    'ON ' .
                        'cf.id = cfd.fieldid ' .
                    'JOIN ' .
                        'content AS c ' .
                    'ON ' .
                        'cfd.contentid = c.id ' .
                    'WHERE ' .
                        'cf.name = \'entryID\'' .
                ') AS c ' .
            'ON ' .
                'ugce.id = c.contentid ' .
            'RIGHT JOIN ' .
                'usergeneratedcontentform AS ugcf ' .
            'ON ' .
                'ugce.formid = ugcf.id ' .
            'WHERE ' .
                'ugcf.id = :formID ' .
            'GROUP BY ' .
                'ugce.id, ' .
                'c.contentid ' .
            'ORDER BY ' .
                'ugce.id ASC ';
        
        // add status criteria only if specified.
        if ($status !== null) {
            $statement .= ' AND ugce.status = :status';
        }
        
        // get connection.
        $em = $this->getEntityManager('edcoms_cms');
        $connection = $em->getConnection();
        
        $query = $connection->prepare($statement);
        
        // bind parameters.
        $query->bindParam('formID', $formID);
        
        if ($status !== null) {
            $query->bindParam('status', $status);
        }
        
        // execute query and fetch results.
        $query->execute();
        $result = $query->fetchAll();
        
        
        return $result;
    }
}
