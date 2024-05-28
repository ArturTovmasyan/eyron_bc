<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/24/15
 * Time: 1:53 PM
 */


namespace AppBundle\Admin;

use AppBundle\Entity\Goal;
use AppBundle\Entity\GoalImage;
use AppBundle\Entity\Tag;
use AppBundle\Form\Type\BlMultipleFileType;
use AppBundle\Form\Type\BlMultipleVideoType;
use AppBundle\Form\Type\LocationType;
use AppBundle\Model\PublishAware;
use AppBundle\Traits\GoalAdminTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Class ModeratorGoalAdmin
 * @package AppBundle\Admin
 * TODO: if all ok need to have parent goalAdmin class
 */
class ModeratorGoalAdmin extends AbstractAdmin
{
    use GoalAdminTrait;

    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'updated',
    ];

    protected $formOptions = [
        'validation_groups' => ['goal']
    ];

    protected  $baseRouteName = 'moderator-goal';
    protected  $baseRoutePattern = 'moderator-goal';

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('merge', $this->getRouterIdParameter().'/merge');
    }

    /**
     * @param \Sonata\AdminBundle\Show\ShowMapper $showMapper
     *
     * @return void
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', null, ['label'=>'admin.label.name.id', 'template' => 'AppBundle:Admin:goal_show_link.html.twig'])
            ->add('title', null, ['label'=>'admin.label.name.title'])
            ->add('author', null, ['template' => 'AppBundle:Admin:author_name_show.html.twig', 'label' => 'admin.label.name.author_name'])
            ->add('description', null, ['label'=>'admin.label.name.description'])
            ->add('tags', null, ['label'=>'admin.label.name.tags'])
            ->add('videoLink', null, ['template' => 'AppBundle:Admin:goal_video_show.html.twig', 'label'=>'admin.label.name.videoLink'])
            ->add('images', null, ['template' => 'AppBundle:Admin:goal_image_show.html.twig', 'label'=>'admin.label.name.images'])
        ;
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {

        $formMapper
            ->add('title', null, ['required' => true, 'label'=>'admin.label.name.title'])
            ->add('description', TextareaType::class, ['required' => false, 'label'=>'admin.label.name.description', 'attr'=> ['rows'=>8]])
            ->add('tags', null, ['label'=>'admin.label.name.tags'])
            ->add('slug', null, ['label'=>'admin.label.name.slug', 'required' => false])
            ->add('publish', null, ['label'=>'admin.label.name.publish'])
            ->add('archived', null, ['label'=>'admin.label.name.archived'])
            ->add('mergedGoalId', null, ['label'=>'admin.label.name.merged_id'])
            ->add('rawLocation', LocationType::class, ['label' => false])
            ->add('videoLink', BlMultipleVideoType::class, ['label' => false])
            ->add('language', ChoiceType::class, ['required' => true, 'choices' => ['en' => 'en', 'ru' => 'ru']])
            ->add('bl_multiple_file', BlMultipleFileType::class, ['label'=>'admin.label.name.images', 'required' => false]
            );
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        //disable listener for stats count
        $this->getConfigurationPool()->getContainer()->get('bl.doctrine.listener')->disableUserStatsLoading();

        $datagridMapper
            ->add('author.email', null, ['label'=>'admin.label.name.author_email', 'show_filter' => true])
            ->add('author.firstname', null, ['label'=>'admin.label.name.author_first_name', 'show_filter' => true])
            ->add('author.lastname', null, ['label'=>'admin.label.name.author_last_name', 'show_filter' => true])
            ->add('id', null, ['label'=>'admin.label.name.id', 'show_filter' => true])
            ->add('title', null, ['label'=>'admin.label.name.title','show_filter' => true])
            ->add('slug', null, ['label'=>'admin.label.name.slug','show_filter' => true])
            ->add('description', null, ['label'=>'admin.label.name.description','show_filter' => true])
            ->add('tags', null, ['label'=>'admin.label.name.tags','show_filter' => true])
            ->add('videoLink', null, ['label'=>'admin.label.name.videoLink','show_filter' => true])
            ->add('archived', null, ['label'=>'admin.label.name.archived','show_filter' => true])
            ->add('mergedGoalId', null, ['label'=>'admin.label.name.merged_id','show_filter' => true])
            ->add('status', null, ['label'=>'admin.label.name.goal_public', 'show_filter' => true, 'editable' => true])
            ->add('created', 'doctrine_orm_callback', [
                'show_filter' => true,
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
                'label'=>'admin.label.name.created'
            ], 'date', ['widget' => 'single_text']
            )
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        //disable goal archived filters
        $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager()->getFilters()->disable('archived_goal_filter');

        $listMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('title', null, ['label'=>'admin.label.name.title'])
            ->add('author', null, ['template' => 'AppBundle:Admin:author_name_list.html.twig', 'label' => 'admin.label.name.author_name']
            )
            ->add('tags', null, ['label'=>'admin.label.name.tags'])
            ->add('archived', null, ['label'=>'admin.label.name.archived'])
            ->add('mergedGoalId', null, ['label'=>'admin.label.name.merged_id'])
            ->add('getListPhoto', null, ['template' => 'AppBundle:Admin:goal_image_list.html.twig', 'label'=>'admin.label.name.getListPhoto']
            )
            ->add('videoLink', null, ['template' => 'AppBundle:Admin:goal_video_list.html.twig', 'label'=>'admin.label.name.videoLink']
            )
            ->add('created', null, ['label'=>'admin.label.name.created'])
            ->add('updated', null, ['label'=>'admin.label.name.updated'])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => ['template' => 'AppBundle:Admin:goal_list_action_show.html.twig'],
                    'edit' => ['template' => 'AppBundle:Admin:goal_list_action_edit.html.twig'],
                    'delete' => ['template' => 'AppBundle:Admin:goal_list_action_delete.html.twig'],
                    'goal_link' => ['template' => 'AppBundle:Admin:goal_list_action_link.html.twig'],
                    'merge' => ['template' => 'AppBundle:Admin:goal_merge_action.html.twig'],
                ]
            ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        $original = $this->getModelManager()->getEntityManager($this->getClass())->getUnitOfWork()->getOriginalEntityData($object);

        if((!isset($original['publish']) || $original['publish'] != $object->getPublish()) && $object->getPublish() == PublishAware::PUBLISH){
            $this->getRequest()->getSession()
                ->getFlashBag()
                ->set('goalPublished','Goal published from Web')
            ;
        }

        $this->updateData($object);

    }


    /**
     * @param string $context
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    public function createQuery($context = 'list') {
        $query = parent::createQuery($context);

        // add selected
        $query->addSelect('sc, im, tg, at');
        $query->leftJoin($query->getRootAlias() . '.successStories', 'sc');
        $query->leftJoin($query->getRootAlias() . '.images', 'im');
        $query->leftJoin($query->getRootAlias() . '.tags', 'tg');
        $query->leftJoin($query->getRootAlias() . '.author', 'at');
        $query->andWhere($query->expr()->eq($query->getRootAliases()[0] . '.status', ':privateStatus'))
                ->setParameter('privateStatus', Goal::PRIVATE_PRIVACY);

        return $query;
    }

}