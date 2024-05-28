<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/21/15
 * Time: 10:56 AM
 */

namespace AppBundle\Entity;

use AppBundle\Model\ImageableInterface;
use AppBundle\Traits\File;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class StoryImage
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\StoryImageRepository")
 * @ORM\Table(name="story_image")
 */
class StoryImage implements ImageableInterface
{
    // use file trait
    use File;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"storyImage"})
     */
    protected $id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="SuccessStory", inversedBy="files", cascade={"persist"})
     * @ORM\JoinColumn(name="story_id", referencedColumnName="id")
     */
    protected $story;

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
     * @Groups({"storyImage", "inspireStory"})
     */
    protected $mobilePhotoPath;

    /**
     * Override getPath function in file trait
     *
     * @return string
     */
    protected function getPath()
    {
        return 'stories';
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
     * Set story
     *
     * @param \AppBundle\Entity\SuccessStory $story
     * @return StoryImage
     */
    public function setStory(\AppBundle\Entity\SuccessStory $story = null)
    {
        $this->story = $story;

        return $this;
    }

    /**
     * Get story
     *
     * @return \AppBundle\Entity\SuccessStory 
     */
    public function getStory()
    {
        return $this->story;
    }


    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param mixed $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

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
}
