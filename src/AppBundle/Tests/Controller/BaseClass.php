<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\UserGoal;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseClass extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $container;

    /**
     * @var null
     */
    protected $client = null;

    /**
     * @var null
     */
    protected $client2 = null;

    /**
     * @var null
     */
    protected $client4 = null;

    /**
     * @var null
     */
    protected $client11 = null;

    /**
     * @var null
     */
    protected $clientSecond = null;

    /**
     * this function create default client for testes
     *
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();
        $this->container = static::$kernel->getContainer();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin@admin.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));
        $this->client->enableProfiler();


        $this->client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user1@user.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));
        $this->client2->enableProfiler();

        $this->client4 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user4@user.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));
        $this->client4->enableProfiler();

        $this->client11 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user11@user.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));
        $this->client11->enableProfiler();

        $this->clientSecond = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user@user.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));
        $this->clientSecond->enableProfiler();
    }

    /**
     * this function create filter Provider data , client for testes
     */
    public function filterProvider()
    {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin@admin.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));
        $this->client->enableProfiler();

        $data = array(
            array( array('request'=> array(
                'filterData'=>array('filter' => array(
                    'f_' . UserGoal::URGENT_IMPORTANT => 'on',
                    'd'=>false)),
                'urlPart'=>'/active-goals'),
                'response'=>array('statusCode'=>Response::HTTP_OK, 'goalName'=>'goal1', 'resultCount'=>2))),

            array( array('request'=> array(
                'filterData'=>array('filter' => array(
                    'f_' . UserGoal::URGENT_IMPORTANT => 'on',
                    'f_' . UserGoal::URGENT_NOT_IMPORTANT => 'on',
                    'd'=>true),
                ), 'urlPart'=>'/completed-goals'),
                'response'=>array('statusCode'=>Response::HTTP_OK, 'goalName'=>null, 'resultCount'=>0))),

            array( array('request'=> array(
                'filterData'=>array('f_' . UserGoal::URGENT_IMPORTANT => 'on',
                    'f_' . UserGoal::URGENT_NOT_IMPORTANT => 'on',
                    'f_' . UserGoal::NOT_URGENT_IMPORTANT => 'on',
                    'd'=>true),
                'urlPart'=>null),
                'response'=>array('statusCode'=>Response::HTTP_OK, 'goalName'=>'goal1', 'resultCount'=>2))),

            array( array('request'=> array(
                'filterData'=>array('f_' . UserGoal::NOT_URGENT_IMPORTANT => 'on',
                    'f_' . UserGoal::NOT_URGENT_NOT_IMPORTANT=> 'on',
                    'd'=>true),
                'urlPart'=>null),
                'response'=>array('statusCode'=>Response::HTTP_OK, 'goalName'=>'goal3', 'resultCount'=>1)))
        );

        return $data;
    }

    /**
     * this function create file Provider data , client for testes
     */
    public function fileProvider()
    {
        self::bootKernel();
        $this->container = static::$kernel->getContainer();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin@admin.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));
        $this->client->enableProfiler();

        $files = $this->em->getRepository('AppBundle:GoalImage')->findAll();

        $fileNames = array();

        $file = reset($files);

        $fileNames[] =
            array(
                'file'. 0 => $file->getFileName()
            );

        return $fileNames;
    }

    /**
     * this function create all file Provider data , client for testes
     */
    public function allFileProvider()
    {
        self::bootKernel();
        $this->container = static::$kernel->getContainer();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin@admin.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));
        $this->client->enableProfiler();

        $files = $this->em->getRepository('AppBundle:GoalImage')->findAll();

        $fileNames = array();

        $file = reset($files);

        $fileNames[] =
            array(
                'file'. 1 => $file->getId()
            );


        return $fileNames;
    }

    /**
     * this function create goal data provider , client for testes
     */
    public function goalProvider()
    {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin@admin.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));
        $this->client->enableProfiler();

        $goals = $this->em->getRepository('AppBundle:Goal')->findAll();

        $goalIds = array();

        for($i = 0; $i<count($goals); $i++)
        {
            $goalIds[] =
                array(
                    'file'.$i => $goals[$i]->getSlug()
                );

        }

        return $goalIds;
    }

    /**
     * this function create goal data provider , client for testes
     */
    public function goalByIdProvider()
    {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin@admin.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));
        $this->client->enableProfiler();

        $goals = $this->em->getRepository('AppBundle:Goal')->findAll();

        $goalIds = array();

        for($i = 0; $i<count($goals); $i++)
        {
            $goalIds[] =
                array(
                    'file'.$i => $goals[$i]->getId()
                );
        }

        return $goalIds;
    }

    /**
     * this function create user goal data provider , client for testes
     */
    public function userGoalProvider()
    {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin@admin.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));
        $this->client->enableProfiler();

        $userGoals = $this->em->getRepository('AppBundle:UserGoal')->findAll();

        $userGoalIds = array();

        $userGoal = reset($userGoals);

        $userGoalIds[] =
            array(
                'file'. 1 => $userGoal->getId()
            );

        return $userGoalIds;
    }

    /**
     * This data provider create data for user-settings create
     * @return array
     */
    public function userSettingsProvider()
    {
        self::bootKernel();
        $this->container = static::$kernel->getContainer();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user@user.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));
        $this->client->enableProfiler();

        $data = array(
            array( array('request'=>array('bl_mobile_user_settings'=>array('firstName'=>'Poxos',
                'lastName'=>'',
                'primary'=>false,
                'addEmail'=>'test@test.ru',
                'birthDate'=>'2015/01/22')), 'response'=>array('statusCode'=>Response::HTTP_BAD_REQUEST))),
            array( array('request'=>array('bl_mobile_user_settings'=>array('firstName'=>'',
                'lastName'=>'Poxosyan',
                'primary'=>false,
                'addEmail'=>'test@test.ru',
                'birthDate'=>'2015/01/22')), 'response'=>array('statusCode'=>Response::HTTP_BAD_REQUEST))),
            array( array('request'=>array('bl_mobile_user_settings'=>array('firstName'=>'Poxos',
                'lastName'=>'Poxosyan',
                'primary'=>false,
                'addEmail'=>'test@test.ru',
                'birthDate'=>'2015/01/22')), 'response'=>array('statusCode'=> Response::HTTP_OK)))
        );

        return $data;
    }


    /**
     * This data provider create data for user-settings create
     * @return array
     */
    public function userChangePasswordProvider()
    {
        self::bootKernel();
        $this->container = static::$kernel->getContainer();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user@user.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));
        $this->client->enableProfiler();

        $this->clientSecond = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user@user.com',
            'PHP_AUTH_PW'   => 'Test4321',
        ));
        $this->clientSecond->enableProfiler();

        $data = array(
            array( array('request'=> array('bl_mobile_change_password'=>array('currentPassword'=>'Test4321',
                'plainPassword'=>array('first'=>'Test1234', 'second'=>'Test1234'))),
                'response'=>array('statusCode'=>Response::HTTP_BAD_REQUEST), 'client'=>$this->client)),

            array( array('request'=> array('bl_mobile_change_password'=>array('currentPassword'=>'Test1234',
                'plainPassword'=>array('first'=>'Test4321', 'second'=>'Test4321'))),
                'response'=>array('statusCode'=>Response::HTTP_OK), 'client'=>$this->client)),

            array( array('request'=> array('bl_mobile_change_password'=>array('currentPassword'=>'Test4321',
                'plainPassword'=>array('first'=>'Test1234', 'second'=>''))),
                'response'=>array('statusCode'=>Response::HTTP_BAD_REQUEST), 'client'=>$this->clientSecond)),

            array( array('request'=> array('bl_mobile_change_password'=>array('currentPassword'=>'Test4321',
                'plainPassword'=>array('first'=>'Test1234', 'second'=>'Test1234'))),
                'response'=>array('statusCode'=>Response::HTTP_OK), 'client'=>$this->clientSecond))
        );
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        $this->container->get('doctrine')->getConnection()->close();
        $this->em->close();
        $this->em = null; // avoid memory leaks
        parent::tearDown();
    }
}