<?php

namespace Application\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Notification
 *
 * @ORM\Table(name="notification")
 * @ORM\Entity()
 */
class Notification
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"notification"})
     */
    protected $id;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     * @Groups({"notification"})
     */
    protected $created;

    /**
     * @ORM\Column(name="body", type="string", length=200, nullable=false)
     * @Groups({"notification"})
     */
    protected $body;

    /**
     * @ORM\Column(name="link", type="string", length=100, nullable=false)
     * @Groups({"notification"})
     */
    protected $link;

    /**
     * @ORM\Column(name="goal_id", type="integer", nullable=true)
     * @Groups({"notification"})
     */
    protected $goalId;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="performer_id", referencedColumnName="id")
     *
     * @Groups({"notification_performer"})
     */
    protected $performer;

    /**
     * @ORM\OneToMany(targetEntity="Application\UserBundle\Entity\UserNotification", mappedBy="notification")
     *
     * @Groups({"notification_userNotification"})
     */
    protected $userNotifications;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Notification
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set body
     *
     * @param string $body
     *
     * @return Notification
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set performer
     *
     * @param \Application\UserBundle\Entity\User $performer
     *
     * @return Notification
     */
    public function setPerformer(\Application\UserBundle\Entity\User $performer = null)
    {
        $this->performer = $performer;

        return $this;
    }

    /**
     * Get performer
     *
     * @return \Application\UserBundle\Entity\User
     */
    public function getPerformer()
    {
        return $this->performer;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userNotifications = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add userNotification
     *
     * @param \Application\UserBundle\Entity\UserNotification $userNotification
     *
     * @return UserNotification
     */
    public function addUserNotification(\Application\UserBundle\Entity\UserNotification $userNotification)
    {
        $this->userNotifications[] = $userNotification;

        return $this;
    }

    /**
     * Remove userNotification
     *
     * @param \Application\UserBundle\Entity\UserNotification $userNotification
     */
    public function removeUserNotification(\Application\UserBundle\Entity\UserNotification $userNotification)
    {
        $this->userNotifications->removeElement($userNotification);
    }

    /**
     * Get userNotifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserNotifications()
    {
        return $this->userNotifications;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param mixed $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return mixed
     */
    public function getGoalId()
    {
        return $this->goalId;
    }

    /**
     * @param mixed $goalId
     */
    public function setGoalId($goalId)
    {
        $this->goalId = $goalId;
    }
}
