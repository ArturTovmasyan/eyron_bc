<?php
/**
 * Created by PhpStorm.
 * User: tigran
 * Date: 2/5/16
 * Time: 8:54 PM
 */

namespace AppBundle\Tests\Controller\Rest;


use AppBundle\Tests\Controller\BaseClass;
use Symfony\Component\HttpFoundation\Response;

class UserGoalControllerTest extends BaseClass
{
    /**
     * This function test getAction
     */
    public function testGet()
    {
        $goalId = $this->em->getRepository('AppBundle:Goal')->findOneByTitle('goal1')->getId();

        // create url for test
        $url = sprintf('/api/v1.0/usergoals/%s', $goalId);

        // try to get user goal
        $this->client->request('GET', $url);
        // check page is opened
        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get user-goal in getAction rest!");
        // check page response content type
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );
        // check database query count
        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(9, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on get user goal page!");
        }
    }

    /**
     * this function try to test putAction
     */
    public function testPut()
    {
        $goalId = $this->em->getRepository('AppBundle:Goal')->findOneByTitle('goal1')->getId();

        // create putAction url
        $url = sprintf('/api/v1.0/usergoals/%s', $goalId);

        // try to put date
        $this->client->request('PUT', $url, array('goal_status'=>true, 'is_visible'=>true, 'note'=>'userGoal note',
            'steps[write step text here]'=>true, "location['address']"=>'Armenia Yerevan',
            "location['latitude']"=>43.222, "location['longitude']"=>40.44, 'urgent'=>true,
            'important'=>true, 'do_date'=>'01/01/2016'));

        //get goal by id
        $goal = $this->em->getRepository('AppBundle:Goal')->find($goalId);

        //get user by username
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(array('email' => 'admin@admin.com'));

        if($goal->getPublish() || (!is_null($author = $goal->getAuthor()) && $user->getId() == $author->getId())) {
            // check page opened status code
            $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "can not create user-goal in putAction rest!");
        }

        // check response result content type
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        // check database query count
        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(13, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on user-goal create rest!");
        }
    }

    /**
     * This function use to test getDoneAction
     */
    public function testGetDone()
    {
        $goalId = $this->em->getRepository('AppBundle:Goal')->findOneByTitle('goal1')->getId();

        // generate url for test getDoneAction
        $url = sprintf('/api/v1.0/usergoals/%s/dones/%s', $goalId, 1);

        // try to get done Action
        $this->client->request('GET', $url);

        //get goal by id
        $goal = $this->em->getRepository('AppBundle:Goal')->find($goalId);

        //get user by username
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(array('email' => 'admin@admin.com'));

        if($goal->getPublish() || (!is_null($author = $goal->getAuthor()) && $user->getId() == $author->getId())) {

            // check page opened status code
            $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "can not in getDoneAction rest!");
        }

        // check response result content type
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        // check database query count
        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on user-goal getDoneAction rest!");
        }
    }

    /**
     * this function try to test postBucketlistAction
     * @depends testGetDone
     */
    public function testPostBucketlist()
    {
        // generate postBucketlistAction rest url
        $url = sprintf('/api/v1.0/usergoals/bucketlists');

        // try to post BucketlistAction
        $this->client->request('POST', $url, array('first'=>1, 'count'=>1,
          ));

        // check page opened status code
        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "can not user-goal postBucketlistAction rest!");

        // check response result content type
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        // check database query count
        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(11, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on  user-goal postBucketlistAction rest!");
        }

        //get response content
        $responseResults = json_decode($this->client->getResponse()->getContent(), true);

        //get user goals array in responseResults
        $userGoals = $responseResults['user_goals'];

        //get user in array
        $user = $responseResults['user'];

        foreach ($userGoals as $userGoal)
        {
            $this->assertArrayHasKey('steps', $userGoal, 'Invalid steps key in bucketlists rest json structure');
            $this->assertArrayHasKey('formatted_steps', $userGoal, 'Invalid formatted_steps key in bucketlists rest json structure');
            $this->assertArrayHasKey('id', $userGoal, 'Invalid id key in bucketlists rest json structure');
            $this->assertArrayHasKey('status', $userGoal, 'Invalid status key in bucketlists rest json structure');
            $this->assertArrayHasKey('is_visible', $userGoal, 'Invalid is_visible key in bucketlists rest json structure');
            $this->assertArrayHasKey('urgent', $userGoal, 'Invalid urgent key in bucketlists rest json structure');
            $this->assertArrayHasKey('important', $userGoal, 'Invalid important key in bucketlists rest json structure');
            $this->assertArrayHasKey('note', $userGoal, 'Invalid note key in bucketlists rest json structure');
            $this->assertArrayHasKey('do_date', $userGoal, 'Invalid do_date key in bucketlists rest json structure');
            $this->assertArrayHasKey('completion_date', $userGoal, 'Invalid completion_date key in bucketlists rest json structure');
            $this->assertArrayHasKey('listed_date', $userGoal, 'Invalid listed_date key in bucketlists rest json structure');
            $this->assertArrayHasKey('goal', $userGoal, 'Invalid goal key in bucketlists rest json structure');

            if(array_key_exists('goal', $userGoal)) {

                $this->assertArrayHasKey('goal', $userGoal, 'Invalid goal key in bucketlists rest json structure');

                //get goal in array
                $goal = $userGoal['goal'];

                $this->assertArrayHasKey('id', $goal, 'Invalid id key in bucketlists rest json structure');
                $this->assertArrayHasKey('description', $goal, 'Invalid description key in bucketlists rest json structure');
                $this->assertArrayHasKey('title', $goal, 'Invalid title key in bucketlists rest json structure');
                $this->assertArrayHasKey('video_link', $goal, 'Invalid video_link key in bucketlists rest json structure');
                $this->assertArrayHasKey('status', $goal, 'Invalid status key in bucketlists rest json structure');
                $this->assertArrayHasKey('created', $goal, 'Invalid created key in bucketlists rest json structure');
                $this->assertArrayHasKey('is_my_goal', $goal, 'Invalid is_my_goal key in bucketlists rest json structure');
                $this->assertArrayHasKey('share_link', $goal, 'Invalid share_link key in bucketlists rest json structure');
                $this->assertArrayHasKey('slug', $goal, 'Invalid slug key in bucketlists rest json structure');

                $goalStats = $goal['stats'];

                $this->assertArrayHasKey('listedBy', $goalStats, 'Invalid listedBy key in bucketlists rest json structure');
                $this->assertArrayHasKey('doneBy', $goalStats, 'Invalid doneBy key in bucketlists rest json structure');

                if(array_key_exists('author', $goal)) {

                    //get goal in array
                    $author = $goal['author'];

                    $this->assertArrayHasKey('id', $author, 'Invalid id key in bucketlists rest json structure');
                    $this->assertArrayHasKey('first_name', $author, 'Invalid first_name key in bucketlists rest json structure');
                    $this->assertArrayHasKey('last_name', $author, 'Invalid last_name key in bucketlists rest json structure');
                    $this->assertArrayHasKey('show_name', $author, 'Invalid show_name key in bucketlists rest json structure');
                    $this->assertArrayHasKey('is_admin', $author, 'Invalid is_admin key in bucketlists rest json structure');
                    $this->assertArrayHasKey('u_id', $author, 'Invalid u_id key in bucketlists rest json structure');

                    $stats = $author['stats'];

                    $this->assertArrayHasKey('listedBy', $stats, 'Invalid listedBy key in bucketlists rest json structure');
                    $this->assertArrayHasKey('doneBy', $stats, 'Invalid doneBy key in bucketlists rest json structure');
                }
            }

            if(array_key_exists('image_path', $userGoal)) {
                $this->assertArrayHasKey('image_path', $userGoal, 'Invalid image_path key in bucketlists rest json structure');
            }
        }

        $this->assertArrayHasKey('id', $user, 'Invalid id key in bucketlists rest json structure');
        $this->assertArrayHasKey('first_name', $user, 'Invalid first_name key in bucketlists rest json structure');
        $this->assertArrayHasKey('last_name', $user, 'Invalid last_name key in bucketlists rest json structure');
        $this->assertArrayHasKey('show_name', $user, 'Invalid show_name key in bucketlists rest json structure');
        $this->assertArrayHasKey('is_admin', $user, 'Invalid is_admin key in bucketlists rest json structure');
        $this->assertArrayHasKey('u_id', $user, 'Invalid is_admin key in bucketlists rest json structure');

        $userStats = $user['stats'];

        $this->assertArrayHasKey('listedBy', $userStats, 'Invalid listedBy key in bucketlists rest json structure');
        $this->assertArrayHasKey('active', $userStats, 'Invalid active key in bucketlists rest json structure');
        $this->assertArrayHasKey('doneBy', $userStats, 'Invalid doneBy key in bucketlists rest json structure');
    }

    /**
     * This function use to test user-goal delete action
     *
     * @dataProvider userGoalProvider
     * @depends testPostBucketlist
     */
    public function testDelete($userGoalId)
    {
        // calculate url for delete user-goal rest
        $url = sprintf('/api/v1.0/usergoals/%s', $userGoalId);

        // try to delete user goals
        $this->client4->request('DELETE', $url);

        //get user goal by id
        $userGoal = $this->em->getRepository('AppBundle:UserGoal')->find($userGoalId);

        //get goal
        $goal = $userGoal->getGoal();

        //get current user
        $currentUser = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(array('email' => 'user4@user.com'));

        //check if goal published and author current user
        if($goal->isAuthor($currentUser) && !$goal->getPublish()) {

            // check page opened status code
            $this->assertEquals($this->client4->getResponse()->getStatusCode(), Response::HTTP_OK, "can not delete user-goal rest!");
        }

        // check response result content type
        $this->assertTrue(
            $this->client4->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client4->getResponse()->headers
        );

        // check database query count
        if ($profile = $this->client4->getProfile()) {
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on delete user-goal rest!");
        }
    }

    /**
     * This function use to test get top ideas action
     *
     */
    public function testGetTopIdeas()
    {
        $url = sprintf('/api/v1.0/top-ideas/%s', 1);

        // try to get goal by id
        $this->client2->request('GET', $url);

        $this->assertEquals($this->client2->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get top ideas rest!");

        $this->assertTrue(
            $this->client2->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client2->getResponse()->headers
        );

        if ($profile = $this->client2->getProfile()) {
            // check the number of requests
            $this->assertLessThan(7, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on testGetTopIdeas rest!");
        }

        //get response content
        $responseResults = json_decode($this->client2->getResponse()->getContent(), true);

        $responseResult = $responseResults[0];

        $imageSizeKey = array_key_exists('image_size', $responseResult);

        //check if imageSizeKey exists in array
        if($imageSizeKey){

            $imageSize = $responseResult['image_size'];

            $width = array_key_exists('width', $imageSize);

            $height = array_key_exists('height', $imageSize);

            if($width && $height) {

                $this->assertArrayHasKey('width', $imageSize, 'Invalid width key in top ideas rest json structure');

                $this->assertArrayHasKey('height', $imageSize, 'Invalid height key in top ideas rest json structure');
            }
        }

        $this->assertArrayHasKey('id', $responseResult, 'Invalid id key in top ideas rest json structure');

        $this->assertArrayHasKey('title', $responseResult, 'Invalid title key in top ideas rest json structure');

        $this->assertArrayHasKey('status', $responseResult, 'Invalid status key in top ideas rest json structure');

        $this->assertArrayHasKey('is_my_goal', $responseResult, 'Invalid is_my_goal key in top ideas rest json structure');

        $this->assertArrayHasKey('share_link', $responseResult, 'Invalid share_link key in top ideas rest json structure');

        $this->assertArrayHasKey('slug', $responseResult, 'Invalid slug key in top ideas rest json structure');

        if(array_key_exists('cached_image', $responseResult)) {
            $this->assertArrayHasKey('cached_image', $responseResult, 'Invalid cached_image key in top ideas rest json structure');
        }

        if(array_key_exists('image_path', $responseResult)) {
            $this->assertArrayHasKey('image_path', $responseResult, 'Invalid image_path key in top ideas rest json structure');
        }
    }


    /**
     * this function try to test testPostBucketlistLocation
     */
    public function testPostBucketlistLocation()
    {
        // generate postBucketlistAction rest url
        $url = sprintf('/api/v1.0/usergoals/locations');

        // try to post BucketlistAction
        $this->client->request('POST', $url, array('first' => 0, 'count' => 1));

        // check page opened status code
        $this->assertEquals($this->client->getResponse()->getStatusCode(), Response::HTTP_OK, "can not testPostBucketlistLocation rest!");

        // check response result content type
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        // check database query count
        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(12, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on  user-goal postBucketlistLocationAction rest!");
        }

        //get response content
        $responseResults = json_decode($this->client->getResponse()->getContent(), true);

        $responseResult = $responseResults[0];

        if(array_key_exists('location', $responseResult)) {

            $this->assertArrayHasKey('location', $responseResult, 'Invalid location key in bucketLists location rest json structure');

            $location = $responseResult['location'];

            $this->assertArrayHasKey('latitude', $location, 'Invalid latitude key in bucketLists location rest json structure');
            $this->assertArrayHasKey('longitude', $location, 'Invalid longitude key in bucketLists location rest json structure');
            $this->assertArrayHasKey('address', $location, 'Invalid address key in bucketLists location rest json structure');
            $this->assertArrayHasKey('editable', $location, 'Invalid editable key in bucketLists location rest json structure');
        }

        $this->assertArrayHasKey('id', $responseResult, 'Invalid id key in bucketLists location rest json structure');

        if(array_key_exists('goal', $responseResult)) {

            $goal = $responseResult['goal'];

            $this->assertArrayHasKey('id', $goal, 'Invalid id key in bucketLists location rest json structure');
            $this->assertArrayHasKey('title', $goal, 'Invalid title key in bucketLists location rest json structure');
            $this->assertArrayHasKey('status', $goal, 'Invalid status key in bucketLists location rest json structure');
            $this->assertArrayHasKey('is_my_goal', $goal, 'Invalid is_my_goal key in bucketLists location rest json structure');
            $this->assertArrayHasKey('share_link', $goal, 'Invalid share_link key in bucketLists location rest json structure');
            $this->assertArrayHasKey('slug', $goal, 'Invalid slug key in bucketLists location rest json structure');

            if(array_key_exists('image_path', $goal)) {
                $this->assertArrayHasKey('image_path', $goal, 'Invalid image_path key in bucketLists location rest json structure');
            }

            $this->assertArrayHasKey('stats', $goal, 'Invalid stats key in bucketLists location rest json structure');

            $stats = $goal['stats'];

            $this->assertArrayHasKey('listedBy', $stats, 'Invalid listedBy key in bucketLists location rest json structure');
            $this->assertArrayHasKey('doneBy', $stats, 'Invalid doneBy key in bucketLists location rest json structure');

            if(array_key_exists('location', $goal)) {

                $goalLocation = $goal['location'];

                $this->assertArrayHasKey('latitude', $goalLocation, 'Invalid latitude key in bucketLists location rest json structure');
                $this->assertArrayHasKey('longitude', $goalLocation, 'Invalid longitude key in bucketLists location rest json structure');
                $this->assertArrayHasKey('address', $goalLocation, 'Invalid address key in bucketLists location rest json structure');
            }
        }
    }

    /**
     * this function try to test testGetUserGoalInfo
     */
    public function testGetUserGoalInfo()
    {
        $goalId = $this->em->getRepository('AppBundle:Goal')->findOneBy(array('title' => 'goal11'))->getId();

        // generate postBucketlistAction rest url
        $url = sprintf('/api/v1.0/usergoals/%s', $goalId);

        // try to post BucketlistAction
        $this->client2->request('GET', $url);

        // check page opened status code
        $this->assertEquals($this->client2->getResponse()->getStatusCode(), Response::HTTP_OK, "can not testGetUserGoalInfo rest!");

        // check response result content type
        $this->assertTrue(
            $this->client2->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client2->getResponse()->headers
        );

        // check database query count
        if ($profile = $this->client2->getProfile()) {
            // check the number of requests
            $this->assertLessThan(11, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on testGetUserGoalInfo rest!");
        }

        //get response content
        $responseResults = json_decode($this->client2->getResponse()->getContent(), true);

        $this->assertArrayHasKey('steps', $responseResults, 'Invalid steps key in bucketlists rest json structure');
        $this->assertArrayHasKey('formatted_steps', $responseResults, 'Invalid formatted_steps key in bucketlists rest json structure');

        if(array_key_exists('location', $responseResults)) {

            $location = $responseResults['location'];

            $this->assertArrayHasKey('latitude', $location, 'Invalid latitude key in top ideas rest json structure');
            $this->assertArrayHasKey('longitude', $location, 'Invalid longitude key in top ideas rest json structure');
            $this->assertArrayHasKey('address', $location, 'Invalid address key in top ideas rest json structure');
            $this->assertArrayHasKey('editable', $location, 'Invalid editable key in top ideas rest json structure');
        }

        $this->assertArrayHasKey('status', $responseResults, 'Invalid status key in bucketlists rest json structure');
        $this->assertArrayHasKey('is_visible', $responseResults, 'Invalid is_visible key in bucketlists rest json structure');
        $this->assertArrayHasKey('urgent', $responseResults, 'Invalid urgent key in bucketlists rest json structure');
        $this->assertArrayHasKey('important', $responseResults, 'Invalid important key in bucketlists rest json structure');

        if(array_key_exists('goal', $responseResults)) {

            $this->assertArrayHasKey('goal', $responseResults, 'Invalid goal key in bucketlists rest json structure');

            //get goal in array
            $goal = $responseResults['goal'];

            $this->assertArrayHasKey('id', $goal, 'Invalid id key in bucketlists rest json structure');
            $this->assertArrayHasKey('description', $goal, 'Invalid description key in bucketlists rest json structure');
            $this->assertArrayHasKey('title', $goal, 'Invalid title key in bucketlists rest json structure');
            $this->assertArrayHasKey('video_link', $goal, 'Invalid video_link key in bucketlists rest json structure');

            $this->assertArrayHasKey('status', $goal, 'Invalid status key in bucketlists rest json structure');
            $this->assertArrayHasKey('publish', $goal, 'Invalid publish key in bucketlists rest json structure');
            $this->assertArrayHasKey('created', $goal, 'Invalid created key in bucketlists rest json structure');

            $this->assertArrayHasKey('is_my_goal', $goal, 'Invalid is_my_goal key in bucketlists rest json structure');
            $this->assertArrayHasKey('share_link', $goal, 'Invalid share_link key in bucketlists rest json structure');
            $this->assertArrayHasKey('slug', $goal, 'Invalid slug key in bucketlists rest json structure');
            $this->assertArrayHasKey('stats', $goal, 'Invalid stats key in bucketlists rest json structure');

            $goalStats = $goal['stats'];

            $this->assertArrayHasKey('listedBy', $goalStats, 'Invalid listedBy key in bucketlists rest json structure');
            $this->assertArrayHasKey('doneBy', $goalStats, 'Invalid doneBy key in bucketlists rest json structure');

            if(array_key_exists('image_path', $goal)) {
                $this->assertArrayHasKey('image_path', $goal, 'Invalid image_path key in bucketlists rest json structure');
            }

            if(array_key_exists('author', $goal)) {

                $this->assertArrayHasKey('author', $goal, 'Invalid author key in goal story rest json structure');

                $author = $goal['author'];

                $this->assertArrayHasKey('id', $author, 'Invalid id key in goal story rest json structure');
                $this->assertArrayHasKey('username', $author, 'Invalid username key in goal story rest json structure');
                $this->assertArrayHasKey('first_name', $author, 'Invalid first_name key in goal story rest json structure');
                $this->assertArrayHasKey('last_name', $author, 'Invalid last_name key in goal story rest json structure');
                $this->assertArrayHasKey('show_name', $author, 'Invalid show_name key in goal story rest json structure');
                $this->assertArrayHasKey('is_admin', $author, 'Invalid is_admin key in goal story rest json structure');
                $this->assertArrayHasKey('is_confirmed', $author, 'Invalid is_confirmed key in goal story rest json structure');
                $this->assertArrayHasKey('u_id', $author, 'Invalid u_id key in goal story rest json structure');

                $this->assertArrayHasKey('stats', $author, 'Invalid stats key in goal story rest json structure');

                $authorStats = $author['stats'];

                $this->assertArrayHasKey('listedBy', $authorStats, 'Invalid listedBy key in goal story rest json structure');
                $this->assertArrayHasKey('active', $authorStats, 'Invalid active key in goal story rest json structure');
                $this->assertArrayHasKey('doneBy', $authorStats, 'Invalid doneBy key in goal story rest json structure');
            }
        }
    }
}
