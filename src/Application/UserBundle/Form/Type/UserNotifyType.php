<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/7/16
 * Time: 11:26 AM
 */
namespace Application\UserBundle\Form\Type;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserNotifyType
 * @package Application\UserBundle\Form\Type
 */
class UserNotifyType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isCommentOnGoalNotify', CheckboxType::class, array('required'=> false))
            ->add('isCommentOnIdeaNotify', CheckboxType::class, array('required'=> false))
            ->add('isSuccessStoryOnGoalNotify', CheckboxType::class, array('required'=> false))
            ->add('isSuccessStoryOnIdeaNotify', CheckboxType::class, array('required'=> false))
            ->add('isSuccessStoryLikeNotify', CheckboxType::class, array('required'=> false))
            ->add('isGoalPublishNotify', CheckboxType::class, array('required'=> false))
            ->add('isCommentReplyNotify', CheckboxType::class, array('required'=> false))
            ->add('isDeadlineExpNotify', CheckboxType::class, array('required'=> false))
            ->add('isNewGoalFriendNotify', CheckboxType::class, array('required'=> false))
            ->add('isNewIdeaNotify', CheckboxType::class, array('required'=> false))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Application\UserBundle\Entity\UserNotify',
            'validation_groups' => 'NotifySettings'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'bl_user_notify_type';
    }
}
