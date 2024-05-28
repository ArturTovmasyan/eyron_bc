<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 2/15/16
 * Time: 6:05 PM
 */

namespace AppBundle\Form\Type;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LngType
 * @package AppBundle\Form\Type
 */
class LngType extends AbstractType
{

    /**
     * @var
     */
    private $container;

    /**
     * LngType constructor.
     * @param $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return null|string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $languages = $this->container->getParameter('languages');

        $resolver->setDefaults(array(
            'choices' => $languages,
            'translation_domain' => 'FOSUserBundle',
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'lng';
    }
}