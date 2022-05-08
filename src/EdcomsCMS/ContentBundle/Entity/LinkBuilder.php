<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use EdcomsCMS\ContentBundle\Entity\Structure;

/**
 * LinkBuilder
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\LinkBuilderRepository")
 */
class LinkBuilder
{
    const TARGET_BLANK = '_blank';
    const TARGET_PARENT = '_parent';
    const TARGET_SELF = '_self';
    const TARGET_TOP = '_top';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Structure
     *
     * @ORM\ManyToOne(targetEntity="EdcomsCMS\ContentBundle\Entity\Structure", inversedBy="linkBuilders")
     * @ORM\JoinColumn(name="structureID", referencedColumnName="id", nullable=true)
     */
    private $structure;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=true)
     */
    private $link;

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="string", length=255, nullable=true)
     */
    private $target;

    /**
     * @var string
     *
     * @ORM\Column(name="friendly_link", type="string", length=255, unique=true)
     */
    private $friendlyLink;


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
     * Set structure.
     *
     * @param Structure $structure
     *
     * @return LinkBuilder
     */
    public function setStructure(Structure $structure = null)
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Get structure.
     *
     * @return Structure
     */
    public function getStructure()
    {
        return $this->structure;
    }

    public function hasStructure()
    {
        return $this->structure !== null;
    }

    /**
     * Set link
     *
     * @param string $link
     * @return LinkBuilder
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
     * Set target
     *
     * @param string $target
     * @return LinkBuilder
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return string 
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set friendlyLink
     *
     * @param string $friendlyLink
     * @return LinkBuilder
     */
    public function setFriendlyLink($friendlyLink)
    {
        $this->friendlyLink = $friendlyLink;

        return $this;
    }

    /**
     * Get friendlyLink
     *
     * @return string 
     */
    public function getFriendlyLink()
    {
        return $this->friendlyLink;
    }
}
