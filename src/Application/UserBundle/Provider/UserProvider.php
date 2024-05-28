<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/9/15
 * Time: 3:41 PM
 */

namespace Application\UserBundle\Provider;

use AppBundle\Services\GoogleAnalyticService;
use Application\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\FacebookResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TwitchResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TwitterResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as BaseProvider;


/**
 * Class UserProvider
 * @package Application\UserBundle\Provider
 */
class UserProvider extends  BaseProvider
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Constructor
     *
     * @param UserManagerInterface $userManager
     * @param Container $container
     */
    public function __construct(UserManagerInterface $userManager, Container $container)
    {
        $this->userManager = $userManager;
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username, $response = null)
    {
        // get owner
        $resourceOwner = $response->getResourceOwner();

        // check owner resource
        if($resourceOwner instanceof GoogleResourceOwner){

            // get google user
            $user = $this->createGoogleUser($response->getResponse());
        }
        elseif($resourceOwner instanceof TwitterResourceOwner){

            //get access token and secret
            $accessToken = $response->getAccessToken();
            $tokenSecret = $response->getTokenSecret();

            // get twitter user
            $user = $this->createTwitterUser($response->getResponse(), $accessToken, $tokenSecret);
        }
        elseif($resourceOwner instanceof FacebookResourceOwner){
            
            // get facebook user
            $user = $this->createFacebookUser($response->getResponse());
        }
        else {
            // return exception if user not found,
            throw new UnsupportedUserException(sprintf('User not found, please try again'));
        }

        if ($user->getSocialPhotoLink() && !$user->getFileName()) {
            $fileName = md5(microtime()) . '.jpg';
            file_put_contents($user->getAbsolutePath() . $fileName, fopen($user->getSocialPhotoLink(), 'r'));
            $user->setFileName($fileName);
            $user->setFileSize(filesize($user->getAbsolutePath() . $fileName));
            $user->setFileOriginalName($user->getFirstName() . '_photo');
        }

        // return user
        return $user;
    }


    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        return $this->loadUserByUsername($response->getNickname(), $response);
    }


    /**
     * This function is used to create google user
     *
     * @param $response
     * @param $accessToken
     * @param $id
     * @return User|\FOS\UserBundle\Model\UserInterface
     */
    private function createGoogleUser($response, $accessToken, $id)
    {
        // check is user in our bd
        $user = $this->userManager->findUserBy(array('gplusUid'=>$response['id']));

        if (!$user && isset($response['email'])){
            $user = $this->userManager->findUserBy(array('email' => $response['email']));

            if ($user && !$user->getFileName() && isset($response['picture'])){
                $photoPath = $response['picture'];
                if (strpos($photoPath, "?")){
                    $photoPath = substr($photoPath, 0, strpos($photoPath, "?"));
                }
                $user->setSocialPhotoLink($photoPath);
            }
        }

        // if user not found in bd, create
        if(!$user) {

            $user = new User();
            $user->setGoogleId($response['id']);
            $user->setEmail(isset($response['email']) ? $response['email'] : $user->getSocialFakeEmail());
            $user->setUsername(isset($response['email']) ? $response['email'] : $user->getSocialFakeEmail());
            $user->setFirstName($response['given_name']);
            $user->setLastName($response['family_name']);
            $user->setGplusData(json_encode($response));

            if (isset($response['gender'])) {
                $user->setGender($response['gender'] == "male" ? User::MALE : User::FEMALE);
            }

            $photoPath = $response['picture'];
            if (strpos($photoPath, "?")){
                $photoPath = substr($photoPath, 0, strpos($photoPath, "?"));
            }
            $user->setSocialPhotoLink($photoPath);
            $user->setPassword('');

            $this->userManager->updateUser($user);
            $socialName = $user->getSocialsName();

            $this->container->get('request_stack')->getCurrentRequest()->getSession()
                ->getFlashBag()
                ->set('userRegistration','User registration by '.$socialName.' from Web')
            ;

            //send post on user Google plus wall
//            $this->container->get('app.post_social_wall')->postOnGoogleWall();
        }

        return $user;
    }

    /**
     * @param $response
     * @param null $accessToken
     * @return User|\FOS\UserBundle\Model\UserInterface
     * @throws \Exception
     * @throws \Throwable
     */
    private function createFacebookUser($response, $accessToken = null)
    {
        // check is user in our bd
        $user = $this->userManager->findUserBy(array('facebookUid' => $response['id']));

        if (!$user && isset($response['email'])){
            $user = $this->userManager->findUserBy(array('email' => $response['email']));

            if ($user && !$user->getFileName()){
                $user->setSocialPhotoLink("https://graph.facebook.com/" . $response['id'] . "/picture?type=large");
            }
        }

        // if user not found in bd, create
        if(!$user) {

            $user = new User();
            $user->setFacebookId($response['id']);
            $user->setEmail(isset($response['email']) ? $response['email'] : $user->getSocialFakeEmail());
            $user->setUsername(isset($response['email']) ? $response['email'] : $user->getSocialFakeEmail());
            $user->setFirstName($response['first_name']);
            $user->setLastName($response['last_name']);
            $user->setPassword('');
            $user->setSocialPhotoLink("https://graph.facebook.com/" . $response['id'] . "/picture?type=large");
            $user->setFacebookData(json_encode($response));

            // update user
            $this->userManager->updateUser($user);

            //get registration social name		
            $socialName = $user->getSocialsName();

            //get session
            $session = $this->container->get('request_stack')->getCurrentRequest()->getSession();

            $session->getFlashBag()
                ->set('userRegistration','User registration by '.$socialName.' from Web');

            //send post on user Facebook wall
            $this->container->get('app.post_social_wall')->postOnFacebookWall();
        }

        return $user;
    }

    /**
     * This function is used to create Twitter user
     * 
     * @param $response
     * @param $accessToken
     * @param $tokenSecret
     * @return User|\FOS\UserBundle\Model\UserInterface
     * @throws \Throwable
     */
    private function createTwitterUser($response, $accessToken, $tokenSecret)
    {
        // check is user in our bd
        $user = $this->userManager->findUserBy(array('twitterUid'=>$response['id']));

        // if user not found in bd, create
        if(!$user) {

            $user = new User();
            $user->setTwitterId($response['id']);
            $user->setEmail($user->getSocialFakeEmail());
            $user->setUsername($user->getSocialFakeEmail());
            $fullName = explode(' ', $response['name']);
            $user->setFirstName($fullName[0]);
            $user->setLastName($fullName[0]);
            $user->setSocialPhotoLink(str_replace('_normal', '', $response['profile_image_url']));
            $user->setTwitterData(json_encode($response));
            $user->setPassword('');

            $this->userManager->updateUser($user);
            $socialName = $user->getSocialsName();

            $this->container->get('request_stack')->getCurrentRequest()->getSession()
                ->getFlashBag()
                ->set('userRegistration','User registration by '.$socialName.' from Web');

            //send post on user Twitter wall
            $this->container->get('app.post_social_wall')->postOnTwitterWall($accessToken, $tokenSecret);
        }

        return $user;
    }
}