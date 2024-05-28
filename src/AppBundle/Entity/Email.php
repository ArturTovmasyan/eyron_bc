<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Email
 *
 * @ORM\Table(name="email", indexes={
 *          @ORM\Index(name="IDX_EMAIL_SEARCH", columns={"sent"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\EmailRepository")
 */
class Email
{
    const DEVICE_MOBILE = 1;
    const DEVICE_TABLET = 2;
    const DEVICE_PC = 3;

    /**
     * @var int
     *
     * @ORM\Column(type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=60)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="binary", length=2000)
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $sent;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $seen;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     */
    private $device = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User", inversedBy="sentEmails")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @return int|string
     */
    public function __toString()
    {
        return ($this->id) ? $this->id : '';
    }

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
     * Set content
     *
     * @param string $content
     *
     * @return Email
     */
    public function setContent($content)
    {
        $this->content = gzcompress($content);
        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        if(is_resource($this->content)){
            $stream = stream_get_contents($this->content);
            if($stream){
                $unGzip = gzuncompress($stream);
                return  $unGzip;
            }
        }

        return $this->content;
    }

    /**
     * Set sent
     *
     * @param \DateTime $sent
     *
     * @return Email
     */
    public function setSent($sent)
    {
        $this->sent = $sent;

        return $this;
    }

    /**
     * Get sent
     *
     * @return \DateTime
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * Set seen
     *
     * @param \DateTime $seen
     *
     * @return Email
     */
    public function setSeen($seen)
    {
        $this->seen = $seen;

        return $this;
    }

    /**
     * Get seen
     *
     * @return \DateTime
     */
    public function getSeen()
    {
        return $this->seen;
    }

    /**
     * Set device
     *
     * @param integer $device
     *
     * @return Email
     */
    public function setDevice($device)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Get device
     *
     * @return int
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Set user
     *
     * @param \Application\UserBundle\Entity\User $user
     *
     * @return Email
     */
    public function setUser(\Application\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Application\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Email
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
}
