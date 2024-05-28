<?php
/**
 * Created by PhpStorm.
 * User: armen
 * Date: 11/18/15
 * Time: 3:44 PM
 */
namespace AppBundle\Tests\Controller\Rest;

use AppBundle\Entity\UserGoal;
use AppBundle\Services\GooglePlaceService;
use AppBundle\Services\PlaceService;
use AppBundle\Tests\Controller\BaseClass;
use AppBundle\Traits\Mock\MockGooglePlaceServiceTrait;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Tests\Services\GooglePlaceServiceTest;

class GoalRestControllerTest extends BaseClass
{
    //use google place service mock trait
    use MockGooglePlaceServiceTrait;
    
    /**
     * This function is used to test putUserPositionAction rest
     */
    public function testPutUserPosition()
    {
        //get latitude and longitude from parameter
        $placesData = $this->container->getParameter('places')[0];
        $latitude = $placesData['latitude'];
        $longitude = $placesData['longitude'];
        
        //get google place service mock 
        $googlePlaceServiceMock = $this->createGooglePlaceServiceMock();

        //create client for set mock service in container
        $this->client2 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user1@user.com',
            'PHP_AUTH_PW'   => 'Test1234',
        ));

        //set mock service in container
        $this->client2->getContainer()->set('app.google_place', $googlePlaceServiceMock);
        
        //create url for test
        $url = sprintf('/api/v1.0/goals/user-position/%s/%s', $latitude, $longitude);

        //try to get goals in place
        $this->client2->request('PUT', $url);

        //check page is opened
        $this->assertEquals($this->client2->getResponse()->getStatusCode(), Response::HTTP_NO_CONTENT, "can not get goals in putUserPositionAction() rest!");

        // check database query count
        if ($profile = $this->client2->getProfile()) {
            // check the number of requests
            $this->assertLessThan(9, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on putUserPositionAction() rest!");
        }
    }

    /**
     * This function is used to test getUserUnConfirmAction() rest
     *
     */
    public function testUserUnConfirmGoal()
    {
        //create url for test
        $url = '/api/v1.0/goals/place/un-confirm';

        //try to confirm goals
        $this->client2->request('GET', $url);

        // check page is opened
        $this->assertEquals($this->client2->getResponse()->getStatusCode(), Response::HTTP_OK, "can not confirm goal in postConfirmGoalsAction() rest!");

        // check database query count
        if ($profile = $this->client2->getProfile()) {

            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on postConfirmGoalsAction() rest!");
        }

        //get response content
        $responseResults = json_decode($this->client2->getResponse()->getContent(), true);

        //get one result in array
        $result = reset($responseResults);

        $imageSizeKey = array_key_exists('image_size', $result);

        //check if imageSizeKey exists in array
        if($imageSizeKey){

            //get image size in array
            $imageSize = $result['image_size'];

            //get width
            $width = array_key_exists('width', $imageSize);

            //get height
            $height = array_key_exists('height', $imageSize);

            if($width && $height) {

                $this->assertArrayHasKey('width', $imageSize, 'Invalid width key in getGoalsInPlaceAction() rest json structure');
                $this->assertArrayHasKey('height', $imageSize, 'Invalid height key in getGoalsInPlaceAction() rest json structure');
            }
        }

        $this->assertArrayHasKey('id', $result, 'Invalid id key in getGoalsInPlaceAction() rest json structure');
        $this->assertArrayHasKey('title', $result, 'Invalid title key in getGoalsInPlaceAction() rest json structure');
        $this->assertArrayHasKey('description', $result, 'Invalid description key in getGoalsInPlaceAction() rest json structure');
        $this->assertArrayHasKey('status', $result, 'Invalid status key in getGoalsInPlaceAction() rest json structure');
        $this->assertArrayHasKey('share_link', $result, 'Invalid share_link key in getGoalsInPlaceAction() rest json structure');
        $this->assertArrayHasKey('publish', $result, 'Invalid publish key in getGoalsInPlaceAction() rest json structure');
        $this->assertArrayHasKey('video_link', $result, 'Invalid video_link key in getGoalsInPlaceAction() rest json structure');
        $this->assertArrayHasKey('created', $result, 'Invalid created key in getGoalsInPlaceAction() rest json structure');
        $this->assertArrayHasKey('is_my_goal', $result, 'Invalid is_my_goal key in getGoalsInPlaceAction() rest json structure');

        if(array_key_exists('image_path', $result)) {
            $this->assertArrayHasKey('image_path', $result, 'Invalid image_path key in getGoalsInPlaceAction() rest json structure');
        }
    }

    /**
     * This function get all goals test
     */
    public function testGetAll()
    {
        // get user goal
        $url = sprintf('/api/v1.0/goals/%s/%s', 1, 2);

        // try to get goal by id
        $this->client->request('GET', $url);

        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get goal getsAction rest!");

        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(8, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group list page!");
        }

        //get response content
        $responseResults = json_decode($this->client->getResponse()->getContent(), true);

        foreach ($responseResults as $responseData)
        {
            $imageSizeKey = array_key_exists('image_size', $responseResults);

            //check if imageSizeKey exists in array
            if($imageSizeKey){

                $imageSize = $responseData['image_size'];

                $width = array_key_exists('width', $imageSize);

                $height = array_key_exists('height', $imageSize);

                if($width && $height) {

                    $this->assertArrayHasKey('width', $imageSize, 'Invalid width key in top ideas rest json structure');

                    $this->assertArrayHasKey('height', $imageSize, 'Invalid height key in top ideas rest json structure');
                }
            }

            $this->assertArrayHasKey('id', $responseData, 'Invalid id key in top ideas rest json structure');

            $this->assertArrayHasKey('title', $responseData, 'Invalid title key in top ideas rest json structure');

            $this->assertArrayHasKey('status', $responseData, 'Invalid status key in top ideas rest json structure');

            $this->assertArrayHasKey('share_link', $responseData, 'Invalid share_link key in top ideas rest json structure');

            $this->assertArrayHasKey('slug', $responseData, 'Invalid slug key in top ideas rest json structure');

            $this->assertArrayHasKey('stats', $responseData, 'Invalid stats key in goal friends rest json structure');

            $stats = $responseData['stats'];

            $this->assertArrayHasKey('listedBy', $stats, 'Invalid listedBy key in goal friends rest json structure');

            $this->assertArrayHasKey('doneBy', $stats, 'Invalid doneBy key in goal friends rest json structure');


            if(array_key_exists('location', $responseData)) {

                $location = $responseData['location'];
                $this->assertArrayHasKey('latitude', $location, 'Invalid latitude key in top ideas rest json structure');
                $this->assertArrayHasKey('longitude', $location, 'Invalid longitude key in top ideas rest json structure');
                $this->assertArrayHasKey('address', $location, 'Invalid address key in top ideas rest json structure');
            }

            if(array_key_exists('image_path', $responseData)) {
                $this->assertArrayHasKey('image_path', $responseData, 'Invalid image_path key in top ideas rest json structure');
            }

            if(array_key_exists('is_my_goal', $responseData)) {
                $this->assertArrayHasKey('is_my_goal', $responseData, 'Invalid is_my_goal key in top ideas rest json structure');
            }
        }
    }

    /**
     * This test is used to check most popular category filter by goal listed count
     */
    public function testMostPopularFilter()
    {
        //get goal
        $url = sprintf('/api/v1.0/goals/%s/%s?category=most-popular', 0, 2);

        //try to get goals by filter most-popular category
        $this->client->request('GET', $url);

        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get goal getsAction rest!");

        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(8, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group list page!");
        }

        //get goal in rest response
        $goals = json_decode($this->client->getResponse()->getContent(), true);

        //set default array
        $allListedBy = array();

        foreach($goals as $goal)
        {
            $allListedBy[] = $goal['stats']['listedBy'];
        }

        //set listed goal
        $listedGoal = $allListedBy;

        //sort array
         asort($allListedBy);

        //check arrays is equal
        $isEqual = $listedGoal !== $allListedBy;

        $this->assertTrue($isEqual, "Most popular category don't sort by listed!");

    }

    /**
     * This function test get goal
     *
     * @dataProvider goalByIdProvider
     */
    public function testGet($goalId)
    {
        // get user goal
        $url = sprintf('/api/v1.0/goals/%s', $goalId);

        // try to get goal by id
        $this->client->request('GET', $url);

        //get goal by id
        $goal = $this->em->getRepository('AppBundle:Goal')->find($goalId);

        //get user by username
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(array('email' => 'user4@user.com'));

        if($goal->getPublish() && !is_null($goal->getAuthor()) && $goal->getAuthor()->getId() == $user->getId()) {
            $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get goal by id in getAction rest!");
        }

        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on goal getAction rest!");
        }
    }

    /**
     * This function test GetCategoriesAction
     */
    public function testGetCategories()
    {

        $url = sprintf('/api/v1.0/goal/categories');

        // try to get goal by id
        $this->client->request('GET', $url);

        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get GetCategoriesAction rest!");

        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(4, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on GetCategoriesAction rest!");
        }

        //get response content
        $responseResults = json_decode($this->client->getResponse()->getContent(), true);

        foreach ($responseResults as $key => $responseData)
        {
            $this->assertArrayHasKey('image_download_link', $responseData, 'Invalid image_download_link key in category rest json structure');
            $this->assertArrayHasKey('id', $responseData, 'Invalid id key in category rest json structure');
            $this->assertArrayHasKey('title', $responseData, 'Invalid title key in category rest json structure');
            $this->assertArrayHasKey('slug', $responseData, 'Invalid slug key in category rest json structure');
        }
    }

    /**
     * This function test putAction
     *
     */
    public function testPut()
    {
        $url = sprintf('/api/v1.0/goals/create');

        // try to get goal by id
        $this->client->request('PUT', $url, array('is_public'=>true, 'title'=>'from rest', 'description'=>'from rest description', 'video_links[0]'=>'www.google.com', 'language' => 'en'));

        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get goal putAction rest!");

        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(7, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on goal putAction rest!");
        }
    }

    /**
     * This function test goal AddImagesAction
     *
     * @dataProvider goalByIdProvider
     * @depends testPut
     */
    public function testAddImages($golId)
    {
        //get goal by id
        $goal = $this->em->getRepository('AppBundle:Goal')->find($golId);

        //get goal author id
        $authorId = $goal->getAuthor()->getId();

        //get current user id
        $currentUser = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(array('email' => 'user4@user.com'));

        $currentUserId = $currentUser->getId();

        $url = sprintf('/api/v1.0/goals/add-images/%s/%s', $golId, $currentUserId);
        // try to get goal by id

        $maindir = $this->container->getParameter('kernel.root_dir');

        $oldPhotoPath = $maindir.'/../src/AppBundle/Tests/Controller/old_photo.jpg';
        $photoPath = __DIR__ . '/photo.jpg';
        // copy photo path
        copy($oldPhotoPath, $photoPath);

        // new uploaded file
        $photo = new UploadedFile(
            $photoPath,
            'photo.jpg',
            'image/jpeg',
            123
        );

        $this->client4->request('POST', $url, array(), array('file' => $photo));

        //check if goal author is current user
        if($authorId == $currentUserId && !$goal->getPublish()) {
            $this->assertEquals($this->client4->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get goal AddImagesAction rest!");

        }
        else {
            $this->assertEquals($this->client4->getResponse()->getStatusCode(), Response::HTTP_FORBIDDEN, "can not get goal AddImagesAction rest!");
        }

        $this->assertTrue(
            $this->client4->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client4->getResponse()->headers
        );

        if ($profile = $this->client4->getProfile()) {
            // check the number of requests
            $this->assertLessThan(9, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on goal AddImagesAction rest!");
        }
    }

    /**
     * This function test RemoveImageAction
     *
     * @dataProvider allFileProvider
     */
    public function testRemoveImage($goalImageId)
    {
        if($goalImageId) {

            $url = sprintf('/api/v1.0/goals/remove-images/%s', $goalImageId);
            // try to get goal by id

            $this->client->request('POST', $url);

            $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "can not remove goal image in RemoveImageAction rest!");

            $this->assertTrue(
                $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
                $this->client->getResponse()->headers
            );

            if ($profile = $this->client->getProfile()) {
                // check the number of requests
                $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group RemoveImageAction rest!");
            }
        }
    }

    /**
     * THis function use to test GetDraftsAction
     */
    public function testGetDrafts()
    {
        $url = sprintf('/api/v1.0/goals/drafts/%s/%s',0,2);

        // try to get goal by id
        $this->client->request('GET', $url);

        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get goal in GetDraftsAction rest!");

        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(7, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on goal GetDraftsAction rest!");
        }

        //get response content
        $responseResults = json_decode($this->client->getResponse()->getContent(), true);

        foreach ($responseResults as $responseData)
        {
            $this->assertArrayHasKey('id', $responseData, 'Invalid id key in drafts rest json structure');
            $this->assertArrayHasKey('title', $responseData, 'Invalid title key in drafts rest json structure');
            $this->assertArrayHasKey('created', $responseData, 'Invalid created key in drafts rest json structure');
        }
    }

    /**
     * This function try to test GetFriendsAction rest
     */
    public function testGetFriends()
    {
        // GET /api/v1.0/goals/{first}/friends/{count}
        $url = sprintf('/api/v1.0/goals/%s/friends/%s', 1,2);

        // try to get goal by id
        $this->client2->request('GET', $url);

        $this->assertEquals($this->client2->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get goal in GetFriendsAction rest!");

        $this->assertTrue(
            $this->client2->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client2->getResponse()->headers
        );

        if ($profile = $this->client2->getProfile()) {
            // check the number of requests
            $this->assertLessThan(7, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on goal getAction rest!");
        }

        //get response content
        $responseResults = json_decode($this->client2->getResponse()->getContent(), true);

        foreach ($responseResults as $responseData)
        {
            $this->assertArrayHasKey('u_id', $responseData, 'Invalid u_id key in goal friends rest json structure');

            $this->assertArrayHasKey('id', $responseData, 'Invalid id key in goal friends rest json structure');

            $this->assertArrayHasKey('username', $responseData, 'Invalid username key in goal friends rest json structure');

            $this->assertArrayHasKey('first_name', $responseData, 'Invalid first_name key in goal friends rest json structure');

            $this->assertArrayHasKey('last_name', $responseData, 'Invalid last_name key in goal friends rest json structure');

            $this->assertArrayHasKey('is_confirmed', $responseData, 'Invalid is_confirmed key in goal friends rest json structure');

            $this->assertArrayHasKey('show_name', $responseData, 'Invalid show_name key in goal friends rest json structure');

            $this->assertArrayHasKey('is_admin', $responseData, 'Invalid is_admin key in goal friends rest json structure');

            $this->assertArrayHasKey('stats', $responseData, 'Invalid stats key in goal friends rest json structure');

            $stats = $responseData['stats'];

            $this->assertArrayHasKey('listedBy', $stats, 'Invalid listedBy key in goal friends rest json structure');

            $this->assertArrayHasKey('active', $stats, 'Invalid active key in goal friends rest json structure');

            $this->assertArrayHasKey('doneBy', $stats, 'Invalid doneBy key in goal friends rest json structure');

            if(array_key_exists('image_path', $responseData)) {
                $this->assertArrayHasKey('image_path', $responseData, 'Invalid image_path key in goal friends rest json structure');
            }
        }
    }

    /**
     * This function try to test PutCommentAction of rest
     *
     */
    public function testPutComment()
    {
        //get goal id by title
        $goalId = $this->em->getRepository('AppBundle:Goal')->findOneBy(array('title' => 'goal13'))->getId();

        $url = sprintf('/api/v1.0/goals/%s/comment', $goalId);

        // try to get goal by id
        $this->client4->request('PUT', $url, array('commentBody'=>'Test for create comment'));

        $this->assertEquals($this->client4->getResponse()->getStatusCode(), Response::HTTP_OK, "can not create goal comment in PutCommentAction rest!");

        $this->assertTrue(
            $this->client4->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client4->getResponse()->headers
        );

        if ($profile = $this->client4->getProfile()) {
            // check the number of requests
            $this->assertLessThan(17, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on goal PutCommentAction rest!");
        }
    }

    /**
     * This function use to test PutSuccessStoryAction rest
     *
     */
    public function testPutSuccessStory()
    {
        //get goal id by title
        $goalId = $this->em->getRepository('AppBundle:Goal')->findOneBy(array('title' => 'goal6'))->getId();

        $url = sprintf('/api/v1.0/goals/%s/successstory', $goalId);

        // try to get goal by id
        $this->client4->request('PUT', $url, array('story'=>'Test for create success story'));

        $this->assertEquals($this->client4->getResponse()->getStatusCode(), Response::HTTP_OK, "can not create goal success story id in PutSuccessStoryAction rest!");

        $this->assertTrue(
            $this->client4->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client4->getResponse()->headers
        );

        if ($profile = $this->client4->getProfile()) {
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on goal add PutSuccessStoryAction rest!");
        }
    }

    /**
     * This function test testGetRandomFriends
     */
    public function testGetRandomFriends()
    {
        $url = sprintf('/api/v1.0/goal/random/friends');

        // try to get goal by id
        $this->client2->request('GET', $url);

        $this->assertEquals($this->client2->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get random friends rest!");

        $this->assertTrue(
            $this->client2->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client2->getResponse()->headers
        );

        if ($profile = $this->client2->getProfile()) {
            // check the number of requests
            $this->assertLessThan(9, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on testGetRandomFriends rest!");
        }

        //get response content
        $responseResults = json_decode($this->client2->getResponse()->getContent(), true);

        $this->assertArrayHasKey('length', $responseResults, 'Invalid length key in Random friends rest json structure');

        $lengthKey = array_key_exists('length', $responseResults);

        //check if length key exists in array
        if ($lengthKey) {
            unset($responseResults['length']);
        }

        foreach ($responseResults[1] as $responseData) {

                $this->assertArrayHasKey('id', $responseData, 'Invalid id key in Random friends rest json structure');

                $this->assertArrayHasKey('username', $responseData, 'Invalid username key in Random friends rest json structure');

                $this->assertArrayHasKey('first_name', $responseData, 'Invalid first_name key in Random friends rest json structure');

                $this->assertArrayHasKey('last_name', $responseData, 'Invalid last_name key in Random friends rest json structure');

                $this->assertArrayHasKey('is_confirmed', $responseData, 'Invalid is_confirmed key in Random friends rest json structure');

                $this->assertArrayHasKey('show_name', $responseData, 'Invalid show_name key in Random friends rest json structure');

                $this->assertArrayHasKey('is_admin', $responseData, 'Invalid is_admin key in Random friends rest json structure');

                if(array_key_exists('cached_image', $responseData)) {
                    $this->assertArrayHasKey('cached_image', $responseData, 'Invalid cached_image key in Random friends rest json structure');

                }

                $this->assertArrayHasKey('u_id', $responseData, 'Invalid u_id key in Random friends rest json structure');

                $this->assertArrayHasKey('stats', $responseData, 'Invalid stats key in Random friends rest json structure');

                $stats = $responseData['stats'];

                $this->assertArrayHasKey('listedBy', $stats, 'Invalid listedBy key in Random friends rest json structure');

                $this->assertArrayHasKey('active', $stats, 'Invalid active key in Random friends rest json structure');

                $this->assertArrayHasKey('doneBy', $stats, 'Invalid doneBy key in Random friends rest json structure');

                if (array_key_exists('image_path', $responseData)) {
                    $this->assertArrayHasKey('image_path', $responseData, 'Invalid image_path key in Random friends rest json structure');
                }
            }
    }

    /**
     * This function try to test testGetGoalStory rest
     */
    public function testGetGoalStory()
    {
        $goalId = $this->em->getRepository('AppBundle:Goal')->findOneBy(array('title' => 'goal11'))->getId();

        // GET /api/v1.0/goals/{first}/friends/{count}
        $url = sprintf('/api/v1.0/story/%s', $goalId);

        // try to get goal by id
        $this->client2->request('GET', $url);

        $this->assertEquals($this->client2->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get goal in testGetGoalStory rest!");

        $this->assertTrue(
            $this->client2->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client2->getResponse()->headers
        );

        if ($profile = $this->client2->getProfile()) {
            // check the number of requests
            $this->assertLessThan(11, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on testGetGoalStory rest!");
        }

        //get response content
        $responseResults = json_decode($this->client2->getResponse()->getContent(), true);

        if(array_key_exists('goal', $responseResults)) {

            $goal = $responseResults['goal'];

            if(array_key_exists('location', $goal)) {

                $this->assertArrayHasKey('location', $goal, 'Invalid location key in goal story rest json structure');
                $location = $goal['location'];
                $this->assertArrayHasKey('latitude', $location, 'Invalid latitude key in goal story rest json structure');
                $this->assertArrayHasKey('longitude', $location, 'Invalid longitude key in goal story rest json structure');
                $this->assertArrayHasKey('address', $location, 'Invalid address key in goal story rest json structure');
            }

            if(array_key_exists('author', $goal)) {

                $this->assertArrayHasKey('author', $goal, 'Invalid author key in goal story rest json structure');

                $author = $goal['author'];

                $this->assertArrayHasKey('id', $author, 'Invalid id key in goal story rest json structure');
                $this->assertArrayHasKey('first_name', $author, 'Invalid first_name key in goal story rest json structure');
                $this->assertArrayHasKey('last_name', $author, 'Invalid last_name key in goal story rest json structure');
                $this->assertArrayHasKey('show_name', $author, 'Invalid show_name key in goal story rest json structure');


                $this->assertArrayHasKey('is_admin', $author, 'Invalid is_admin key in goal story rest json structure');
                $this->assertArrayHasKey('image_size', $author, 'Invalid image_size key in goal story rest json structure');
                $this->assertArrayHasKey('u_id', $author, 'Invalid u_id key in goal story rest json structure');
                $this->assertArrayHasKey('stats', $author, 'Invalid stats key in goal story rest json structure');

                $authorStats = $author['stats'];

                $this->assertArrayHasKey('listedBy', $authorStats, 'Invalid listedBy key in goal story rest json structure');
                $this->assertArrayHasKey('active', $authorStats, 'Invalid active key in goal story rest json structure');
                $this->assertArrayHasKey('doneBy', $authorStats, 'Invalid doneBy key in goal story rest json structure');
            }

            $this->assertArrayHasKey('id', $goal, 'Invalid id key in goal story rest json structure');
            $this->assertArrayHasKey('description', $goal, 'Invalid description key in goal story rest json structure');
            $this->assertArrayHasKey('title', $goal, 'Invalid title key in goal story rest json structure');
            $this->assertArrayHasKey('status', $goal, 'Invalid status key in goal story rest json structure');
            $this->assertArrayHasKey('publish', $goal, 'Invalid publish key in goal story rest json structure');
            $this->assertArrayHasKey('is_my_goal', $goal, 'Invalid is_my_goal key in goal story rest json structure');
            $this->assertArrayHasKey('share_link', $goal, 'Invalid share_link key in goal story rest json structure');
            $this->assertArrayHasKey('slug', $goal, 'Invalid slug key in goal story rest json structure');
            $this->assertArrayHasKey('stats', $goal, 'Invalid stats key in goal story rest json structure');

            $goalStats = $goal['stats'];

            $this->assertArrayHasKey('listedBy', $goalStats, 'Invalid listedBy key in goal story rest json structure');
            $this->assertArrayHasKey('doneBy', $goalStats, 'Invalid doneBy key in goal story rest json structure');

            if(array_key_exists('image_path', $goal)) {
                $this->assertArrayHasKey('image_path', $goal, 'Invalid image_path key in goal story rest json structure');
            }

            if(array_key_exists('cached_image', $goal)) {
                $this->assertArrayHasKey('cached_image', $goal, 'Invalid cached_image key in goal story rest json structure');
            }
        }

        if(array_key_exists('story', $responseResults)) {

            $story = $responseResults['story'];

            $this->assertArrayHasKey('id', $story, 'Invalid id key in goal story rest json structure');
            $this->assertArrayHasKey('files', $story, 'Invalid files key in goal story rest json structure');
            $this->assertArrayHasKey('created', $story, 'Invalid created key in goal story rest json structure');
            $this->assertArrayHasKey('story', $story, 'Invalid story key in goal story rest json structure');
            $this->assertArrayHasKey('video_link', $story, 'Invalid video_link key in goal story rest json structure');
        }
    }

    /**
     * This function test testGetGoal action with checked json structure
     *
     */
    public function testGetGoal()
    {
        //get goal by title
        $goalId = $this->em->getRepository('AppBundle:Goal')->findOneBy(array('title' => 'goal10'))->getId();

        // get user goal
        $url = sprintf('/api/v1.0/goals/%s', $goalId);

        // try to get goal by id
        $this->client->request('GET', $url);

        //get goal by id
        $goal = $this->em->getRepository('AppBundle:Goal')->find($goalId);

        //get user by username
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(array('email' => 'user5@user.com'));

        if($goal->getPublish() && !is_null($goal->getAuthor()) && $goal->getAuthor()->getId() == $user->getId()) {
            $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get goal by id in testGetGoal rest!");
        }

        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on goal testGetGoal rest!");
        }

        //get response content
        $responseResults = json_decode($this->client->getResponse()->getContent(), true);

        $goalKey = array_key_exists('goal', $responseResults);
        $commentKey = array_key_exists('comments', $responseResults);

        //check if imageSizeKey exists in array
        if($goalKey){

            $goal = $responseResults['goal'];

            if(array_key_exists('image_size', $goal)) {

                $imageSize = $goal['image_size'];

                $width = array_key_exists('width', $imageSize);

                $height = array_key_exists('height', $imageSize);

                if($width && $height) {

                    $this->assertArrayHasKey('width', $imageSize, 'Invalid width key in testGetGoal rest json structure');

                    $this->assertArrayHasKey('height', $imageSize, 'Invalid height key in testGetGoal rest json structure');
                }
            }

            if(array_key_exists('location', $goal)) {

                $location = $goal['location'];

                $this->assertArrayHasKey('latitude', $location, 'Invalid latitude key in testGetGoal rest json structure');
                $this->assertArrayHasKey('longitude', $location, 'Invalid longitude key in testGetGoal rest json structure');
                $this->assertArrayHasKey('address', $location, 'Invalid address key in testGetGoal rest json structure');
            }

            $this->assertArrayHasKey('id', $goal, 'Invalid id key in testGetGoal rest json structure');
            $this->assertArrayHasKey('title', $goal, 'Invalid title key in testGetGoal rest json structure');
            $this->assertArrayHasKey('description', $goal, 'Invalid description key in testGetGoal rest json structure');
            $this->assertArrayHasKey('video_link', $goal, 'Invalid video_link key in testGetGoal rest json structure');
            $this->assertArrayHasKey('status', $goal, 'Invalid status key in testGetGoal rest json structure');
            $this->assertArrayHasKey('created', $goal, 'Invalid created key in testGetGoal rest json structure');
            $this->assertArrayHasKey('stats', $goal, 'Invalid stats key in testGetGoal rest json structure');

            $goalStats = $goal['stats'];
            $this->assertArrayHasKey('listedBy', $goalStats, 'Invalid listedBy key in testGetGoal rest json structure');
            $this->assertArrayHasKey('doneBy', $goalStats, 'Invalid doneBy key in testGetGoal rest json structure');
            $this->assertArrayHasKey('is_my_goal', $goal, 'Invalid is_my_goal key in testGetGoal rest json structure');
            $this->assertArrayHasKey('share_link', $goal, 'Invalid share_link key in testGetGoal rest json structure');

            if(array_key_exists('image_path', $goal)) {
                $this->assertArrayHasKey('image_path', $goal, 'Invalid image_path key in testGetGoal rest json structure');
            }

            if(array_key_exists('images', $goal)) {

                $images = $goal['images'][0];

                if(array_key_exists('image_size', $images)) {

                    $imageSize = $images['image_size'];

                    $width = array_key_exists('width', $imageSize);

                    $height = array_key_exists('height', $imageSize);

                    if($width && $height) {

                        $this->assertArrayHasKey('width', $imageSize, 'Invalid width key in testGetGoal rest json structure');

                        $this->assertArrayHasKey('height', $imageSize, 'Invalid height key in testGetGoal rest json structure');
                    }

                    $this->assertArrayHasKey('id', $images, 'Invalid id key in testGetGoal rest json structure');
                    $this->assertArrayHasKey('list', $images, 'Invalid list key in testGetGoal rest json structure');
                    $this->assertArrayHasKey('cover', $images, 'Invalid cover key in testGetGoal rest json structure');
                    $this->assertArrayHasKey('image_path', $images, 'Invalid image_path key in testGetGoal rest json structure');
                    $this->assertArrayHasKey('file_original_name', $images, 'Invalid file_original_name key in testGetGoal rest json structure');
                    $this->assertArrayHasKey('file_name', $images, 'Invalid file_name key in testGetGoal rest json structure');
                    $this->assertArrayHasKey('file_size', $images, 'Invalid file_size key in testGetGoal rest json structure');
                }
            }

            if(array_key_exists('success_stories', $goal)) {

                $story = $goal['success_stories'][0];

                $this->assertArrayHasKey('id', $story, 'Invalid id key in testGetGoal rest json structure');
                $this->assertArrayHasKey('created', $story, 'Invalid created key in testGetGoal rest json structure');
                $this->assertArrayHasKey('story', $story, 'Invalid story key in testGetGoal rest json structure');
                $this->assertArrayHasKey('video_link', $story, 'Invalid video_link key in testGetGoal rest json structure');

                if(array_key_exists('user', $story)) {
                    $storyUser = $story['user'];

                    $this->assertArrayHasKey('id', $storyUser, 'Invalid id key in testGetGoal rest json structure');
                    $this->assertArrayHasKey('first_name', $storyUser, 'Invalid first_name key in testGetGoal rest json structure');
                    $this->assertArrayHasKey('last_name', $storyUser, 'Invalid last_name key in testGetGoal rest json structure');
                    $this->assertArrayHasKey('show_name', $storyUser, 'Invalid show_name key in testGetGoal rest json structure');
                    $this->assertArrayHasKey('is_admin', $storyUser, 'Invalid is_admin key in testGetGoal rest json structure');
                    $this->assertArrayHasKey('image_size', $storyUser, 'Invalid image_size key in testGetGoal rest json structure');
                    $this->assertArrayHasKey('u_id', $storyUser, 'Invalid u_id key in testGetGoal rest json structure');
                    $this->assertArrayHasKey('stats', $storyUser, 'Invalid stats key in testGetGoal rest json structure');

                    $userStats = $storyUser['stats'];

                    $this->assertArrayHasKey('listedBy', $userStats, 'Invalid listedBy key in testGetGoal rest json structure');
                    $this->assertArrayHasKey('active', $userStats, 'Invalid active key in testGetGoal rest json structure');
                    $this->assertArrayHasKey('doneBy', $userStats, 'Invalid doneBy key in testGetGoal rest json structure');
                }

            }

            if(array_key_exists('author', $goal)) {

                $this->assertArrayHasKey('author', $goal, 'Invalid author key in testGetGoal rest json structure');
                $author = $goal['author'];

                $this->assertArrayHasKey('id', $author, 'Invalid id key in testGetGoal rest json structure');
                $this->assertArrayHasKey('first_name', $author, 'Invalid first_name key in testGetGoal rest json structure');
                $this->assertArrayHasKey('last_name', $author, 'Invalid last_name key in testGetGoal rest json structure');
                $this->assertArrayHasKey('show_name', $author, 'Invalid show_name key in testGetGoal rest json structure');
                $this->assertArrayHasKey('is_admin', $author, 'Invalid is_admin key in testGetGoal rest json structure');
                $this->assertArrayHasKey('image_size', $author, 'Invalid image_size key in testGetGoal rest json structure');
                $this->assertArrayHasKey('u_id', $author, 'Invalid u_id key in testGetGoal rest json structure');
                $this->assertArrayHasKey('stats', $author, 'Invalid stats key in testGetGoal rest json structure');

                $authorStats = $author['stats'];
                $this->assertArrayHasKey('listedBy', $authorStats, 'Invalid listedBy key in testGetGoal rest json structure');
                $this->assertArrayHasKey('active', $authorStats, 'Invalid active key in testGetGoal rest json structure');
                $this->assertArrayHasKey('doneBy', $authorStats, 'Invalid doneBy key in testGetGoal rest json structure');
            }
        }

        //check if comment exists in array
        if($commentKey) {

            //get comments
            $comments = $responseResults['comments'];

            //get comment
            $comment = reset($comments);

            $this->assertArrayHasKey('id', $comment, 'Invalid id key in testGetGoal rest json structure');
            $this->assertArrayHasKey('comment_body', $comment, 'Invalid comment_body key in testGetGoal rest json structure');
            $this->assertArrayHasKey('created_at', $comment, 'Invalid created_at key in testGetGoal rest json structure');
            $this->assertArrayHasKey('updated_at', $comment, 'Invalid updated_at key in testGetGoal rest json structure');

            if(array_key_exists('author', $comment)) {

                $this->assertArrayHasKey('author', $comment, 'Invalid author key in testGetGoal rest json structure');
                $author = $comment['author'];

                $this->assertArrayHasKey('id', $author, 'Invalid id key in testGetGoal rest json structure');
                $this->assertArrayHasKey('first_name', $author, 'Invalid first_name key in testGetGoal rest json structure');
                $this->assertArrayHasKey('last_name', $author, 'Invalid last_name key in testGetGoal rest json structure');
                $this->assertArrayHasKey('show_name', $author, 'Invalid show_name key in testGetGoal rest json structure');
                $this->assertArrayHasKey('is_admin', $author, 'Invalid is_admin key in testGetGoal rest json structure');
                $this->assertArrayHasKey('image_size', $author, 'Invalid image_size key in testGetGoal rest json structure');
                $this->assertArrayHasKey('u_id', $author, 'Invalid u_id key in testGetGoal rest json structure');
                $this->assertArrayHasKey('stats', $author, 'Invalid stats key in testGetGoal rest json structure');

                $authorStats = $author['stats'];
                $this->assertArrayHasKey('listedBy', $authorStats, 'Invalid listedBy key in testGetGoal rest json structure');
                $this->assertArrayHasKey('active', $authorStats, 'Invalid active key in testGetGoal rest json structure');
                $this->assertArrayHasKey('doneBy', $authorStats, 'Invalid doneBy key in testGetGoal rest json structure');
            }
        }
    }
}