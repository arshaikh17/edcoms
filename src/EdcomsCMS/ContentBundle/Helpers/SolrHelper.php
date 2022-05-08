<?php


namespace EdcomsCMS\ContentBundle\Helpers;

use EdcomsCMS\ContentBundle\Helpers\SearchEngineHelper;
use EdcomsCMS\ContentBundle\Controller\MediaController;


/**
 * Description of SolrHelper
 *
 * @author richard
 */
class SolrHelper extends SearchEngineHelper {
    /**
     *
     * @var SolrClient
     */
    private $client;
    private $config;
    private $query;
    private $media;
    private $parameters;
    public function __construct($config, $media)
    {
        $obj = [
            'hostname'=>$config['url'],
            'port'=>$config['port'],
            'path'=>$config['path']
        ];
        $this->config = $obj;
        $this->parameters = $config;
        $this->client = new \SolrClient($this->config);
        $this->media = $media;
    }
    
    public function getName()
    {
        return 'solr';
    }
    
    public function getResults($q, $results=0, $page=0)
    {
        $this->query = new \SolrQuery();
        $this->query->setQuery(\SolrUtils::escapeQueryChars($q));
        $this->query->setStart($page);
        if ($results > 0) {
            $this->query->setRows($results);
        }
        $resp = $this->client->query($this->query);

        $output = (object)[
            'docs'=>$resp->getResponse()->response->docs,
            'numFound'=>$resp->getResponse()->response->numFound,
            'start'=>$resp->getResponse()->response->start,
            'limit'=>$results
        ];
        return $output;
    }
    
    public function getFieldResults($fields, $return, $q, $results=0, $page=0, $structureIds=null)
    {
        $this->query = new \SolrQuery();
        $this->query->setStart($page);
        $this->query->setQuery($q);
        foreach ($return as $rfield) {
            $this->query->addField($rfield);
        }
        //specified fields to search can be added here
        $fq = '';
        if (is_array($fields)) {
            foreach ($fields as $i=>$value) {
                $fq .= (($i !== 0) ? ' || ' : '').$value;
            }
            $this->query->addFilterQuery($fq);
        }
        //if structure ids supplied added to query here
        if (!is_null($structureIds)) {
            if (is_array($structureIds)) {
                $structure_fq = 'structure:(';
                foreach ($structureIds as $i=>$value) {
                    $structure_fq .= (($i !== 0) ? ' OR ' : '').$value;
                }
                $structure_fq .= ')';
                $this->query->addFilterQuery($structure_fq);
                //set size of results to be size of array of structure ids
                $this->query->setRows(count($structureIds));
            }
        }
        //override size of return
        if ($results > 0) {
            $this->query->setRows($results);
        }

        $resp = $this->client->query($this->query);
        $output = (object)[
            'docs'=>$resp->getResponse()->response->docs,
            'numFound'=>$resp->getResponse()->response->numFound,
            'start'=>$resp->getResponse()->response->start,
            'limit'=>$results
        ];
        return $output;
    }
    
    /**
     * This method is used to add a text based entry to the index
     * @param array $object
     * @return SolrUpdateResponse
     */
    public function addEntry($object)
    {
        $doc = new \SolrInputDocument();
        $resp = [];
        if(array_key_exists('content_type', $this->parameters)) {
            if (array_key_exists($object['content_type'], $this->parameters['content_type'])) {
                $doc->setBoost($this->parameters['content_type'][$object['content_type']]);
            } else {
                $doc->setBoost($this->parameters['content_type']['Default']);
            }
        }
        if($object['template_file'] == 'download' && $object['content_type'] == 'Download'){
            $resp[] = ($this->addFile($object['file'][1], $object)) ? '<info>File: '.$object['file'][1].'</info>' : '<error>==== Error adding file: '.$object['file'][1].'</error>';
        } else {
            foreach ($object as $prop => $val) {
                if (!is_array($val)) {
                    $doc->addField($prop, $this->prepareField($val));
                    $resp[] = '<info>Field: ' . $prop . ': ' . $this->prepareField($val) . '</info>';
                } else if (!is_array($val[1])) {
                    $doc->addField($prop . '_' . $this->detectFieldType($val[0]), $this->prepareField($val[1], $val[0]));
                    $resp[] = '<info>Field: ' . $prop . ': ' . $this->prepareField($val[1], $val[0]) . '</info>';
                } else {
                    foreach ($val[1] as $sub_prop => $sub_val) {
                        $doc->addField($prop . '_' . $sub_prop . '_' . $this->detectFieldType($sub_val[0]), $this->prepareField($sub_val[1], $sub_val[0]));
                        $resp[] = '<info>Nested Field: ' . $prop . '_' . $sub_prop . ': ' . $this->prepareField($sub_val[1], $sub_val[0]) . '</info>';
                    }
                }
            }
            try {
                $this->client->addDocument($doc);
            } catch (\SolrClientException $ex) {
                return $ex->getMessage();
            }
        }
        return $resp;
    }
    
