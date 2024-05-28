<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class EmailAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('sent', 'doctrine_orm_date_range', ['label' => 'admin.label.name.sent'], 'sonata_type_date_range_picker',
                [
                    'field_options_start'=> ['format'=>'yyyy-MM-dd'],
                    'field_options_end'=> ['format'=>'yyyy-MM-dd']
                ]
            )
            ->add('seen', 'doctrine_orm_date_range', ['label' => 'admin.label.name.seen'], 'sonata_type_date_range_picker',
                [
                    'field_options_start'=> ['format'=>'yyyy-MM-dd'],
                    'field_options_end'=> ['format'=>'yyyy-MM-dd']
                ]
            )
            ->add('device', null, ['label'=>'admin.label.name.device'])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('sent', null, ['label'=>'admin.label.name.sent'])
            ->add('seen', null, ['label'=>'admin.label.name.seen'])
            ->add('device', null, ['label'=>'admin.label.name.device'])
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
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
            ->add('content', null, ['label'=>'admin.label.name.content'])
            ->add('sent', 'sonata_type_date_picker', ['label'=>'admin.label.name.sent'])
            ->add('seen', 'sonata_type_date_picker', ['label'=>'admin.label.name.seen', 'required' => false])
            ->add('device', null, ['label'=>'admin.label.name.device'])
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('content', null, ['label'=>'admin.label.name.content', 'template' => "AppBundle:Admin:email_content_show.html.twig"])
            ->add('title', null, ['label'=>'admin.label.name.title'])
            ->add('sent', 'sonata_type_date_picker', ['label'=>'admin.label.name.sent'])
            ->add('seen', 'sonata_type_date_picker', ['label'=>'admin.label.name.seen'])
            ->add('device', null, ['label'=>'admin.label.name.device'])
        ;
    }
}
