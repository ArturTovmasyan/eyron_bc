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
use AppBundle\Services\UserNotifyService;
use AppBundle\Traits\GoalAdminTrait;
use Application\UserBundle\Entity\Badge;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Class GoalAdmin
 * @package AppBundle\Admin
 */
class GoalAdmin extends AbstractAdmin
{
    use GoalAdminTrait;

    protected $formOptions = [
        'validation_groups' => ['goal']
    ];

    protected  $baseRouteName = 'admin-goal';
    protected  $baseRoutePattern = 'admin-goal';

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
        $query->addSelect('sc, im, tg, at, pl');
        $query->leftJoin($query->getRootAlias() . '.successStories', 'sc');
        $query->leftJoin($query->getRootAlias() . '.images', 'im');
        $query->leftJoin($query->getRootAlias() . '.tags', 'tg');
        $query->leftJoin($query->getRootAlias() . '.author', 'at');
        $query->leftJoin($query->getRootAlias() . '.place', 'pl');

        return $query;
    }

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
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('title', null, ['label'=>'admin.label.name.title'])
            ->add('featuredDate', null, ['label'=>'admin.label.name.featured_date'])
            ->add('author', null, ['template' => 'AppBundle:Admin:author_name_show.html.twig', 'label' => 'admin.label.name.author_name']
            )
            ->add('description', null, ['label'=>'admin.label.name.description'])
            ->add('tags', null, ['label'=>'admin.label.name.tags'])
            ->add('videoLink', null, ['template' => 'AppBundle:Admin:goal_video_show.html.twig', 'label'=>'admin.label.name.videoLink']
            )
            ->add('images', null, ['template' => 'AppBundle:Admin:goal_image_show.html.twig', 'label'=>'admin.label.name.images']
            )
        ;
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title', null, ['required' => true, 'label'=>'admin.label.name.title'])
            ->add('description', TextareaType::class, ['required' => false, 'label'=>'admin.label.name.description', 'attr'=> ['rows'=>8]])
            ->add('featuredDate', 'date', ['widget' => 'single_text', 'label'=>'admin.label.name.featured_date', 'required' => false])
            ->add('tags', 'sonata_type_model_autocomplete', ['label'=>'admin.label.name.tags', 'property'=>'tag', 'multiple' => true, 'required' => false])
            ->add('slug', null, ['label'=>'admin.label.name.slug', 'required' => false])
            ->add('publish', null, ['label'=>'admin.label.name.publish'])
            ->add('status', null, ['label'=>'admin.label.name.goal_status'])
            ->add('archived', null, ['label'=>'admin.label.name.archived'])
            ->add('place', 'sonata_type_model_autocomplete', ['label'=>'admin.label.name.place', 'property'=>'name', 'multiple' => true, 'required' => false])
//            ->add('mergedGoalId', null, ['label'=>'admin.label.name.merged_id'])
            ->add('rawLocation', LocationType::class, ['label' => false])
            ->add('videoLink', BlMultipleVideoType::class, ['label' => false])
            ->add('language', ChoiceType::class, ['label'=>'admin.label.name.language', 'required' => true, 'choices' => ['en' => 'admin.label.name.en', 'ru' => 'admin.label.name.ru']])
            ->add('bl_multiple_file', BlMultipleFileType::class, ['label'=>'admin.label.name.images', 'required' => false])
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        //get container
        $container = $this->getConfigurationPool()->getContainer();

        //disable listener for stats count
        $container->get('bl.doctrine.listener')->disableUserStatsLoading();

        $datagridMapper
            ->add('author.email', null, ['label'=>'admin.label.name.author_email'])
            ->add('author.firstname', null, ['label'=>'admin.label.name.author_first_name'])
            ->add('author.lastname', null, ['label'=>'admin.label.name.author_last_name'])
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('publish', null, ['label'=>'admin.label.name.publish'])
            ->add('title', null, ['label'=>'admin.label.name.title'])
            ->add('slug', null, ['label'=>'admin.label.name.slug'])
            ->add('description', null, ['label'=>'admin.label.name.description'])
            ->add('featuredDate', null, ['widget' => 'single_text', 'label'=>'admin.label.name.featured_date'])
            ->add('tags.tag', null, ['label'=>'admin.label.name.tags'])
            ->add('videoLink', null, ['label'=>'admin.label.name.videoLink'])
            ->add('archived', null, ['label'=>'admin.label.name.archived'])
