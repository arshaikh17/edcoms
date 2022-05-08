<?php

namespace EdcomsCMS\ContentBundle\Helpers;

class Lists {
    private $doctrine;
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    public function getList($entityName, $fields=[], $order=[], $extra=[])
    {
        $entities = $this->doctrine->getManager('edcoms_cms')->getRepository($entityName);
        $entityList = [];
        if (array_key_exists('version', $extra)) {
            $v = 1;
            $cur = '';
        }
        $fields = array_merge($fields, array_keys($extra));
        foreach ($entities->findBy([], $order) as $entity) {
            if (array_key_exists('version', $extra)) {
                if ($cur!==$entity->{$extra['version']}()) {
                    $cur = $entity->{$extra['version']}();
                    $v = 1;
                }
                $entity->version = $v++;
            }
            $entityList[] = $entity->toJSON($fields);
        }
        return $entityList;
    }
}