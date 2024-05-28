<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/15/15
 * Time: 11:39 AM
 */

namespace AppBundle\Traits;

/**
 * Class Location
 * @package AppBundle\Traits
 */
trait Location
{

    /**
     * @ORM\Column(type="string", name="address", nullable=true)
     * @var
     */
    protected $address;

    /**
     * @ORM\Column(type="float", name="lat", nullable=true)
     * @var
     */
    protected $lat;

    /**
     * @ORM\Column(type="float", name="lng", nullable=true)
     * @var
     */
    protected $lng;


    /**
     * Set address
     *
     * @param integer $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return integer
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set lat
     *
     * @param float $lat
     * @return $this
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param float $lng
     * @return $this
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng
     *
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param $location
     * @return $this
     */
    public function setRawLocation($location)
    {
        if (is_null($location)){
            $this->setAddress(null);
            $this->setLat(null);
            $this->setLng(null);
        }

        $location = json_decode($location);
        if (isset($location->address) && isset($location->location) && isset($location->location->latitude) && isset($location->location->longitude)){
            $this->setAddress($location->address);
            $this->setLat($location->location->latitude);
            $this->setLng($location->location->longitude);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getRawLocation()
    {
        $location = [
            'address' => $this->address,
            'location' => [
                'latitude' => $this->lat,
                'longitude' => $this->lng
            ]
        ];

        return json_encode($location);
    }

    /**
     * This function is used to return json location for twig
     *
     * @return string
     */
    public function getJsonLocations()
    {
        // check data
        if($this->getLng() && $this->getLat() && $this->getAddress()){
            $result = array(
                "location" =>
                    array(
                        "latitude" => $this->getLng(),
                        "longitude" => $this->getLat()
                    ),
                "address" => $this->getAddress() );

            return json_encode($result);
        }

        return null;
    }

    /**
     * @return array|null
     */
    public function getLocation()
    {
        if($this->getLng() && $this->getLat() && $this->getAddress()){
            $result = array(
                "latitude" =>  $this->getLat(),
                "longitude" =>$this->getLng(),
                "address" => $this->getAddress()
            );

            return $result;
        }

        return null;
    }

}