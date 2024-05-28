<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 1/22/16
 * Time: 1:25 PM
 */

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

/**
 * Class BlMultipleVideoType
 * @package AppBundle\Form
 */
class BlMultipleVideoType extends AbstractType
{
    /**
     * @return string
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'bl_multiple_video';
    }
}