<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/10/15
 * Time: 9:56 AM
 */

namespace AppBundle\Form;

use AppBundle\Form\Type\BlMultipleVideoType;
use AppBundle\Form\Type\LngType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Class GoalType
 * @package AppBundle\Form
 */
class GoalType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, array('required' => true))
            ->add('description', TextareaType::class, array('required' => true))
            ->add('status')
            ->add('files', HiddenType::class, array('mapped' => false))
            ->add('hashTags', HiddenType::class, array('mapped' => false))
            ->add('videoLink', BlMultipleVideoType::class, array('required' => false))
            ->add('language', LngType::class, array('required' => true, 'label' => 'language'))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Goal',
            'validation_groups' => 'goal'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'app_bundle_goal';
    }
}
