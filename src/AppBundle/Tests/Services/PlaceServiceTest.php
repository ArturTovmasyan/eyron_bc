<?php

namespace AppBundle\Tests\Services;

use AppBundle\Services\PlaceService;
use AppBundle\Tests\Controller\BaseClass;
use AppBundle\Traits\Mock\MockGooglePlaceServiceTrait;

/**
 * Class PlaceServiceTest
 * @package AppBundle\Tests\Service
 */
class PlaceServiceTest extends BaseClass
{
    //use google place service mock trait
    use MockGooglePlaceServiceTrait;
    
    /**
     * This function is used to test createPlace() method in place service
     */
    public function testCreatePlace()
    {
        //get latitude and longitude from parameter
        $placesData = $this->container->getParameter('places');
        $latitude = $placesData[0]['latitude'];
        $longitude = $placesData[0]['longitude'];

        //get user
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneByUsername('user5@user.com');

        //get google place service mock 
        $googlePlaceServiceMock = $this->createGooglePlaceServiceMock();

        //get place service and inject mock it in
        $placeService = new PlaceService($googlePlaceServiceMock, $this->em);

        //create not existing place and userPlace for user
        $placeService->createPlace($latitude, $longitude, $user);

        //get all userPlace
        $userPlaces = $this->em->getRepository('AppBundle:UserPlace')->findBy(['latitude' => $latitude, 'longitude' => $longitude]);

        //get userPlace count
        $userPlacesCount = count($userPlaces);

        $this->assertEquals(4, $userPlacesCount, 'createUserPlace() method in PlaceService don\'t work correctly');
    }
}