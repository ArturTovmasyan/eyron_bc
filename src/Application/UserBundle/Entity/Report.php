<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 8/2/16
 * Time: 1:50 PM
 */
namespace Application\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use JMS\Serializer\Annotation\Groups;

/**
 * Class Report
 * @package Application\UserBundle\Entity
 *
 * @ORM\Table(name="report")
 * @ORM\Entity(repositoryClass="Application\UserBundle\Entity\Repository\ReportRepository")
 */
class Report
{
    const COMMENT       = 0;
    const SUCCESS_STORY = 1;

    const SPAM          = 2;
    const SHOULD_NOT    = 3;
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"report"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="reported_user_id", referencedColumnName="id", nullable=false)
     */
    protected $reportedUser;

    /**
     * @ORM\Column(name="content_type", type="smallint", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice(callback = "getAllowedTypes")
     * @Groups({"report"})
     */
    protected $contentType;

    /**
     * @ORM\Column(name="content_id", type="string", length=30, nullable=false)
     * @Assert\NotBlank()
     * @Groups({"report"})
     */
    protected $contentId;

    /**
     * @ORM\Column(name="report_type", type="smallint", nullable=true)
     * @Assert\Choice(callback = "getAllowedReportTypes")
     * @Groups({"report"})
     */
    protected $reportType;

    /**
     * @ORM\Column(name="message", type="string", length=1000, nullable=true)
     * @Groups({"report"})
     */
    protected $message;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    public function __toString()
    {
        return ($this->getContentType() == self::COMMENT ? 'Comment' : 'Success story') . ' reply';
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
     * Set message
     *
     * @param string $message
     *
     * @return Report
     */
    public function setMessage($message)
    {
        $this->message = (!is_null($message)) ? strip_tags($message) : $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set user
     *
     * @param \Application\UserBundle\Entity\User $user
     *
     * @return Report
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
     * Set reportedUser
     *
     * @param \Application\UserBundle\Entity\User $reportedUser
     *
     * @return Report
     */
    public function setReportedUser(\Application\UserBundle\Entity\User $reportedUser = null)
    {
        $this->reportedUser = $reportedUser;

        return $this;
    }

    /**
     * Get reportedUser
     *
     * @return \Application\UserBundle\Entity\User
     */
    public function getReportedUser()
    {
        return $this->reportedUser;
    }

    /**
     * Set contentType
     *
     * @param integer $contentType
     *
     * @return Report
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Get contentType
     *
     * @return integer
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set contentId
     *
     * @param string $contentId
     *
     * @return Report
     */
    public function setContentId($contentId)
    {
        $this->contentId = $contentId;

        return $this;
    }

    /**
     * Get contentId
     *
     * @return string
     */
    public function getContentId()
    {
        return $this->contentId;
    }

    /**
     * @return array
     */
    public static function getAllowedTypes()
    {
        return [self::COMMENT, self::SUCCESS_STORY];
    }

    /**
     * @return array
     */
    public static function getAllowedReportTypes()
    {
        return [self::SPAM, self::SHOULD_NOT];
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Report
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReportType()
    {
        return $this->reportType;
    }

    /**
     * @param mixed $reportType
     */
    public function setReportType($reportType)
    {
        $this->reportType = $reportType;
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
     * @return string
     */
    public function getContentTypeString()
    {
        return $this->getContentType() == self::COMMENT ? 'Comment' : 'Success story';
    }

    /**
     * @return string
     */
    public function getReportTypeString()
    {
        return $this->getReportType() == self::SPAM ? "It's annoying or spam" : "I think it shouldn't be on BucketList127";
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (is_null($this->getReportType()) && is_null($this->getMessage())) {
            $context->buildViolation('Report type or message is required')
                ->addViolation();
        }
    }
}
