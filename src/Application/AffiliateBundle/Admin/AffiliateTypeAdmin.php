<?php

namespace Application\AffiliateBundle\Admin;

use Application\AffiliateBundle\Entity\AffiliateType;
use Application\AffiliateBundle\Form\Type\AdminFileType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AffiliateTypeAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, ['label' => 'admin.label.name.id'])
            ->add('name', null, ['label' => 'admin.label.name.name'])
            ->add('zone', null, ['label' => 'admin.label.name.zone'], ChoiceType::class, [
                'choices' => [
                    AffiliateType::LEFT_ZONE    => 'admin.label.name.left',
                    AffiliateType::RIGHT_ZONE   => 'admin.label.name.right',
                    AffiliateType::TOP_ZONE     => 'admin.label.name.top',
                    AffiliateType::BOTTOM_ZONE  => 'admin.label.name.bottom',
                    AffiliateType::INNER_ZONE   => 'admin.label.name.inner',
                ]
            ]
            )
            ->add('defaultLink', null, ['label' => 'admin.label.name.default_link'])
            ->add('htmlContent', null, ['label' => 'admin.label.name.html_content'])
            ->add('jsContent', null, ['label' => 'admin.label.name.js_content'])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        AffiliateType::$bookingAId = $this->getConfigurationPool()->getContainer()->getParameter('booking_aid');

        $listMapper
            ->add('id', null, ['label' => 'admin.label.name.id'])
            ->add('name', null, ['label' => 'admin.label.name.name'])
            ->add('zoneString', null, ['label' => 'admin.label.name.zone'])
            ->add('htmlContent', null, ['label' => 'admin.label.name.content', 'template' => 'ApplicationAffiliateBundle:Admin:affiliateTypeList.html.twig'])
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
            ->add('name', TextType::class, ['label' => 'admin.label.name.name'])
            ->add('defaultLink', TextType::class, ['label' => 'admin.label.name.default_link'])
            ->add('zone', ChoiceType::class, [
                'label' => 'admin.label.name.zone',
                'choices' => [
                    AffiliateType::LEFT_ZONE    => 'admin.label.name.left',
                    AffiliateType::RIGHT_ZONE   => 'admin.label.name.right',
                    AffiliateType::TOP_ZONE     => 'admin.label.name.top',
                    AffiliateType::BOTTOM_ZONE  => 'admin.label.name.bottom',
                    AffiliateType::INNER_ZONE   => 'admin.label.name.inner',
                ]
            ]
            )
            ->add('htmlContent', TextareaType::class, ['label' => 'admin.label.name.html_content', 'required' => false])
            ->add('jsContent', TextareaType::class, ['label' => 'admin.label.name.js_content', 'required' => false])
            ->add('file', AdminFileType::class, ['label' => 'admin.label.name.images', 'required' => false])
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', null, ['label' => 'admin.label.name.id'])
            ->add('name', null, ['label' => 'admin.label.name.name'])
            ->add('zoneString', null, ['label' => 'admin.label.name.zone'])
            ->add('htmlContent', null, ['label' => 'admin.label.name.html_content', 'template' => 'ApplicationAffiliateBundle:Admin:affiliateTypeList.html.twig'])
        ;
    }

    /**
     * @param mixed $object
     */
    public function prePersist($object)
    {
        $bucketService = $this->getConfigurationPool()->getContainer()->get('bl_service');
        $bucketService->uploadFile($object);
    }

    /**
     * @param mixed $object
     */
    public function preUpdate($object)
    {
        $this->prePersist($object);
    }
}
