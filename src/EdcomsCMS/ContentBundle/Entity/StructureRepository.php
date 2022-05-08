<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * StructureRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StructureRepository extends EntityRepository
{
    public function findOneByRoot($link='', $visibility=false)
    {

        $where = "WHERE ''=:link ";
        if($visibility){
            $where .= " AND s.visible=true ";
        }
        $order = "ORDER BY s.id ASC";
        if (!empty($link)) {
            $where = "WHERE s.link=:link";
        }
        $structure = $this->getEntityManager('edcoms_cms')
                ->createQuery("SELECT s "
                            . "FROM EdcomsCMSContentBundle:Structure s "
                            . "$where AND s.deleted=false "
                            . "$order")
                ->setMaxResults(1)
                ->setParameter('link', $link)
                ->getOneOrNullResult();

        if (null === $structure) {
            $message = sprintf(
                'Unable to find an entry EdcomsCMSContentBundle:Structure object identified by "%s".',
                $link
            );
            throw new NotFoundResourceException($message);
        }

        return $structure;
    }
    public function findByParent($parent, $visibility=false)
    {
        if($visibility){
            $condition = " AND s.visible=true ";
        }else{
            $condition = "";
        }
        $structure = $this->getEntityManager('edcoms_cms')
                ->createQuery("SELECT s, c, ct "
                            . "FROM EdcomsCMSContentBundle:Structure s "
                            . "LEFT JOIN s.content c "
                            . "LEFT JOIN c.contentType ct "
                            . "WHERE s.parent=:parent AND s.deleted=false $condition"
                            . "ORDER BY s.priority ASC, s.addedOn, s.id DESC")
                ->setParameter('parent', $parent)
                ->getResult();


        if (null === $structure) {
            $message = sprintf(
                'Unable to find an entry EdcomsCMSContentBundle:Structure object identified by "%s".',
                $parent
            );
            throw new NotFoundResourceException($message);
        }

        return $structure;
    }
    public function findAllAndKeys($deleted=false, $visibility=false)
    {
        if($visibility){
            $condition = " AND s.visible = true ";
        }else{
            $condition = "";
        }
        $structure = $this->getEntityManager('edcoms_cms')
                     ->createQuery("SELECT s FROM EdcomsCMSContentBundle:Structure s WHERE s.deleted=:deleted $condition ")
                      ->setParameter('deleted', $deleted)
                     ->getResult();
        if ($structure) {
            $this->ids = [];
            array_walk($structure, array(&$this, 'GetIDs'));
            return ['structures'=>$structure, 'keys'=>$this->ids];
        }
    }
    private function GetIDs($structure)
    {
        $this->ids[] = $structure->getId();
    }

    /**
     * Find content restricted by content type
     *
     * @param $type_id - type of content to search for
     * @param int $parent - optional parent to search for
     * @return mixed
     */
    public function findByContentType($type_id, $parent = 0) {

        $where = "WHERE c.contentType=:type_id ";
        $params['type_id'] = $type_id;
        if ($parent > 0) {
            $where .= "AND s.parent=:parent";
            $params['parent'] = $parent;
        }
        $structure = $this->getEntityManager('edcoms_cms')
            ->createQuery("SELECT s, c, ct, cf, cfd "
                . "FROM EdcomsCMSContentBundle:Structure s "
                . "LEFT JOIN s.content c "
                . "LEFT JOIN c.contentType ct "
                . "LEFT JOIN ct.custom_fields cf "
                . "LEFT JOIN c.custom_field_data cfd "
                . "$where AND s.deleted=false "
                . "ORDER BY s.id ASC")
            ->setParameters($params)
            ->getResult();

        if (empty($structure)) {
            $message = sprintf(
                'Unable to find an entry EdcomsCMSContentBundle:Structure object identified by parent id "%d" & content type id "%s".',
                $parent,
                $type_id
            );
            throw new NotFoundResourceException($message);
        }
        return $structure[0];
    }
    public function findByParentFiltered($id, $status, $limit, $page)
    {
        $structure = $this->getEntityManager('edcoms_cms')
                ->createQuery("SELECT s, c, ct "
                            . "FROM EdcomsCMSContentBundle:Structure s "
                            . "LEFT JOIN s.content c "
                            . "LEFT JOIN c.contentType ct "
                            . "WHERE s.parent=:parent AND s.deleted=false AND c.status=:status "
                            . "ORDER BY s.priority ASC, s.addedOn, s.id DESC")
                ->setParameter('parent', $id)
                ->setParameter('status', $status);
        if ($limit > 0) {
            $structure
                ->setFirstResult($page)
                ->setMaxResults($limit);
        }
                
        $paginator = new Paginator($structure, $fetchJoinCollection = true);

        if (null === $structure) {
            $message = sprintf(
                'Unable to find an entry EdcomsCMSContentBundle:Structure object identified by "%s".',
                $id
            );
            throw new NotFoundResourceException($message);
        }
        return $paginator;
    }
    public function findByNotParentNotDeleted($ids, $parent, $status)
    {
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        $structure = $this->getEntityManager('edcoms_cms')
                          ->createQuery("SELECT s, c, ct "
                                      . "FROM EdcomsCMSContentBundle:Structure s "
                                      . "LEFT JOIN s.content c "
                                      . "LEFT JOIN c.contentType ct "
                                      . "WHERE s.parent!=:parent AND s.deleted=false AND c.status=:status AND s.id IN (:ids) "
                                      . "ORDER BY s.priority ASC, s.addedOn, s.id DESC")
                          ->setParameter('parent', $parent)
                          ->setParameter('status', $status)
                          ->setParameter('ids', $ids)
                          ->getResult();
        if (null === $structure) {
            $message = sprintf(
                'Unable to find an entry EdcomsCMSContentBundle:Structure object identified by "%s".',
                implode(', ',$ids)
            );
            throw new NotFoundResourceException($message);
        }
        return $structure;
    }

    /**
     * Returns the structure with the ID of '$structure',
     * also recursively fetching all of the parent structures.
     *
     * @param   int     $structureID    ID of the structure to fetch.
     *
     * @return  Structure               The found structure.
     */
    public function findWithAncestors($structureID)
    {
        $em = $this->getEntityManager('edcoms_cms');
        $rsm = new ResultSetMapping();

        // grab Structure entity meta data.
        // we'll use this to build the SQL query with.
        $structureMeta = $em->getClassMetadata(Structure::class);
        $deletedColumn = $structureMeta->getColumnName('deleted');
        $idColumn = $structureMeta->getColumnName($structureMeta->getIdentifier()[0]);
        $parentColumn = $structureMeta->getAssociationMapping('parent')['joinColumns'][0]['name'];
        $tableName = $structureMeta->getTableName();

        // add entities to result set mapper.
        $rsm->addEntityResult(Structure::class, 'st');
        $rsm->addJoinedEntityResult(Structure::class, 'pt', 'st', 'parent');

        // add the parent join columns to the select column statement. 
        $statement = "st.$parentColumn AS st_$parentColumn, " .
            "pt.$parentColumn AS pt_$parentColumn";

        // add all of the columns from the Structure entity.
        // ignore associated columns.
        foreach ($structureMeta->getFieldNames() as $field) {
            $statement .= ", st.{$structureMeta->getColumnName($field)} AS st_{$structureMeta->getColumnName($field)}, " .
                "pt.{$structureMeta->getColumnName($field)} AS pt_{$structureMeta->getColumnName($field)}";

            $rsm->addFieldResult('st', "st_{$structureMeta->getColumnName($field)}", $field);
            $rsm->addFieldResult('pt', "pt_{$structureMeta->getColumnName($field)}", $field);
        }

        // build up statement.
        $statement =
            'WITH RECURSIVE s AS (' .
                'SELECT ' .
                    '1 AS level, ' .
                    "$idColumn, " .
                    "$parentColumn " .
                'FROM ' .
                    "$tableName " .
                'WHERE ' .
                    "$deletedColumn = FALSE " .
                    "AND $idColumn = :structureid " .
                'UNION ALL ' .
                'SELECT ' .
                    's.level + 1 AS level, ' .
                    "p.$idColumn, " .
                    "p.$parentColumn " .
                'FROM ' .
                    "$tableName AS p " .
                    "JOIN s ON p.$idColumn = s.$parentColumn AND p.$deletedColumn = FALSE" .
            ') ' .
            'SELECT ' .
                "$statement " .
            'FROM ' .
                "$tableName st " .
                "JOIN s ON st.$idColumn = s.$idColumn " .
                "LEFT JOIN $tableName pt ON st.$parentColumn = pt.$idColumn " .
            'ORDER BY ' .
                's.level ASC';

        // execute query and fetch the results.
        $structure = $em
            ->createNativeQuery($statement, $rsm)
            ->setParameter('structureid', $structureID)
            ->getResult();

        // expecting an array to be returned,
        // so set the first structure, or null if not found.
        $structure = empty($structure) ? null : $structure[0];

        return $structure;
    }
}
