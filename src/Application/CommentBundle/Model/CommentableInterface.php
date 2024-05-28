<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/12/16
 * Time: 2:11 PM
 */
namespace Application\CommentBundle\Model;

/**
 * Interface CommentableInterface
 * @package Application\CommentBundle\Model
 */
interface CommentableInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getTitle();
}