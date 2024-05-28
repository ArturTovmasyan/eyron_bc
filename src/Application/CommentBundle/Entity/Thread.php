<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/14/15
 * Time: 7:30 PM
 */

namespace Application\CommentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="thread")
 */
class Thread
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="string")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Application\CommentBundle\Entity\Comment", mappedBy="thread")
     */
    protected $comments;

    /**
     * @ORM\Column(name="num_comments", type="integer")
     */
    protected $numComments = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->getId();
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Thread
     */
    public function setId($id)
    {
        $this->id = $id;

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
     * Set numComments
     *
     * @param integer $numComments
     *
     * @return Thread
     */
    public function setNumComments($numComments)
    {
        $this->numComments = $numComments;

        return $this;
    }

    /**
     * Get numComments
     *
     * @return integer
     */
    public function getNumComments()
    {
        return $this->numComments;
    }

    /**
     * Add comment
     *
     * @param \Application\CommentBundle\Entity\Comment $comment
     *
     * @return Thread
     */
    public function addComment(\Application\CommentBundle\Entity\Comment $comment)
    {
        $this->comments[] = $comment;
        $this->numComments++;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \Application\CommentBundle\Entity\Comment $comment
     */
    public function removeComment(\Application\CommentBundle\Entity\Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }
}
