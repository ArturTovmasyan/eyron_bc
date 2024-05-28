<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/2/15
 * Time: 10:29 AM
 */

namespace Application\UserBundle\Form;

use Application\UserBundle\Entity\User;
use Application\UserBundle\Entity\UserNotify;
use Application\UserBundle\Form\Type\UserNotifyType;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserNotifySettingsType
 * @package Application\UserBundle\Form
 */
class UserNotifySettingsType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('userNotifySettings', UserNotifyType::class, array('required'=> false))
            ->add('file', FileType::class, array('required' => false, 'label' => 'form.file', 'translation_domain' => 'FOSUserBundle'))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Application\UserBundle\Entity\User',
            'validation_groups' => 'NotifySettings'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'bl_user_notify_settings';
    }
}