<?php

namespace EdcomsCMS\ContentBundle\Helpers;

use EdcomsCMS\ContentBundle\Entity\Structure;

/**
 * Class to fetch and prepare content ready for Twig.
 * NOTE: we really ought to get rid of this class and replace it with something better.
 *
 * @author  Richard Wilson <richard.wilson@edcoms.co.uk>
 */
class GetContent
{
    /**
     * @var  EntityManager
     */
    protected $em;

    /**
     * @var  ContentHelper
     */
    protected $contentHelper;

    /**
     * @var  integer
     */
    protected $limit;

    /**
     * @var  integer
     */
    protected $page;

    /**
     * @var  string
     */
    protected $status;

    /**
     * @var  array
     */
    protected $content = [];

    /**
     * @param  Doctrine            $doctrine  The doctrine service.
     * @param  ContainerInterface  $container  The container.
     */
    public function __construct($doctrine, $container)
    {
        $this->em = $doctrine->getManager('edcoms_cms');

        $this->contentHelper = new ContentHelper(
            $this->em->getRepository('EdcomsCMSContentBundle:Content'),
            $this->em->getRepository('EdcomsCMSContentBundle:Structure'),
            $doctrine,
            null,
            $container->get('edcoms.content.service.configuration')
        );
    }

    /**
     * Get an array of paginated content objects
     *
     * @param $id - parent structure id
     * @param $status - status of the paginated content to get
     * @param $limit - how many itmes of content to get
     * @param $page - which page of content to get
     * @return array - array of content objects
     */
    public function GetContentByParent($id, $status, $limit, $page)
    {
        $this->limit = intval($limit);
        $this->page = intval($page);
        $this->status = $status;

        $structure = $this
            ->em
            ->getRepository('EdcomsCMSContentBundle:Structure')
            ->findByParentFiltered(intval($id), $this->status, $this->limit, $this->page);

        $this->content = [];//reset the content array
        $properties = ['content' => true];

        foreach ($structure->getIterator() as $index => $structure) {
            $structureContent = $this->structureToJson($structure, $properties);

            if (false !== $structureContent) {
                $this->content[$index] = $structureContent;
            }
        }

        return $this->content;
    }

    /**
     * Get and array of content objects from a csv string of structure ids
     *
     * @param   array  $ids  string csv structure ids
     *
     * @return  array
     */
    public function getContentByStructureId($ids)
    {

        //Get the structures from the DB in an array
        $idArray = explode(',', $ids);
        $structureArray = $this->em->getRepository('EdcomsCMSContentBundle:Structure')->findById($idArray);

        // duplicate the content array as this method should have no bearing on the value stored in the object instance.
        $content = array_merge($this->content);
        $properties = ['content' => true, 'validateStatus' => true];

        foreach ($structureArray as $index => $structure) {
            $structureContent = $this->structureToJson($structure, $properties);

            if (false !== $structureContent) {
                $content[$index] = $structureContent;
            }
        }

        return $content;
    }

    /**
     * Will behave the same way in terms of structure as the "getContentByStructureId" method
     * with the following differences
     *  - It doesn't include children data (in order to improve performance. Current serialisation implementation is heavy)
     *  - It accepts either a structure id or a Structure object
     *
     * @param \EdcomsCMS\ContentBundle\Entity\Structure $structure
     * @return null|array
     */
    public function getContentByStructure($structure){
      $returnData = null;
      $structureObject = null;
      if(is_object($structure) && $structure instanceof Structure){
        $structureObject = $structure;
      }elseif(is_numeric($structure)){
        $structureObject = $this->em->getRepository(Structure::class)->find($structure);
      }

      if($structureObject){
        $returnData = $this->structureToJson($structureObject, ['content' => true, 'validateStatus' => true], ['parent', 'content'], true);
      }
      return $returnData;
    }

    /**
     * Serializes '$entity' and returns a native array ready to be encoded in JSON format.
     *
     * @param   Structure  $entity                    The entity to serialize into JSON.
     * @param   array      $properties                Collection of properties which may affect the returning JSON array.
     * @param   array      $structureJSONRules        Collection of properties that control the serialisation of the Structure entity.
     *
     * @return  array|null                            The JSON array of the entity, or 'null' if the content was returned back as 'null'.
     */
    private function structureToJson($entity, $properties = [], $structureJSONRules=[], $fullSerialization=false)
    {
        $oldEntity = clone $entity;
        $json = $entity->toJSON($structureJSONRules, $fullSerialization);

        if (!empty($properties)) {
            if (isset($properties['content']) && $properties['content']) {
                $content = $this->prepContent($oldEntity);

                if ($content === null) {
                    return false;
                }

                $json['content'] = $content;
            }

            // add support for new properties as and when they're needed.
        }

        return $json;
    }

    /**
     * Processes the Content entities in '$structure' and returns a convenient array.
     *
     * @param   Structure  $structure  The parent Structure entity to process it's content entities from.
     *
     * @return  array|null             Collection of processed content entities, or 'null' if no content was found matching the status criteria.
     */
    private function prepContent($structure)
    {
        $structureContent = isset($this->status) ? $structure->getContent($this->status)->first() : $structure->getPublishedContent();

        if (false === $structureContent) {
            return null;
        }

        $this->contentHelper->handleContent($structureContent);

        return $this->contentHelper->prepContentArr($structureContent);
    }
}
