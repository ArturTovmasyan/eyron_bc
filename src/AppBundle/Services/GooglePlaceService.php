<?php

namespace AppBundle\Services;

use AppBundle\Entity\Place;
use AppBundle\Entity\PlaceType;
use Doctrine\ORM\EntityManager;

class GooglePlaceService
{
    const URL = 'https://maps.googleapis.com/maps/api/geocode/json';

    /**
     * @var EntityManager $em
     */
    protected $em;

    /**
     * @var string
     */
    protected $googleServerKey;


    /**
     * NotifyAboutDoneGoalByPlaceService constructor.
     * @param EntityManager $em
     * @param $googleServerKey string
     */
    public function __construct(EntityManager $em, $googleServerKey)
    {
        $this->em = $em;
        $this->googleServerKey = $googleServerKey;
    }

    /**
     * This function is used to get place data and notify about goal done
     *
     * @param $latitude float
     * @param $longitude float
     * @param $save boolean
     * @return mixed
     * @throws \Exception
     */
    public function getPlace($latitude, $longitude, $save = false)
    {
        //concat latitude and longitude by comma for api
        $latLng = trim($latitude).','.trim($longitude);

        //generate geo coding url for get place data by lang and long
        $url = sprintf('%s?latlng=%s&language=en&result_type=locality|country&key=%s', self::URL, $latLng, $this->googleServerKey);

        //use curl for get response
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //get response
        $response = curl_exec($ch);

        //close curl
        curl_close($ch);

        //json decode data
        $response = json_decode($response, true);

        //check response
        if (!$response) {
            throw new \Exception('Connection error');
        }

        //get error messages
        $errorMessage = array_key_exists('error_message', $response) ? $response['error_message'] : null;

        //check if error messages exist
        if (count($errorMessage) > 0) {
            throw new \InvalidArgumentException($errorMessage);
        }

        //set default array
        $placeArray = [];

        //get results
        $results = $response['results'];

        //check if results not empty
        if (!empty($results)) {

            //set country and city in placeArray
            $addressComponents = $results[0]['address_components'];

            foreach ($addressComponents as $address) {

                if ($address['types'][0] == "locality" && $address['types'][1] == "political") {
                    //set city
                    $city = $address['long_name'];
                    $placeArray[PlaceType::TYPE_CITY] = trim(strtolower($city));
                }

                if ($address['types'][0] == "country" && $address['types'][1] == "political") {
                    //set country
                    $country = $address['long_name'];
                    $placeArray[PlaceType::TYPE_COUNTRY] = trim(strtolower($country));
                }
            }

            //get max and min bounds for city
            $cityBounds = $results[0]['geometry']['bounds'];
            $cityMinBounds = $cityBounds['southwest'];
            $cityMaxBounds = $cityBounds['northeast'];

            //for country
            if (isset($results[1]) && isset($results[1]['geometry']) && isset($results[1]['geometry']['bounds'])) {
                $countryBounds = $results[1]['geometry']['bounds'];
                $countryMinBounds = $countryBounds['southwest'];
                $countryMaxBounds = $countryBounds['northeast'];
            }
            else{
                $countryMinBounds = $cityMinBounds;
                $countryMaxBounds = $cityMaxBounds;
            }

            //set place short name in placeArray
            if (isset($results[1]) && isset($results[1]['address_components']) && isset($results[1]['address_components'][0])
                && isset($results[1]['address_components'][0]['short_name'])
            ) {
                $placeArray[PlaceType::COUNTRY_SHORT_NAME] = strtolower(
                    $results[1]['address_components'][0]['short_name']
                );
            } elseif (isset($results[0]) && isset($results[0]['address_components']) && isset($results[0]['address_components'][0])
                && isset($results[0]['address_components'][0]['short_name'])
            ) {
                $placeArray[PlaceType::COUNTRY_SHORT_NAME] = strtolower(
                    $results[0]['address_components'][0]['short_name']
                );
            }

            //check if save value is true
            if ($save) {

                //remove short_name data in places array
                if (array_key_exists(PlaceType::COUNTRY_SHORT_NAME, $placeArray)) {
                    unset($placeArray[PlaceType::COUNTRY_SHORT_NAME]);
                }

                //set default placeIds value
                $placeIds = [];

                //get places in DB
                $placeInDb = $this->em->getRepository('AppBundle:Place')->findIdNameByName(array_values($placeArray));

                //remove existing place in createPlaces array
                if ($placeInDb) {

                    foreach ($placeInDb as $place) {

                        //get place ids
                        $placeIds[] = $place['id'];

                        //get place name
                        $placeName = $place['name'];

                        //remove existing place name in array
                        if (($key = array_search($placeName, $placeArray)) !== false) {
                            unset($placeArray[$key]);
                        }
                    }
                }

                //check if createPlaces exist
                if ($placeArray) {

                    //get all placeType index by name
                    $placeType = $this->em->getRepository('AppBundle:PlaceType')->findAllIndexByName();

                    foreach ($placeArray as $key => $place) {

                        if ($key == PlaceType::TYPE_COUNTRY) {
                            $minBounds = $countryMinBounds;
                            $maxBounds = $countryMaxBounds;
                        }
                        else {
                            $minBounds = $cityMinBounds;
                            $maxBounds = $cityMaxBounds;
                        }

                        $minLng = $minBounds['lng'];
                        $maxLng = $maxBounds['lng'];

                        $maxLng = $maxLng < $minLng ? $maxLng + 360 : $maxLng;

                        //create new place
                        $newPlace = new Place();
                        $newPlace->setName($place);
                        $newPlace->setPlaceType($placeType[$key]);

                        $newPlace->setMinLatitude($minBounds['lat']);
                        $newPlace->setMaxLatitude($maxBounds['lat']);

                        $newPlace->setMinLongitude($minLng);
                        $newPlace->setMaxLongitude($maxLng);

                        $this->em->persist($newPlace);
                        $this->em->flush();

                        $placeIds[] = $newPlace->getId();
                    }
                }
                
                return $placeIds;
            }

            return $placeArray;
        }

        return null;
    }
}