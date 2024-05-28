<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 6/24/16
 * Time: 3:43 PM
 */
namespace AppBundle\Model;

/**
 * Interface ImageableInterface
 * @package AppBundle\Model
 */
interface ImageableInterface
{
    /**
     * @return mixed
     */
    public function getImagePath();

    /**
     * @param $path
     * @return mixed
     */
    public function setMobileImagePath($path);
}