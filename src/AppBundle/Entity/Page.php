<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/16/15
 * Time: 12:12 PM
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Translation\PageTranslation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\PageRepository")
 * @Gedmo\TranslationEntity(class="AppBundle\Entity\Translation\PageTranslation")
 * @ORM\Table(name="page")
 * @UniqueEntity(fields={"name"}, errorPath="name", message="You already have this page")
 */
class Page
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string")
     * @Gedmo\Translatable
     */
    protected $name;

    /**
     * @ORM\Column(name="title", type="string")
     * @Gedmo\Translatable
     */
    protected $title;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug", type="string", unique=true, nullable=false)
     */
    protected $slug;

    /**
     * @var
     * @ORM\Column(name="position", type="smallint", nullable=false)
     */
    protected $position;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="description", type="text")
     */
    protected $description;

    /**
     * @ORM\OneToMany(
     *   targetEntity="AppBundle\Entity\Translation\PageTranslation",
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
     * @ORM\Column(name="is_term", type="boolean", nullable=false)
     */
    protected $isTerm = false;

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

    public function __toString()
    {
        return $this->name ? $this->name : '';
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Page
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Page
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
     * Set position
     *
     * @param integer $position
     * @return Page
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Page
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     * Add translations
     *
     * @param \AppBundle\Entity\Translation\PageTranslation $translations
     * @return Page
     */
    public function addTranslation(\AppBundle\Entity\Translation\PageTranslation $translations)
    {
        $this->translations[] = $translations;

        $translations->setObject($this);
        return $this;
    }

    /**
     * Remove translations
     *
     * @param \AppBundle\Entity\Translation\PageTranslation $translations
     */
    public function removeTranslation(\AppBundle\Entity\Translation\PageTranslation $translations)
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
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Set isTerm
     *
     * @param boolean $isTerm
     *
     * @return Page
     */
    public function setIsTerm($isTerm)
    {
        $this->isTerm = $isTerm;

        return $this;
    }

    /**
     * Get isTerm
     *
     * @return boolean
     */
    public function getIsTerm()
    {
        return $this->isTerm;
    }
}
