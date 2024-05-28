<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactUsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->setAction('#')
            ->add('fullName', TextType::class, array('required' => true, 'label'=>'page.contacr_us.form.full_name'))
            ->add('email', EmailType::class, array('required' => true, 'label'=>'page.contacr_us.form.email'))
            ->add('subject', TextType::class, array('required' => true, 'label'=>'page.contacr_us.form.subject'))
            ->add('message', TextareaType::class, array('required' => true, 'label'=>'page.contacr_us.form.message', 'attr'=>array('rows'=>'5')))
            ->add('send', SubmitType::class, array('label'=>'page.contacr_us.form.send'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getBlockPrefix()
    {
        return 'app_bundle_contact_us';
    }
}
