<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/8/15
 * Time: 3:32 PM
 */

namespace AppBundle\Entity;

use AppBundle\Model\ActivityableInterface;
use AppBundle\Model\ArchivedGoalInterface;
use AppBundle\Model\ImageableInterface;
use AppBundle\Model\MultipleFileInterface;
use AppBundle\Model\PublishAware;
use AppBundle\Traits\Location;
use Application\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use AppBundle\Validator\Constraints as AppAssert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\GoalRepository")
 * @ORM\Table(name="goal", indexes={
 *          @ORM\Index(name="search", columns={"language", "publish", "title", "updated"}),
 *          @ORM\Index(name="fulltext_index_title", columns={"title"}, flags={"fulltext"}),
 *          @ORM\Index(name="fulltext_index_description", columns={"description"}, flags={"fulltext"}),
 *          @ORM\Index(name="fulltext_index", columns={"title", "description"}, flags={"fulltext"}),
 *          @ORM\Index(name="idx_active_publish", columns={"publish", "archived"}),
 * })
 */
class Goal implements MultipleFileInterface, PublishAware, ArchivedGoalInterface, ActivityableInterface, ImageableInterface, \Serializable
{
    // constants for privacy status
    const PUBLIC_PRIVACY = true;
    const PRIVATE_PRIVACY = false;

    // constants for readinessStatus
    const DRAFT = false;
    const TO_PUBLISH = true;


    // constants for inner page
    const INNER = "inner";
    const VIEW = "view";

