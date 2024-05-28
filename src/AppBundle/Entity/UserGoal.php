<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/8/15
 * Time: 3:32 PM
 */

namespace AppBundle\Entity;

use AppBundle\Model\ActivityableInterface;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Traits\Location;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\UserGoalRepository")
 * @ORM\Table(name="users_goals", uniqueConstraints={@ORM\UniqueConstraint(name="IDX_duplicate_user_goal", columns={"user_id", "goal_id"})},
 *                                indexes={@ORM\Index(name="IDX_profile_last_modified", columns={"user_id", "id", "goal_id", "updated"}),
 *                                         @ORM\Index(name="IDX_goal_visible_user", columns={"goal_id", "is_visible", "user_id"})})
 * @ORM\EntityListeners({"AppBundle\Listener\UserGoalListener"})
 */
class UserGoal implements ActivityableInterface
{
    // constants for status
    const ACTIVE = 1;
    const COMPLETED = 2;
    const NONE = 0; // this constant is used to hide not interested goals

    //constants for date status
    const OLL = 1;
    const ONLY_YEAR = 2;
    const ONLY_YEAR_MONTH = 3;

    // constants for filter in twig
    const URGENT_IMPORTANT = 1;
    const URGENT_NOT_IMPORTANT = 2;
    const NOT_URGENT_IMPORTANT = 3;
    const NOT_URGENT_NOT_IMPORTANT = 4;

    // constants for steps
    const TO_DO = 0;
    const DONE = 1;
    
