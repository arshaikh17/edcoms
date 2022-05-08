<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TaxonomyHooks
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\TaxonomyHooksRepository")
 */
class TaxonomyHooks
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="tagID", type="integer")
     */
    private $tagID;

    /**
     * @var string
     *
     * @ORM\Column(name="item_table", type="string", length=20)
     */
    private $itemTable;

    /**
     * @var integer
     *
     * @ORM\Column(name="itemID", type="integer")
     */
    private $itemID;


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
     * Set tagID
     *
     * @param integer $tagID
     * @return TaxonomyHooks
     */
    public function setTagID($tagID)
    {
        $this->tagID = $tagID;

        return $this;
    }

    /**
     * Get tagID
     *
     * @return integer 
     */
    public function getTagID()
    {
        return $this->tagID;
    }

    /**
     * Set itemTable
     *
     * @param string $itemTable
     * @return TaxonomyHooks
     */
    public function setItemTable($itemTable)
    {
        $this->itemTable = $itemTable;

        return $this;
    }

    /**
     * Get itemTable
     *
     * @return string 
     */
    public function getItemTable()
    {
        return $this->itemTable;
    }

    /**
     * Set itemID
     *
     * @param integer $itemID
     * @return TaxonomyHooks
     */
    public function setItemID($itemID)
    {
        $this->itemID = $itemID;

        return $this;
    }

    /**
     * Get itemID
     *
     * @return integer 
     */
    public function getItemID()
    {
        return $this->itemID;
    }
}
