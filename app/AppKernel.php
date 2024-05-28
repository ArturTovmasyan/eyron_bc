<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;


class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            // custom bundles
            new AppBundle\AppBundle(),
            new Application\UserBundle\ApplicationUserBundle(),

            // fos bundles
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),

            //oauth bundle
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),

            // sonata bundles
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
            new Sonata\IntlBundle\SonataIntlBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\UserBundle\SonataUserBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),

            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),

            // jms bundles
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),


            new Application\CommentBundle\ApplicationCommentBundle(),

            new Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),
            new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),
            new RMS\PushNotificationsBundle\RMSPushNotificationsBundle(),

            new Liip\ImagineBundle\LiipImagineBundle(),
            // Sitemap Bundle include
            new Presta\SitemapBundle\PrestaSitemapBundle(),

            //include Genemu Bundle
            new Genemu\Bundle\FormBundle\GenemuFormBundle(),

            //Enable markdown bundle
            new Knp\Bundle\MarkdownBundle\KnpMarkdownBundle(),
            new SunCat\MobileDetectBundle\MobileDetectBundle(),

            new Lexik\Bundle\MaintenanceBundle\LexikMaintenanceBundle(),
            new Application\AffiliateBundle\ApplicationAffiliateBundle(),
            new Nelmio\CorsBundle\NelmioCorsBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test', 'behat'))) {
            $bundles[] = new Liuggio\ExcelBundle\LiuggioExcelBundle();
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
            $bundles[] = new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle();
            $bundles[] = new W3docs\LogViewerBundle\W3docsLogViewerBundle();
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Bazinga\Bundle\FakerBundle\BazingaFakerBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }

//    protected function initializeContainer() {
//        parent::initializeContainer();
//        if (PHP_SAPI == 'cli') {
//            $this->getContainer()->enterScope('request');
//            $this->getContainer()->set('request', new \Symfony\Component\HttpFoundation\Request(), 'request');
//        }
//    }

}
