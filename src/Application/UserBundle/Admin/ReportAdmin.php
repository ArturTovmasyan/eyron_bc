<?php

namespace Application\UserBundle\Admin;

use Application\UserBundle\Entity\Report;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ReportAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'created',
    ];

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        //disable listener for stats count
        $this->getConfigurationPool()->getContainer()->get('bl.doctrine.listener')->disableUserStatsLoading();

        $datagridMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('user', null, ['label'=>'admin.label.name.user'])
            ->add('reportedUser', null, ['label'=>'admin.label.name.report_user'])
            ->add('contentType', null, ['label'=>'admin.label.name.content_type'], ChoiceType::class, [
                'choices' => [
                    Report::COMMENT       => 'admin.label.name.comments',
                    Report::SUCCESS_STORY => 'admin.label.name.success_story'
                ]
            ]
            )
            ->add('reportType', null, [], ChoiceType::class, [
                'choices' => [
                    Report::SPAM       => 'admin.label.name.report_spam',
                    Report::SHOULD_NOT => 'admin.label.name.report_not'
                ]
            ]
            )
            ->add('contentId', null, ['label'=>'admin.label.name.content'])
            ->add('message', null, ['label'=>'admin.label.name.messages'])
            ->add('created', 'doctrine_orm_callback', [
                'callback' => function($queryBuilder, $alias, $field, $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $queryBuilder
                        ->andWhere("DATE(" . $alias . ".created) = DATE(:value)")
                        ->setParameter('value', $value['value'])
                    ;

                    return true;
                },
                'label'=>'admin.label.name.created',
            ], 'date', ['widget' => 'single_text']
            )
        ;
    }

    public $comments;
    public $successStories;


    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        //disable listener for stats count
        $this->getConfigurationPool()->getContainer()->get('bl.doctrine.listener')->disableUserStatsLoading();
        $em = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();
        $ids = $em->getRepository('ApplicationUserBundle:Report')->findCommentAndSuccessStoriesIds();

        $this->comments       = $em->getRepository('ApplicationCommentBundle:Comment')->findByIds($ids['commentIds']);
        $this->successStories = $em->getRepository('AppBundle:SuccessStory')->findByIds($ids['successStoryIds']);

        $listMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('user', null, ['label'=>'admin.label.name.user'])
            ->add('reportedUser', null, ['label'=>'admin.label.name.report_user'])
            ->add('contentTypeString', null, ['label'=>'admin.label.name.content_type'])
            ->add('reportTypeString', null, ['label'=>'admin.label.name.report_type'])
            ->add('contentId', null, ['template' => 'ApplicationUserBundle:Admin:content_list_field.html.twig', 'label'=>'admin.label.name.content']
            )
            ->add('message', null, ['label'=>'admin.label.name.messages'])
            ->add('created', null, ['label'=>'admin.label.name.created'])
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                    'goal_link' => ['template' => 'ApplicationUserBundle:Admin:report_list_action_link.html.twig'],
                ]
            ]
            )
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        //disable listener for stats count
        $this->getConfigurationPool()->getContainer()->get('bl.doctrine.listener')->disableUserStatsLoading();

        $formMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('user', null, ['label'=>'admin.label.name.user'])
            ->add('reportedUser', null, ['label'=>'admin.label.name.report_user'])
            ->add('contentType', ChoiceType::class, [
                'choices' => [
                    Report::COMMENT       => 'admin.label.name.comment',
                    Report::SUCCESS_STORY => 'admin.label.name.success_story'
                ],
               'label'=>'admin.label.name.content_type'
            ]
            )
            ->add('reportType', ChoiceType::class, [
                'choices' => [
                    Report::SPAM       => 'admin.label.name.report_spam',
                    Report::SHOULD_NOT => 'admin.label.name.report_not'
                ],
                'label'=>'admin.label.name.report_type'
            ]
            )
            ->add('contentId', null, ['label'=>'admin.label.name.content'])
            ->add('message', null, ['label'=>'admin.label.name.messages'])
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('user', null, ['label'=>'admin.label.name.user'])
            ->add('reportedUser', null, ['label'=>'admin.label.name.report_user'])
            ->add('contentTypeString', null, ['label'=>'admin.label.name.content_type'])
            ->add('reportTypeString',  null, ['label'=>'admin.label.name.report_type'])
            ->add('contentId', null, ['label'=>'admin.label.name.content'])
            ->add('message', null, ['label'=>'admin.label.name.messages'])
            ->add('created', null, ['label'=>'admin.label.name.created'])
        ;
    }
}
