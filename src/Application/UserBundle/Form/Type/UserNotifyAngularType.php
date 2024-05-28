<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/7/16
 * Time: 11:26 AM
 */
namespace Application\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserNotifyAngularType
 * @package Application\UserBundle\Form\Type
 */
class UserNotifyAngularType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isCommentOnGoalNotify', CheckboxType::class, ['required'=> false])
            ->add('isCommentOnIdeaNotify', CheckboxType::class, ['required'=> false])
            ->add('isSuccessStoryOnGoalNotify', CheckboxType::class, ['required'=> false])
            ->add('isSuccessStoryOnIdeaNotify', CheckboxType::class, ['required'=> false])
            ->add('isSuccessStoryLikeNotify', CheckboxType::class, ['required'=> false])
            ->add('isGoalPublishNotify', CheckboxType::class, ['required'=> false])
            ->add('isCommentReplyNotify', CheckboxType::class, ['required'=> false])
            ->add('isDeadlineExpNotify', CheckboxType::class, ['required'=> false])
            ->add('isNewGoalFriendNotify', CheckboxType::class, ['required'=> false])
            ->add('isNewIdeaNotify', CheckboxType::class, ['required'=> false])
//            ->add('file', FileType::class, array('required'=> false));
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Application\UserBundle\Entity\UserNotify',
            'validation_groups' => 'NotifySettings',
            'allow_extra_fields' => true,
            'csrf_protection' => false,

        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'bl_user_notify_angular_type';
    }
}
