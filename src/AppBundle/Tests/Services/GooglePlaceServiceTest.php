<?php

namespace AppBundle\Tests\Services;

use AppBundle\Entity\PlaceType;
use AppBundle\Tests\Controller\BaseClass;

/**
 * Class GooglePlaceServiceTest
 * @package AppBundle\Tests\Service
 */
class GooglePlaceServiceTest extends BaseClass
{
    /**
     * This data provider create data for place
     *
     * @return array
     */
    public function placeData()
    {
        //get places data from parameter
        $placesData = static::createClient()->getContainer()->getParameter('places');

        $latitudeArmenia = $placesData[0]['latitude'];
        $longitudeArmenia = $placesData[0]['longitude'];
        $armShortName = $placesData[0]['short_name'];

        $armenia = $placesData[0]['country'];
        $yerevan = $placesData[0]['city'];

        $latitudeRussia = $placesData[1]['latitude'];
        $longitudeRussia = $placesData[1]['longitude'];

        $data = array(
            array('latitude' => $latitudeArmenia,
                'longitude' => $longitudeArmenia,
                'save' => false,
                'placeName' => [PlaceType::TYPE_CITY => $yerevan, PlaceType::TYPE_COUNTRY => $armenia, PlaceType::COUNTRY_SHORT_NAME => $armShortName]),

            array('latitude' => $latitudeRussia,
                'longitude' => $longitudeRussia,
                'save' => true,
                'placeName' => []));

        return $data;
    }
    
    /**
     * This function is used to test getPlace() method in google place service
     * 
     * @dataProvider placeData
     *
     * @param $latitude
     * @param $longitude
     * @param $save
     * @param $placeName
     */
    public function testGetPlace($latitude, $longitude, $save, $placeName)
    {
        //get google place service
        $googlePlaceService = $this->container->get('app.google_place');

        //get place by service
        $googlePlace = $googlePlaceService->getPlace($latitude, $longitude, $save);

        //check if save value is false
        if (!$save) {
            $this->assertEquals($placeName, $googlePlace, 'Places don\'t found, please check your google server key');

        } else {
            $this->assertEquals(2, count($googlePlace), 'getPlace() method by param save don\'t work correctly');
        }
    }
}