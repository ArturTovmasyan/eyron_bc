<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/2/15
 * Time: 10:29 AM
 */

namespace Application\UserBundle\Form;

use AppBundle\Form\Type\LngType;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SettingsMobileType
 * @package Application\UserBundle\Form\Type
 */

class SettingsMobileType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('firstName', TextType::class, array('required'=>true))
            ->add('lastName', TextType::class, array('required'=>true))
            ->add('addEmail', EmailType::class, array('required' => false))
            ->add('birthDate', DateType::class, array('required' => false,  'widget' => 'single_text', 'format' => 'yyyy/MM/dd'))
            ->add('primary', EmailType::class, array('required' => false))
            ->add('language', LngType::class, array('required' => true, 'label' => 'form.language'))
            ->add('file', FileType::class, array('required' => false))
            ->add('isCommentNotify', ChoiceType::class, array('required' => false, 'choices' => array(0 => false, 1 => true)))
            ->add('isSuccessStoryNotify', ChoiceType::class, array('required' => false, 'choices' => array(0 => false, 1 => true)))
            ->add('isCommentPushNote', ChoiceType::class, array('required' => false, 'choices' => array(0 => false, 1 => true)))
            ->add('isSuccessStoryPushNote', ChoiceType::class, array('required' => false, 'choices' => array(0 => false, 1 => true)))
            ->add('isProgressPushNote', ChoiceType::class, array('required' => false, 'choices' => array(0 => false, 1 => true)))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Application\UserBundle\Entity\User',
            'validation_groups' => 'MobileSettings',
            'csrf_protection' => false,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'bl_mobile_user_settings';
    }
}