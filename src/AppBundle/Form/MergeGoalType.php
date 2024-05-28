<?php
/**
 * Created by PhpStorm.
 * User: artur
 */

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;


/**
 * Class MergeGoalType
 * @package AppBundle\Form
 */

class MergeGoalType extends AbstractType
{

    //set goalId private variable
    private $goalId;

    public function __construct($goalId = null)
    {
        //get goal id
        $this->goalId = $goalId;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //get goal id
        $goalId = $this->goalId;

        $builder
            ->add('goal', 'genemu_jqueryselect2_entity', [
                'class' => 'AppBundle\Entity\Goal',
                'property' => 'title',
                'placeholder' => 'admin.label.name.select_goal',
                'query_builder' => function(EntityRepository $er) use ($goalId) {
                    return $er->createQueryBuilder('g')
                        ->where('g.id != :goalId')
                    ->setParameter('goalId', $goalId);
                },
                'label' => 'admin.label.name.goal'
            ]
            )
            ->add('tags', CheckboxType::class, ['label' => 'admin.label.name.tags', 'required' => false])
            ->add('successStory', CheckboxType::class, ['label' => 'admin.label.name.success_story', 'required' => false, 'attr' => ['checked' => 'checked']])
            ->add('comment', CheckboxType::class, ['label' => 'admin.label.name.comment', 'required' => false, 'attr' => ['checked' => 'checked']])
            ->add('user', CheckboxType::class, ['label' => 'admin.label.name.user', 'required' => false, 'attr' => ['checked' => 'checked']])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'app_bundle_merge_goal';
    }
}
