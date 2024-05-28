<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 8/4/16
 * Time: 12:17 PM
 */
namespace Application\AffiliateBundle\Entity;

use AppBundle\Traits\File;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * Class AffiliateType
 * @package Application\AffiliateBundle\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="affiliate_type")
 */
class AffiliateType
{
    const AID_PLACEHOLDER        = '%aid%';
    const IMAGE_PLACEHOLDER      = '%image%';
    const LINK_PLACEHOLDER       = '%link%';
    const UFI_PLACEHOLDER        = '%ufi%';
    const PLACE_TYPE_PLACEHOLDER = '%place_type%';

    const LEFT_ZONE     = 0;
    const RIGHT_ZONE    = 1;
    const TOP_ZONE      = 2;
    const BOTTOM_ZONE   = 3;
    const INNER_ZONE    = 4;

    public static $bookingAId;

    use File;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"affiliateType"})
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=50, nullable=false, unique=true)
     * @Groups({"affiliateType"})
     */
    protected $name;

    /**
     * @ORM\Column(name="zone", type="smallint", nullable=false)
     * @Groups({"affiliateType"})
     * @Assert\Choice(callback = "getAllowedZones")
     */
    protected $zone;

    /**
     * @ORM\Column(name="html_content", type="string", length=5000, nullable=true)
     * @Groups({"affiliateType"})
     */
    protected $htmlContent;

    /**
     * @ORM\Column(name="js_content", type="string", length=5000, nullable=true)
     * @Groups({"affiliateType"})
     */
    protected $jsContent;

    /**
     * @ORM\Column(name="default_link", type="string", length=500, nullable=true)
     */
    protected $defaultLink;

    /**
     * @var
     */
    protected $cacheDownloadLink;

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
     * Set htmlContent
     *
     * @param string $htmlContent
     *
     * @return AffiliateType
     */
    public function setHtmlContent($htmlContent)
    {
        $this->htmlContent = $htmlContent;

        return $this;
    }

    /**
     * Get htmlContent
     *
     * @return string
     */
    public function getHtmlContent()
    {
        return $this->htmlContent;
    }

    /**
     * Set jsContent
     *
     * @param string $jsContent
     *
     * @return AffiliateType
     */
    public function setJsContent($jsContent)
    {
        $this->jsContent = $jsContent;

        return $this;
    }

    /**
     * Get jsContent
     *
     * @return string
     */
    public function getJsContent()
    {
        return $this->jsContent;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDefaultLink()
    {
        return $this->defaultLink;
    }

    /**
     * @param mixed $defaultLink
     */
    public function setDefaultLink($defaultLink)
    {
        $this->defaultLink = $defaultLink;
    }

    /**
     * @return mixed
     */
    public function getCacheDownloadLink()
    {
        return $this->cacheDownloadLink ? $this->cacheDownloadLink : $this->getDownloadLink();
    }

    /**
     * @param $cacheDownloadLink
     * @return $this
     */
    public function setCacheDownloadLink($cacheDownloadLink)
    {
        $this->cacheDownloadLink = $cacheDownloadLink;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * @param mixed $zone
     */
    public function setZone($zone)
    {
        $this->zone = $zone;
    }

    /**
     * @return array
     */
    public static function getAllowedZones()
    {
        return [self::LEFT_ZONE, self::RIGHT_ZONE, self::TOP_ZONE, self::BOTTOM_ZONE, self::INNER_ZONE];
    }

    public function getZoneString()
    {
        $zones = [
            AffiliateType::LEFT_ZONE    => 'Left',
            AffiliateType::RIGHT_ZONE   => 'Right',
            AffiliateType::TOP_ZONE     => 'Top',
            AffiliateType::BOTTOM_ZONE  => 'Bottom',
            AffiliateType::INNER_ZONE   => 'Inner'
        ];

        return isset($zones[$this->getZone()]) ? $zones[$this->getZone()] : '';
    }

    public function replacePlaceHolders($content)
    {
        $newContent = str_replace(AffiliateType::LINK_PLACEHOLDER, $this->getDefaultLink(), $content);
        $newContent = str_replace(AffiliateType::IMAGE_PLACEHOLDER, $this->getCacheDownloadLink(), $newContent);
        $newContent = str_replace(AffiliateType::AID_PLACEHOLDER, self::$bookingAId, $newContent);

        return $newContent;
    }
}
