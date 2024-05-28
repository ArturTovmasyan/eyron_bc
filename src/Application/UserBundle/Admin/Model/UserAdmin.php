<?php
/**
 * Created by PhpStorm.
 * User: artur
 * Date: 28/06/2016
 * Time: 14:24 PM
 */

namespace Application\UserBundle\Admin\Model;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\UserBundle\Model\UserManagerInterface;


class UserAdmin extends AbstractAdmin
{
    protected $baseRouteName    = 'admin-user';
    protected $baseRoutePattern = 'admin-user';
    public    $usersCount       = 0;

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('sendMessage', 'send-message');
    }

    /**
     * @param string $name
     * @return mixed|null|string
     */
    public function getTemplate($name)
    {
        switch ($name) {
            case 'list':
                return 'ApplicationUserBundle:Admin:user_list.html.twig';
                break;
            case 'show':
                return 'ApplicationUserBundle:Admin:user_show.html.twig';
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
            ->tab('General')
            ->with('User')
            ->add('id', null, ['label' =>'show.label_id'])
            ->add('email', null, ['label' => 'show.label_email'])
            ->add('firstname', null, ['label'=>'show.label_firstname'])
            ->add('lastname', null, ['label' => 'show.label_lastname'])
            ->add('picture', null, ['label' => 'show.label_picture', 'template' => 'ApplicationUserBundle:Admin:user_show_picture.html.twig'])
            ->add('profile', null, ['label' => 'show.label_profile', 'template' => 'ApplicationUserBundle:Admin:user_show_profile_link.html.twig'])
            ->add('userSocial', null, ['label' => 'show.label_user_social', 'template' => 'ApplicationUserBundle:Admin:user_social_icon_show.html.twig'])
            ->add('userMobileOs', null, ['label' => 'show.label_user_mobile', 'template' => 'ApplicationUserBundle:Admin:user_mobile_os_icon_show.html.twig'])
            ->add('registrationIds', null, ['label' => 'show.label_registration_ids', 'template' => 'ApplicationUserBundle:Admin:registration_ids_show.html.twig'])
            ->add('enabled', null, ['label' => 'form.label_enabled'])
            ->add('deleteReason', null, ['label' => 'form.delete_reason'])
            ->add('listedGoals', null, ['label' => 'show.label_listed_goal', 'template' => 'ApplicationUserBundle:Admin:user_show_listed_goal_count.html.twig'])
            ->add('createdGoals', null, ['label' => 'show.label_created_goal', 'template' => 'ApplicationUserBundle:Admin:user_show_created_goal.html.twig'])
            ->add('successStory', null, ['label' => 'show.label_story_count', 'template' => 'ApplicationUserBundle:Admin:user_show_goal_story.html.twig'])
            ->add('sex', null, ['label' => 'show.label_sex'])
            ->add('lastLogin', null, ['label' => 'show.label_last_login'])
            ->add('createdAt', 'datetime', ['label' => 'show.label_reg_date'])
            ->end()
            ->end()
            ->tab('Emails')
            ->with('Sent Emails')
            ->add('sentEmails', null, ['label' => 'show.label_sent_email', 'template' => 'ApplicationUserBundle:Admin:user_emails.html.twig'])
            ->end()
            ->end()
        ;
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {

        $formMapper
            ->add('email', null, ['label'=>'show.label_email'])
            ->add('plainPassword', 'repeated', [
                'first_name' => 'password',
                'required' => true,
                'second_name' => 'confirm',
                'type' => 'password',
                'invalid_message' => 'Passwords do not match',
                'first_options' => ['label' => 'admin.label.name.password'],
                'second_options' => ['label' => 'admin.label.name.repeat_password']] )
            ->add('firstname', null, ['label'=>'show.label_firstname'])
            ->add('lastname', null, ['label'=>'show.label_lastname'])
            ->add('enabled', null, ['label'=>'form.label_enabled'])
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        //disable listener for stats count
        $this->getConfigurationPool()->getContainer()->get('bl.doctrine.listener')->disableUserStatsLoading();

        $datagridMapper
            ->add('id', null, ['label'=>'show.label_id', 'show_filter' => true])
            ->add('registrationIds', null, ['label' => 'show.label_user_mobile', 'show_filter' => true],
                'choice', ['choices'=>['android' => 'admin.label.name.android', 'ios'=>'admin.label.name.ios']
                ])
            ->add('deviceId', 'doctrine_orm_callback', [
                'mapped' => false,
                'show_filter' => true,
                'callback' => function($queryBuilder, $alias, $field, $value) {
                    if (!$value['value']) {
                        return;
                    }
                    $queryBuilder
                        ->andWhere($alias . ".registrationIds LIKE :value")
                        ->setParameter('value', '%'.$value['value'].'%');
                    return true;
                },
                'label'=>'show.label_registration_ids'
            ], 'text')
            ->add('email', null, ['label'=>'show.label_email_username','show_filter' => true])
            ->add('enabled', null, ['label'=>'form.label_enabled','show_filter' => true])
            ->add('firstname', null, ['label'=>'show.label_firstname','show_filter' => true])
            ->add('lastname', null, ['label'=>'show.label_lastname','show_filter' => true])
            ->add('createdAt','doctrine_orm_date_range', ['label' => 'form.label_created_at', 'show_filter' => true], 'sonata_type_date_range_picker',
                [
                    'field_options_start' => ['format' => 'yyyy-MM-dd'],
                    'field_options_end' => ['format' => 'yyyy-MM-dd']
                ]
            );
    }

    protected function configureListFields(ListMapper $listMapper)
    {

        $listMapper
            ->add('id', null, ['label' => 'show.label_id'])
            ->add('username', null, ['label'=>'show.label_username'])
            ->add('firstname', null, ['label'=>'show.label_firstname'])
            ->add('lastname', null, ['label'=>'show.label_lastname'])
            ->add('userSocial', null, ['label'=>'show.label_user_social', 'template' => 'ApplicationUserBundle:Admin:user_social_icon.html.twig'])
            ->add('userMobileOs', null, ['label' => 'show.label_user_mobile','template' => 'ApplicationUserBundle:Admin:user_mobile_os_icon_list.html.twig'])
            ->add('enabled', null, ['label'=>'form.label_enabled'])
            ->add('createdAt', 'datetime', ['label' => 'show.label_created_at'])
            ->add('_action', 'actions', [
                    'label' => 'show.label_actions',
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
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $container = $this->getConfigurationPool()->getContainer();

        $user = $container->get('security.token_storage')->getToken()->getUser();
        $em = $container->get('doctrine')->getManager();
        $this->usersCount = $em->getRepository('ApplicationUserBundle:User')->findAllCount();

        $query = parent::createQuery($context);

        //check if user has ROLE_GOD
        if (!$user->hasRole('ROLE_GOD')) {

            $query->andWhere($query->getRootAliases()[0] . ".roles LIKE :roleAdmin or " . $query->getRootAliases()[0] . ".roles LIKE :roleSuper");
            $query->setParameter('roleAdmin', '%ROLE_SUPER_ADMIN%');
            $query->setParameter('roleSuper', '%ROLE_ADMIN%');
        }

        return $query;
    }

    public function prePersist($object)
    {
        $object->addRole("ROLE_ADMIN");
        $object->addRole("ROLE_SUPER_ADMIN");
    }

    /**
     * @param UserManagerInterface $userManager
     */
    public function setUserManager(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @return UserManagerInterface
     */
    public function getUserManager()
    {
        return $this->userManager;
    }
}