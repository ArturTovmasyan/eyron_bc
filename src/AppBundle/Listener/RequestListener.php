<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 2/16/16
 * Time: 11:37 AM
 */

namespace AppBundle\Listener;

use AppBundle\Controller\Rest\MainRestController;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class LocaleListener
 * @package AppBundle\Listener
 */
class RequestListener //implements EventSubscriberInterface
{
    private $defaultLocale;
    private $mandatoryVersions;
    private $tokenStorage;
    private $em;
    private $translator;
    private $stopwatch;
    private $angular2Host;

    /**
     * RequestListener constructor.
     * @param string $defaultLocale
     * @param $iosMandatoryVersion
     * @param $androidMandatoryVersion
     * @param TokenStorage $tokenStorage
     * @param EntityManager $entityManager
     * @param $translator
     * @param Stopwatch $stopwatch
     * @param $angular2Host
     */
    public function __construct($defaultLocale = "en", $iosMandatoryVersion, $androidMandatoryVersion,
                                TokenStorage $tokenStorage, EntityManager $entityManager, $translator, Stopwatch $stopwatch, $angular2Host)
    {
        $this->defaultLocale = $defaultLocale;
        $this->tokenStorage  = $tokenStorage;
        $this->em            = $entityManager;
        $this->translator    = $translator;
        $this->stopwatch     = $stopwatch;
        $this->angular2Host  = $angular2Host;

        $this->mandatoryVersions = [
            MainRestController::IOS_REQUEST_PARAM     => $iosMandatoryVersion,
            MainRestController::ANDROID_REQUEST_PARAM => $androidMandatoryVersion
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        //get stopwatch component
        $stopwatch = $this->stopwatch;

        // Start event named 'eventName'
        $stopwatch->start('bl_set_locale_listener');

        $request = $event->getRequest();

        $mobileAppVersion  = $request->query->get('mobileAppVersion');
        $mobileAppPlatform = $request->query->get('mobileAppPlatform');

        if ($mobileAppVersion && $mobileAppPlatform){
            if(isset($this->mandatoryVersions[$mobileAppPlatform]) &&
                version_compare($mobileAppVersion, $this->mandatoryVersions[$mobileAppPlatform]) == -1){
                $event->setResponse(new Response('You need to update your app', Response::HTTP_UPGRADE_REQUIRED));

                $stopwatch->stop('bl_set_locale_listener');
                return;
            }
        }

        $token = $this->tokenStorage->getToken();
        if ($token && is_object($user = $token->getUser())){

            if (strpos($t = $request->get('_route'), 'sonata_admin') === 0){
                if ($this->em->getFilters()->isEnabled('visibility_filter')) {
                    $this->em->getFilters()->disable('visibility_filter');
                }
            }
            else {
                $filter = $this->em->getFilters()->getFilter('visibility_filter');
                $filter->setParameter('userId', $user->getId());
            }
        }

        $currentUrl = $request->getUri();

        //check if current url is not admin
        if (strpos($currentUrl, 'admin') == false) {
            if (!$request->hasPreviousSession()) {
                $stopwatch->stop('bl_set_locale_listener');
                return;
            }

            // try to see if the locale has been set as a _locale routing parameter
            if ($locale = $request->attributes->get('_locale')) {
                $request->getSession()->set('_locale', $locale);
            } else {
                // if no explicit locale has been set on this request, use one from the session
                $locale = $request->getSession()->get('_locale', $this->defaultLocale);
                $request->setLocale($locale);
                $this->translator->setLocale($locale);
            }
        }
        $stopwatch->stop('bl_set_locale_listener');
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {

//        $responseHeaders = $event->getResponse()->headers;
//        $responseHeaders->set('Access-Control-Allow-Headers', 'origin, content-type, accept,Authorization, X-Requested-With, apikey');
//        $responseHeaders->set('Access-Control-Allow-Origin', $this->angular2Host);
//        $responseHeaders->set('Access-Control-Allow-Methods', 'POST, GET, PUT, DELETE, PATCH, OPTIONS');

        $request = $event->getRequest();
        if ($request->getPathInfo() == '/logout') {
            $response = $event->getResponse();
            $response->headers->clearCookie('REMEMBERME');
        }

    }
}