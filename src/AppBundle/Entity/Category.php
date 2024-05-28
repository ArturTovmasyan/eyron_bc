<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/9/15
 * Time: 7:04 PM
 */

namespace AppBundle\Entity;

use AppBundle\Traits\File;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\CategoryRepository")
 * @ORM\Table(name="category")
 * @Gedmo\TranslationEntity(class="AppBundle\Entity\Translation\CategoryTranslation")
 */
class Category
{
    use File;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups("category")
     */
    protected $id;

    /**
     * @ORM\Column(name="title", type="string", length=25)
     * @Groups("category")
     * @Gedmo\Translatable
     */
    protected $title;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(name="slug", type="string", unique=true, length=25)
     * @Groups("category")
     */
    protected $slug;

    /**
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="category")
     * @ORM\JoinTable(name="categories_tags",
     *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     *      )
     **/
    protected $tags;

    /**
     * @ORM\OneToMany(
     *   targetEntity="AppBundle\Entity\Translation\CategoryTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    protected $translations;

    /**
     * Required for Translatable behaviour
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * @VirtualProperty()
     * @Serializer\SerializedName("svg_download_link")
     * @Groups("category")
     */
    public function getImageDownloadLink()
    {
        return $this->getDownloadLink();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->title;
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
     * Set title
     *
     * @param string $title
     * @return Category
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
     * Set slug
     *
     * @param string $slug
     * @return Category
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
     * Constructor
     */
    public function __construct()
    {
        $this->goals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add tags
     *
     * @param \AppBundle\Entity\Tag $tags
     * @return Category
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
     * Add translations
     *
     * @param \AppBundle\Entity\Translation\CategoryTranslation $translations
     * @return Category
     */
    public function addTranslation(\AppBundle\Entity\Translation\CategoryTranslation $translations)
    {
        $this->translations[] = $translations;

        $translations->setObject($this);
        return $this;
    }

    /**
     * Remove translations
     *
     * @param \AppBundle\Entity\Translation\CategoryTranslation $translations
     */
    public function removeTranslation(\AppBundle\Entity\Translation\CategoryTranslation $translations)
    {
        $this->translations->removeElement($translations);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTranslations()
    {
        return $this->translations;
    }


    /**
     * @return null|string
     * @VirtualProperty()
     * @Serializer\SerializedName("image_download_link")
     *  @Groups("category")
     */
    public function getDownloadLinkPng()
    {
        $path = '/' . $this->getUploadDir() . '/' . $this->getPath() . '/' . $this->getSlug() . '.png';

        return $path;
    }
}
