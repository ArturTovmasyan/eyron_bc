<?php

namespace Application\UserBundle\Tests\Controller\Rest;

use AppBundle\Tests\Controller\BaseClass;
use Symfony\Component\HttpFoundation\Response;

class BadgeControllerTest extends BaseClass
{

    /**
     * This data provider create data for Badge type
     *
     * @return array
     */
    public function badgeTypeData()
    {
        $data = [['traveller' => 1], ['motivator' => 2], ['innovator' => 3]];

        return $data;
    }
    
    /**
     * This function is used to check cgetAction in Badge rest
     *
     */
    public function testGetAction()
    {
        // try to POST create user settings
        $this->client2->request('GET', '/api/v1.0/badges');

        // check response status code
        $this->assertEquals($this->client2->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get badge in getAction rest!");

        // check database query count
        if ($profile = $this->client2->getProfile()) {
            // check the number of requests
            $this->assertLessThan(7, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on badge getAction rest!");
        }

        //get response content
        $responseResult = json_decode($this->client2->getResponse()->getContent(), true);

        if (array_key_exists('min', $responseResult)) {
            $min = $responseResult['min'];

            $this->assertArrayHasKey('innovator', $min, 'Invalid innovator key in cgetAction() rest json structure');
            $this->assertArrayHasKey('motivator', $min, 'Invalid motivator key in cgetAction() rest json structure');
            $this->assertArrayHasKey('traveller', $min, 'Invalid traveller key in cgetAction() rest json structure');

            $this->assertEquals(10, $min['innovator'], 'Score normalizer don\'t work correctly');
            $this->assertEquals(10, $min['motivator'], 'Score normalizer don\'t work correctly');
            $this->assertEquals(10, $min['traveller'], 'Score normalizer don\'t work correctly');
        }

        if (array_key_exists('badges', $responseResult)) {
            $badges = $responseResult['badges'];

        //innovator array check
            $this->assertArrayHasKey('innovator', $badges, 'Invalid innovator key in cgetAction() rest json structure');

            $badgeInnovator = reset($badges['innovator']);
            $this->assertArrayHasKey('score', $badgeInnovator, 'Invalid score key in cgetAction() rest json structure');
            $this->assertArrayHasKey('user', $badgeInnovator, 'Invalid user key in cgetAction() rest json structure');

            $userInnovator = $badgeInnovator['user'];
            $this->assertArrayHasKey('id', $userInnovator, 'Invalid id key in cgetAction() rest json structure');
            $this->assertArrayHasKey('first_name', $userInnovator, 'Invalid first_name key in cgetAction() rest json structure');
            $this->assertArrayHasKey('last_name', $userInnovator, 'Invalid last_name key in cgetAction() rest json structure');

        //motivator array check
            $this->assertArrayHasKey('motivator', $badges, 'Invalid innovator key in cgetAction() rest json structure');

            $badgeMotivator = reset($badges['motivator']);
            $this->assertArrayHasKey('score', $badgeMotivator, 'Invalid score key in cgetAction() rest json structure');
            $this->assertArrayHasKey('user', $badgeMotivator, 'Invalid user key in cgetAction() rest json structure');

            $userMotivator = $badgeMotivator['user'];
            $this->assertArrayHasKey('id', $userMotivator, 'Invalid id key in cgetAction() rest json structure');
            $this->assertArrayHasKey('first_name', $userMotivator, 'Invalid first_name key in cgetAction() rest json structure');
            $this->assertArrayHasKey('last_name', $userMotivator, 'Invalid last_name key in cgetAction() rest json structure');

        //traveller array check
            $this->assertArrayHasKey('traveller', $badges, 'Invalid innovator key in cgetAction() rest json structure');

            $badgeTraveller = reset($badges['traveller']);
            $this->assertArrayHasKey('score', $badgeTraveller, 'Invalid score key in cgetAction() rest json structure');
            $this->assertArrayHasKey('user', $badgeTraveller, 'Invalid user key in cgetAction() rest json structure');

            $userTraveller = $badgeTraveller['user'];
            $this->assertArrayHasKey('id', $userTraveller, 'Invalid id key in cgetAction() rest json structure');
            $this->assertArrayHasKey('first_name', $userTraveller, 'Invalid first_name key in cgetAction() rest json structure');
            $this->assertArrayHasKey('last_name', $userTraveller, 'Invalid last_name key in cgetAction() rest json structure');
        }

        $this->assertArrayHasKey('users', $responseResult, 'Invalid users key in cgetAction() rest json structure');
    }

    /**
     * @dataProvider badgeTypeData
     */
    public function testGetTopuser($type)
    {
        $url = sprintf('/api/v1.0/badges/%s/topusers/%s', $type, 2);

        // try to POST create user settings
        $this->client2->request('GET', $url);

        // check response status code
        $this->assertEquals($this->client2->getResponse()->getStatusCode(), Response::HTTP_OK, "can not get badge in getAction rest!");

        // check database query count
        if ($profile = $this->client2->getProfile()) {
            // check the number of requests
            $this->assertLessThan(5, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on badge getAction rest!");
        }

        //get response content
        $responseResult = json_decode($this->client2->getResponse()->getContent(), true);

        $data = reset($responseResult);

        $this->assertArrayHasKey('score', $data, 'Invalid score key in getTopUserAction() rest json structure');
        $this->assertArrayHasKey('user', $data, 'Invalid user key in getTopUserAction() rest json structure');

        $user = $data['user'];
        $this->assertArrayHasKey('id', $user, 'Invalid id key in getTopUserAction() rest json structure');
        $this->assertArrayHasKey('first_name', $user, 'Invalid first_name key in getTopUserAction() rest json structure');
        $this->assertArrayHasKey('last_name', $user, 'Invalid last_name key in getTopUserAction() rest json structure');
    }
}