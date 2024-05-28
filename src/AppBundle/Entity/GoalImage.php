<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/9/15
 * Time: 6:33 PM
 */

namespace AppBundle\Entity;

use AppBundle\Model\ImageableInterface;
use AppBundle\Traits\File;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class GoalImage
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\GoalImageRepository")
 * @ORM\Table(name="goal_image")
 * @ORM\HasLifecycleCallbacks()
 */
class GoalImage implements ImageableInterface
{
    // use file trait
    use File;

    // constants for folder
    const COVER = 'cover';
    const F_LIST = 'list';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"image"})
     */
    protected $id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Goal", inversedBy="images", cascade={"persist"})
     * @ORM\JoinColumn(name="goal_id", referencedColumnName="id")
     */
    protected $goal;

    /**
     * @ORM\Column(type="boolean", name="list_image")
     * @Groups({"image"})
     */
    protected $list = false;

    /**
     * @ORM\Column(type="boolean", name="cover_image")
     * @Groups({"image"})
     */
    protected $cover = false;

    /**
     * @var
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
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
     * @SerializedName("image_path")
     * @Groups({"image"})
     */
    protected $mobilePhotoPath;


    /**
     * @return string
     */
    public function getImagePath()
    {
        return $this->getDownloadLink();
    }

    /**
     * @param $path
     * @return $this
     */
    public function setMobileImagePath($path)
    {
        $this->mobilePhotoPath = $path;

        return $this;
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
     * Set goal
     *
     * @param \AppBundle\Entity\Goal $goal
     * @return GoalImage
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
     * Override getPath function in file trait
     *
     * @return string
     */
    protected function getPath()
    {
        return 'images';
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return GoalImage
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
     * @return GoalImage
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
     * @return mixed
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param mixed $list
     */
    public function setList($list = 0)
    {
        $this->list = $list;
    }

    /**
     * @return mixed
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * @param mixed $cover
     */
    public function setCover($cover = 0)
    {
        $this->cover = $cover;
    }

    /**
     * @ORM\PreRemove
     */
    public function preRemove()
    {
        // get origin file path
        $filePath = $this->getAbsolutePath() . $this->getFileName();

        // check file and remove
        if (file_exists($filePath) && is_file($filePath)){
            unlink($filePath);
        }
    }
}
