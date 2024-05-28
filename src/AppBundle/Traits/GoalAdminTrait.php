<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/10/16
 * Time: 3:28 PM
 */

namespace AppBundle\Traits;

use AppBundle\Entity\Goal;
use AppBundle\Entity\GoalImage;
use AppBundle\Entity\Tag;
use AppBundle\Model\PublishAware;

/**
 * Class GoalAdmin
 * @package AppBundle\Traits
 */
trait GoalAdminTrait
{
    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        $object->setPublish(PublishAware::PUBLISH);
        $object->setStatus(Goal::PUBLIC_PRIVACY);
        $this->preUpdate($object);
    }

    /**
     * @param $object
     */
    public function getAndAddTags(&$object)
    {
        // get container
        $container =  $this->getConfigurationPool()->getContainer();

        // get entity manager
        $em = $container->get('doctrine')->getManager();

        // get content
        $content = $object->getDescription();

        // get tags from description
        $tags = $this->getHashTags($content);

        // check tags
        if($tags){

            // get tags from db
            $dbTags = $em->getRepository("AppBundle:Tag")->getTagTitles();

            // get new tags
            $newTags = array_diff($tags, $dbTags);

            // tags that is already exist in database
            $existTags = array_diff($tags, $newTags);

            // get tags from database
            $oldTags = $em->getRepository("AppBundle:Tag")->findTagsByTitles($existTags);

            // loop for array
            foreach($newTags as $tagString){

                // create new tag
                $tag = new Tag();

                $title = strtolower($tagString);

                // replace ',' symbols
                $title = str_replace(',', '', $title);

                // replace ':' symbols
                $title = str_replace(':', '', $title);

                // replace '.' symbols
                $title = str_replace('.', '', $title);

                // set tag title
                $tag->setTag($title);

                // add tag
                $object->addTag($tag);

                // persist tag
                $em->persist($tag);

            }

            // loop for tags n database
            foreach($oldTags as $oldTag){

                // check tag in collection
                if(!$object->getTags() || !$object->getTags()->contains($oldTag)){

                    // add tag
                    $object->addTag($oldTag);

                    // persist tag
                    $em->persist($oldTag);
                }
            }

            $em->flush();

        }
    }

    /**
     * @param $object
     */
    public function addImages(&$object)
    {
        $bucketService = $this->getConfigurationPool()->getContainer()->get('bl_service');

        //get images
        $images = $object->getImages();

        // check images
        if($images) {

            $hasListPhoto = false;
            $hasCoverPhoto = false;

            // loop for images
            foreach($images as $image) {
                if (!($image instanceof GoalImage)){
                    $object->removeImage($image);
                    continue;
                }

                if(!$image->getId() && !$image->getFile()){
                    continue;
                }

                if ($image->getList() == true){
                    $hasListPhoto = true;
                }else{
                    $image->setList(false);
                }

                if ($image->getCover() == true){
                    $hasCoverPhoto = true;
                }else{
                    $image->setCover(false);
                }

                // upload file
                $bucketService->uploadFile($image);
                $image->setGoal($object);
            }

            if (!$hasListPhoto && $images->first()){
                $images->first()->setList(true);
            }

            if (!$hasCoverPhoto && $images->first()){
                $images->first()->setCover(true);
            }
        }
    }

    /**
     * @param $text
     * @return mixed
     */
    public function getHashTags($text)
    {
        // get description
        $content = strtolower($text);

        // get hash tags
        preg_match_all("/#(\w+)/", $content, $hashTags);

        // return hash tags
        return $hashTags[1];
    }


    /**
     * @param $object
     */
    public function updateData(&$object)
    {
        $original = $this->getModelManager()->getEntityManager($this->getClass())->getUnitOfWork()->getOriginalEntityData($object);

        $user = $this->getConfigurationPool()->getContainer()->get('security.token_storage')->getToken()->getUser();

        // check and set author
        if((!$original && $object->getPublish() ==  PublishAware::PUBLISH) ||
            ($original['publish'] != $object->getPublish() &&
                $object->getPublish() == PublishAware::PUBLISH)){

            $object->setPublishedBy($user->getUserName());
        }

        $description = $object->getDescription();
        $object->setDescription($description);

        $object->setEditor($user);
        $object->setReadinessStatus(Goal::TO_PUBLISH);

        $this->getAndAddTags($object);
        $this->addImages($object);

        if ($videoLinks = $object->getVideoLink()){
            $videoLinks = array_values($videoLinks);
            $videoLinks = array_filter($videoLinks);

            $object->setVideoLink($videoLinks);
        }
    }
}