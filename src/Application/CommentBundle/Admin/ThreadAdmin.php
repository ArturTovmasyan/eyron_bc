<?php

namespace Application\CommentBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ThreadAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('numComments', null, ['label'=>'admin.label.name.num_comments'])
            ->add('_action', null, [
                'actions' => [
                    'show'      => [],
                    'edit'      => [],
                    'delete'    => [],
                    'goal_link' => ['template' => 'ApplicationCommentBundle:Admin:thread_list_action_link.html.twig'],
                    'comments'  => ['template' => 'ApplicationCommentBundle:Admin:comments.html.twig'],
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
        $formMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('numComments', null, ['label'=>'admin.label.name.num_comments'])
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('numComments', null, ['label'=>'admin.label.name.num_comments'])
        ;
    }
}
