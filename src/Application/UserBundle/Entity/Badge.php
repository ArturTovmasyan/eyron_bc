<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/30/16
 * Time: 4:09 PM
 */
namespace Application\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="Application\UserBundle\Entity\Repository\BadgeRepository")
 * @ORM\Table(name="badge",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="user_type_unique",columns={"user_id", "type"})},
 *     indexes={@ORM\Index(name="idx_type_rating", columns={"type", "score", "user_id"})})
 * @UniqueEntity(fields={"user", "type"}, errorPath="user", message="badge.duplicate")
 */
class Badge
{
    // constants for type
    const TYPE_TRAVELLER = 1;
    const TYPE_MOTIVATOR = 2;
    const TYPE_INNOVATOR = 3;

    const MAXIMUM_NORMALIZE_SCORE = 10;

    /**
     * This value is used to show score normalized
     *
     * @Groups({"badge"})
     * @Serializer\SerializedName("score")
     * @var
     */
    public $normalizedScore;

    /**
     * This value is used to show position
     *
     * @Groups({"badge"})
     * @var
     */
    public $position;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Assert\NotBlank(message="badge.type.notBlank")
     * @Assert\Choice({Badge::TYPE_TRAVELLER, Badge::TYPE_MOTIVATOR, Badge::TYPE_INNOVATOR}, message = "badge.type.choice")
     * @ORM\Column(name="type", type="smallint")
     * @Groups({"badge"})
     */
    protected $type;

    /**
     * @Assert\NotBlank(message="badge.type.notBlank")
     * @ORM\Column(name="score", type="float")
     * @Groups({"badge"})
     * @Serializer\SerializedName("points")
     */
    protected $score = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User", inversedBy="badges")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @Groups({"badge"})
     */
    protected $user;

    /**
     * @var
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updated;
    
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
     * Set type
     *
     * @param integer $type
     *
     * @return Badge
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set score
     *
     * @param float $score
     *
     * @return Badge
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Get score
     *
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set user
     *
     * @param \Application\UserBundle\Entity\User $user
     *
     * @return Badge
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
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Badge
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }


    /**
     * @return array
     */
    static public function getTypesAsString()
    {
        return array(
            self::TYPE_INNOVATOR => 'innovator',
            self::TYPE_MOTIVATOR => 'mentor',
            self::TYPE_TRAVELLER => 'traveler',
        );
    }
}
