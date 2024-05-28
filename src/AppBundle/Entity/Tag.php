<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/9/15
 * Time: 7:04 PM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\TagRepository")
 * @ORM\Table(name="tag")
 * @UniqueEntity(fields={"tag"}, errorPath="tag", message="Duplicate og tag")
 *
 */
class Tag
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="tag", type="string" , unique=true)
     */
    protected $tag;

    /**
     * @Gedmo\Slug(fields={"tag"})
     * @ORM\Column(name="slug", type="string", unique=true, nullable=false)
     */
    protected $slug;

    /**
     * @ORM\ManyToMany(targetEntity="Category", mappedBy="tags")
     */
    protected $category;

    /**
     * @ORM\ManyToMany(targetEntity="Goal", mappedBy="tags")
     */
    protected $goal;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Aphorism", mappedBy="tags")
     */
    protected $aphorism;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->tag;
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
     * Set tag
     *
     * @param string $tag
     * @return Category
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return string 
     */
    public function getTag()
    {
        return $this->tag;
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
        $this->category = new \Doctrine\Common\Collections\ArrayCollection();
        $this->goal = new \Doctrine\Common\Collections\ArrayCollection();
        $this->aphorism = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add category
     *
     * @param \AppBundle\Entity\Category $category
     * @return Tag
     */
    public function addCategory(\AppBundle\Entity\Category $category)
    {
        $this->category[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \AppBundle\Entity\Category $category
     */
    public function removeCategory(\AppBundle\Entity\Category $category)
    {
        $this->category->removeElement($category);
    }

    /**
     * Get category
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add goal
     *
     * @param \AppBundle\Entity\Goal $goal
     * @return Tag
     */
    public function addGoal(\AppBundle\Entity\Goal $goal)
    {
        $this->goal[] = $goal;

        return $this;
    }

    /**
     * Remove goal
     *
     * @param \AppBundle\Entity\Goal $goal
     */
    public function removeGoal(\AppBundle\Entity\Goal $goal)
    {
        $this->goal->removeElement($goal);
    }

    /**
     * Get goal
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGoal()
    {
        return $this->goal;
    }

    /**
     * Add aphorism
     *
     * @param \AppBundle\Entity\Aphorism $aphorism
     * @return Tag
     */
    public function addAphorism(\AppBundle\Entity\Aphorism $aphorism)
    {
        $this->aphorism[] = $aphorism;

        return $this;
    }

    /**
     * Remove aphorism
     *
     * @param \AppBundle\Entity\Aphorism $aphorism
     */
    public function removeAphorism(\AppBundle\Entity\Aphorism $aphorism)
    {
        $this->aphorism->removeElement($aphorism);
    }

    /**
     * Get aphorism
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAphorism()
    {
        return $this->aphorism;
    }
}
