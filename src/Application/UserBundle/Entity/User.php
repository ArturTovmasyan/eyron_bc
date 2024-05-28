<?php
/**
 * Created by PhpStorm.
 * User: Artur
 * Date: 9/8/15
 * Time: 2:16 PM
 */

namespace Application\UserBundle\Entity;

use AppBundle\Entity\UserGoal;
use AppBundle\Services\UserNotifyService;
use AppBundle\Traits\File;
use JMS\Serializer\Annotation as Serializer;
use Sonata\UserBundle\Entity\BaseUser as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Application\UserBundle\Entity\Repository\UserRepository")
 * @ORM\Table(name="fos_user")
 * @UniqueEntity(fields={"username"}, errorPath="email", message="fos_user.email.already_used" , groups={"Register", "update_email"})
 * @UniqueEntity(fields={"email"}, errorPath="primary_error", message="fos_user.email.primary_error" , groups={"Settings", "MobileSettings"})
 * @Assert\Callback(methods={"validate"}, groups={"Settings", "MobileSettings"})
 * @ORM\EntityListeners({"AppBundle\Listener\SettingsListener"})
 * @ORM\HasLifecycleCallbacks()
 */
class User extends BaseUser
{
    const MALE = 0;
    const FEMALE = 1;

    // constants for percent
    const SIGN_UP = 15;
    const CONFIRM_ACCOUNT = 15;
    const UPLOAD_IMAGE = 10;
    const ADD_GOAL = 15;
    const SET_DEADLINE = 15;
    const COMPLETE_GOAL = 15;
    const SUCCESS_STORY = 15;
    const FACEBOOK = 'Facebook';
    const GOOGLE = 'Google';
    const TWITTER = 'Twitter';
    const FOLLOWERS = 'followerByUser-';

