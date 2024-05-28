<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/9/15
 * Time: 7:43 PM
 */

namespace AppBundle\Admin;

use AppBundle\Entity\Tag;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Class AphorismAdmin
 * @package AppBundle\Admin
 */
class AphorismAdmin extends AbstractAdmin
{
    protected  $baseRouteName = 'admin-aphorism';
    protected  $baseRoutePattern = 'admin-aphorism';

    /**
     * override list query
     *
     * @param string $context
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface */

    public function createQuery($context = 'list')
    {
        // call parent query
        $query = parent::createQuery($context);

        // add selected
        $query->addSelect('tg');
        $query->leftJoin($query->getRootAlias() . '.tags', 'tg');

        return $query;
    }

    /**
     * @param \Sonata\AdminBundle\Show\ShowMapper $showMapper
     *
     * @return void
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('author', null, ['label'=>'admin.label.name.author'])
            ->add('content', null, ['label'=>'admin.label.name.content'])
            ->add('tags', null, ['label'=>'admin.label.name.tags'])
        ;
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('author', null, ['label'=>'admin.label.name.author'])
            ->add('content', 'textarea', ['label'=>'admin.label.name.content'])
            ->add('tags', null, ['label'=>'admin.label.name.tags'])
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, ['label'=>'admin.label.name.id','show_filter' => true])
            ->add('author', null, ['label'=>'admin.label.name.author','show_filter' => true])
            ->add('content', null, ['label'=>'admin.label.name.content','show_filter' => true])
            ->add('tags', null, ['label'=>'admin.label.name.tags','show_filter' => true])
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, ['label'=>'admin.label.name.id'])
            ->add('author', null, ['label'=>'admin.label.name.author'])
            ->add('content', null, ['label'=>'admin.label.name.content'])
            ->add('tags', null, ['label'=>'admin.label.name.tags'])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        $this->getAndAddTags($object);
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {
        $this->getAndAddTags($object);
    }

    /**
     * @param $object
     */
    private function getAndAddTags($object)
    {
        // get container
        $container =  $this->getConfigurationPool()->getContainer();

        // get entity manager
        $em = $container->get('doctrine')->getManager();

        // get content
        $content = $object->getContent();

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
                if(!$object->getTags()->contains($oldTag)){

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
     * @param $text
     * @return mixed
     */
    private function getHashTags($text)
    {
        // get description
        $content = strtolower($text);

        // get hash tags
        preg_match_all("/#(\w+)/", $content, $hashTags);

        // return hash tags
        return $hashTags[1];
    }
}