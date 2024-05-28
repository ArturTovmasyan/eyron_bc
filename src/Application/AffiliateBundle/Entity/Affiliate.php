<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 8/4/16
 * Time: 7:12 PM
 */
namespace Application\AffiliateBundle\Entity;

use AppBundle\Traits\File;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * Class Affiliate
 * @package Application\AffiliateBundle\Entity
 *
 * @ORM\Entity(repositoryClass="Application\AffiliateBundle\Entity\Repository\AffiliateRepository")
 * @ORM\Table(name="affiliate")
 */
class Affiliate
{
    const CITY_TYPE    = "city";
    const REGION_TYPE  = "region";
    const COUNTRY_TYPE = "country";

    use File;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"affiliate"})
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     * @Groups({"affiliate"})
     */
    protected $name;

    /**
     * @ORM\Column(name="link", type="string", length=500, nullable=true)
     */
    protected $link;

    /**
     * @ORM\Column(name="ufi", type="string", length=20, nullable=true, unique=true)
     */
    protected $ufi;

    /**
     * @ORM\Column(name="place_type", type="string", length=20, nullable=true)
     */
    protected $placeType = self::CITY_TYPE;

    /**
     * @ORM\Column(name="size", type="array", nullable=true)
     * @Groups({"affiliate"})
     */
    protected $size;

    /**
     * @ORM\Column(name="links", type="array", nullable=true)
     * @Groups({"affiliate_links"})
     */
    protected $links;

    /**
     * @ORM\ManyToOne(targetEntity="Application\AffiliateBundle\Entity\AffiliateType")
     * @ORM\JoinColumn(name="affiliate_type_id", referencedColumnName="id", nullable=false)
     *
     * @Groups({"affiliate_affiliateType"})
     */
    protected $affiliateType;

    /**
     * @ORM\Column(name="publish", type="boolean")
     */
    protected $publish = false;

    /**
     * @var
     */
    protected $cacheDownloadLink;

    /**
     * @VirtualProperty()
     * @Groups({"affiliate"})
     */
    public function getHtmlContent()
    {
        return $this->replacePlaceHolders($this->getAffiliateType()->getHtmlContent());
    }
    

    /**
     * @VirtualProperty()
     * @Groups({"affiliate"})
     */
    public function getJsContent()
    {
        return $this->replacePlaceHolders($this->getAffiliateType()->getJsContent());
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName() ? $this->getName() : "_" . $this->getId();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Affiliate
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
     * Set link
     *
     * @param string $link
     *
     * @return Affiliate
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set affiliateType
     *
     * @param \Application\AffiliateBundle\Entity\AffiliateType $affiliateType
     *
     * @return Affiliate
     */
    public function setAffiliateType(\Application\AffiliateBundle\Entity\AffiliateType $affiliateType = null)
    {
        $this->affiliateType = $affiliateType;

        return $this;
    }

    /**
     * Get affiliateType
     *
     * @return \Application\AffiliateBundle\Entity\AffiliateType
     */
    public function getAffiliateType()
    {
        return $this->affiliateType;
    }

    /**
     * @return mixed
     */
    public function getSizeString()
    {
        return  $this->getSize() ? implode('x', $this->getSize()) : '';
    }

    /**
     * @param $size
     * @return $this
     */
    public function setSizeString($size)
    {
        $size = explode('x', $size);
        if (count($size) == 2){
            $this->size = $size;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * Set links
     *
     * @param array $links
     *
     * @return Affiliate
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * @param $link
     * @return $this
     */
    public function addLink($link)
    {
        if (!is_array($this->links)){
            $this->links = [];
        }

        $this->links[] = $link;

        return $this;
    }

    /**
     * Get links
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @return mixed
     */
    public function getCacheDownloadLink()
    {
        return $this->cacheDownloadLink ? $this->cacheDownloadLink : $this->getDownloadLink();
    }

    /**
     * @param mixed $cacheDownloadLink
     */
    public function setCacheDownloadLink($cacheDownloadLink)
    {
        $this->cacheDownloadLink = $cacheDownloadLink;
    }

    /**
     * @return mixed
     */
    public function getUfi()
    {
        return $this->ufi;
    }

    /**
     * @param $ufi
     * @return $this
     */
    public function setUfi($ufi)
    {
        $this->ufi = $ufi;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlaceType()
    {
        return $this->placeType;
    }

    /**
     * @param $placeType
     * @return $this
     * @throws \Exception
     */
    public function setPlaceType($placeType)
    {
        if(!in_array($placeType, [self::CITY_TYPE, self::REGION_TYPE, self::COUNTRY_TYPE])){
            throw new \Exception('Invalid place type');
        }

        $this->placeType = $placeType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPublish()
    {
        return $this->publish;
    }

    /**
     * @param $publish
     * @return $this
     */
    public function setPublish($publish)
    {
        $this->publish = $publish;

        return $this;
    }

    /**
     * @param $content
     * @return mixed
     */
    public function replacePlaceHolders($content)
    {
        $newContent = str_replace(AffiliateType::LINK_PLACEHOLDER, $this->getLink(), $content);
        $newContent = str_replace(AffiliateType::IMAGE_PLACEHOLDER, $this->getCacheDownloadLink(), $newContent);
        $newContent = str_replace(AffiliateType::AID_PLACEHOLDER, AffiliateType::$bookingAId, $newContent);
        $newContent = str_replace(AffiliateType::UFI_PLACEHOLDER, $this->getUfi(), $newContent);
        $newContent = str_replace(AffiliateType::PLACE_TYPE_PLACEHOLDER, $this->getPlaceType(), $newContent);

        return $newContent;
    }
}
