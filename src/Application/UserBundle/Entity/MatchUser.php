<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/29/16
 * Time: 3:21 PM
 */
namespace Application\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class MatchUsers
 * @package Application\UserBundle\Entity
 *
 * @ORM\Table(name="match_user", uniqueConstraints={@ORM\UniqueConstraint(name="IDX_userId_matchUserId", columns={"user_id", "match_user_id"})},
 *     indexes={@ORM\Index(name="IDX_userId_matchUserId_cFactor_cCount", columns={"user_id", "match_user_id", "common_factor", "common_count"})})
 * @ORM\Entity
 */
class MatchUser
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User", inversedBy="matchedUsers")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="match_user_id", referencedColumnName="id", nullable=false)
     */
    protected $matchUser;

    /**
     * @ORM\Column(name="common_factor", type="float", scale=4, nullable=false)
     */
    protected $commonFactor;

    /**
     * @ORM\Column(name="common_count", type="integer", nullable=false)
     */
    protected $commonCount;

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
     * Set commonFactor
     *
     * @param float $commonFactor
     *
     * @return MatchUsers
     */
    public function setCommonFactor($commonFactor)
    {
        $this->commonFactor = $commonFactor;

        return $this;
    }

    /**
     * Get commonFactor
     *
     * @return float
     */
    public function getCommonFactor()
    {
        return $this->commonFactor;
    }

    /**
     * Set commonCount
     *
     * @param integer $commonCount
     *
     * @return MatchUsers
     */
    public function setCommonCount($commonCount)
    {
        $this->commonCount = $commonCount;

        return $this;
    }

    /**
     * Get commonCount
     *
     * @return integer
     */
    public function getCommonCount()
    {
        return $this->commonCount;
    }

    /**
     * Set user
     *
     * @param \Application\UserBundle\Entity\User $user
     *
     * @return MatchUsers
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
     * Set matchUser
     *
     * @param \Application\UserBundle\Entity\User $matchUser
     *
     * @return MatchUsers
     */
    public function setMatchUser(\Application\UserBundle\Entity\User $matchUser = null)
    {
        $this->matchUser = $matchUser;

        return $this;
    }

    /**
     * Get matchUser
     *
     * @return \Application\UserBundle\Entity\User
     */
    public function getMatchUser()
    {
        return $this->matchUser;
    }
}
