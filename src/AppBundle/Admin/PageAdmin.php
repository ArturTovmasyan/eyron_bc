<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/16/15
 * Time: 12:21 PM
 */

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class PageAdmin extends AbstractAdmin
{
    protected  $baseRouteName = 'admin-page';
    protected  $baseRoutePattern = 'admin-page';

    /**
     * @var bool
     */
    public $supportsPreviewMode = true;
    /**
     * @param string $name
     * @return null|string
     */
    public function getTemplate($name)
    {
        switch ($name) {
            case 'preview':
                return 'AppBundle:Admin:page_preview.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
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
            ->add('name', null, ['label'=>'admin.label.name.name'])
            ->add('title', null, ['label'=>'admin.label.name.title'])
            ->add('description', null, ['label'=>'admin.label.name.description'])
            ->add('position', null, ['label'=>'admin.label.name.position'])

        ;
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('translations', 'a2lix_translations_gedmo', [
                'by_reference' => false,
                'translatable_class' => 'AppBundle\Entity\Page',
                'fields' => [                      // [optional] Manual configuration of fields to display and options. If not specified, all translatable fields will be display, and options will be auto-detected
                    'description' => [
                            'attr' => ['class' => 'tinymce']
                    ],
                ],
                'label'=>'admin.label.name.translations'
            ]
            )
            ->add('position', null, ['label'=>'admin.label.name.position'])
            ->add('isTerm', null, ['label'=>'admin.label.name.is_term'])
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, ['label'=>'admin.label.name.id','show_filter' => true])
            ->add('name', null, ['label'=>'admin.label.name.name','show_filter' => true])
            ->add('title', null, ['label'=>'admin.label.name.title','show_filter' => true])
            ->add('description', null, ['label'=>'admin.label.name.description','show_filter' => true])
            ->add('position', null, ['label'=>'admin.label.name.position','show_filter' => true])
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('name', null, ['label'=>'admin.label.name.name'])
            ->add('title', null, ['label'=>'admin.label.name.title'])
            ->add('position', null, ['label'=>'admin.label.name.position'])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ]
            ]
            )
        ;
    }
}