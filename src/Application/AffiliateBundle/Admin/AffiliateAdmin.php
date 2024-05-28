<?php

namespace Application\AffiliateBundle\Admin;

use Application\AffiliateBundle\Entity\Affiliate;
use Application\AffiliateBundle\Entity\AffiliateType;
use Application\AffiliateBundle\Form\Type\AdminFileType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AffiliateAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, ['label' => 'admin.label.name.id'])
            ->add('name', null, ['label' => 'admin.label.name.name'])
            ->add('link', null, ['label' => 'admin.label.name.link'])
            ->add('links', null, ['label' => 'admin.label.name.link'])
            ->add('ufi', null, ['label' => 'admin.label.name.ufi'])
            ->add('placeType', null, ['label' => 'admin.label.name.place_type'],  ChoiceType::class, ['required' => false,
                'choices' => [
                    Affiliate::CITY_TYPE    => 'admin.label.name.city',
                    Affiliate::REGION_TYPE  => 'admin.label.name.region',
                    Affiliate::COUNTRY_TYPE => 'admin.label.name.country',
                ]
            ])
            ->add('affiliateType', null, ['label' => 'admin.label.name.affiliate_type'])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        AffiliateType::$bookingAId = $this->getConfigurationPool()->getContainer()->getParameter('booking_aid');

        $listMapper
            ->add('publish', null, ['label' => 'admin.label.name.publish', 'editable' => true])
            ->add('name', null, ['label' => 'admin.label.name.name'])
            ->add('ufi', null, ['label' => 'admin.label.name.ufi'])
            ->add('placeType', null, ['label' => 'admin.label.name.place_type'])
            ->add('links', null, ['label' => 'admin.label.name.links', 'template' => 'ApplicationAffiliateBundle:Admin:listLinks.html.twig'])
            ->add('affiliateType.name', null, ['label' => 'admin.label.name.type_name'])
            ->add('affiliateType.id', null, ['label' => 'admin.label.name.content', 'template' => 'ApplicationAffiliateBundle:Admin:affiliateList.html.twig'])
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
            ->add('name', TextType::class, ['label' => 'admin.label.name.name', 'required' => false])
            ->add('link', TextType::class, ['label' => 'admin.label.name.link', 'required' => false])
            ->add('ufi', TextType::class, ['label' => 'admin.label.name.ufi', 'required' => false])
            ->add('placeType', ChoiceType::class, ['label' => 'admin.label.name.place_type', 'required' => false,
                'choices' => [
                    Affiliate::CITY_TYPE    => 'admin.label.name.city',
                    Affiliate::REGION_TYPE  => 'admin.label.name.region',
                    Affiliate::COUNTRY_TYPE => 'admin.label.name.country',
                ]
            ])
            ->add('affiliateType', null, ['label' => 'admin.label.name.affiliate_type'])
            ->add('publish', null, ['label' => 'admin.label.name.publish'])
            ->add('links', CollectionType::class, [
                'label' => 'admin.label.name.links',
                'entry_type'   => TextType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required'     => false
            ]
            )
            ->add('sizeString', TextType::class, ['label' => 'admin.label.name.size', 'required' => false])
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
            ->add('publish', null, ['label' => 'admin.label.name.publish'])
            ->add('sizeString', null, ['label' => 'admin.label.name.size'])
            ->add('link', null, ['label' => 'admin.label.name.link'])
            ->add('ufi', null, ['label' => 'admin.label.name.ufi'])
            ->add('placeType', null, ['label' => 'admin.label.name.place_type'])
            ->add('links', null, ['label' => 'admin.label.name.links'])
            ->add('affiliateType.name', null, ['label' => 'admin.label.name.type_name'])
            ->add('affiliateType.id', null, ['label' => 'admin.label.name.type_id', 'template' => 'ApplicationAffiliateBundle:Admin:affiliateList.html.twig'])
        ;
    }

    /**
     * @param mixed $object
     */
    public function prePersist($object)
    {
        $container            = $this->getConfigurationPool()->getContainer();
        $bucketService        = $container->get('bl_service');
        $liipManager          = $container->get('liip_imagine.cache.manager');
        $filterConfiguration  = $container->get('liip_imagine.filter.configuration');
        $configuration        = $filterConfiguration->get('affiliate_image');
        $imagemanagerResponse = $container->get('liip_imagine.controller');

        $bucketService->uploadFile($object);

        if ($object->getSize()) {

            $liipManager->remove($object->getDownloadLink(), 'affiliate_image');
            $configuration['filters']['thumbnail']['size'] = $object->getSize();
            $filterConfiguration->set('affiliate_image', $configuration);

            $imagemanagerResponse->filterAction($this->getRequest(), $object->getDownloadLink(), 'affiliate_image');
        }
    }

    /**
     * @param mixed $object
     */
    public function preUpdate($object)
    {
        $this->prePersist($object);
    }
}
