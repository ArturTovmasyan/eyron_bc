<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/11/15
 * Time: 1:29 PM
 */

namespace AppBundle\Services;

use Application\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\Container;


/**
 * Class BucketListService
 * @package AppBundle\Services
 */
class BucketListService
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected  $container;

    /**
     * @var
     */
    protected $em;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
    }

    /**
     * This function is used to upload image
     *
     */

    /**
     * @param $object
     */
    public function uploadFile(&$object)
    {
        // the file property can be empty if the field is not required
        if (null == $object->getFile())
        {
            return;
        }
        // check file name
        if($object->getFileName()){
            // get file path
            $path = $object->getAbsolutePath() . $object->getFileName();
            // check file
            if(file_exists($path) && is_file($path)){
                // remove file
                unlink($path);
            }
        }

        // get file originalName
        $object->setFileOriginalName($object->getFile()->getClientOriginalName());

        // get file
        $path_parts = pathinfo($object->getFile()->getClientOriginalName());

        // generate filename
        if(!$path_parts['extension']){
            $extension = $object->getFile()->getMimeType();
            $extension = substr($extension ,strpos($extension, '/') + 1);
        }else{
            $extension = $path_parts['extension'];
        }
        $object->setFileName(md5(microtime()) . '.' . $extension);

        // set size
        $object->setFileSize($object->getFile()->getClientSize());

        // upload file
        $object->getFile()->move($object->getAbsolutePath(), $object->getFileName());

        // set file to null
        $object->setFile(null);
    }

    /**
     * This function is used to generate files for tablet
     *
     * @param $object
     */
    public function generateFilesForApplication(&$object)
    {
        // get original file
        $file = $object->getAbsolutePath() . $object->getFileName() ;

        if(!file_exists($file) && is_file($file)){
            return;
        }
        // create imagick for mobile image
        $im = new \Imagick($file);
        $im->setImageCompressionQuality(100);
        $im->resizeImage($this->container->getParameter('mobile')['with'], $this->container->getParameter('mobile')['height'],
            \Imagick::FILTER_LANCZOS, 0.7, false);

        // check if folder is not exist, create it
        if (!file_exists($object->getAbsoluteMobilePath())) {
            mkdir($object->getAbsoluteMobilePath(), 0777, true);
        }

        $mobileFile = $object->getAbsoluteMobilePath() . $object->getFileName();
        $im->writeImage( $mobileFile );
        $im->clear();
        $im->destroy();

        // create imagick for tablet image
        $im = new \Imagick($file);
        $im->setImageCompressionQuality(100);
        $im->resizeImage($this->container->getParameter('tablet')['with'], $this->container->getParameter('tablet')['height'],
            \Imagick::FILTER_LANCZOS, 0.7, false);

        // check if folder is not exist, create it
        if (!file_exists($object->getAbsoluteTabletPath())) {
            mkdir($object->getAbsoluteTabletPath(), 0777, true);
        }

        $tabletFile = $object->getAbsoluteTabletPath() . $object->getFileName();
        $im->writeImage( $tabletFile );
        $im->clear();
        $im->destroy();
    }

    /**
     * This function is used to check user have activity and set it
     *
     * @param $user
     * @param string $url
     */
    public function setUserActivity($user, &$url = null)
    {
        if ($user->getActivity()){
            $url = 'activity';
            return;
        }

        $feedCount = $this->em->getRepository('AppBundle:NewFeed')->findNewFeed($user->getId(), true);

        if ($feedCount == 0) {
            $url = 'goals_list';
            $user->setActivity(false);
        }
        else {
            $url = 'activity';
            $user->setActivity(true);
        }

        $this->em->persist($user);
        $this->em->flush();
    }
}