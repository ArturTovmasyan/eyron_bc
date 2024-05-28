<?php
/**
 * Created by PhpStorm.
 * User: armen
 * Date: 1/25/16
 * Time: 1:46 PM
 */

namespace Application\UserBundle\Tests\Controller\Rest;

use Application\UserBundle\Entity\Report;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{

    protected $container;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

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
    protected $client3 = null;

    /**
     * @var null
     */
    protected $clientAuthorized = null;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();
        $this->container = static::$kernel->getContainer();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->client = static::createClient();
        $this->client->enableProfiler();
        $this->clientAuthorized = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin@admin.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));
        $this->clientAuthorized->enableProfiler();

        $this->client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user1@user.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));
        $this->client2->enableProfiler();
    }

    /**
     *This function is used to check report rest
     *
     */
    public function testReport()
    {
        $url = '/api/v1.0/report';

        $comment = $this->em->getRepository('ApplicationCommentBundle:Comment')->findByBody('Comment2');

        $commentId = reset($comment)->getId();

        // try to register new user
        $this->client2->request('PUT', $url,
            array(
                'reportType' => Report::SPAM,
                'contentType' => Report::COMMENT,
                'contentId' => $commentId,
                'message' => 'It is bad user',
            )
        );

        // Assert that the response status code is 2xx
        $this->assertTrue($this->client2->getResponse()->isSuccessful(), "can not create report in report putAction rest!");

        $this->assertTrue(
            $this->client2->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client2->getResponse()->headers
        );

        if ($profile = $this->client2->getProfile()) {
            // check the number of requests
            $this->assertLessThan(8, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on POST postSettingsAction rest!");
        }
    }

    /**
     * This function is used to check Post function in rest
     */
    public function testPost()
    {
        $url = '/api/v1.0/users';

        $brochuresDir = str_replace('app', 'src', $this->container->getParameter('kernel.root_dir')).'/AppBundle/DataFixtures/ORM/';
        $oldPhotoPath = $brochuresDir . 'old_photo.jpg';
        $photoPath = $brochuresDir . 'photo.jpg';

        copy($oldPhotoPath, $photoPath);

        // new uploaded file
        $photo = new UploadedFile(
            $photoPath,
            'photo.jpg',
            'image/jpeg',
            123
        );

        // try to register new user
        $this->client->request('POST', $url,
            array(
                'email' => 'test@test.ko',
                'plainPassword' => 'Test1234',
                'firstName' => 'Test',
                'lastName' => 'Testyan',
                'birthday' => '01/12/1990',
            ),
            array('profile_image' => $photo)
        );

        // Assert that the response status code is 2xx
        $this->assertTrue($this->client->getResponse()->isSuccessful(), "can not register new user in postAction(user registration) rest!");

        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(8, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on POST postSettingsAction rest!");
        }

        //get response content
        $responseResults = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('sessionId', $responseResults, 'Invalid sessionId key in Registration rest json structure');

        $userInfo = $responseResults['userInfo'];

        $this->assertArrayHasKey('id', $userInfo, 'Invalid id key in Registration rest json structure');

        $this->assertArrayHasKey('username', $userInfo, 'Invalid username key in Registration rest json structure');

        $this->assertArrayHasKey('first_name', $userInfo, 'Invalid first_name key in Registration rest json structure');

        $this->assertArrayHasKey('last_name', $userInfo, 'Invalid last_name key in Registration rest json structure');

        $this->assertArrayHasKey('is_confirmed', $userInfo, 'Invalid is_confirmed key in Registration rest json structure');

        $this->assertArrayHasKey('show_name', $userInfo, 'Invalid show_name key in Registration rest json structure');

        $this->assertArrayHasKey('is_admin', $userInfo, 'Invalid is_admin key in Registration rest json structure');

        $this->assertArrayHasKey('u_id', $userInfo, 'Invalid u_id key in Registration rest json structure');

        $this->assertArrayHasKey('stats', $userInfo, 'Invalid stats key in Registration rest json structure');

        $stats = $userInfo['stats'];

        $this->assertArrayHasKey('listedBy', $stats, 'Invalid listedBy key in Registration rest json structure');

        $this->assertArrayHasKey('active', $stats, 'Invalid active key in Registration rest json structure');

        $this->assertArrayHasKey('doneBy', $stats, 'Invalid doneBy key in Registration rest json structure');

        if(array_key_exists('draft_count', $userInfo)) {
            $this->assertArrayHasKey('draft_count', $userInfo, 'Invalid draft_count key in Registration rest json structure');
        }
    }

    /**
     * This function is used to check postLogin function in rest
     */
    public function testPostLogin()
    {
        $url = '/api/v1.0/users/logins';

        // try to login
        $this->clientAuthorized->request('POST', $url,
            array(
                'username' => 'admin@admin.com',
                'password' => 'Test1234',
            )
        );

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientAuthorized->getResponse()->isSuccessful(), "can not login in postLoginAction rest!");

        $this->assertTrue(
            $this->clientAuthorized->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientAuthorized->getResponse()->headers
        );

        if ($profile = $this->clientAuthorized->getProfile()) {
            // check the number of requests
            $this->assertLessThan(7, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on POST postSettingsAction rest!");
        }

        //get response content
        $responseResults = json_decode($this->clientAuthorized->getResponse()->getContent(), true);

        $this->assertArrayHasKey('sessionId', $responseResults, 'Invalid sessionId key in Registration rest json structure');

        $userInfo = $responseResults['userInfo'];

        $this->assertArrayHasKey('id', $userInfo, 'Invalid id key in Login rest json structure');

        $this->assertArrayHasKey('username', $userInfo, 'Invalid username key in Login rest json structure');

        $this->assertArrayHasKey('first_name', $userInfo, 'Invalid first_name key in Login rest json structure');

        $this->assertArrayHasKey('last_name', $userInfo, 'Invalid last_name key in Login rest json structure');

        $this->assertArrayHasKey('is_confirmed', $userInfo, 'Invalid is_confirmed key in Login rest json structure');

        $this->assertArrayHasKey('show_name', $userInfo, 'Invalid show_name key in Login rest json structure');

        $this->assertArrayHasKey('is_admin', $userInfo, 'Invalid is_admin key in Login rest json structure');

        $this->assertArrayHasKey('u_id', $userInfo, 'Invalid u_id key in Login rest json structure');

        $this->assertArrayHasKey('stats', $userInfo, 'Invalid stats key in Login rest json structure');

        $stats = $userInfo['stats'];

        $this->assertArrayHasKey('listedBy', $stats, 'Invalid listedBy key in Login rest json structure');

        $this->assertArrayHasKey('active', $stats, 'Invalid active key in Login rest json structure');

        $this->assertArrayHasKey('doneBy', $stats, 'Invalid doneBy key in Login rest json structure');

        $this->assertArrayHasKey('draft_count', $userInfo, 'Invalid draft_count key in Login rest json structure');

        if(array_key_exists('image_path', $userInfo)) {

            $this->assertArrayHasKey('image_path', $userInfo, 'Invalid image_path key in Login rest json structure');
        }

    }

    /**
     * This function is used to check getRegistered function in rest
     */
    public function testGetRegistered()
    {
        // get user
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneByEmail('admin@admin.com');

        $url = sprintf('/api/v1.0/users/%s/registered', $user->getEmail());

        // try to get user registered status
        $this->clientAuthorized->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientAuthorized->getResponse()->isSuccessful(), "can not get user registered status in getRegisteredAction rest!");

        // assert that user registered status is true
        $this->assertContains(
            'true',
            $this->clientAuthorized->getResponse()->getContent()
        );

        $this->assertTrue(
            $this->clientAuthorized->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientAuthorized->getResponse()->headers
        );

        if ($profile = $this->clientAuthorized->getProfile()) {
            // check the number of requests
            $this->assertLessThan(4, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on POST postSettingsAction rest!");
        }

        $url = sprintf('/api/v1.0/users/%s/registered', 'nonExistentEmail');

        // try to get user registered status
        $this->clientAuthorized->request('GET', $url);

        // assert that user registered status is false
        $this->assertContains(
            'false',
            $this->clientAuthorized->getResponse()->getContent()
        );
    }

    /**
     * This function is used to check testGetUserStats function in rest
     */
    public function testGetUserStats()
    {
        // get user
        $userId = $this->em->getRepository('ApplicationUserBundle:User')->findOneByEmail('user1@user.com')->getId();

        $url = sprintf('/api/v1.0/users/%s/states', $userId);

        // try to register new user
        $this->client->request('GET', $url);

        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "Can not get user stats in rest!");

        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(2, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on POST postSettingsAction rest!");
        }

        //get response content
        $responseResults = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('listedBy', $responseResults, 'Invalid listedBy key in get user stats rest json structure');
        $this->assertArrayHasKey('doneBy', $responseResults, 'Invalid doneBy key in get user stats rest json structure');

    }

    /**
     * This function is used to check testRegisteredUserEmail function in rest
     */
    public function testRegisteredUserEmail()
    {
        $url = sprintf('/api/v1.0/users/%s/registered', 'user1@user.com');

        // try to register new user
        $this->client->request('GET', $url);

        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "Can not get user stats in rest!");

        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(3, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on POST postSettingsAction rest!");
        }

        //get response content
        $responseResults = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('registered', $responseResults, 'Invalid registered key in registered user email rest json structure');

        if(array_key_exists('image_path', $responseResults)) {
            $this->assertArrayHasKey('image_path', $responseResults, 'Invalid image_path key in registered user email rest json structure');
        }
    }

    /**
     * This function is used to check testGetLastMobileVersion function in rest
     */
    public function testGetLastMobileVersion()
    {
        $url = sprintf('/api/v1.0/apps/%s/version', 'ios');

        // try to register new user
        $this->client->request('GET', $url);

        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "Can not get user stats in rest!");

        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(1, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on POST postSettingsAction rest!");
        }

        //get response content
        $responseResults = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('mandatory', $responseResults, 'Invalid mandatory key in get last mobile version rest json structure');

        $this->assertArrayHasKey('optional', $responseResults, 'Invalid optional key in get last mobile version rest json structure');
    }

    /**
     * THis function use to test testGetCurrentUser
     */
    public function testGetCurrentUser()
    {
        $url = sprintf('/api/v1.0/user');

        // try to get goal by id
        $this->client2->request('GET', $url);

        $this->assertEquals($this->client2->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get user in testGetCurrentUser rest!");

        $this->assertTrue(
            $this->client2->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client2->getResponse()->headers
        );

        if ($profile = $this->client2->getProfile()) {
            // check the number of requests
            $this->assertLessThan(8, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on goal testGetCurrentUser rest!");
        }

        //get response content
        $responseResults = json_decode($this->client2->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $responseResults, 'Invalid id key in get current user rest json structure');
        $this->assertArrayHasKey('username', $responseResults, 'Invalid username key in get current user rest json structure');
        $this->assertArrayHasKey('first_name', $responseResults, 'Invalid first_name key in get current user rest json structure');
        $this->assertArrayHasKey('last_name', $responseResults, 'Invalid last_name key in get current user rest json structure');
        $this->assertArrayHasKey('is_confirmed', $responseResults, 'Invalid is_confirmed key in get current user rest json structure');
        $this->assertArrayHasKey('show_name', $responseResults, 'Invalid show_name key in get current user rest json structure');
        $this->assertArrayHasKey('is_admin', $responseResults, 'Invalid is_admin key in get current user rest json structure');
        $this->assertArrayHasKey('u_id', $responseResults, 'Invalid u_id key in get current user rest json structure');
        $this->assertArrayHasKey('draft_count', $responseResults, 'Invalid draft_count key in get current user rest json structure');
        $this->assertArrayHasKey('stats', $responseResults, 'Invalid stats key in get current user rest json structure');

        $stats = $responseResults['stats'];

        $this->assertArrayHasKey('listedBy', $stats, 'Invalid listedBy key in get current user rest json structure');
        $this->assertArrayHasKey('active', $stats, 'Invalid active key in get current user rest json structure');
        $this->assertArrayHasKey('doneBy', $stats, 'Invalid doneBy key in get current user rest json structure');

        if(array_key_exists('image_path', $responseResults)) {
            $this->assertArrayHasKey('image_path', $responseResults, 'Invalid image_path key in get current user rest json structure');
        }
    }
}