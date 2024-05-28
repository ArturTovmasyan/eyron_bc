<?php

namespace Application\CommentBundle\Admin;

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class CommentAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('body', null, ['label'=>'admin.label.name.body'])
            ->add('thread', null, ['label'=>'admin.label.name.thread'])
            ->add('createdAt', 'doctrine_orm_callback', [
                'callback' => function($queryBuilder, $alias, $field, $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $queryBuilder
                        ->andWhere("DATE(" . $alias . ".createdAt) = DATE(:value)")
                        ->setParameter('value', $value['value'])
                    ;

                    return true;
                },
                'label'=>'admin.label.name.created'
            ], 'date', ['widget' => 'single_text']
            )

            ->add('goal', 'doctrine_orm_callback', [
                'mapped' => false,
                'callback' => function($queryBuilder, $alias, $field, $value) {
                    if (!$value['value']) {
                        return;
                    }

                    //get slug in url
                    $slug = parse_url($value['value']);
                    $slug = substr($slug['path'], strrpos($slug['path'], '/') + 1);
                    $slug = 'goal_'.$slug;

                    $queryBuilder
                        ->leftJoin($alias . '.thread', 'tr')
                        ->andWhere('tr.id = :slug')
                        ->setParameter('slug', $slug)
                    ;

                    return $queryBuilder;
                },

                'label'=>'admin.label.name.goal_link'
            ]
            )
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('body', null, ['label'=>'admin.label.name.body'])
            ->add('createdAt', null, ['label'=>'admin.label.name.created'])
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                    'goal_link' => ['template' => 'ApplicationCommentBundle:Admin:comment_list_action_link.html.twig'],
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
            ->add('body', null, ['label'=>'admin.label.name.body'])
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('body', null, ['label'=>'admin.label.name.body'])
            ->add('createdAt', null, ['label'=>'admin.label.name.created'])
        ;
    }
}
