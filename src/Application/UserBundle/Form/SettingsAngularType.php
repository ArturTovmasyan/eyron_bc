<?php

namespace Application\UserBundle\Form;

use AppBundle\Form\Type\LngType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SettingsAngularType
 * @package Application\UserBundle\Form
 */
class SettingsAngularType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('firstName', null, ['required'=>true, 'label' => 'form.firstName', 'translation_domain' => 'FOSUserBundle'])
            ->add('lastName', null, ['required'=>true, 'label' => 'form.lastName', 'translation_domain' => 'FOSUserBundle'])
            ->add('addEmail', EmailType::class, ['required' => false, 'label' => 'form.add_email'])
            ->add('language', LngType::class, ['required' => true, 'label' => 'form.language'])
            ->add('email', null, ['attr' => ['readonly' => true], 'required' => true, 'label' => 'form.email', 'translation_domain' => 'FOSUserBundle'])
            ->add('currentPassword', PasswordType::class, [
                'required' => false,
                'label' => 'form.current_password',
                'translation_domain' => 'FOSUserBundle',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'required' => false,
                'type' => 'password',
                'options' => ['translation_domain' => 'FOSUserBundle'],
                'first_options' => ['label' => 'form.password'],
                'second_options' => ['label' => 'form.password_confirmation'],
                'invalid_message' => 'fos_user.password.mismatch',
            ])
            ->add('birthDate', DateType::class, ['required' => false,  'widget' => 'single_text', 'format' => 'yyyy/MM/dd'])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Application\UserBundle\Entity\User',
            'allow_extra_fields' => true,
            'error_bubbling' => true,
            'csrf_protection' => false,
            'validation_groups' => function(FormInterface $form) {

                //get form data
                $data = $form->getData();

                //get plain password
                $plainPassword = $data->getPlainPassword();

                //check if plainPassword exist
                if ($plainPassword) {
                    return ['Settings', 'MobileChangePassword'];
                } else {
                    return 'Settings';
                }
            },
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'bl_user_angular_settings';
    }
}