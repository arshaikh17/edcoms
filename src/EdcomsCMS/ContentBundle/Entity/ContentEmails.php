<?php

namespace EdcomsCMS\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContentEmails
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EdcomsCMS\ContentBundle\Entity\ContentEmailsRepository")
 */
class ContentEmails
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
     * @ORM\Column(name="subject", type="string", length=150)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="message_text", type="text")
     */
    private $messageText;

    /**
     * @var string
     *
     * @ORM\Column(name="message_html", type="text")
     */
    private $messageHtml;

    /**
     * @var integer
     *
     * @ORM\Column(name="triggerID", type="integer")
     */
    private $triggerID;

    /**
     * @var string
     *
     * @ORM\Column(name="recipient", type="string", length=150)
     */
    private $recipient;


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
     * Set subject
     *
     * @param string $subject
     * @return Content_emails
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set messageText
     *
     * @param string $messageText
     * @return ContentEmails
     */
    public function setMessageText($messageText)
    {
        $this->messageText = $messageText;

        return $this;
    }

    /**
     * Get messageText
     *
     * @return string 
     */
    public function getMessageText()
    {
        return $this->messageText;
    }

    /**
     * Set messageHtml
     *
     * @param string $messageHtml
     * @return ContentEmails
     */
    public function setMessageHtml($messageHtml)
    {
        $this->messageHtml = $messageHtml;

        return $this;
    }

    /**
     * Get messageHtml
     *
     * @return string 
     */
    public function getMessageHtml()
    {
        return $this->messageHtml;
    }

    /**
     * Set triggerID
     *
     * @param integer $triggerID
     * @return ContentEmails
     */
    public function setTriggerID($triggerID)
    {
        $this->triggerID = $triggerID;

        return $this;
    }

    /**
     * Get triggerID
     *
     * @return integer 
     */
    public function getTriggerID()
    {
        return $this->triggerID;
    }

    /**
     * Set recipient
     *
     * @param string $recipient
     * @return ContentEmails
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get recipient
     *
     * @return string 
     */
    public function getRecipient()
    {
        return $this->recipient;
    }
}
