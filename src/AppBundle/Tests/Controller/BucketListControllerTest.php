<?php
//
//namespace AppBundle\Tests\Controller;
//
//
//use AppBundle\Entity\UserGoal;
//use Symfony\Component\HttpFoundation\Response;
//
//class BucketListControllerTest extends BaseClass
//{
//
//    /**
//     * @dataProvider filterProvider
//     */
//    public function testMyList($filter)
//    {
//        // try to open goal view page
//        $crawler = $this->client->request('GET', '/user-profile'.$filter['request']['urlPart'] , $filter['request']['filterData']);
//
//        $this->assertEquals($this->client->getResponse()->getStatusCode(), $filter['response']['statusCode'], 'can not open goal view page!');
//
//         // Assert that the response content contains a string goal1
//        if($filter['response']['goalName'] != null)
//        {
//            $this->assertContains( $filter['response']['goalName'], $this->client->getResponse()->getContent(), 'can not find goal1!');
//        }
//
//        $this->assertEquals($filter['response']['resultCount'], $crawler->filter('article')->count());
//
//        if ($profile = $this->client->getProfile()) {
//            // check the number of requests
//            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group list page!");
//        }
//
//    }
//}