    // use file trait
    use File;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"user", "tiny_user", "badge"})
     */
    protected $id;

    /**
     * @ORM\Column(name="u_id", type="string", length=9, unique=true)
     * @Groups({"user", "tiny_user", "badge", "inspireStory"})
     */
    protected $uId;

    /**
     * @var
     * @ORM\Column(name="registration_ids", type="text", nullable=true)
     */
    protected $registrationIds;

    /**
     * @ORM\Column(name="ios_version", type="string", length=10, nullable=true)
     */
    protected $iosVersion;

    /**
     * @ORM\Column(name="android_version", type="string", length=10, nullable=true)
     */
    protected $androidVersion;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserGoal", indexBy="goal_id", mappedBy="user", cascade={"persist"})
     **/
    protected $userGoal;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\SuccessStory", indexBy="goal_id", mappedBy="user", cascade={"persist"})
     **/
    protected $SuccessStories;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Goal", indexBy="goal_id", mappedBy="author", cascade={"persist"})
     **/
    protected $authorGoals;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Email", mappedBy="user")
     **/
    protected $sentEmails;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Goal", indexBy="goal_id", mappedBy="editor", cascade={"persist"})
     **/
    protected $editedGoals;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserPlace", indexBy="goal_id", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $userPlace;

    /**
     * @ORM\OneToMany(targetEntity="Badge", mappedBy="user", cascade={"persist", "remove"})
     * @Groups({"badge"})
     */
    protected $badges;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\SuccessStoryVoters", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $successStoryVoters;

    /**
     * @Assert\NotBlank(groups={"personal"}, message="not_blank")
     * @Groups({"user"})
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $username;

    /**
     * @ORM\Column(name="api_key", type="string", length=50, nullable=true, unique=true)
     */
    protected $apiKey;

    /**
     * @var
     *
     * @SerializedName("first_name")
     */
    protected $firstname;

    /**
     * @Assert\NotBlank()
     * @Assert\NotBlank(groups={"Settings", "Register", "MobileSettings"})
     * @Assert\Regex("/^[\p{L}\' -]+$/u", groups={"Default", "Settings", "Register", "MobileSettings"})
     */
    protected $firstName;

    /**
     * @Assert\Length(groups={"Settings", "Register", "MobileSettings", "MobileChangePassword"},
     *      min = 6,
     *      minMessage = "fos_user.password.validation",
     * )
     *
     * @Assert\Regex(groups={"Settings", "Register", "MobileSettings", "MobileChangePassword"},
     *     pattern="/^[a-zA-Z\d\.]+$/i",
     *     match=true,
     *     message = "fos_user.password.validation",
     * )
     *
     * @Assert\NotBlank()
     * @Assert\NotBlank(groups={"MobileChangePassword"})
     */
    protected $plainPassword;

    /**
     * @var
     * @Assert\NotBlank(groups={"MobileChangePassword"})
     * @SecurityAssert\UserPassword(
     *     message = "Invalid current password", groups={"MobileChangePassword"}
     * )
     */
    public $currentPassword;

    /**
     * @var
     * @Assert\Date
     * @Groups({"settings", "user"})
     * @SerializedName("birth_date")
     * @Type("DateTime<'Y-m-d'>")
     */
    protected $dateOfBirth;

    /**
     * @var
     * @SerializedName("last_name")
     */
    protected $lastname;

    /**
     * @Assert\NotBlank()
     * @Assert\NotBlank(groups={"Settings", "Register", "MobileSettings"})
     * @Assert\Regex("/^[\p{L}\' -]+$/u", groups={"Default", "Settings", "Register", "MobileSettings"})
     */
    protected $lastName;

    /**
     * @var
     * @ORM\Column(name="about_me", type="string", nullable=true)
     */
    protected $aboutMe;

    /**
     * @var
     * @Groups({"user"})
     */
    protected $enabled;

    /**
     * @var
     * @Groups({"user"})
     */
    protected $twitterUid;

    /**
     * @var
     * @Groups({"user"})
     */
    protected $facebookUid;

    /**
     * @var
     * @Groups({"user"})
     */
    protected $gplusUid;

    /**
     * @var
     * @ORM\Column(name="delete_reason", type="string", nullable=true)
     */
    protected $deleteReason;

    /**
     * @var
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"image_info"})
     */
    protected $socialPhotoLink;

    /**
     * @var
     * @ORM\Column(name="registration_token", type="string", nullable=true, unique=true)
     * @Groups({"user"})
     */
    protected $registrationToken;

    /**
     * @var
     * @ORM\Column(name="user_emails", type="array", nullable=true)
     * @Groups({"user"})
     */
    protected $userEmails;

    /**
     * @Groups({"user"})
     */
    protected $draftCount;

    /**
     * @Groups({"user"})
     */
    protected $goalFriendsCount;

    /**
     * @var
     * @Assert\Email(groups={"Settings", "MobileSettings"})
     */
    public $addEmail;

    /**
     * @var
     * @Assert\Email(groups={"Settings", "MobileSettings"})
     */
    public $primary;

    /**
     * @var
     * @Groups({"tiny_user"})
     */
    private $stats;

    /**
     * @ORM\Column(name="activity", type="boolean", nullable=false)
     * @Groups({"user"})
     */
    protected $activity = false;

    /**
     * @ORM\Column(name="profile_completed_percent", type="integer", nullable=false)
     */
    protected $profileCompletedPercent = 0;

    /**
     * @ORM\Column(name="active_time", type="integer", nullable=true)
     */
    protected $activeTime;

    /**
     * @ORM\Column(name="active_day_of_week", type="integer", nullable=true)
     */
    protected $activeDayOfWeek;

    /**
     * @ORM\Column(name="last_push_note_date", type="datetime", nullable=true)
     */
    protected $lastPushNoteDate;

    /**
     * @ORM\Column(name="is_admin", type="boolean", nullable=false)
     * @Groups({"user", "tiny_user"})
     */
    protected $isAdmin = false;

    /**
     * @ORM\OneToMany(targetEntity="Application\UserBundle\Entity\MatchUser", mappedBy="user", indexBy="match_user_id")
     */
    protected $matchedUsers;

    /**
     * @ORM\Column(name="active_factor", type="float", scale=4, nullable=true)
     */
    protected $activeFactor = 0;

    /**
     * @ORM\Column(name="factor_command_date", type="datetime", nullable=true)
     */
    protected $factorCommandDate;

    /**
     * @Groups({"user", "tiny_user", "settings", "badge", "inspireStory"})
     */
    private $cachedImage;

    /**
     * This fields are used for optimization or profile completion
     */
    private $hasDeadLines     = null;

    private $hasCompletedGoal = null;

    private $hasSuccessStory  = null;

    private $userGoalCount    = null;

    /**
     * @Groups({"user", "tiny_user"})
     */
    private $commonGoalsCount = null;

    /**
     * @deprecated
     * @ORM\Column(name="is_comment_notify", type="boolean", nullable=true)
     * @var
     * @Groups({"settings"})
     */
    private $isCommentNotify = true;

    /**
     * @deprecated
     * @ORM\Column(name="is_success_story_notify", type="boolean", nullable=true)
     * @var
     * @Groups({"settings"})
     */
    private $isSuccessStoryNotify = true;

    /**
     * @deprecated
     * @ORM\Column(name="is_comment_push_note", type="boolean", nullable=true)
     * @var
     * @Groups({"settings"})
     */
    private $isCommentPushNote = true;

    /**
     * @deprecated
     * @ORM\Column(name="is_success_story_push_note", type="boolean", nullable=true)
     * @var
     * @Groups({"settings"})
     */
    private $isSuccessStoryPushNote = true;

    /**
     * @ORM\Column(name="is_progress_push_note", type="boolean", nullable=true)
     * @var
     * @Groups({"settings"})
     */
    private $isProgressPushNote = true;

    /**
     * @SerializedName("image_path")
     * @Groups({"user", "tiny_user", "settings", "badge"})
     */
    private $mobileImagePath;

    /**
     * @ORM\Column(name="user_goal_remove_date", type="datetime", nullable=true)
     */
    private $userGoalRemoveDate;

    /**
     * @Groups({"tiny_user", "user", "badge"})
     * @ORM\Column(name="innovator", type="boolean", nullable=false)
     */
    protected $innovator = false;
    
    /**
     * @Groups({"tiny_user", "user", "badge"})
     * @ORM\Column(name="mentor", type="boolean", nullable=false)
     */
    protected $mentor = false;

    /**
     * @Groups({"tiny_user", "user", "badge"})
     * @ORM\Column(name="traveler", type="boolean", nullable=false)
     */
    protected $traveler = false;

    /**
     * @ORM\ManyToMany(targetEntity="Application\UserBundle\Entity\User", indexBy="id")
     * @ORM\JoinTable(name="following",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="following_id", referencedColumnName="id")}
     *      )
     */
    protected $followings;

    /**
     * @Groups({"tiny_user", "user", "badge"})
     */
    protected $isFollow;
    

    /**
     * @ORM\OneToOne(targetEntity="Application\UserBundle\Entity\UserNotify", inversedBy="user", cascade={"persist"})
     */
    private $userNotifySettings;


    public function __sleep()
    {
        return ['firstName', 'lastName', 'uId'];
    }

    /**
     * @return mixed
     */
    public function getDraftCount()
    {
        return $this->draftCount;
    }

    /**
     * @param mixed $draftCount
     */
    public function setDraftCount($draftCount)
    {
        $this->draftCount = $draftCount;
    }

    /**
     * @return mixed
     */
    public function getGoalFriendsCount()
    {
        return $this->goalFriendsCount;
    }

    /**
     * @param mixed $goalFriendsCount
     */
    public function setGoalFriendsCount($goalFriendsCount)
    {
        $this->goalFriendsCount = $goalFriendsCount;
    }

    /**
     * @return string
     */
    public function getImagePath()
    {
        return $this->isEnabled() ? $this->getDownloadLink(): $this->getDefaultDownloadLink();
    }

    /**
     * @param $path
     * @return $this
     */
    public function setMobileImagePath($path)
    {
        $this->mobileImagePath = $path;

        return $this;
    }

    /**
     * @VirtualProperty
     * @Groups({"user"})
     */
    public function getIsConfirmed()
    {
        return $this->registrationToken ? false : true;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->enabled = true;

        $this->goals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->matchedUsers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sentEmails = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return mixed
     */
    public function getProfileCompletedPercent()
    {
        return $this->profileCompletedPercent;
    }

    /**
     * @param mixed $profileCompletedPercent
     */
    public function setProfileCompletedPercent($profileCompletedPercent)
    {
        $this->profileCompletedPercent = $profileCompletedPercent;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstname = $firstName;
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     * @VirtualProperty()
     * @SerializedName("first_name")
     * @Groups({"user", "tiny_user", "settings", "badge", "inspireStory"})
     */
    public function getFirstNameProperty()
    {
        return $this->getFirstName();
    }

    /**
     * @return string
     * @VirtualProperty()
     * @SerializedName("last_name")
     * @Groups({"user", "tiny_user", "settings", "badge", "inspireStory"})
     */
    public function getLastNameProperty()
    {
        return $this->getLastName();
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        $this->firstName = $this->firstname;
        return $this->isEnabled() ? $this->firstname : 'Bucketlist127';
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastname = $lastName;
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        $this->lastName = $this->lastname;
        return $this->isEnabled() ? $this->lastname : 'User';
    }

    /**
     * Set aboutMe
     *
     * @param string $aboutMe
     * @return User
     */
    public function setAboutMe($aboutMe)
    {
        $this->aboutMe = $aboutMe;

        return $this;
    }

    /**
     * Get aboutMe
     *
     * @return string
     */
    public function getAboutMe()
    {
        return $this->aboutMe;
    }

    /**
     * Set deleteReason
     *
     * @param string $deleteReason
     * @return User
     */
    public function setDeleteReason($deleteReason)
    {
        $this->deleteReason = $deleteReason;

        return $this;
    }

    /**
     * Get deleteReason
     *
     * @return string
     */
    public function getDeleteReason()
    {
        return $this->deleteReason;
    }

    public function getAge()
    {
        if($birthDate = $this->getDateOfBirth()){
            $now = new \DateTime();
            return $birthDate->diff($now)->y;
        }

        return null;
    }

    /**
     * Override getPath function in file trait
     *
     * @return string
     */
    protected function getPath()
    {
        return 'photo';
    }

    /**
     * Override getPath function in file trait
     *
     * @return string
     */
    protected function getMobilePath()
    {
        return $this->getPath() . '/mobile';
    }

    /**
     * Override getPath function in file trait
     *
     * @return string
     */
    protected function getTabletPath()
    {
        return $this->getPath() . '/tablet';
    }

    /**
     * Add userGoal
     *
     * @param \AppBundle\Entity\UserGoal $userGoal
     * @return User
     */
    public function addUserGoal(\AppBundle\Entity\UserGoal $userGoal)
    {
        $this->userGoal[] = $userGoal;
        $userGoal->setUser($this);

        return $this;
    }


    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        $this->username = $email;

        return $this;
    }

    /**
     * Remove userGoal
     *
     * @param \AppBundle\Entity\UserGoal $userGoal
     */
    public function removeUserGoal(\AppBundle\Entity\UserGoal $userGoal)
    {
        $this->userGoal->removeElement($userGoal);
    }

    /**
     * Get userGoal
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserGoal()
    {
        return $this->userGoal;
    }

    /**
     * Set facebookId
     *
     * @param string $facebookId
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookUid = $facebookId;

        return $this;
    }

    /**
     * Get facebookId
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookUid;
    }

    /**
     * Set googleId
     *
     * @param string $googleId
     * @return User
     */
    public function setGoogleId($googleId)
    {
        $this->gplusUid = $googleId;

        return $this;
    }

    /**
     * Get googleId
     *
     * @return string
     */
    public function getGoogleId()
    {
        return $this->gplusUid;
    }

    /**
     * Set socialPhotoLink
     *
     * @param string $socialPhotoLink
     * @return User
     */
    public function setSocialPhotoLink($socialPhotoLink)
    {
        $this->socialPhotoLink = $socialPhotoLink;

        return $this;
    }

    /**
     * Get socialPhotoLink
     *
     * @return string
     */
    public function getSocialPhotoLink()
    {
        return $this->socialPhotoLink;
    }

    /**
     * Set twitterId
     *
     * @param string $twitterId
     * @return User
     */
    public function setTwitterId($twitterId)
    {
        $this->twitterUid = $twitterId;

        return $this;
    }

    /**
     * Get twitterId
     *
     * @return string
     */
    public function getTwitterId()
    {
        return $this->twitterUid;
    }

    /**
     * Set birthDate
     *
     * @param \DateTime $dateOfBirth
     * @return User
     */
    public function setBirthDate($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * Get birthDate
     *
     * @return \DateTime
     */
    public function getBirthDate()
    {
        return $this->dateOfBirth;
    }


    /**
     * @return string
     * @Groups({"inspireStory"})
     * @VirtualProperty()
     */
    public function getPhotoLink()
    {
        // if uses have photo
        if($this->getFileName()){
            return $this->getDownloadLink();
        }
        // if social user, return social img
        elseif(($this->getFacebookId() || $this->getGoogleId() || $this->getTwitterId()) && $this->getSocialPhotoLink() ){
            return $this->getSocialPhotoLink();
        }

        return null;
    }

    /**
     * @return string
     * @Groups({"user", "tiny_user", "inspireStory"})
     * @VirtualProperty()
     */
    public function showName()
    {
        if (!$this->getFirstName() && !$this->getLastName()){
            return null;
        }

        return $this->getFirstName() .  ' ' . $this->getLastName();
    }


    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->getIsAdmin();
    }

    /**
     * Set registrationToken
     *
     * @param string $registrationToken
     * @return User
     */
    public function setRegistrationToken($registrationToken)
    {
        $this->registrationToken = $registrationToken;

        return $this;
    }

    /**
     * Get registrationToken
     *
     * @return string
     */
    public function getRegistrationToken()
    {
        return $this->registrationToken;
    }

    /**
     * Add authorGoals
     *
     * @param \AppBundle\Entity\Goal $authorGoals
     * @return User
     */
    public function addAuthorGoal(\AppBundle\Entity\Goal $authorGoals)
    {
        $this->authorGoals[] = $authorGoals;
        $authorGoals->setAuthor($this);

        return $this;
    }

    /**
     * Remove authorGoals
     *
     * @param \AppBundle\Entity\Goal $authorGoals
     */
    public function removeAuthorGoal(\AppBundle\Entity\Goal $authorGoals)
    {
        $this->authorGoals->removeElement($authorGoals);
    }

    /**
     * Get authorGoals
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthorGoals()
    {
        return $this->authorGoals;
    }

    /**
     * Add editedGoals
     *
     * @param \AppBundle\Entity\Goal $editedGoals
     * @return User
     */
    public function addEditedGoal(\AppBundle\Entity\Goal $editedGoals)
    {
        $this->editedGoals[] = $editedGoals;
        $editedGoals->setEditor($this);

        return $this;
    }

    /**
     * Remove editedGoals
     *
     * @param \AppBundle\Entity\Goal $editedGoals
     */
    public function removeEditedGoal(\AppBundle\Entity\Goal $editedGoals)
    {
        $this->editedGoals->removeElement($editedGoals);
    }

    /**
     * Get editedGoals
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEditedGoals()
    {
        return $this->editedGoals;
    }

    protected $currentCompletedPercent = null;
    /**
     * This function is used to check percent of completed profile
     *
     * @VirtualProperty()
     * @SerializedName("completed_percent")
     * @Groups({"completed_profile"})
     * @return int
     */
    public function getCompletedPercent()
    {
        if ($this->getProfileCompletedPercent() == 100){
            return 100;
        }

        if (!is_null($this->currentCompletedPercent)){
            return $this->currentCompletedPercent;
        }

        // default percent
        $percent = 0;

        // set default sign up
        $percent += self::SIGN_UP;

        // check confirmation
        $percent += is_null($this->registrationToken) ? self::CONFIRM_ACCOUNT : 0;

        // check image
        $percent += $this->socialPhotoLink || $this->fileName ? self::UPLOAD_IMAGE : 0;

        // check goal
        $percent += $this->getUserGoalCount() > 0 ? self::ADD_GOAL : 0;

        // check deadlines
        $percent += $this->checkDeadLines() ? self::SET_DEADLINE : 0;

        // check completed goals
        $percent += $this->checkCompletedGoals() ? self::COMPLETE_GOAL : 0;

        // check success story
        $percent +=  $this->checkSuccessStory() ? self::SUCCESS_STORY : 0;

        return $this->currentCompletedPercent = $percent;
    }

    /**
     * This function is used to check hav user add deadline
     *
     * @VirtualProperty()
     * @SerializedName("check_deadline")
     * @Groups({"completed_profile"})
     * @return bool
     */
    public function checkDeadLines()
    {
        if (!is_null($this->hasDeadLines)){
            return true;
        }

        // get user goal
        $userGoals = $this->userGoal;

        // check user goal
        if($userGoals){

            // loop for user goals
            foreach($userGoals as $userGoal){

                // check deadlines
                if($userGoal->getDoDate()){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * This function is used to check have user complete goal
     *
     * @VirtualProperty()
     * @SerializedName("check_completed_goals")
     * @Groups({"completed_profile"})
     * @return bool
     */
    public function checkCompletedGoals()
    {
        if (!is_null($this->hasCompletedGoal)){
            return true;
        }

        // get user goal
        $userGoals = $this->userGoal;

        // check user goal
        if($userGoals){

            // loop for user goals
            foreach($userGoals as $userGoal){

                // check status
                if($userGoal->getStatus() == UserGoal::COMPLETED){
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * This function is used to check have user add success story
     *
     * @VirtualProperty()
     * @SerializedName("check_success_story")
     * @Groups({"completed_profile"})
     *
     * @return bool
     */
    public function checkSuccessStory()
    {
        if (!is_null($this->hasSuccessStory)){
            return true;
        }

        // get user goal
        $userGoals = $this->userGoal;

        // check user goal
        if($userGoals){

            // loop for user goals
            foreach($userGoals as $userGoal){

                // get goal
                $goal = $userGoal->getGoal();

                // check success stories
                if($goal->getSuccessStories()->count() > 0){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @VirtualProperty()
     * @SerializedName("stats")
     * @Groups({"user"})
     */
    public function getRawStats()
    {
        return $this->getStats();
    }

    /**
     * @return array
     */
    public function getStats()
    {
        if ($this->stats){
            return $this->stats;
        }


        $active = 0;
        $doneBy = 0;

        $userGoals = $this->getUserGoal();
        if($userGoals){
            foreach($userGoals as $userGoal){
                switch($userGoal->getStatus()){
                    case UserGoal::ACTIVE:
                        $active++;
                        break;
                    case UserGoal::COMPLETED:
                        $doneBy++;
                        break;
                }
            }
        }

        return [
            "listedBy"  => $active + $doneBy,
            "active"    => $active,
            "doneBy"    => $doneBy
        ];
    }

    /**
     * @param $stats
     * @return $this
     */
    public function setStats($stats)
    {
        $this->stats = $stats;

        return $this;
    }

    /**
     * Set userEmails
     *
     * @param array $userEmails
     * @return User
     */
    public function setUserEmails($userEmails)
    {
        $this->userEmails = $userEmails;

        return $this;
    }

    /**
     * Get userEmails
     *
     * @return array
     */
    public function getUserEmails()
    {
        return $this->userEmails;
    }

    /**
     * This function is used to set primary email and change username
     *
     * @ORM\PreUpdate()
     */
    public function calculationSettingsProcess()
    {
        $userEmailsInDb = array();
        $primaryEmail = $this->primary;
        $currentEmail = $this->getEmail();
        $userEmails = $this->getUserEmails();

        if ($primaryEmail && $primaryEmail !== $currentEmail) {
            $this->setEmail($primaryEmail);
        }

        if ($userEmails) {
            $userEmailsInDb = array_map(function ($item){return $item['userEmails'];}, $userEmails);
        }

        if ($primaryEmail && $userEmailsInDb && ($key = array_search($primaryEmail, $userEmailsInDb)) !== false){
            unset($userEmails[$key]);
        }

        if ($userEmailsInDb && $primaryEmail && $primaryEmail !== $currentEmail
            && (array_search($currentEmail, $userEmailsInDb) == false)
            && $currentEmail != $this->getSocialFakeEmail())
        {
            $currentEmailData          = ['userEmails' => $currentEmail, 'token' => null, 'primary' => false];
            $userEmails[$currentEmail] = $currentEmailData;
        }

        $this->setUserEmails($userEmails);
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        // generate password groups
        $validGroups = array("MobileSettings", "Settings");

        // get groups
        $groups = $context->getGroup();

        if(in_array($groups, $validGroups)){

            //get add email
            $addEmail = $this->addEmail;

            //get current email
            $currentEmail = $this->getEmail();

            //get primary email
            $primaryEmail = $this->primary;

            //get user emails
            $userEmails = $this->getUserEmails();

            //check if primary email equal currentEmail
            if ($primaryEmail && (!$userEmails || !array_key_exists($primaryEmail, $userEmails) ||
                    ($primaryEmail == $currentEmail))) {

                $context->buildViolation('Set invalid primary email')
                    ->atPath('primary')
                    ->addViolation();
            }

            // check if new email equal currentEmail
            if ($addEmail == $currentEmail || ($userEmails && array_key_exists($addEmail, $userEmails))) {

                $context->buildViolation('This email already exists')
                    ->atPath('addEmail')
                    ->addViolation();
            }
        }
    }

    /**
     * @return null|string
     * @VirtualProperty()
     * @SerializedName("social_email")
     * @Groups({"user"})
     */
    public function getSocialFakeEmail()
    {
        if ($this->getFacebookId()){
            return $this->getFacebookId() . '@facebook.com';
        }
        elseif($this->getGoogleId()){
            return $this->getGoogleId() . '@google.com';
        }
        elseif($this->getTwitterId()){
            return $this->getTwitterId() . '@twitter.com';
        }

        return null;
    }

    /**
     * @return mixed
     * @VirtualProperty()
     * @SerializedName("language")
     * @Groups({"user"})
     */
    public function getLanguage()
    {
        return $this->locale;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language)
    {
        $this->locale = $language;
    }


    /**
     * This function is used to get last email in userEmails array
     *
     * @VirtualProperty()
     * @SerializedName("lastUserEmail")
     * @Groups({"settings"})
     */
    public function getLastInUserEmails()
    {
        //get userEmails
        $userEmails = $this->getUserEmails();

        if (is_array($userEmails)) {
            //get last userEmails in array
            $userEmail = end($userEmails);
        }

        return isset($userEmail) ? $userEmail : null;
    }

    /**
     * Set uId
     *
     * @param string $uId
     * @return User
     */
    public function setUId($uId)
    {
        $this->uId = $uId;

        return $this;
    }

    /**
     * Get uId
     *
     * @return string
     */
    public function getUId()
    {
        return $this->uId;
    }


    /**
     * Set activeTime
     *
     * @param integer $activeTime
     * @return User
     */
    public function setActiveTime($activeTime)
    {
        $this->activeTime = $activeTime;

        return $this;
    }

    /**
     * Get activeTime
     *
     * @return integer
     */
    public function getActiveTime()
    {
        return $this->activeTime;
    }

    /**
     * Add SuccessStories
     *
     * @param \AppBundle\Entity\SuccessStory $successStories
     * @return User
     */
    public function addSuccessStory(\AppBundle\Entity\SuccessStory $successStories)
    {
        $this->SuccessStories[] = $successStories;

        return $this;
    }

    /**
     * Remove SuccessStories
     *
     * @param \AppBundle\Entity\SuccessStory $successStories
     */
    public function removeSuccessStory(\AppBundle\Entity\SuccessStory $successStories)
    {
        $this->SuccessStories->removeElement($successStories);
    }

    /**
     * Get SuccessStories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSuccessStories()
    {
        return $this->SuccessStories;
    }

    /**
     * Get mostActiveTime
     *
     * @return integer
     */
    public function getMostActiveTimeAndDay()
    {
        $userGoals = $this->getUserGoal();
        $timesAdd = [];
        $daysAdd  = [];
        if ($userGoals){
            foreach($userGoals as $userGoal){

                if($userGoal->getCompletionDate()){
                    $time = $userGoal->getCompletionDate()->format('H');
                    $timesAdd[$time] = isset($timesAdd[$time]) ? $timesAdd[$time] + 1 : 1;

                    $dayOfWeek = $userGoal->getCompletionDate()->format('w');
                    $daysAdd[$dayOfWeek] = isset($daysAdd[$dayOfWeek]) ? $daysAdd[$dayOfWeek] + 1 : 1;
                };

                if($userGoal->getListedDate()){
                    $time = $userGoal->getListedDate()->format('H');
                    $timesAdd[$time] = isset($timesAdd[$time]) ? $timesAdd[$time] + 1 : 1;

                    $dayOfWeek = $userGoal->getListedDate()->format('w');
                    $daysAdd[$dayOfWeek] = isset($daysAdd[$dayOfWeek]) ? $daysAdd[$dayOfWeek] + 1 : 1;
                }
            }
        }
        $successStories = $this->getSuccessStories();
        if($successStories){
            foreach($successStories as $story){
                if($story->getUpdatedAt()){
                    $time = $story->getUpdatedAt()->format('H');
                    $timesAdd[$time] = isset($timesAdd[$time]) ? $timesAdd[$time] + 1 : 1;

                    $dayOfWeek = $story->getUpdatedAt()->format('w');
                    $daysAdd[$dayOfWeek] = isset($daysAdd[$dayOfWeek]) ? $daysAdd[$dayOfWeek] + 1 : 1;
                };
            }
        }

        $activeTime    = (count($timesAdd) > 0) ? array_keys($timesAdd, max($timesAdd))[0] : 0;
        $activeWeekDay = (count($daysAdd)  > 0) ? array_keys($daysAdd,  max($daysAdd))[0]  : 0;

        return [
            'activeTime'    => $activeTime,
            'activeWeekDay' => $activeWeekDay
        ];

    }

    /**
     * Update ActiveTime
     *
     * @return $this
     */
    public function updateActiveTime()
    {
        $active = $this->getMostActiveTimeAndDay();
        $this->setActiveTime($active['activeTime']);
        $this->setActiveDayOfWeek($active['activeWeekDay']);
        return $this;
    }
    
    /**
     * @return array
     */
    public function getComingGoals()
    {
        $comingGoals = [];
        $userGoals = $this->getUserGoal();
        if ($userGoals)
        {
            foreach($userGoals as $userGoal){
                if($userGoal->getStatus() != UserGoal::COMPLETED){
                    //if goal have listed and do dates
                    if($userGoal->getListedDate() && $userGoal->getDoDate()){
                        $time1 = $userGoal->getDoDate();
                        $time2 = new \DateTime('now');
                        $limit = date_diff($time1,$time2)->days;

                        if($limit == 1 || $limit == 0){
                            $comingGoals[] = $userGoal;
                        };
                    }
                }
            }
        }
        return $comingGoals;
    }

    /**
     * @param mixed $data
     */
    public function setRegistrationIds($data)
    {
        $this->registrationIds = json_encode($data) ;
    }

    /**
     * @return mixed
     */
    public function getRegistrationIds()
    {
        $idsArray = json_decode($this->registrationIds, true);
        return is_array($idsArray)?$idsArray:[];
    }

    /**
     * Set activity
     *
     * @param boolean $activity
     * @return User
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get activity
     *
     * @return boolean
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @return mixed
     *
     * @VirtualProperty
     * @Groups({"user", "tiny_user", "settings", "inspireStory"})
     */
    public function getCachedImage()
    {
        return $this->cachedImage;
    }

    /**
     * @param mixed $cachedImage
     */
    public function setCachedImage($cachedImage)
    {
        $this->cachedImage = $cachedImage;
    }

    /**
     * @return mixed
     */
    public function getHasDeadLines()
    {
        return $this->hasDeadLines;
    }

    /**
     * @param mixed $hasDeadLines
     */
    public function setHasDeadLines($hasDeadLines)
    {
        $this->hasDeadLines = $hasDeadLines;
    }

    /**
     * @return mixed
     */
    public function getHasCompletedGoal()
    {
        return $this->hasCompletedGoal;
    }

    /**
     * @param mixed $hasCompletedGoal
     */
    public function setHasCompletedGoal($hasCompletedGoal)
    {
        $this->hasCompletedGoal = $hasCompletedGoal;
    }

    /**
     * @return mixed
     */
    public function getHasSuccessStory()
    {
        return $this->hasSuccessStory;
    }

    /**
     * @param mixed $hasSuccessStory
     */
    public function setHasSuccessStory($hasSuccessStory)
    {
        $this->hasSuccessStory = $hasSuccessStory;
    }

    /**
     * @VirtualProperty()
     * @SerializedName("user_goal_count")
     * @Groups({"completed_profile"})
     * @return null
     */
    public function getUserGoalCount()
    {
        if (is_null($this->userGoalCount)){
            $this->userGoalCount = $this->userGoal ? $this->userGoal->count() : 0;
        }

        return $this->userGoalCount;
    }

    /**
     * @param null $userGoalCount
     */
    public function setUserGoalCount($userGoalCount)
    {
        $this->userGoalCount = $userGoalCount;
    }


    /**
     * Set isCommentNotify
     *
     * @param boolean $isCommentNotify
     * @return User
     */
    public function setIsCommentNotify($isCommentNotify)
    {
        $this->isCommentNotify = $isCommentNotify;

        return $this;
    }

    /**
     * Get isCommentNotify
     *
     * @return boolean
     */
    public function getIsCommentNotify()
    {
        return $this->isCommentNotify;
    }

    /**
     * Set isSuccessStoryNotify
     *
     * @param boolean $isSuccessStoryNotify
     * @return User
     */
    public function setIsSuccessStoryNotify($isSuccessStoryNotify)
    {
        $this->isSuccessStoryNotify = $isSuccessStoryNotify;

        return $this;
    }

    /**
     * Get isSuccessStoryNotify
     *
     * @return boolean
     */
    public function getIsSuccessStoryNotify()
    {
        return $this->isSuccessStoryNotify;
    }

    /**
     * Set isCommentPushNote
     *
     * @param boolean $isCommentPushNote
     * @return User
     */
    public function setIsCommentPushNote($isCommentPushNote)
    {
        $this->isCommentPushNote = $isCommentPushNote;

        return $this;
    }

    /**
     * Get isCommentPushNote
     *
     * @return boolean
     */
    public function getIsCommentPushNote()
    {
        return $this->isCommentPushNote;
    }

    /**
     * Set isSuccessStoryPushNote
     *
     * @param boolean $isSuccessStoryPushNote
     * @return User
     */
    public function setIsSuccessStoryPushNote($isSuccessStoryPushNote)
    {
        $this->isSuccessStoryPushNote = $isSuccessStoryPushNote;

        return $this;
    }

    /**
     * Get isSuccessStoryPushNote
     *
     * @return boolean
     */
    public function getIsSuccessStoryPushNote()
    {
        return $this->isSuccessStoryPushNote;
    }

    /**
     * Set isProgressPushNote
     *
     * @param boolean $isProgressPushNote
     * @return User
     */
    public function setIsProgressPushNote($isProgressPushNote)
    {
        $this->isProgressPushNote = $isProgressPushNote;

        return $this;
    }

    /**
     * Get isProgressPushNote
     *
     * @return boolean
     */
    public function getIsProgressPushNote()
    {
        return $this->isProgressPushNote;
    }

    /**
     * This function is used to get login social name
     *
     */
    public function getSocialsName()
    {
        //check if login by facebook
        if($this->getFacebookId()) {
            return self::FACEBOOK;
        }
        //check if login by google
        if($this->getGoogleId()) {
            return self::GOOGLE;
        }

        //check if login by twitter
        if($this->getTwitterId()) {
            return self::TWITTER;
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getActiveDayOfWeek()
    {
        return $this->activeDayOfWeek;
    }

    /**
     * @param mixed $activeDayOfWeek
     */
    public function setActiveDayOfWeek($activeDayOfWeek)
    {
        $this->activeDayOfWeek = $activeDayOfWeek;
    }

    /**
     * @return mixed
     */
    public function getLastPushNoteDate()
    {
        return $this->lastPushNoteDate;
    }

    /**
     * @param mixed $lastPushNoteDate
     */
    public function setLastPushNoteData($lastPushNoteDate)
    {
        $this->lastPushNoteDate = $lastPushNoteDate;
    }


    /**
     * @return mixed
     */
    public function getIosVersion()
    {
        return $this->iosVersion;
    }

    /**
     * @param mixed $iosVersion
     */
    public function setIosVersion($iosVersion)
    {
        $this->iosVersion = $iosVersion;
    }

    /**
     * @return mixed
     */
    public function getAndroidVersion()
    {
        return $this->androidVersion;
    }

    /**
     * @param mixed $androidVersion
     */
    public function setAndroidVersion($androidVersion)
    {
        $this->androidVersion = $androidVersion;
    }

    /**
     * @return mixed
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * @param mixed $isAdmin
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;
    }

    /**
     * @return mixed
     */
    public function getCommonGoalsCount()
    {
        return $this->commonGoalsCount;
    }

    /**
     * @param mixed $commonGoalsCount
     */
    public function setCommonGoalsCount($commonGoalsCount)
    {
        $this->commonGoalsCount = $commonGoalsCount;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param mixed $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }


    /**
     * @return mixed
     */
    public function getActiveFactor()
    {
        return $this->activeFactor;
    }

    /**
     * @param mixed $activeFactor
     */
    public function setActiveFactor($activeFactor)
    {
        $this->activeFactor = $activeFactor;
    }

    /**
     * @return mixed
     */
    public function getFactorCommandDate()
    {
        return $this->factorCommandDate;
    }

    /**
     * @param mixed $factorCommandDate
     */
    public function setFactorCommandDate($factorCommandDate)
    {
        $this->factorCommandDate = $factorCommandDate;
    }

    /**
     * Set lastPushNoteDate
     *
     * @param \DateTime $lastPushNoteDate
     *
     * @return User
     */
    public function setLastPushNoteDate($lastPushNoteDate)
    {
        $this->lastPushNoteDate = $lastPushNoteDate;

        return $this;
    }

    /**
     * Add matchedUser
     *
     * @param \Application\UserBundle\Entity\MatchUser $matchedUser
     *
     * @return User
     */
    public function addMatchedUser(\Application\UserBundle\Entity\MatchUser $matchedUser)
    {
        $this->matchedUsers[] = $matchedUser;

        return $this;
    }

    /**
     * Remove matchedUser
     *
     * @param \Application\UserBundle\Entity\MatchUser $matchedUser
     */
    public function removeMatchedUser(\Application\UserBundle\Entity\MatchUser $matchedUser)
    {
        $this->matchedUsers->removeElement($matchedUser);
    }

    /**
     * Get matchedUsers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMatchedUsers()
    {
        return $this->matchedUsers;
    }

    /**
     * Add userPlace
     *
     * @param \AppBundle\Entity\UserPlace $userPlace
     *
     * @return User
     */
    public function addUserPlace(\AppBundle\Entity\UserPlace $userPlace)
    {
        $this->userPlace[] = $userPlace;

        return $this;
    }

    /**
     * Remove userPlace
     *
     * @param \AppBundle\Entity\UserPlace $userPlace
     */
    public function removeUserPlace(\AppBundle\Entity\UserPlace $userPlace)
    {
        $this->userPlace->removeElement($userPlace);
    }

    /**
     * Get userPlace
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserPlace()
    {
        return $this->userPlace;
    }

    /**
     * Set userGoalRemoveDate
     *
     * @param \DateTime $userGoalRemoveDate
     *
     * @return User
     */
    public function setUserGoalRemoveDate($userGoalRemoveDate)
    {
        $this->userGoalRemoveDate = $userGoalRemoveDate;
    }

    /**
     * Get userGoalRemoveDate
     *
     * @return \DateTime
     */
    public function getUserGoalRemoveDate()
    {
        return $this->userGoalRemoveDate;
    }

    /**
     * @param $type
     * @return bool
     */
    public function mustEmailNotify($type)
    {
        // get user notification
        $userNotify = $this->getUserNotifySettings();

        // by default send notification
        if($type == UserNotifyService::USER_ACTIVITY){
            return true;
        }

        return !$userNotify ? true : $userNotify->mustEmailNotify($type);
    }

    /**
     * @param $type
     * @return bool
     */
    public function mustPushedNotify($type)
    {
        // get user notification
        $userNotify = $this->getUserNotifySettings();

        // by default send notification
        if($userNotify || $type == UserNotifyService::USER_ACTIVITY){
            return true;
        }

        return !$userNotify ? true : $userNotify->mustEmailNotify($type);
    }

    /**
     * Set userNotifySettings
     *
     * @param \Application\UserBundle\Entity\UserNotify $userNotifySettings
     *
     * @return User
     */
    public function setUserNotifySettings(\Application\UserBundle\Entity\UserNotify $userNotifySettings = null)
    {
        $this->userNotifySettings = $userNotifySettings;
        $userNotifySettings->setUser($this);

        return $this;
    }

    /**
     * Get userNotifySettings
     *
     * @return \Application\UserBundle\Entity\UserNotify
     */
    public function getUserNotifySettings()
    {
        return $this->userNotifySettings;
    }

    /**
     * Add badge
     *
     * @param \Application\UserBundle\Entity\Badge $badge
     *
     * @return User
     */
    public function addBadge(\Application\UserBundle\Entity\Badge $badge)
    {
        $this->badges[] = $badge;
        $badge->setUser($this);

        return $this;
    }

    /**
     * Remove badge
     *
     * @param \Application\UserBundle\Entity\Badge $badge
     */
    public function removeBadge(\Application\UserBundle\Entity\Badge $badge)
    {
        $this->badges->removeElement($badge);
    }

    /**
     * Get badges
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBadges()
    {
        return $this->badges;
    }

    /**
     * Add sentEmail
     *
     * @param \AppBundle\Entity\Email $sentEmail
     *
     * @return User
     */
    public function addSentEmail(\AppBundle\Entity\Email $sentEmail)
    {
        $this->sentEmails[] = $sentEmail;

        return $this;
    }

    /**
     * Remove sentEmail
     *
     * @param \AppBundle\Entity\Email $sentEmail
     */
    public function removeSentEmail(\AppBundle\Entity\Email $sentEmail)
    {
        $this->sentEmails->removeElement($sentEmail);
    }

    /**
     * Get sentEmails
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSentEmails()
    {
        return $this->sentEmails;
    }

    /**
     * @return mixed
     */
    public function getInnovator()
    {
        return $this->innovator;
    }

    /**
     * @param mixed $innovator
     */
    public function setInnovator($innovator)
    {
        $this->innovator = $innovator;
    }

    /**
     * @return mixed
     */
    public function getMentor()
    {
        return $this->mentor;
    }

    /**
     * @param mixed $mentor
     */
    public function setMentor($mentor)
    {
        $this->mentor = $mentor;
    }

    /**
     * @return mixed
     */
    public function getTraveler()
    {
        return $this->traveler;
    }

    /**
     * @param mixed $traveler
     */
    public function setTraveler($traveler)
    {
        $this->traveler = $traveler;
    }
    /**
     * Add following
     *
     * @param \Application\UserBundle\Entity\User $following
     *
     * @return User
     */
    public function addFollowing(\Application\UserBundle\Entity\User $following)
    {
        $this->followings[] = $following;
        $followings = apc_fetch(sha1(__FILE__) . self::FOLLOWERS . $this->getId());

        if(is_array($followings)){
            $followings[] = $following->getId();
        } else {
            $followings = [$following->getId()];
        }

        apc_store(sha1(__FILE__) . self::FOLLOWERS . $this->getId(), $followings, 3600);

        return $this;
    }

    /**
     * Remove following
     *
     * @param \Application\UserBundle\Entity\User $following
     */
    public function removeFollowing(\Application\UserBundle\Entity\User $following)
    {
        apc_delete(sha1(__FILE__) . self::FOLLOWERS . $this->getId());
        $this->followings->removeElement($following);
    }

    /**
     * @param $user
     * @return mixed
     */
    public function isFollowing($user)
    {
        return $this->followings->contains($user)?1:0;
    }

    /**
     * @param $type
     * @param $level
     */
    public function setLevel($type, $level)
    {
        switch ($type){
            case Badge::TYPE_INNOVATOR:
                $this->setInnovator($level);
                break;
            case Badge::TYPE_MOTIVATOR:
                $this->setMentor($level);
                break;
            case Badge::TYPE_TRAVELLER:
                $this->setTraveler($level);
                break;
        }
    }

    /**
     * @return mixed
     */
    public function getIsFollow()
    {
        return $this->isFollow;
    }

    /**
     * @param mixed $isFollow
     */
    public function setIsFollow($isFollow)
    {
        $this->isFollow = $isFollow;
    }
    
    /**
     * Get followings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFollowings()
    {
        return $this->followings;
    }

    /**
     * @return mixed
     */
    public function getFollowingIds()
    {
        $followingIds = apc_fetch(sha1(__FILE__) . self::FOLLOWERS . $this->getId());
        if(!$followingIds){
            if($followings = $this->getFollowings()){
                $followingIds = array_keys($followings->toArray());
                apc_store(sha1(__FILE__) . self::FOLLOWERS . $this->getId(), $followingIds, 3600);
            }
        }

        return $followingIds;
    }

    /**
     * Serializes the user.
     *
     * The serialized data have to contain the fields used by the equals method and the username.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id,
            $this->firstname,
            $this->lastname,
            $this->fileName,
            $this->mobileImagePath,
            $this->cachedImage,
            $this->uId
        ));
    }

    /**
     * Unserializes the user.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 2, null));

        list(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id,
            $this->firstname,
            $this->lastname,
            $this->fileName,
            $this->mobileImagePath,
            $this->cachedImage,
            $this->uId
            ) = $data;
    }


    /**
     * Add successStoryVoter
     *
     * @param \AppBundle\Entity\SuccessStoryVoters $successStoryVoter
     *
     * @return User
     */
    public function addSuccessStoryVoter(\AppBundle\Entity\SuccessStoryVoters $successStoryVoter)
    {
        $this->successStoryVoters[] = $successStoryVoter;

        return $this;
    }

    /**
     * Remove successStoryVoter
     *
     * @param \AppBundle\Entity\SuccessStoryVoters $successStoryVoter
     */
    public function removeSuccessStoryVoter(\AppBundle\Entity\SuccessStoryVoters $successStoryVoter)
    {
        $this->successStoryVoters->removeElement($successStoryVoter);
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
