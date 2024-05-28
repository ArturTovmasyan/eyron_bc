<?php

namespace AppBundle\Form;

use AppBundle\Entity\Blog;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class BlogType
 * @package AppBundle\Form
 */
class BlogType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class,  ['label'=>'admin.label.name.type',
                                              'choices' => [Blog::TYPE_TEXT => 'admin.label.name.text',
                                                            Blog::TYPE_GOAL => 'admin.label.name.goal']])
            ->add('content', TextareaType::class, ['label'=>'admin.label.name.content', 'required' => false])
            ->add('position', HiddenType::class, ['label'=>'admin.label.name.position', 'required' => false])
            ->add('goal', 'text', [
                'label'=>'admin.label.name.goal',
                'required' => false,
            ]
            )
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'app_bundle_blog';
    }
}