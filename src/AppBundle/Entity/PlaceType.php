<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PlaceType
 *
 * @ORM\Table(name="place_type")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\PlaceTypeRepository")
 * @UniqueEntity(
 *     fields={"name"},
 *     message="This place type is already use."
 * )
 */
class PlaceType
{
    const TYPE_CITY               = 'city';
    const TYPE_COUNTRY            = 'country';
    const COUNTRY_SHORT_NAME      = 'country_short_name';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="name", type="string", length=60, unique=true)
     * @Assert\Type("string")
     * @Assert\Length(
     *      min = 2,
     *      max = 60,
     *      minMessage = "Your place type must be at least {{ limit }} characters long",
     *      maxMessage = "Your place type cannot be longer than {{ limit }} characters"
     * )
     * @Assert\NotBlank(message = "Place type can't be blank")
     */
    protected $name;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return PlaceType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
    }
}
