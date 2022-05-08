<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;

class User extends BaseUser
{

    protected $id;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var boolean
     */
    protected $confirmed;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $confirmedAt;

    /**
     * @var string
     */
    protected $pendingEmail;

    /**
     * @var string
     */
    protected $pendingEmailCanonical;

    /**
     * @var array
     */
    protected $previousEmails;

    /**
     * @var \DateTime
     */
    protected $emailChangeRequestedAt;

    /**
     * @var boolean
     *
     */
    protected $rtbfApplied;

    /**
     * @var \DateTime
     */
    protected $rtbfAppliedOn;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->previousEmails = array();
    }   

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return bool
     */
    public function isConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * @param bool $confirmed
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getConfirmedAt()
    {
        return $this->confirmedAt;
    }

    /**
     * @param \DateTime $confirmedAt
     */
    public function setConfirmedAt($confirmedAt)
    {
        $this->confirmedAt = $confirmedAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return string
     */
    public function getPendingEmail()
    {
        return $this->pendingEmail;
    }

    /**
     * @param string $pendingEmail
     */
    public function setPendingEmail($pendingEmail)
    {
        $this->pendingEmail = $pendingEmail;
    }


    /**
     * @return string
     */
    public function getPendingEmailCanonical()
    {
        return $this->pendingEmailCanonical;
    }

    /**
     * @param string $pendingEmailCanonical
     */
    public function setPendingEmailCanonical($pendingEmailCanonical)
    {
        $this->pendingEmailCanonical = $pendingEmailCanonical;
    }


    /**
     * {@inheritdoc}
     */
    public function getPreviousEmails()
    {
        $previousEmails = is_null($this->previousEmails) ? array() : $this->previousEmails;

        return array_unique($previousEmails);
    }

    /**
     * {@inheritdoc}
     */
    public function setPreviousEmails(array $previousEmails)
    {
        $this->previousEmails = array();

        foreach ($previousEmails as $previousEmail) {
            $this->addPreviousEmail($previousEmail);
        }

        return $this;
    }    

    /**
     * {@inheritdoc}
     */
    public function hasPreviousEmail($previousEmail)
    {
        return in_array(strtolower($previousEmail), $this->getPreviousEmails(), true);
    } 

    /**
     * {@inheritdoc}
     */
    public function addPreviousEmail($previousEmail)
    {
        if (!in_array(strtolower($previousEmail), $this->getPreviousEmails(), true)) {
            $this->previousEmails[] = strtolower($previousEmail);
        }

        return $this;
    } 

    /**
     * {@inheritdoc}
     */
    public function removePreviousEmail($previousEmail)
    {
        if (false !== $key = array_search(strtoupper($previousEmail), $this->previousEmails, true)) {
            unset($this->previousEmails[$key]);
            $this->previousEmails = array_values($this->previousEmails);
        }

        return $this;
    }        

    /**
     * @return \DateTime
     */
    public function getEmailChangeRequestedAt()
    {
        return $this->emailChangeRequestedAt;
    }

    /**
     * @param \DateTime $emailChangeRequestedAt
     */
    public function setEmailChangeRequestedAt($emailChangeRequestedAt)
    {
        $this->emailChangeRequestedAt = $emailChangeRequestedAt;
    }

    /**
     * @return bool
     */
    public function isRtbfApplied() {
        return $this->rtbfApplied;
    }

    /**
     * @param bool $rtbfApplied
     */
    public function setRtbfApplied($rtbfApplied) {
        $this->rtbfApplied = $rtbfApplied;
    }

    /**
     * @return \DateTime
     */
    public function getRtbfAppliedOn() {
        return $this->rtbfAppliedOn;
    }

    /**
     * @param \DateTime $rtbfAppliedOn
     */
    public function setRtbfAppliedOn($rtbfAppliedOn) {
        $this->rtbfAppliedOn = $rtbfAppliedOn;
    }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }




}
