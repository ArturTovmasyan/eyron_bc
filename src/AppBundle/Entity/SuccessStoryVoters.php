<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * SuccessStoryVoters
 *
 * @ORM\Table(name="success_story_voters", uniqueConstraints={@ORM\UniqueConstraint(name="IDX_duplicate_voters", columns={"success_story_id", "user_id"})})
 * @ORM\Entity()
 * @UniqueEntity(
 *     fields={"successStory", "user"},
 *     message="This user already liked this success story."
 * )
 */
class SuccessStoryVoters
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SuccessStory", inversedBy="successStoryVoters")
     * @ORM\JoinColumn(name="success_story_id", referencedColumnName="id", nullable=false)
     */
    private $successStory;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User", inversedBy="successStoryVoters")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

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
     * @return SuccessStoryVoters
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
     * Set successStory
     *
     * @param SuccessStory $successStory
     *
     * @return SuccessStoryVoters
     */
    public function setSuccessStory(SuccessStory $successStory = null)
    {
        $this->successStory = $successStory;

        return $this;
    }

    /**
     * Get successStory
     *
     * @return SuccessStory
     */
    public function getSuccessStory()
    {
        return $this->successStory;
    }

    /**
     * Set user
     *
     * @param \Application\UserBundle\Entity\User $user
     *
     * @return SuccessStoryVoters
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
}
