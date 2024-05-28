<?php

namespace AppBundle\Admin;

use AppBundle\Entity\StoryImage;
use AppBundle\Form\StoryImageType;
use AppBundle\Form\Type\BlMultipleVideoType;
use AppBundle\Form\Type\StoryMultipleFileType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class SuccessStoryAdmin extends AbstractAdmin
{
    /**
     * override list query
     *
     * @param string $context
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    public function createQuery($context = 'list')
    {
        // call parent query
        $query = parent::createQuery($context);
        // add selected
        $query->addSelect('gl, f');
        $query->leftJoin($query->getRootAlias() . '.goal', 'gl');
        $query->leftJoin($query->getRootAlias() . '.files', 'f');

        return $query;
    }

    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'updated',
    ];

    /**
     * @param string $name
     * @return mixed|null|string
     */
    public function getTemplate($name)
    {
        switch ($name) {
            case 'list':
                return 'AppBundle:Admin:success_story_list.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }
    
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('created', 'doctrine_orm_date_range', ['label' => 'admin.label.name.created', 'show_filter' => true], 'sonata_type_date_range_picker',
                [
                    'field_options_start'=> ['format'=>'yyyy-MM-dd'],
                    'field_options_end'=> ['format'=>'yyyy-MM-dd']
                ]
            )
            ->add('story', null, ['label'=>'admin.label.name.story'])
            ->add('goal.title', null, ['label'=>'admin.label.name.goal'])
            ->add('isInspire', null, ['label'=>'admin.label.name.is_inspire'])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('created', null, ['label'=>'admin.label.name.created'])
            ->add('updated', null, ['label'=>'admin.label.name.updated'])
            ->addIdentifier('goal.title', null, ['label'=>'admin.label.name.goal'])
            ->add('story', null, ['label'=>'admin.label.name.story', 'template' => 'AppBundle:Admin:success_story_list_field.html.twig'])
            ->add('isInspire', null, ['label'=>'admin.label.name.is_inspire', 'editable' => true])
            ->add('files', null, ['label'=>'admin.label.name.images', 'template' => 'AppBundle:Admin:success_story_image_list.html.twig'])
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                    'goal_link' => ['template' => 'AppBundle:Admin:success_story_list_action_link.html.twig'],
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
            ->add('created', DateType::class, ['label'=>'admin.label.name.created', 'widget' => 'single_text'])
            ->add('updated', DateType::class, ['label'=>'admin.label.name.updated', 'widget' => 'single_text'])
            ->add('story', null ,['label'=>'admin.label.name.story'])
            ->add('isInspire', null, ['label'=>'admin.label.name.is_inspire'])
            ->add('videoLink', BlMultipleVideoType::class, ['label'=>'admin.label.name.video_link'])
            ->add('files', StoryMultipleFileType::class, ['label'=>'admin.label.name.images'])
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('story_goal', null, ['template' => 'AppBundle:Admin:success_story_show_link.html.twig', 'mapped' => false])
            ->add('created', null, ['label'=>'admin.label.name.created'])
            ->add('updated', null, ['label'=>'admin.label.name.updated'])
            ->add('story', null, ['label'=>'admin.label.name.story'])
            ->add('isInspire', null, ['label'=>'admin.label.name.is_inspire'])
            ->add('files', null, ['label'=>'admin.label.name.images', 'template' => 'AppBundle:Admin:success_story_image_show.html.twig'])
            ->add('videoLink', null, ['label'=>'admin.label.name.video_link', 'template' => 'AppBundle:Admin:goal_video_show.html.twig'])
        ;
    }

    /**
     * @param mixed $object
     */
    public function preUpdate($object)
    {
        $videoLink = array_values($object->getVideoLink());
        $object->setVideoLink(array_filter($videoLink));

        $bucketService = $this->getConfigurationPool()->getContainer()->get('bl_service');
        $images = $object->getFiles();

        if($images) {
            foreach($images as $image) {
                if (!($image instanceof StoryImage)){
                    $object->removeFile($image);
                    continue;
                }

                $bucketService->uploadFile($image);
                $image->setStory($object);
            }
        }
    }

    /**
     * @param mixed $object
     */
    public function prePersist($object)
    {
        $this->preUpdate($object);
    }
}