    // use location trait
    use Location;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"goal", "tiny_goal", "goal_draft"})
     */
    protected $id;

    /**
     * @Assert\Length(
     *      groups={"goal"},
     *      min = 3,
     *      max = 10000,
     *      minMessage = "Your description be at least {{ limit }} characters long",
     *      maxMessage = "Your description cannot be longer than {{ limit }} characters"
     * )
     * @Assert\NotBlank(message = "Goal description can't be blank", groups={"goal"})
     * @ORM\Column(name="description", type="text", length=10000, nullable=false)
     * @Groups({"goal", "goal_description"})
     */
    protected $description;

    /**
     * @Assert\Length(
     *      groups={"goal"},
     *      min = 3,
     *      max = 64,
     *      minMessage = "Your title must be at least {{ limit }} characters long",
     *      maxMessage = "Your title name cannot be longer than {{ limit }} characters"
     * )
     * @Assert\NotBlank(groups={"goal"}, message = "Goal title can't be blank")
     * @ORM\Column(name="title", type="string", length=64, nullable=false)
     * @Groups({"goal", "tiny_goal", "goal_draft", "inspireStory"})
     */
    protected $title;

    /**
     * @ORM\Column(name="video_link", type="json_array", nullable=true)
     * @AppAssert\ValidLink(groups={"goal"})
     * @Groups({"goal"})
     */
    protected $videoLink;

    /**
     * @ORM\OneToMany(targetEntity="GoalImage", mappedBy="goal", cascade={"persist", "remove"})
     * @Assert\Valid()
     * @Groups({"goal_image"})
     */
    protected $images;

    /**
     * @ORM\OneToMany(targetEntity="SuccessStory", mappedBy="goal", cascade={"persist", "remove"})
     * @Assert\Valid()
     * @Groups({"goal_successStory"})
     */
    protected $successStories;

    /**
     * @ORM\OneToMany(targetEntity="UserGoal", mappedBy="goal", cascade={"persist", "remove"})
     **/
    protected $userGoal;

    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User", inversedBy="authorGoals")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     * @Groups({"goal_author"})
     **/
    protected $author;


    /**
     * @ORM\ManyToOne(targetEntity="Application\UserBundle\Entity\User", inversedBy="editedGoals")
     * @ORM\JoinColumn(name="editor_id", referencedColumnName="id")
     **/
    protected $editor;

    /**
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="goal", cascade={"persist"})
     * @ORM\JoinTable(name="goals_tags",
     *      joinColumns={@ORM\JoinColumn(name="goal_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     *      )
     **/
    protected $tags;

    /**
     * @ORM\ManyToMany(targetEntity="Place", inversedBy="goal", cascade={"persist"})
     * @ORM\JoinTable(name="goal_place",
     *      joinColumns={@ORM\JoinColumn(name="goal_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="place_id", referencedColumnName="id")}
     *      )
     **/
    protected $place;

    /**
     * @var
     * @Groups({"goal", "tiny_goal"})
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    protected $status = self::PUBLIC_PRIVACY;

    /**
     * @var
     * @ORM\Column(name="readiness_status", type="boolean", nullable=true)
     */
    protected $readinessStatus = self::DRAFT;

    /**
     * @var
     * @ORM\Column(name="publish", type="boolean")
     * @Groups({"tiny_goal", "goal", "inspireStory"})
     */
    protected $publish = PublishAware::NOT_PUBLISH;

    /**
     * @var
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @Groups({"goal", "goal_draft"})
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
     * @ORM\Column(name="featured_date", type="date", nullable=true)
     */
    protected $featuredDate;

    /**
     * @var
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $publishedDate;

    /**
     * @Groups({"goal", "tiny_goal", "inspireStory"})
     */
    protected $stats = null;

    /**
     * @Groups({"goal", "tiny_goal"})
     */
    protected $isMyGoal;

    /**
     * @Groups({"goal", "tiny_goal"})
     */
    protected $shareLink;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(length=128, unique=true, nullable=false)
     * @Groups({"tiny_goal", "userGoal_goal", "inspireStory"})
     */
    protected $slug;

    /**
     * @Assert\NotBlank(groups={"goal"}, message = "Language can't be blank")
     * @ORM\Column(name="language", type="string", length=3, nullable=true)
     * @Groups({"goal"})
     * @var
     */
    protected $language = "en";

    /**
     * @var string $publishedBy
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\Blameable(on="change", field="publish", value="1")
     */
    private $publishedBy;

    /**
     * @Groups({"tiny_goal", "goal", "inspireStory", "goal_draft"})
     */
    private $cachedImage;

    /**
     * @Groups({"tiny_goal"})
     */
    public $distance = 0;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    private $archived = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var integer
     */
    private $mergedGoalId;

    /**
     * @SerializedName("image_path")
     * @Groups({"tiny_goal", "goal", "image_link", "inspireStory"})
     */
    private $mobileImagePath;

    /**
     * @ORM\ManyToMany(targetEntity="Application\AffiliateBundle\Entity\Affiliate")
     * @ORM\JoinTable(name="goal_affiliates",
     *      joinColumns={@ORM\JoinColumn(name="goal_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="affiliate_id", referencedColumnName="id")}
     *      )
     */
    private $affiliates;

    /**
     * @var
     */
    private $listPhotoDownloadLink;

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
     * @return string
     */
    public function __toString()
    {
        return (string) $this->id;
    }

    /**
     * @return mixed
     */
    public function getShareLink()
    {
        return $this->shareLink;
    }

    /**
     * @param mixed $shareLink
     */
    public function setShareLink($shareLink)
    {
        $this->shareLink = $shareLink;
    }

    /**
     * @return mixed
     */
    public function getIsMyGoal()
    {
        return $this->isMyGoal;
    }

    /**
     * @param mixed $isMyGoal
     */
    public function setIsMyGoal($isMyGoal)
    {
        $this->isMyGoal = $isMyGoal;
    }

    /**
     * Add images
     *
     * @param \AppBundle\Entity\GoalImage $images
     * @return Goal
     */
    public function addImage(\AppBundle\Entity\GoalImage $images)
    {
        $this->images[] = $images;
        $images->setGoal($this);

        $hasListPhoto = false;
        $hasCoverPhoto = false;

        foreach($this->images as $image){
            if ($image->getList() == true){
                $hasListPhoto = true;
            }
            if ($image->getCover() == true){
                $hasCoverPhoto = true;
            }
        }

        if (!$hasListPhoto){
            $this->images->first()->setList(true);
        }

        if (!$hasCoverPhoto){
            $this->images->first()->setCover(true);
        }

        return $this;
    }

    /**
     * Remove images
     *
     * @param \AppBundle\Entity\GoalImage $images
     */
    public function removeImage($images)
    {
        $this->images->removeElement($images);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getImages()
    {
        return $this->images;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->images     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userGoal   = new \Doctrine\Common\Collections\ArrayCollection();
        $this->affiliates = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags     = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * This function clone goal
     */
    public function __clone() {

        $this->id = null;
        $this->userGoal = null;
        $this->successStories = null;
        $this->author = null;
        $this->editor = null;

        $imagenes = $this->getImages();
        $this->images = new ArrayCollection();
        if(count($imagenes) > 0){
            foreach ($imagenes as $imagen) {
                $cloneImages = clone $imagen;
                $this->images->add($cloneImages);
                $cloneImages->setGoal($this);
            }
        }
    }


    /**
     * Add userGoal
     *
     * @param \AppBundle\Entity\UserGoal $userGoal
     * @return Goal
     */
    public function addUserGoal(\AppBundle\Entity\UserGoal $userGoal)
    {
        $this->userGoal[] = $userGoal;
        $userGoal->setGoal($this);

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
     * Set description
     *
     * @param string $description
     * @return Goal
     */
    public function setDescription($description)
    {
        $this->description = strip_tags($description);

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add tags
     *
     * @param \AppBundle\Entity\Tag $tags
     * @return Goal
     */
    public function addTag(\AppBundle\Entity\Tag $tags)
    {
        $this->tags[] = $tags;

        return $this;
    }

    /**
     * Remove tags
     *
     * @param \AppBundle\Entity\Tag $tags
     */
    public function removeTag(\AppBundle\Entity\Tag $tags)
    {
        $this->tags->removeElement($tags);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * This function is used to get hash tags from description
     *
     * @return mixed
     */
    public function getHashTags()
    {
        // get description
        $content = strtolower($this->description);

        // get hash tags
        preg_match_all('/#([^\s]+)/', $content, $hashTags);

        // return hash tags
        return $hashTags[1];
    }


    /**
     * @return bool|mixed
     */
    public function getListPhoto()
    {
        // get images
        $images = $this->getImages();

        // check images
        if($images){

            // loop for images
            foreach($images as $image){

                // check is list
                if($image->getList()){
                    return $image;
                }
            }
        }

        return null;
    }

    /**
     * @return null
     */
    public function getImagePath()
    {
        return $this->getListPhotoDownloadLink();
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
     * @Groups({"inspireStory"})
     * @return null
     */
    public function getListPhotoDownloadLink()
    {
        if ($this->listPhotoDownloadLink){
            return $this->listPhotoDownloadLink;
        }

        // get list image
        $image = $this->getListPhoto();

        // return download link
        return $image ? $image->getDownloadLink() : null;
    }


    /**
     * @VirtualProperty
     * @SerializedName("image_size")
     * @return null
     * @Groups({"tiny_goal", "goal"})
     */
    public function getListPhotoImageSize()
    {
        // get list image
        $image = $this->getListPhoto();

        // return download link
        return $image ? $image->getImageSize() : null;
    }

    /**
     * @return bool|mixed
     */
    public function getCoverPhoto()
    {
        // get images
        $images = $this->getImages();

        // check images
        if($images){

            // loop for images
            foreach($images as $image){

                // check is cover
                if($image->getCover()){
                    return $image;
                }
            }
        }

        return null;
    }

    /**
     * @return null
     */
    public function getCoverPhotoDownloadLink()
    {
        // get list image
        $image = $this->getCoverPhoto();

        // return download link
        return $image ? $image->getDownloadLink() : null;
    }

    /**
     * @return array
     */
    public function  getBlMultipleFile()
    {
        // check images and return array
        if($this->images){

            return $this->images->toArray();
        }
        return array();
    }

    /**
     * @param $multipleFile
     */
    public function  setBlMultipleFile($multipleFile)
    {
        // check added images
        if(count($multipleFile) > 0){

            $this->images = new ArrayCollection($multipleFile);
        }
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Goal
     */
    public function setTitle($title)
    {
        $this->title = strip_tags($title);

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

    /**
     * Set videoLink
     *
     * @param string $videoLink
     * @return Goal
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
     * Set status
     *
     * @param boolean $status
     * @return Goal
     */
    public function setStatus($status = self::PRIVATE_PRIVACY)
    {
        $this->status = $status;

        return $this;
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
     * Set created
     *
     * @param \DateTime $created
     * @return Goal
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
     * Set updated
     *
     * @param \DateTime $updated
     * @return Goal
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
     * Set publish
     *
     * @param boolean $publish
     * @return Goal
     */
    public function setPublish($publish)
    {
        //set published date
        if(isset($this->publish) && $publish == self::PUBLISH && $this->publish != self::PUBLISH){
            $this->setPublishedDate(new \DateTime('now'));
        }

        $this->publish = $publish;

        return $this;
    }

    /**
     * Get publish
     *
     * @return boolean
     */
    public function getPublish()
    {
        return $this->publish;
    }

    /**
     * Add successStories
     *
     * @param \AppBundle\Entity\SuccessStory $successStories
     * @return Goal
     */
    public function addSuccessStory(\AppBundle\Entity\SuccessStory $successStories)
    {
        $this->successStories[] = $successStories;
        $successStories->setGoal($this);

        return $this;
    }

    /**
     * Remove successStories
     *
     * @param \AppBundle\Entity\SuccessStory $successStories
     */
    public function removeSuccessStory(\AppBundle\Entity\SuccessStory $successStories)
    {
        $this->successStories->removeElement($successStories);
    }

    /**
     * Get successStories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSuccessStories()
    {
        return $this->successStories;
    }

    /**
     * Set successStories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function setSuccessStories($successStories)
    {
        $this->successStories = $successStories;

        return $this;
    }

    /**
     * Set author
     *
     * @param \Application\UserBundle\Entity\User $author
     * @return Goal
     */
    public function setAuthor(\Application\UserBundle\Entity\User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \Application\UserBundle\Entity\User 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set editor
     *
     * @param \Application\UserBundle\Entity\User $editor
     * @return Goal
     */
    public function setEditor(\Application\UserBundle\Entity\User $editor = null)
    {
        $this->editor = $editor;

        return $this;
    }

    /**
     * Get editor
     *
     * @return \Application\UserBundle\Entity\User 
     */
    public function getEditor()
    {
        return $this->editor;
    }

    /**
     * @return mixed
     */
    public function getReadinessStatus()
    {
        return $this->readinessStatus;
    }

    /**
     * @param mixed $readinessStatus
     */
    public function setReadinessStatus($readinessStatus)
    {
        $this->readinessStatus = $readinessStatus;
    }


    /**
     * This function is used to check is user author if this goal
     *
     * @param $user
     * @return bool
     */
    public function isAuthor($user)
    {
        // get author
        $author = $this->getAuthor();

        // check author
        if(!is_null($author) && $author == $user){
            return true;
        }
        return false;
    }


    /**
     * @return mixed
     */
    public function getStats()
    {
        if (!is_null($this->stats)){
            return $this->stats;
        }

        $this->stats = ['listedBy' => 0, 'doneBy' => 0];

        $userGoals = $this->getUserGoal();

        if($userGoals){
            foreach($userGoals as $userGoal){
                $userGoal->getStatus() == UserGoal::ACTIVE ? $this->stats['listedBy'] ++ : $this->stats['doneBy'] ++;
            }
        }

        return $this->stats;
    }

    /**
     * @param mixed $stats
     */
    public function setStats($stats)
    {
        $this->stats = $stats;
    }

    /**
     * @return array
     *
     * @VirtualProperty()
     * @SerializedName("location")
     * @Groups({"goal", "tiny_goal"})
     */
    public function getGoalLocation()
    {
        return $this->getLocation();
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Goal
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }



    /**
     * Set publishedBy
     *
     * @param string $publishedBy
     * @return Goal
     */
    public function setPublishedBy($publishedBy)
    {
        $this->publishedBy = $publishedBy;

        return $this;
    }

    /**
     * Get publishedBy
     *
     * @return string 
     */
    public function getPublishedBy()
    {
        return $this->publishedBy;
    }

    /**
     * Set publishedDate
     *
     * @param \DateTime $publishedDate
     * @return Goal
     */
    public function setPublishedDate($publishedDate)
    {
        $this->publishedDate = $publishedDate;

        return $this;
    }

    /**
     * Get publishedDate
     *
     * @return \DateTime 
     */
    public function getPublishedDate()
    {
        return $this->publishedDate;
    }

    /**
     * Set archived
     *
     * @param boolean $archived
     * @return Goal
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;

        return $this;
    }

    /**
     * Get archived
     *
     * @return boolean 
     */
    public function getArchived()
    {
        return $this->archived;
    }

    /**
     * Set mergedGoalId
     *
     * @param integer $mergedGoalId
     * @return Goal
     */
    public function setMergedGoalId($mergedGoalId)
    {
        $this->mergedGoalId = $mergedGoalId;

        return $this;
    }

    /**
     * Get mergedGoalId
     *
     * @return integer
     */
    public function getMergedGoalId()
    {
        return $this->mergedGoalId;
    }

    /**
     * This function is used to check goal has author for notify
     * 
     * @param $userId
     * @return bool
     */
    public function hasAuthorForNotify($userId)
    {
        if($this->getAuthor() && $this->getAuthor()->getId() !== $userId) {
            return true;
        }
        
        return false;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return json_encode([
            'id'        => $this->getId(),
            'slug'      => $this->getSlug(),
            'title'     => $this->getTitle(),
            'image'     => $this->getListPhotoDownloadLink(),
            'publish'   => $this->getPublish(),
            'listedBy'  => $this->getStats()['listedBy'],
            'doneBy'    => $this->getStats()['doneBy']
        ]);
    }

    /**
     * @param string $data
     * @return $this
     */
    public function unserialize($data)
    {
        $data = json_decode($data);
        $this->id = $data->id;
        $this->setSlug($data->slug);
        $this->setTitle($data->title);
        $this->setPublish(isset($data->publish) ? $data->publish : true);
        $this->setStats(['listedBy' => $data->listedBy, 'doneBy' => $data->doneBy]);

        $this->listPhotoDownloadLink = $data->image;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFeaturedDate()
    {
        return $this->featuredDate;
    }

    /**
     * @param mixed $featuredDate
     */
    public function setFeaturedDate($featuredDate)
    {
        $this->featuredDate = $featuredDate;
    }

    /**
     * Add affiliate
     *
     * @param \Application\AffiliateBundle\Entity\Affiliate $affiliate
     *
     * @return Goal
     */
    public function addAffiliate(\Application\AffiliateBundle\Entity\Affiliate $affiliate)
    {
        $this->affiliates[] = $affiliate;

        return $this;
    }

    /**
     * Remove affiliate
     *
     * @param \Application\AffiliateBundle\Entity\Affiliate $affiliate
     */
    public function removeAffiliate(\Application\AffiliateBundle\Entity\Affiliate $affiliate)
    {
        $this->affiliates->removeElement($affiliate);
    }

    /**
     * Get affiliates
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAffiliates()
    {
        return $this->affiliates;
    }

    /**
     * Add place
     *
     * @param \AppBundle\Entity\Place $place
     *
     * @return Goal
     */
    public function addPlace(\AppBundle\Entity\Place $place)
    {
        $this->place[] = $place;

        return $this;
    }

    /**
     * Remove place
     *
     * @param \AppBundle\Entity\Place $place
     */
    public function removePlace(\AppBundle\Entity\Place $place)
    {
        $this->place->removeElement($place);
    }

    /**
     * Get place
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlace()
    {
        return $this->place;
    }
}
