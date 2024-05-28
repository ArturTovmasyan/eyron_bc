<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/9/15
 * Time: 7:43 PM
 */

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Class CategoryAdmin
 * @package AppBundle\Admin
 */
class CategoryAdmin extends AbstractAdmin
{
    protected  $baseRouteName = 'admin-category';
    protected  $baseRoutePattern = 'admin-category';

    /**
     * override list query
     *
     * @param string $context
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface */

    public function createQuery($context = 'list')
    {
        // call parent query
        $query = parent::createQuery($context);

        // add selected
        $query->addSelect('tg');
        $query->leftJoin($query->getRootAlias() . '.tags', 'tg');

        return $query;
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
            ->add('title', null, ['label'=>'admin.label.name.title'])
            ->add('tags', null, ['label'=>'admin.label.name.tags'])
            ->add('downloadLink', null, ['template' => 'AppBundle:Admin:image_show.html.twig', 'label'=>'admin.label.name.downloadLink']
            )

        ;
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('translations', 'a2lix_translations_gedmo', [
                'label'=>'admin.label.name.title',
                'translatable_class' => 'AppBundle\Entity\Category',
            ]
            )
            ->add('tags', null, ['label'=>'admin.label.name.tags'])
            ->add('file', 'file', ['required' => false, 'label'=>'admin.label.name.file'])
        ;
    }

    protected $formOptions = [
        'validation_groups' => ['logo']
    ];

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, ['label'=>'admin.label.name.id','show_filter' => true])
            ->add('title', null, ['label'=>'admin.label.name.title','show_filter' => true])
            ->add('tags', null, ['label'=>'admin.label.name.tags'])
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('title', null, ['label'=>'admin.label.name.title'])
            ->add('tags', null, ['label'=>'admin.label.name.tags'])
            ->add('downloadLink', null, ['template' => 'AppBundle:Admin:image_list.html.twig', 'label'=>'admin.label.name.downloadLink']
            )
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


    public function prePersist($object)
    {
        $this->preUpdate($object);
    }

    public function preUpdate($object)
    {
        $bucketService = $this->getConfigurationPool()->getContainer()->get('bl_service');
        $bucketService->uploadFile($object);
    }
}