<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/20/15
 * Time: 8:07 PM
 */


namespace AppBundle\Entity;

use AppBundle\Model\ActivityableInterface;
use Application\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class SuccessStory
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\SuccessStoryRepository")
 * @ORM\Table(name="success_story")
 */
class SuccessStory implements ActivityableInterface
{
    const MIN_LETTERS_IN_STORY = 3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"successStory","inspireStory"})
     */
    protected $id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Goal", inversedBy="successStories", cascade={"persist"})
     * @ORM\JoinColumn(name="goal_id", referencedColumnName="id", nullable=false)
     * @Groups({"inspireStory"})
     */
    protected $goal;

    /**
     * @ORM\OneToMany(targetEntity="StoryImage", mappedBy="story", cascade={"persist", "remove"})
     * @Groups({"successStory_storyImage", "inspireStory"})
     * @Assert\Valid()
     * @Assert\Count(
     *      max = "6",
     *      groups={"successStoryValidate"},
     *      maxMessage = "success_story.max_files"
     * )
     */
    protected $files;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User", inversedBy="SuccessStories")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @Groups({"successStory_user", "inspireStory"})
     **/
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\SuccessStoryVoters", mappedBy="successStory", cascade={"persist", "remove"})
     */
    protected $successStoryVoters;

    /**
     * @var
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @Groups({"successStory", "inspireStory"})
     */
    protected $created;

    /**
     * @var
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\Column(name="story", type="text")
     * @Groups({"successStory", "inspireStory"})
     * @Assert\NotBlank(groups={"successStoryValidate"})
     * @Assert\Length(
     *     min=3, 
     *     groups={"successStoryValidate"},
     *     minMessage="Story must have at least {{ limit }} characters."
     * )
     */
    protected $story;

    /**
     * @ORM\Column(name="video_link", type="json_array", nullable=true)
     * @Groups({"successStory", "inspireStory"})
     */
    protected $videoLink;

    /**
     * @ORM\Column(name="is_inspire", type="boolean", nullable=true)
     */
    protected $isInspire;

    /**
     * @Groups({"successStory", "inspireStory"})
     */
    protected $isVote;

    /**
     * @VirtualProperty()
     * @Groups({"successStory", "inspireStory"})
     *
     * @return int
     */
    public function getVotersCount()
    {
        return $this->successStoryVoters->count();
    }

    /**
     * This function is used to set isVote value
     *
     * @param $user
     * @return $this
     */
    public function setIsVote($user)
    {
        //set default is vote
        $isVote = false;

        //get voters
        $voters = $this->successStoryVoters;

         foreach ($voters as $voter)
         {
             if($voter->getUser() == $user) {
                 $isVote = true;
             }
         }

        $this->isVote = $isVote;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsVote()
    {
        return $this->isVote;
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
     * Set created
     *
     * @param \DateTime $created
     * @return SuccessStory
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
     * @return mixed
     */
    public function getIsInspire()
    {
        return $this->isInspire;
    }

    /**
     * @param $isInspire
     * @return $this
     */
    public function setIsInspire($isInspire)
    {
        $this->isInspire = $isInspire;

        return $this;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return SuccessStory
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
     * Set goal
     *
     * @param \AppBundle\Entity\Goal $goal
     * @return SuccessStory
     */
    public function setGoal(\AppBundle\Entity\Goal $goal = null)
    {
        $this->goal = $goal;

        return $this;
    }

    /**
     * Get goal
     *
     * @return \AppBundle\Entity\Goal 
     */
    public function getGoal()
    {
        return $this->goal;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->files  = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add files
     *
     * @param \AppBundle\Entity\StoryImage $files
     * @return SuccessStory
     */
    public function addFile(\AppBundle\Entity\StoryImage $files)
    {
        if (!$this->files->contains($files)){
            $this->files[] = $files;
            $files->setStory($this);
        }

        return $this;
    }

    /**
     * Remove files
     *
     * @param \AppBundle\Entity\StoryImage $files
     */
    public function removeFile(\AppBundle\Entity\StoryImage $files)
    {
        $this->files->removeElement($files);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set story
     *
     * @param string $story
     * @return SuccessStory
     */
    public function setStory($story)
    {
        $this->story = strip_tags(trim($story));

        return $this;
    }

    /**
     * Get story
     *
     * @return string
     */
    public function getStory()
    {
        return $this->story;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return SuccessStory
     */
    public function setUser(User $user = null)
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
     * Set videoLink
     *
     * @param string $videoLink
     * @return SuccessStory
     */
    public function setVideoLink($videoLink)
    {
        $this->videoLink = $videoLink;

        return $this;
    }

    /**
     * Get videoLink
     *
     * @return string 
     */
    public function getVideoLink()
    {
        return $this->videoLink;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Story of ' . $this->getGoal()->getTitle();
    }

    /**
     * @param $user
     * @return bool
     */
    public function isVote(User $user)
    {
        return $this->successStoryVoters->contains($user);
    }

    /**
     * Add successStoryVoter
     *
     * @param SuccessStoryVoters $successStoryVoter
     *
     * @return SuccessStory
     */
    public function addSuccessStoryVoter(SuccessStoryVoters $successStoryVoter)
    {

        if (!isset($this->successStoryVoters[$successStoryVoter->getId()])) {
            $this->successStoryVoters[$successStoryVoter->getId()] = $successStoryVoter;
        }

        return $this;
    }

    /**
     * @param SuccessStoryVoters $successStoryVoter
     * @return $this
     */
    public function removeSuccessStoryVoter(SuccessStoryVoters $successStoryVoter)
    {
        if (isset($this->successStoryVoters[$successStoryVoter->getId()])) {
            $this->successStoryVoters->removeElement($successStoryVoter);
        }

        return $this;

    }

    /**
     * Get successStoryVoters
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSuccessStoryVoters()
    {
        return $this->successStoryVoters;
    }
}
