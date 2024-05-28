<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 8/4/16
 * Time: 2:44 PM
 */
namespace Application\AffiliateBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminFileType extends AbstractType
{
    /**
     * @return string
     */
    public function getParent()
    {
        return 'file';
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'admin_file';
    }

}
