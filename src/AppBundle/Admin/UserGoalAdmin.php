<?php
namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class UserGoalAdmin extends AbstractAdmin
{
    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }

    /**
     * @param \Sonata\AdminBundle\Show\ShowMapper $showMapper
     *
     * @return void
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('getStringStatus', null, ['label'=>'admin.label.name.status'])
            ->add('dateStatus', null, ['label'=>'admin.label.name.date_status'])
            ->add('doDateStatus', null, ['label'=>'admin.label.name.do_date_status'])
            ->add('isVisible', null, ['label'=>'admin.label.name.is_visible'])
            ->add('urgent', null, ['label'=>'admin.label.name.urgent'])
            ->add('important', null, ['label'=>'admin.label.name.important'])
            ->add('note', null, ['label'=>'admin.label.name.note'])
            ->add('confirmed', null, ['label'=>'admin.label.name.confirmed'])
            ->add('goal', null, ['label'=>'admin.label.name.goal', 'admin_code' => 'sonata.admin.app.goal'])
            ->add('user', null, ['label'=>'admin.label.name.user'])
            ->add('doDate', null, ['label'=>'admin.label.name.do_date'])
            ->add('completionDate', null, ['label'=>'admin.label.name.completion_date'])
            ->add('listedDate', null, ['label'=>'admin.label.name.listed_date'])
            ->add('updated', null, ['label'=>'admin.label.name.updated'])
        ;
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('status', 'choice', ['label'=>'admin.label.name.status',
                            'choices'=>[1=>'user_goal.active', 2=>'user_goal.completed']])
            ->add('dateStatus', 'choice', ['label'=>'admin.label.name.date_status',
                                 'choices'=>[1=>'admin.label.name.all', 2=>'admin.label.name.only_year',
                                             3=>'admin.label.name.only_year_month']])
            ->add('doDateStatus','choice', ['label'=>'admin.label.name.do_date_status',
                                 'choices'=>[1=>'admin.label.name.all', 2=>'admin.label.name.only_year',
                                             3=>'admin.label.name.only_year_month']])
            ->add('isVisible', null, ['label'=>'admin.label.name.is_visible'])
            ->add('urgent', null, ['label'=>'admin.label.name.urgent'])
            ->add('important', null, ['label'=>'admin.label.name.important'])
            ->add('note', null, ['label'=>'admin.label.name.note', 'required' => false])
            ->add('confirmed', null, ['label'=>'admin.label.name.confirmed', 'data' => false, 'required' => false])
            ->add('goal', 'sonata_type_model_autocomplete', ['label'=>'admin.label.name.goal', 'property' => 'title'], ['admin_code' => 'sonata.admin.app.goal'])
            ->add('user', 'sonata_type_model_autocomplete', ['label'=>'admin.label.name.user', 'property' => 'email'])
            ->add('doDate', 'sonata_type_date_picker', ['label'=>'admin.label.name.do_date', 'required' => false])
            ->add('completionDate', 'sonata_type_date_picker', ['label'=>'admin.label.name.completion_date', 'required' => false])
            ->add('listedDate', 'sonata_type_date_picker', ['label'=>'admin.label.name.listed_date'])
            ->add('updated', 'sonata_type_date_picker', ['label'=>'admin.label.name.updated'])
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('status', 'doctrine_orm_string',['label'=>'admin.label.name.status', 'show_filter' => true],
                'choice', ['choices'=>[1=>'user_goal.active',
                                       2=>'user_goal.completed'] ])
            ->add('isVisible', null, ['label'=>'admin.label.name.is_visible'])
            ->add('urgent', null, ['label'=>'admin.label.name.urgent'])
            ->add('important', null, ['label'=>'admin.label.name.important'])
            ->add('confirmed', null, ['label'=>'admin.label.name.confirmed'])
            ->add('goal.title', null, ['label'=>'admin.label.name.goal_title'])
            ->add('user.email', null, ['label'=>'admin.label.name.email'])
            ->add('user.firstname', null, ['label'=>'admin.label.name.firstName'])
            ->add('user.lastname', null, ['label'=>'admin.label.name.lastName'])
            ->add('doDate', 'doctrine_orm_date_range', ['label' => 'admin.label.name.do_date'], 'sonata_type_date_range_picker',
                [
                    'field_options_start'=> ['format'=>'yyyy-MM-dd'],
                    'field_options_end'=> ['format'=>'yyyy-MM-dd']
                ]
            )
            ->add('listedDate', 'doctrine_orm_date_range', ['label' => 'admin.label.name.listed_date', 'show_filter' => true], 'sonata_type_date_range_picker',
                [
                    'field_options_start'=> ['format'=>'yyyy-MM-dd'],
                    'field_options_end'=> ['format'=>'yyyy-MM-dd']
                ]
            )
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('user', null, ['label'=>'admin.label.name.user'])
            ->add('getStringStatus', null, ['label'=>'admin.label.name.status'])
            ->add('isVisible', null, ['label'=>'admin.label.name.is_visible'])
            ->add('confirmed', null, ['label'=>'admin.label.name.confirmed'])
            ->add('urgent', null, ['label'=>'admin.label.name.urgent'])
            ->add('important', null, ['label'=>'admin.label.name.important'])
            ->add('goal', null, ['label'=>'admin.label.name.goal', 'admin_code' => 'sonata.admin.app.goal'])
            ->add('listedDate', null, ['label'=>'admin.label.name.listed_date'])
            ->add('updated', null, ['label'=>'admin.label.name.updated'])
            ->add('_action', 'actions', [
                    'actions' => [
                        'show' => [],
                        'delete' => [],
                    ]
                ]
            )
        ;
    }
}