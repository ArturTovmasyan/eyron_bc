<?php

namespace AppBundle\Services;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Session\Session;
use TwitterAPIExchange;

class PostOnSocialWallService
{
    const FACEBOOK_SHARE_LINK = 'https://web.facebook.com/dialog/share';
    const TWITTER_SHARE_LINK = 'https://api.twitter.com/1.1/statuses/update.json';
    const TWITTER_SHARE_MEDIA_LINK = 'https://upload.twitter.com/1.1/media/upload.json';
    const GOOGLE_SHARE_LINK = 'https://plus.google.com/share';

    /**
     * @var Container $container
     */
    private $container;

    /**
     * @var $protocol string
     */
    private $protocol;

    /**
     * @var $projectHost string
     */
    private $projectHost;

    /**
     * @var Translator $translator
     */
    private $translator;

    /**
     * @var $imageLink string
     */
    private $imageLink;

    /**
     * @var $message string
     */
    private $message;

    /**
     * @var Session $session
     */
    private $session;

    /**
     * PostOnSocialWallService constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->projectHost = $this->container->getParameter('project_name');
        $this->translator = $this->container->get('translator');
        $this->protocol = $this->container->getParameter('protocol');
        $this->imageLink = $this->protocol.'://'.$this->projectHost.'/bundles/app/images/BL127.png';
        $this->message = $this->translator->trans('social_post_text', [], 'messages');
        $this->session = $this->container->get('session');
    }

    /**
     * This function is used to save facebook share link in session
     *
     */
    public function postOnFacebookWall()
    {
        $appId = $this->container->getParameter('facebook_client_id');
        $projectName = $this->container->getParameter('email_sender');

        //generate data for post on wall
        $urlParams = [
            'app_id' => $appId,
            'display' => 'page',
            'title' => $projectName,
            'image' => $this->imageLink,
            'quote' => $this->message,
            'href' => $this->projectHost,
            'redirect_uri' => $this->protocol.'://'.$this->projectHost,
            'hashtag' => '#BucketList127'
        ];

        //generate post on FB wall url
        $url = sprintf('%s', self::FACEBOOK_SHARE_LINK).'?'.http_build_query($urlParams);

        //set session for FB post
        $this->session->set('post_url', $url);
    }

    /**
     * This function is used to send post on user twitter wall
     *
     * @param $accessToken
     * @param $tokenSecret
     * @throws \Exception
     */
    public function postOnTwitterWall($accessToken, $tokenSecret)
    {
        //twitter secret and keys
        $twitterConsumerKey = $this->container->getParameter('twitter_client_id');
        $twitterConsumerSecret =  $this->container->getParameter('twitter_client_secret');

        //generate Twitter authorization settings
        $settings = ['oauth_access_token' => $accessToken,
            'oauth_access_token_secret' => $tokenSecret,
            'consumer_key' => $twitterConsumerKey,
            'consumer_secret' => $twitterConsumerSecret
        ];

        //get twitter class
        $twitter = new TwitterAPIExchange($settings);

        //generate data for upload twitter image
        $postImageData = ['media' => base64_encode(file_get_contents($this->imageLink))];

        //upload image for twitter
        $imageData = $twitter->buildOauth(self::TWITTER_SHARE_MEDIA_LINK, 'POST')
            ->setPostfields($postImageData)
            ->performRequest();

        //json decode $imageData
        $imageData = json_decode($imageData, true);

        //get image id
        $imageId = $imageData['media_id'];

        //generate twitter status
        $status = substr($this->message, 0, 98);
        $status = $status.' '.$this->projectHost.' #BucketList127';

        //generate data for post on twitter wall
        $postData = ['status' => $status, 'media_ids' => $imageId];

        try{
            //send post on twitter wall
            $twitter->buildOauth(self::TWITTER_SHARE_LINK, 'POST')
                ->setPostfields($postData)
                ->performRequest();

        }catch (\Exception $e){
        }
    }

    /**
     * This function is used to save google+ share link in session
     *
     */
    public function postOnGoogleWall()
    {
        //generate google+ share link
        $url = sprintf('%s',self::GOOGLE_SHARE_LINK).'?url='.$this->projectHost;

        //set session for FB post
        $this->session->set('post_url', $url);
    }
}