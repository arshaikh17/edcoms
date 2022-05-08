<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SearchPhrases
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\SearchPhrasesRepository")
 */
class SearchPhrases
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
     * @var string
     *
     * @ORM\Column(name="phrase", type="text")
     */
    private $phrase;
    
    /**
     * @var integer
     * @ORM\Column(name="totalUsed", type="integer")
     */
    private $totalUsed;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_used", type="datetime")
     */
    private $lastUsed;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="first_used", type="datetime")
     */
    private $firstUsed;
    
    /**
     * Set id
     *
     * @param int $id
     * @return Media
     */
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
     * Set phrase
     *
     * @param string $phrase
     * @return SearchPhrases
     */
    public function setPhrase($phrase)
    {
        $this->phrase = $phrase;

        return $this;
    }

    /**
     * Get phrase
     *
     * @return string 
     */
    public function getPhrase()
    {
        return $this->phrase;
    }
    /**
     * Set total used
     *
     * @param integer $used
     * @return SearchPhrases
     */
    public function setTotalUsed($used)
    {
        $this->totalUsed = $used;

        return $this;
    }
    
    /**
     * 
     * @return integer
     */
    public function getTotalUsed()
    {
        return $this->totalUsed;
    }

    /**
     * Set last used
     *
     * @param \DateTime $lastUsed
     * @return SearchPhrases
     */
    public function setLastUsed($lastUsed)
    {
        $this->lastUsed = $lastUsed;

        return $this;
    }
    
    /**
     * 
     * @return \DateTime
     */
    public function getLastUsed()
    {
        return $this->lastUsed;
    }
    
    /**
     * 
     * @param \DateTime $firstUsed
     * @return SearchPhrases
     */
    public function setFirstUsed($firstUsed)
    {
        $this->firstUsed = $firstUsed;
        return $this;
    }
}
