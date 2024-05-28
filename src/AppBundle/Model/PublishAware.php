<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/16/15
 * Time: 10:31 AM
 */

namespace AppBundle\Model;

/**
 * Interface PublishAware
 * @package W3\BookBundle\Model
 */
interface PublishAware
{
    const PUBLISH = true;
    const NOT_PUBLISH = false;

    /**
     * @param bool $publish
     * @return mixed
     */
    public function setPublish($publish);

    /**
     * @return boolean
     */
    public function getPublish();
}