//            ->add('mergedGoalId', null, ['label'=>'admin.label.name.merged_id'])
            ->add('status', null, ['label'=>'admin.label.name.goal_public', 'editable' => true])


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
            ], 'date', ['widget' => 'single_text'])
            ->add('updated', 'doctrine_orm_callback', [
                'show_filter' => true,
                'callback' => function($queryBuilder, $alias, $field, $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $queryBuilder
                        ->andWhere("DATE(" . $alias . ".updated) = DATE(:value)")
                        ->setParameter('value', $value['value'])
                    ;

                    return true;
                },
                'label'=>'admin.label.name.updated'
            ], 'date', ['widget' => 'single_text'])

        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        //get container
        $container = $this->getConfigurationPool()->getContainer();

        //disable goal archived filters
        $container->get('doctrine')->getManager()->getFilters()->disable('archived_goal_filter');

        $listMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('publish', null, ['editable' => true, 'label'=>'admin.label.name.publish'])
            ->add('status', null, ['editable' => true, 'label'=>'admin.label.name.goal_status'])
            ->add('title', null, ['label'=>'admin.label.name.title'])
            ->add('author', null, ['template' => 'AppBundle:Admin:author_name_list.html.twig', 'label' => 'admin.label.name.author_name']
            )
            ->add('tags', null, ['label'=>'admin.label.name.tags'])
            ->add('place', null, ['label'=>'admin.label.name.place'])
            ->add('archived', null, ['label'=>'admin.label.name.archived'])
//            ->add('mergedGoalId', null, ['label'=>'admin.label.name.merged_id'])
            ->add('getListPhoto', null, ['template' => 'AppBundle:Admin:goal_image_list.html.twig', 'label'=>'admin.label.name.getListPhoto']
            )
            ->add('videoLink', null, ['template' => 'AppBundle:Admin:goal_video_list.html.twig', 'label'=>'admin.label.name.videoLink']
            )
            ->add('created', 'date', ['label'=>'admin.label.name.created', 'pattern' => 'yyyy-MM-dd'])
            ->add('updated', 'date', ['label'=>'admin.label.name.updated', 'pattern' => 'yyyy-MM-dd'])
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

            if ($object->getSlug() && $object->getAuthor()) {
                $container = $this->getConfigurationPool()->getContainer();
                $link = $container->get('router')->generate('inner_goal', ['slug' => $object->getSlug()]);
                $body = $container->get('translator')->trans('notification.publish_goal', ['%goal%' => $object->getTitle()], null, 'en');
                $container->get('bl_notification')->sendNotification(null, $link, $object->getId(), $body, $object->getAuthor());

                // get user notify service
                $container->get('user_notify')->sendNotifyToUser($object->getAuthor(),
                    UserNotifyService::PUBLISH_GOAL,
                    ['goalId'=> $object->getId()]);

            }
        }

        $this->updateData($object);

        // add badge
        $this->addBadge($object, $original);

    }

    /**
     * @param $object
     */
    private function addBadge($object, $originObject)
    {
        if (count($originObject) == 0){
            return;
        }

        $addBadge = 0;
        $removeBadge = 0;

        if($originObject['publish'] == false && $object->getPublish() == true){
            $addBadge++;
        }
        elseif ($originObject['publish'] == true && $object->getPublish() == false){
            $removeBadge++;
        }

        if($object->getAuthor() && !$object->getAuthor()->isAdmin()){

            $badgeService = $this->getConfigurationPool()->getContainer()->get("app.goal");

            // check badge
            if($addBadge > 0){

                // add score for innovator
                $badgeService->addBadgeForPublish('bl.badge.service', 'addScore',
                    [Badge::TYPE_INNOVATOR, $object->getAuthor()->getId(), $addBadge]
                );
                // check badge
            }elseif($removeBadge > 0){

                // add score for innovator
                $badgeService->addBadgeForPublish('bl.badge.service', 'removeScore',
                    [Badge::TYPE_INNOVATOR, $object->getAuthor()->getId(), $removeBadge]
                );
            }
        }
    }
}