    //constants for delete or unlisted user goal
    const DELETE = 1;
    const UNLISTED = 0;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"userGoal", "tiny_user"})
     */
    protected $id;

    /**
     * @var
     * @ORM\Column(name="status", type="smallint")
     * @Groups({"userGoal"})
     */
    protected $status = self::ACTIVE;

    /**
     * @var
     * @ORM\Column(name="date_status", type="smallint", options={"default"=1})
     * @Groups({"userGoal"})
     */
    protected $dateStatus = self::OLL;

    /**
     * @var
     * @ORM\Column(name="do_date_status", type="smallint", options={"default"=1})
     * @Groups({"userGoal"})
     */
    protected $doDateStatus = self::OLL;

    /**
     * @var
     * @ORM\Column(name="is_visible", type="boolean")
     * @Groups({"userGoal"})
     */
    protected $isVisible = false;

    /**
     * @var
     * @ORM\Column(name="urgent", type="boolean")
     * @Groups({"userGoal"})
     */
    protected $urgent = false;

    /**
     * @var
     * @ORM\Column(name="important", type="boolean")
     * @Groups({"userGoal"})
     */
    protected $important = true;

    /**
    * @var
    * @ORM\Column(name="not_interested", type="boolean")
    */
    protected $notInterested = false;

    /**
     * @var
     * @ORM\Column(name="note", type="string", length=1000, nullable=true)
     * @Groups({"userGoal"})
     */
    protected $note;

    /**
     * @var
     * @Groups({"userGoal"})
     * @ORM\Column(name="steps", type="array", nullable=true)
     */
    protected $steps = [];

    /**
     * @var
     * @ORM\Column(name="due_date", type="datetime", nullable=true)
     * @Groups({"userGoal"})
     */
    protected $doDate;

    /**
     * @var
     * @ORM\Column(name="completion_date", type="datetime", nullable=true)
     * @Groups({"userGoal"})
     */
    protected $completionDate;

    /**
     * @var
     * @ORM\Column(name="listed_date", type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @Groups({"userGoal"})
     */
    protected $listedDate;

    /**
     * @ORM\ManyToOne(targetEntity="Goal", inversedBy="userGoal", cascade={"persist"})
     * @ORM\JoinColumn(name="goal_id", referencedColumnName="id", nullable=false)
     * @Groups({"userGoal_goal"})
     **/
    protected $goal;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User", inversedBy="userGoal")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id",  nullable=false)
     * @Groups({"user"})
     **/
    protected $user;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
     * @Groups({"userGoal"})
     */
    protected $updated;

    /**
     * @var
     * @ORM\Column(name="confirmed", type="boolean")
     */
    protected $confirmed = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = self::ACTIVE;
    }

    /**
     * @return int|string
     */
    public function __toString()
    {
        return (string)($this->id) ? (string)$this->id : '';
    }

    /**
     * @VirtualProperty()
     * @SerializedName("steps")
     * @Groups({"userGoal"})
     */
    public function getFormattedSteps()
    {
        $formattedSteps = [];
        foreach($this->steps as $key => $value){
            $formattedSteps[] = ['step' => $key, 'is_done' => $value];
        }

        return $formattedSteps;
    }

    /**
     * @return int|null
     */
    public function getUrgentImportantStatus()
    {
        if ($this->urgent === true && $this->important === true){
            return UserGoal::URGENT_IMPORTANT;
        }
        elseif ($this->urgent === true && $this->important === false){
            return UserGoal::URGENT_NOT_IMPORTANT;
        }
        elseif ($this->urgent === false && $this->important === true){
            return UserGoal::NOT_URGENT_IMPORTANT;
        }
        elseif ($this->urgent === false && $this->important === false){
            return UserGoal::NOT_URGENT_NOT_IMPORTANT;
        }

        return null;
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
     * Set status
     *
     * @param integer $status
     * @return UserGoal
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * This function is used to get status integer, and convert to string
     *
     * @return null|string
     */
    public function getStatusString()
    {
        // empty result
        $result = null;

        // switch for status and return result
        switch($this->status){
            case self::ACTIVE:
                $result = 'user_goal.active';
                break;
            case self::COMPLETED:
                $result = 'user_goal.completed';
                break;
        }

        return $result;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * This function is used to get privacy integer, and convert to string
     *
     * @return null|string
     */
    public function getPrivacyString()
    {
        // empty result
        $result = null;

        // switch for privacy and return result
        switch($this->privacy){
            case self::PRIVATE_PRIVACY:
                $result = 'user_goal.private';
                break;
            case self::PUBLIC_PRIVACY:
                $result = 'user_goal.public';
                break;
        }

        return $result;
    }

    /**
     * Set goal
     *
     * @param \AppBundle\Entity\Goal $goal
     * @return UserGoal
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
     * Set user
     *
     * @param \Application\UserBundle\Entity\User $user
     * @return UserGoal
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
     * Set note
     *
     * @param string $note
     * @return UserGoal
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string 
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set doDate
     *
     * @param \DateTime $doDate
     * @return UserGoal
     */
    public function setDoDate($doDate)
    {
        $this->doDate = $doDate;

        return $this;
    }

    /**
     * Get doDate
     *
     * @return \DateTime 
     */
    public function getDoDate()
    {
        return $this->doDate;
    }

    /**
     * Set steps
     *
     * @param array $steps
     * @return UserGoal
     */
    public function setSteps($steps)
    {
        $this->steps = $steps;

        return $this;
    }

    /**
     * Get steps
     *
     * @return array 
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * @return mixed
     */
    public function getCompletionDate()
    {
        return $this->completionDate;
    }

    /**
     * @param mixed $completionDate
     */
    public function setCompletionDate($completionDate)
    {
        $this->completionDate = $completionDate;
    }

    /**
     * @return mixed
     */
    public function getListedDate()
    {
        return $this->listedDate;
    }

    /**
     * @param mixed $listedDate
     */
    public function setListedDate($listedDate)
    {
        $this->listedDate = $listedDate;
    }

    /**
     * @return mixed
     */
    public function getUrgent()
    {
        return $this->urgent;
    }

    /**
     * @param mixed $urgent
     */
    public function setUrgent($urgent)
    {
        $this->urgent = $urgent;
    }

    /**
     * @return mixed
     */
    public function getImportant()
    {
        return $this->important;
    }

    /**
     * @param mixed $important
     */
    public function setImportant($important)
    {
        $this->important = $important;
    }

    /**
     * @return int
     * @VirtualProperty
     * @Groups({"userGoal"})
     */
    public function getCompleted()
    {
        // get all steps
        $steps = $this->getSteps();

        // check step
        if($steps){

            // get count of steps
            $count = count($steps);

            // count of steps for done
            $done = 0;

            // loop for steps
            foreach($steps as $step){

                // check is step done
                if($step == self::DONE) {
                    $done ++;
                }
            }
            return $done * 100 / $count;
        }

        return 100;
    }

    /**
     * @VirtualProperty()
     * @SerializedName("location")
     * @Groups({"userGoal_location"})
     */
    public function getUserGoalLocation()
    {
        $location = $this->getGoal()->getLocation();
        if(!$location){
            $location = $this->getLocation();
            if ($location){
                $location['editable'] = true;
            }
        }
        else {
            $location['editable'] = false;
        }

        return $location;
    }

    /**
     * This function is used to return json location for twig
     *
     * @return string
     * @VirtualProperty()
     * @SerializedName("formatted_steps")
     * @Groups({"userGoal"})
     */
    public function getStepsJson()
    {
        $result= array();

        // get steps
        $steps = $this->getSteps();

        if($steps){
            foreach($steps as $text => $switch){
                $result[] = array('text' => $text, 'switch' => $switch == self::DONE);
            }
        }
        else{
            $result[] = array();
        }
        return $result;
    }

    /**
     * Set isVisible
     *
     * @param boolean $isVisible
     * @return UserGoal
     */
    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    /**
     * Get isVisible
     *
     * @return boolean 
     */
    public function getIsVisible()
    {
        return $this->isVisible;
    }

    /**
     * Set dateStatus
     *
     * @param integer $dateStatus
     *
     * @return UserGoal
     */
    public function setDateStatus($dateStatus)
    {
        $this->dateStatus = $dateStatus;

        return $this;
    }

    /**
     * Get dateStatus
     *
     * @return integer
     */
    public function getDateStatus()
    {
        return $this->dateStatus;
    }

    /**
     * Set doDateStatus
     *
     * @param integer $doDateStatus
     *
     * @return UserGoal
     */
    public function setDoDateStatus($doDateStatus)
    {
        $this->doDateStatus = $doDateStatus;

        return $this;
    }

    /**
     * Get doDateStatus
     *
     * @return integer
     */
    public function getDoDateStatus()
    {
        return $this->doDateStatus;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return UserGoal
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
     * Set confirmed
     *
     * @param boolean $confirmed
     *
     * @return UserGoal
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * Get confirmed
     *
     * @return boolean
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * @return mixed
     */
    public function getNotInterested()
    {
        return $this->notInterested;
    }

    /**
     * @param mixed $notInterested
     */
    public function setNotInterested($notInterested)
    {
        $this->notInterested = $notInterested;
    }


    /**
     * @deprecated
     * @return int
     */
    public function getLat()
    {
        return 0;
    }

    /**
     * @deprecated
     * @return int
     */
    public function getLng()
    {
        return 0;
    }
    /**
     * @deprecated
     * @return int
     */
    public function getLocation()
    {
        return null;
    }

    /**
     * This function is used to get status string name
     *
     * @return null|string
     */
    public function getStringStatus()
    {
        $stringName = null;

        switch($this->status) {
            case 1:
                $stringName = "Active";
                break;
            case 2:
                $stringName = "Completed";
                break;
            default:
                echo "";
        }

        return $stringName;
    }
}
