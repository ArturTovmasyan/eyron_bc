<?php

namespace AppBundle\Traits\Mock;

/**
 * Class MockGooglePlaceServiceTrait
 * @package AppBundle\Traits
 */
trait MockGooglePlaceServiceTrait 
{
    /**
     * This function is used to create mock for Google place service
     */
    public function createGooglePlaceServiceMock()
    {
        //set place array data
        $placeData = ['city' => 'yerevan', 'country' => 'armenia'];

        //create mock for getPlace() method in google place service
        $mock = $this
            ->getMockBuilder('\AppBundle\Services\GooglePlaceService')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->any())
            ->method('getPlace')
            ->will($this->returnValue($placeData));

        return $mock;
    }
}