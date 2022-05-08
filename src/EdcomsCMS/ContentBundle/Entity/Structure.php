<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Structure
 *
 * @ORM\Table()
 * @UniqueEntity(fields={"link"})
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\StructureRepository")
 */
class Structure
{
    use \EdcomsCMS\ContentBundle\Traits\EntityHydration;
    const ParentID = '0';
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=150)
     */
    private $link;

    /**
     * @var integer
     * @ORM\Column(name="priority", type="integer", nullable=true)
     */
    private $priority;

    /**
     * @var datetime
     * @ORM\Column(name="addedOn", type="datetime", nullable=true)
     */
    private $addedOn;


    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Structure", mappedBy="parent")
     * @ORM\OrderBy({"priority"="ASC", "addedOn"="DESC", "id"="DESC"})
     */
    private $children;

    /**
     *
     * @var Array
     */
    private $childrenArr = [];

    /**
     * @var Structure
     * @ORM\ManyToOne(targetEntity="Structure", inversedBy="children", fetch="EAGER")
     * @ORM\JoinColumn(name="parentID", referencedColumnName="id")
     */
    private $parent;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Content", mappedBy="structure", fetch="EXTRA_LAZY", cascade={"all"})
     * @ORM\OrderBy({"addedOn"="DESC"})
     */
    private $content;

    /**
     *
     * @var boolean
     * @ORM\Column(name="deleted", type="boolean", nullable=false, options={"default"=false}))
     */
    private $deleted = false;
    
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Rating", mappedBy="structure", fetch="EXTRA_LAZY")
     */
    private $rating;
    
    /**
     * @var boolean
     * @ORM\Column(name="rateable", type="boolean", nullable=true)
     */
    private $rateable;


    /**
     * @var Structure
     * @ORM\ManyToOne(targetEntity="Structure", inversedBy="linked", fetch="EAGER")
     * @ORM\JoinColumn(name="masterID", referencedColumnName="id", nullable=true)
     */
    private $master;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Structure", mappedBy="master")
     * @ORM\OrderBy({"priority"="ASC", "addedOn"="DESC", "id"="DESC"})
     */
    private $linked;

    /**
     * @var boolean
     * @ORM\Column(name="visible", type="boolean",options={"default" = true})
     */
    private $visible;
    
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="LinkBuilder", mappedBy="structure")
     */
    private $linkBuilders;

    /**
     * @var StructureContextInterface
     * @ORM\OneToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\StructureContext", mappedBy="structure", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $context;

    /**
     * @var PageMetadata
     * @Assert\Valid()
     * @ORM\OneToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\PageMetadata", inversedBy="structure", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="page_metadata_id", referencedColumnName="id")
     */
    private $pageMetadata;

    public function __construct() {
        $this->children = new ArrayCollection();
        $this->content = new ArrayCollection();
        $this->linkBuilders = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Structure
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set link
     *
     * @param string $link
     * @return Structure
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Get the full link of the Structure.
     *
     * @param   bool  $returnStr  'true' if the return should be the full link as a compiled string.
     *
     * @return  array|string
     */
    public function getFullLink($returnStr = false)
    {
        $current = $this;
        $link = [];

        while ($current !== null) {
            $link[] = $current->getLink();
            $current = $current->getParent();
        }

        // remove 'home' structure at beginnnig of the link.
        array_pop($link);
        $link = array_reverse($link);

        return $returnStr ? implode('/', $link) : $link;
    }
    /**
     * Set priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Get priority
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set addedOn
     */
    public function setAddedOn($addedOn)
    {
        $this->addedOn = $addedOn;
        return $this;
    }

    /**
     * Get addedOn
     * @return datetime
     */
    public function getAddedOn()
    {
        return $this->addedOn;
    }
    /**
     * Set deleted
     *
     * @param boolean $deleted
     *
     * @return Structure
     */
    public function setDeleted($deleted) {
        $this->deleted = $deleted;
        return $this;
    }
    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeleted() {
        return $this->deleted;
    }

    public function hasChildren()
    {
        return (isset($this->children) && sizeof($this->children) > 0)? true: false;
    }

    /**
     * Set the parent Structure. This must not be itself.
     *
     * @param   Structure  $parent        The parent Structure to set.
     *
     * @return  self                      Self for method chaining.
     * @throws  InvalidArgumentException  If the parent is the Structure itself.
     */
    public function setParent(Structure $parent)
    {
        if ($parent->getId() === $this->getId()) {
            throw new \InvalidArgumentException('Cannot set self as the parent structure.');
        }

        $this->parent = $parent;

        return $this;
    }

    public function getParent() {
        return $this->parent;
    }

    public function setChildren($children) {
        $this->children = $children;
        return $this;
    }

    /**
     * Get the children of this structure.
     * Default is to return without deleted items.
     * Use the $deleted param to include deleted items in the returned collection
     *
     * @param bool|false $deleted - set to true to include deleted items
     * @param bool|false $order - array (string) of property to order by and direction of ordering (ASC, DESC)
     * @return \Doctrine\Common\Collections\Collection|static
     */

    public function getChildren($deleted = false, $order=false) {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('deleted', $deleted));

        //add ordering if requested
        if ($order && is_array($order)) {
            $criteria->orderBy($order);
        }
        return $this->children->matching($criteria);
    }
    /**
     * Get the deleted children of this structure.
     *
     * @return \Doctrine\Common\Collections\Collection|static
     */
    public function getDeletedChildren() {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('deleted', true));
        return $this->children->matching($criteria);
    }
    public function setChildrenArr($child)
    {
        $this->childrenArr[$child->getId()] = $child;
        return $this;
    }
    public function getChildrenArr()
    {
        uasort($this->childrenArr, array(&$this, 'sortChildren'));
        return $this->childrenArr;
    }

    /**
     * @param string $status
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function getContent($status='published')
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('status', $status));
        return $this->content->matching($criteria);
    }

    /**
     * @return Content|null
     */
    public function getPublishedContent(){
        $collection = $this->getContent();
        return $collection->first();
    }
    
    /**
     * @return ArrayCollection
     */
    public function getRating()
    {
        return $this->rating;
    }
    /**
     * 
     * @param ArrayCollection $rating
     * @return Structure
     */
    public function setRating(ArrayCollection $rating)
    {
        $this->rating = $rating;
        return $this;
    }
    
    /**
     * 
     * @param \EdcomsCMS\ContentBundle\Entity\Rating $rating
     * @param boolean $recurs
     * @return Structure
     */
    public function addRating(Rating $rating, $recurs=true)
    {
        $this->rating[] = $rating;
        if ($recurs) {
            $rating->setStructure($this, false);
        }
        return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getRateable()
    {
        return $this->rateable;
    }
    
    /**
     * 
     * @param boolean $rateable
     * @return Structure
     */
    public function setRateable($rateable)
    {
        $this->rateable = $rateable;
        return $this;
    }


    public function setMaster(Structure $master) {
        $this->master = $master;
        return $this;
    }

    public function getMaster() {
        return $this->master;
    }

    public function setLinked($linked) {
        $this->linked = $linked;
        return $this;
    }
    public function getLinked($deleted=false) {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('deleted', $deleted));
        return $this->linked->matching($criteria);
    }

    /**
     *
     * @return boolean
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     *
     * @param boolean $visible
     * @return Structure
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @return  ArrayCollection     The associated LinkBuilder entities.
     */
    public function getLinkBuilders()
    {
        return $this->linkBuilders;
    }

    /**
     * Adds a LinkBuilder entity to the 'linkBuilders' collection.
     * Automatically associates the LinkBuilder's 'structure' as this.
     *
     * @param   LinkBuilder     $linkBuilder    Entity to add.
     *
     * @return  Structure                       This current structure, used for method chaining.
     */
    public function addLinkBuilder(LinkBuilder $linkBuilder)
    {
        if (!$this->linkBuilders->contains($linkBuilder)) {
            $linkBuilder->setStructure($this);
            $this->linkBuilders->add($linkBuilder);
        }

        return $this;
    }

    /**
     * Removes a LinkBuilder entity from the 'linkBuilders' collection.
     * Automatically associates the LinkBuilder's 'structure' as null.
     *
     * @param   LinkBuilder     $linkBuilder    Entity to remove.
     *
     * @return  Structure                       This current structure, used for method chaining.
     */
    public function removeLinkBuilder(LinkBuilder $linkBuilder)
    {
        if ($this->linkBuilders->contains($linkBuilder)) {
            $linkBuilder->setStructure(null);
            $this->linkBuilders->remove($linkBuilder);
        }

        return $this;
    }

    public function toJSON($vars=[], $fullSerialization=false) {
        unset($this->json);
        if (empty($vars) || $fullSerialization===true) {
            $this->json = get_object_vars($this);
            // remove anything with an _ \\
            $this->json = array_filter($this->json, array(&$this, 'json_filter'), ARRAY_FILTER_USE_BOTH);
            if ($this->getAddedOn()) {
                $this->json['addedOn'] = $this->getAddedOn()->format('d/m/Y');
            }
        }
        if (empty($vars) || in_array('parent', $vars)) {
            $this->json['parent'] = (!isset($this->json['parent']) || !is_array($this->json['parent'])) ? (isset($this->parent) && !is_null($this->parent)) ? $this->parent->toJSON(['id', 'title', 'link', 'priority', 'parent']) : null : $this->parent;
        }
        if (isset($this->children) && (empty($vars) || in_array('children', $vars))) {
            $tempchildren = [];
            $children = $this->getChildren();
            foreach ($children as $key=>$child) {
                $tempchildren[$key] = $child->toJSON($vars);
            }
            $this->json['children'] = $tempchildren;
        }
        if (isset($this->content) && (empty($vars) || in_array('content', $vars) || isset($vars['content']))) {
            // only need the first 1 here \\
            // if $vars['content'] is an array then we want the first one of set status \\
            if (isset($vars['content'])) {
                $content = $this->getContent($vars['content'])->first();
                // now reset it to the default value \\
            } else {
                $content = $this->content->first();
            }
            $this->json['content'] = (is_a($content, 'EdcomsCMS\ContentBundle\Entity\Content')) ? $content->toJSON(['id', 'title', 'addedOn', 'addedUser', 'contentType', 'status']) : [];
        }
        if (isset($this->master) && (empty($vars) || in_array('master', $vars))) {
          $this->json['master'] = (!isset($this->json['master']) || !is_array($this->json['master'])) ? (isset($this->master) && !is_null($this->master)) ? $this->master->toJSON(['id', 'title', 'link', 'priority', 'parent']) : null : $this->master;
        }
        if (empty($vars) || $fullSerialization) {
            return $this->json;
        }
        $obj = [];
        foreach ($vars as $key=>$prop) {
            if (!is_int($key)) {
                $prop = $key;
            }
            $obj[$prop] = (isset($this->json[$prop])) ? $this->json[$prop] : $this->{$prop};
            if ($prop === 'addedOn' && $this->getAddedOn()) {
                $obj[$prop] = $this->getAddedOn()->format('d/m/Y');
            }
        }
        return $obj;
    }
    public function json_filter($val, $key) {
        if (!strstr($key, '__')) {
            return true;
        }
        return false;
    }
    private function sortChildren($a, $b)
    {
        return ($a->getPriority() < $b->getPriority()) ? -1 : 1;
    }

    public function __toString()
    {
        return $this->getTitle() ? $this->getTitle() : '';
    }

    /**
     * @return StructureContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param StructureContextInterface $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return PageMetadata
     */
    public function getPageMetadata()
    {
        return $this->pageMetadata;
    }

    /**
     * @param PageMetadata $pageMetadata
     */
    public function setPageMetadata($pageMetadata)
    {
        $this->pageMetadata = $pageMetadata;
    }


}