    /**
     * Detect the type of field stored in the DB and let Solr have a valid field type to store the data
     * @param string $type
     * @return string
     */
    private function detectFieldType($type)
    {
        $resp = 's';
        switch ($type) {
            case 'int':
                $resp = 'i';
                break;
            case 'date':
                $resp = 'dt';
                break;
            default:
                $resp = 's';
                break;
        }
        return $resp;
    }
    
    private function prepareField($field, $type='')
    {
        $resp = $field;
        if (isset($type)) {
            switch ($type) {
                case 'date':
                    $resp = \DateTime::createFromFormat('d/m/Y', $resp);
                    break;
            }
        }
        if (is_a($resp, 'DateTime')) {
            $resp = $resp->format(\DateTime::ATOM);
        }
        return $resp;
    }

    /**
     * This method is used to upload a file to the index
     * @param string $fileloc
     * @return string | boolean
     */
    public function addFile($fileloc, $parent)
    {
        $result = false;
        // get a file through Media from it's path \\
        
        $info = $this->media->getFileInfo($fileloc);
        if ($info === false || in_array($info['type'], $this->parameters['file_types']) === false) {
            return false;
        }
        if(isset($parent['parent_url'])){
            $url = $parent['parent_url'];
        }else{
            $url = $parent['url'];
        }

        $type = $info['type'];
        $size = $info['size'];
        $directlink = $fileloc;
        $parentlink = $parent['url'];
        $posturl = [
            'literal.id'=> $parent['id'],
            'literal.parent'=>$parent['parent'],
            'literal.url'=>$url,
            'literal.parent_url'=>$parentlink,
            'literal.date_added'=>$info['added_on']->format(\DateTime::ATOM),
            'literal.status'=>'published',
            'literal.title'=>$info['title'],
            'literal.type_s'=>$type,
            'literal.size_i'=>$size
        ];

        $remove = array('id','status','title','url','parent','date_added','parent_url','type_s','size_i');
        foreach ($parent as $k => $v) {
            if(!in_array($k, $remove)){
                unset ($parent[$k]);
                $new_key = "literal.$k";
                $parent[$new_key] = $v;
            }else{
                unset ($parent[$k]);
            }
        }
        $posturl = array_merge($posturl,$parent);

        $url = $this->config['hostname'] . ':' . $this->config['port'] . '/'.$this->config['path'].'/update/extract?';
        $url .= http_build_query($posturl);
        
        if (file_exists($info['file'])) {
            $ch = curl_init($url);
            // set filename here to post a file \\
            $params = ['myfile'=> curl_file_create($info['file'])];
            curl_setopt_array($ch, [
                CURLOPT_POST=>1,
                CURLOPT_POSTFIELDS=>$params,
                CURLOPT_RETURNTRANSFER=>true
            ]);
            
            curl_exec($ch);
            $result = true;
            if (!empty(curl_error($ch))) {
                $result = curl_error($ch);
            }
        }
        
        return $result;
    }
    /**
     * Commit the changes to the search engine
     */
    public function commit()
    {
        $this->client->commit();
    }
    public function deleteById($ID)
    {
        $this->client->deleteById($ID);
        $resp = "Deleting index ...".$ID;
        return $resp;
    }
}
