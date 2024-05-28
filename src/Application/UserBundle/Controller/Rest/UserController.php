<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 1/20/16
 * Time: 4:24 PM
 */
namespace Application\UserBundle\Controller\Rest;

use Application\UserBundle\Entity\User;
use AppBundle\Entity\UserGoal;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\UserBundle\Model\UserInterface;
use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


/**
 * @Rest\RouteResource("User")
 * @Rest\Prefix("/api/v1.0")
 */
class UserController extends FOSRestController
{
    /**
     * This function is used to get current user overall progress
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to to get current user overall progress",
     *  statusCodes={
     *         200="Returned when status changed",
     *         401="Access allowed only for registered users"
     *     },
     * )
     * @Secure(roles="ROLE_USER")
     * @Rest\View()
     */
    public function getOverallAction(Request $request)
    {
        //disable listener for stats count
        $this->container->get('bl.doctrine.listener')->disableUserStatsLoading();
        $sendNoteService = $this->get('bl_put_notification_service');

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        if($request->getContentType() == 'application/json' || $request->getContentType() == 'json'){
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        // check conditions
        switch($request->get('condition')){
            case UserGoal::ACTIVE:
                $condition = UserGoal::ACTIVE;
                break;
            case UserGoal::COMPLETED:
                $condition = UserGoal::COMPLETED;
                break;
            default:
                $condition = null;
        }

        $dream = $this->toBool($request->query->get('isDream'));
        $first = 0;
        $count = null;
        $owned = $request->get('owned');

        $requestFilter = [];
        $requestFilter[UserGoal::URGENT_IMPORTANT]          = $this->toBool($request->get('urgentImportant'));
        $requestFilter[UserGoal::URGENT_NOT_IMPORTANT]      = $this->toBool($request->get('urgentNotImportant'));
        $requestFilter[UserGoal::NOT_URGENT_IMPORTANT]      = $this->toBool($request->get('notUrgentImportant'));
        $requestFilter[UserGoal::NOT_URGENT_NOT_IMPORTANT]  = $this->toBool($request->get('notUrgentNotImportant'));


        if($owned){
            $lastUpdated = $em->getRepository('AppBundle:UserGoal')
                ->findOwnedUserGoals($currentUser, true);
        } else{
            $lastUpdated = $em->getRepository('AppBundle:UserGoal')
                ->findAllByUser($currentUser->getId(), $condition, $dream, $requestFilter, $first, $count, true);
        }

        if(is_null($lastUpdated)){
            return array('progress' => 0);
        }

        $response = new Response();

        $lastDeleted = $currentUser->getUserGoalRemoveDate();
        $lastModified = $lastDeleted > $lastUpdated ? $lastDeleted: $lastUpdated;

        $response->setLastModified($lastModified);

        $response->headers->set('cache-control', 'private, must-revalidate');

        // check is modified
        if ($response->isNotModified($request)) {
            return $response;
        }

        if($owned){
            $userGoals = $em->getRepository('AppBundle:UserGoal')
                ->findOwnedUserGoals($currentUser);
        } else{
            $userGoals = $em->getRepository('AppBundle:UserGoal')
                ->findAllByUser($currentUser->getId(), $condition, $dream, $requestFilter, $first, $count);
        }

        $progress = $sendNoteService->calculateProgress($userGoals);
        if ($progress) {
            $result = array('progress' => $progress);
            $response->setContent(json_encode($result));

            return $response;
        }

        return array('progress' => 0);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to register a new user",
     *  statusCodes={
     *         400="Bad request",
     *         200="The user was registered"
     *     },
     * parameters={
     *      {"name"="email", "dataType"="email", "required"=true, "description"="User`s email"},
     *      {"name"="plainPassword", "dataType"="string", "required"=true, "description"="User`s password"},
     *      {"name"="firstName", "dataType"="string", "required"=true, "description"="User`s first name | min=3 / max=20 symbols"},
     *      {"name"="lastName", "dataType"="string", "required"=true, "description"="User`s last name | min=3 / max=20 symbols"},
     *      {"name"="birthday", "dataType"="string", "required"=true, "description"="User`s birthday | in this 01/12/2015 format"},
     *      {"name"="profile_image", "dataType"="file", "required"=true, "description"="Users profile image file" },
     *      {"name"="apikey",   "dataType"="string",   "required"=false, "description"="User`s apikey"}
     *
     * }
     * )
     * @Rest\View(serializerGroups={"user"})
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //check if request content type is json
        if ($request->getContentType() == 'application/json' || $request->getContentType() == 'json') {

            //get content and add it in request after json decode
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        //get all data in request
        $data = $request->request->all();

        $returnResult = [];
        $tr = $this->get('translator');

        //check if plain password is not valid
        if($data['plainPassword'] !== $data['password']) {
            $returnResult['password'] = $tr->trans('fos_user.password.mismatch', [], 'validators');
        }

        $user = new User();
        $user->setUsername(array_key_exists('email', $data) ? $data['email'] : null);
        $user->setEmail(array_key_exists('email', $data) ? $data['email'] : null);
        $user->setPlainPassword(array_key_exists('plainPassword', $data) ? $data['plainPassword'] : null);
        $user->setFirstName(array_key_exists('firstName', $data) ? $data['firstName'] : null);
        $user->setLastName(array_key_exists('lastName', $data) ? $data['lastName'] : null);
        $user->setDateOfBirth(array_key_exists('birthday', $data) ? \DateTime::createFromFormat('d/m/Y', $data['birthday'])  : null);

        $validator = $this->get('validator');
        $errors = $validator->validate($user, null, ['Register', 'Default']);

        if(count($errors) > 0 || count($returnResult) > 0){

            foreach($errors as $error)
            {
                $returnResult[$error->getPropertyPath()] = $error->getMessage();
            }

            return new JsonResponse($returnResult, Response::HTTP_BAD_REQUEST);
        }

        $profileImage = $request->files->get('profile_image');

        if($profileImage){
            $user->setFile($profileImage);
            $blService = $this->container->get('bl_service');
            $blService->uploadFile($user);
        }

        $token = md5(microtime());
        $user->setRegistrationToken($token);

        $this->container->get('bl.email.sender')->sendConfirmEmail($user->getEmail(), $token, $user->getFirstName());

        $em->persist($user);
        $em->flush();

        $response = $this->loginAction($user, ['user']);

        return $response;
    }

    /**
     * @param User $user
     * @param $group
     * @param $isRegistered
     * @return mixed
     */
    private function loginAction(User $user, array $group, $isRegistered = null)
    {
        //check if user have image path
        if($user->getImagePath()) {
            $liipManager = $this->container->get('liip_imagine.cache.manager');
            $route = $this->container->get('router');
            $liipManager->getBrowserPath($user->getImagePath(), 'user_goal');
            $params = ['path' => ltrim($user->getImagePath(), '/'), 'filter' => 'user_goal'];
            $filterUrl = $route->generate('liip_imagine_filter', $params);
            $user->setMobileImagePath($filterUrl);
            $user->setCachedImage($liipManager->getBrowserPath($user->getImagePath(), 'user_image'));;
        }

        $request     = $this->get('request_stack')->getCurrentRequest();
        $providerKey = $this->container->getParameter('fos_user.firewall_name');
        $session     = $this->get('session');
        $response    = new JsonResponse();
        $token       = new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());

        $em = $this->getDoctrine()->getManager();
        $em->getRepository("AppBundle:Goal")->findMyDraftsAndFriendsCount($user);


        if ($request->get('apikey')){
            $apiKey = $user->getApiKey();
            if (is_null($apiKey)){
                $apiKey = md5($user->getUsername() . $this->container->getParameter('secret'));
                $user->setApiKey($apiKey);
                $em->flush();
            }
        }
        else{
            $this->get('security.token_storage')->setToken($token);
            $session->set($providerKey, serialize($token));
            $session->save();
        }


        $content = ['userInfo' => $user];

        if($isRegistered != null){
            $content['registred'] = $isRegistered;
        }

        if (isset($apiKey)){
            $content['apiKey'] = $apiKey;
        }
        else {

            $cookie = $request->cookies;
            $phpSessionId = $cookie->get('PHPSESSID');

            if(!$phpSessionId){
                $phpSessionId = $session->getId();
            }

            $content['sessionId'] = $phpSessionId;
        }


        $serializer = $this->get('serializer');
        if(!$request->query->get('mobileAppPlatform')){
            $group = array_merge($group, ["completed_profile", "image_info"]);

            $states = $content['userInfo']->getStats();

            $states['created'] = $em->getRepository('AppBundle:Goal')->findOwnedGoalsCount($content['userInfo']->getId(), false);

            $content['userInfo']->setStats($states);

            // get drafts
            $em->getRepository("AppBundle:Goal")->findMyIdeasCount($content['userInfo']);
            $em->getRepository("AppBundle:Goal")->findRandomGoalFriends($content['userInfo']->getId(), null, $goalFriendsCount, true);
            $content['userInfo']->setGoalFriendsCount($goalFriendsCount);
        }
        $contentJson = $serializer->serialize($content, 'json', SerializationContext::create()->setGroups($group));

        $response->setContent($contentJson);

        return $response;
    }

    /**
     * This function is used to login user
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to login user",
     *  statusCodes={
     *         200="Returned when was login",
     *         404="User not found"
     *     },
     * parameters={
     *      {"name"="username", "dataType"="string",   "required"=true,  "description"="User`s username"},
     *      {"name"="password", "dataType"="password", "required"=true,  "description"="User`s password"},
     *      {"name"="apikey",   "dataType"="string",   "required"=false, "description"="User`s apikey"}
     *
     * }
     *
     * )
     *
     * @Rest\View(serializerGroups={"user", "badge"})
     * @param $request
     * @return Response
     */
    public function postLoginAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $username = $request->get('username');
        $password = $request->get('password');

        if(!$username && !$password){
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
            $username = $request->get('username');
            $password = $request->get('password');
        }

        $user = $em->getRepository("ApplicationUserBundle:User")->findOneBy(array('username' => $username));

        if($user && ($user->isEnabled() || date_diff($user->getUpdatedAt(), (new \DateTime('now')))->y == 0)) ;
        {
            if(!$user->isEnabled()) {
                $user->setEnabled(true);
                $em->flush();
            }

            $encoderService = $this->get('security.encoder_factory');
            $encoder = $encoderService->getEncoder($user);

            if($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())){

                $response = $this->loginAction($user, array('user'));

                return $response;
            }
        }

        return new JsonResponse('Bad credentials', Response::HTTP_NOT_FOUND);
    }

    /**
     * This function is used login by social data
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used login by social data",
     *  statusCodes={
     *         400="Returned when no such status code",
     *         204="There is no information to send back"
     *     },
     * requirements={
     *      {"name"="type", "dataType"="string", "requirement"=true, "description"="social type | twitter, facebook, google"},
     *      {"name"="accessToken", "dataType"="string", "requirement"=true, "description"="User`s social access_token"},
     *      {"name"="tokenSecret", "dataType"="string", "requirement"=true, "description"="User`s social tokenSecret"},
     *      {"name"="apikey",   "dataType"="string",   "required"=false, "description"="User`s apikey"}
     * }
     * )
     * @param $type
     * @param $accessToken
     * @param $tokenSecret
     * @return Response
     * @Rest\View(serializerGroups={"user", "badge"})
     * @Rest\Get("/users/social-login/{type}/{accessToken}/{tokenSecret}", defaults={"tokenSecret"=null}, name="application_user_rest_user_getsociallogin", options={"method_prefix"=false})
     */
    public function getSocialLoginAction($type, $accessToken, $tokenSecret)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();
        $id = null;

        //set reg value for mobile
        $isRegistred = false;

        $newUser = new User();

        // switch for type
        switch($type){
            case "facebook":
                try{
                    $data = file_get_contents("https://graph.facebook.com/me?access_token=" . $accessToken . '&fields=id,email,first_name,last_name,gender,birthday,picture');
                    $newUser->setFacebookData($data);
                    $data = json_decode($data);
                    $id = $data->id;
                    $newUser->setFacebookId($id);
                    $newUser->setEmail(isset($data->email) ? $data->email : $newUser->getSocialFakeEmail());
                    $newUser->setUsername(isset($data->email) ? $data->email : $newUser->getSocialFakeEmail());

                    $newUser->setFirstName($data->first_name);
                    $newUser->setLastName(isset($data->last_name) ? $data->last_name : '');

                    $photoPath = "https://graph.facebook.com/" . $id . "/picture?type=large";
                }
                catch(\Exception $e){
                    return new JsonResponse("Wrong access token", Response::HTTP_BAD_REQUEST);
                }
                break;
            case "google":
                try{
                    $data = file_get_contents("https://www.googleapis.com/plus/v1/people/me?access_token=" . $accessToken);
                    $newUser->setGplusData($data);
                    $data = json_decode($data);
                    $id = $data->id;
                    $newUser->setGoogleId($id);
                    $email = $newUser->getSocialFakeEmail();
                    if (isset($data->emails) && isset($data->emails[0])){
                        $email = $data->emails[0]->value;
                    }

                    $newUser->setEmail($email);
                    $newUser->setUsername($email);
                    $newUser->setFirstName($data->name->givenName);
                    $newUser->setLastName($data->name->familyName);
                    if (isset($data->gender)) {
                        $newUser->setGender($data->gender == "male" ? User::MALE : User::FEMALE);
                    }

                    $photoPath  = $data->image->url;
                    $photoPath = substr($photoPath, 0, strpos($photoPath, "?"));
                }
                catch(\Exception $e){
                    return new JsonResponse("Wrong access token", Response::HTTP_BAD_REQUEST);
                }
                break;
            case "twitter":
                $data = explode('-', $accessToken);
                $id = is_array($data) && isset($data[1]) ?  $data[0] : null;
                $newUser->setTwitterId($id);

                $data = $this->getTwitterData($id, $accessToken, $tokenSecret);
                $newUser->setTwitterData(json_encode($data));

                if (!isset($data->id)){
                    $id = null;
                    break;
                }

                $id = $data->id;
                $newUser->setEmail($newUser->getSocialFakeEmail());
                $newUser->setUsername($newUser->getSocialFakeEmail());
                $fullName = explode(' ', $data->name);
                $newUser->setFirstName($fullName[0]);
                $newUser->setLastName("");

                $photoPath = $data->profile_image_url;
                $photoPath = str_replace('_normal', '', $photoPath);

                break;
            default:
                return new JsonResponse("Wrong type, type must be 'facebook', 'twitter', 'instagram'", Response::HTTP_BAD_REQUEST);
                break;
        }

        if (!$id){
            return new JsonResponse("Wrong access token", Response::HTTP_BAD_REQUEST);
        }

        $user = $em->getRepository('ApplicationUserBundle:User')->findBySocial($type, $id, $newUser->getEmail());

        if(!$user){
            $fileName = md5(microtime()) . '.jpg';
            file_put_contents($newUser->getAbsolutePath() . $fileName, fopen($photoPath, 'r'));
            $newUser->setFileName($fileName);
            $newUser->setFileSize(filesize($newUser->getAbsolutePath() . $fileName));
            $newUser->setFileOriginalName($newUser->getFirstName() . '_photo');
            $newUser->setPassword('');

            $newUser->setSocialPhotoLink($photoPath);
            $em->persist($newUser);
            $em->flush();

            //get registration user
            $user = $newUser;

            //set reg status for mobile
            $isRegistred = true;
        } else {
            if($user->isEnabled() || date_diff($user->getUpdatedAt(), (new \DateTime('now')))->y == 0){
                if(!$user->isEnabled()) {
                    $user->setEnabled(true);
                    $em->flush();
                }
            } else {
                return new JsonResponse("User has been Deleted", Response::HTTP_BAD_REQUEST);
            }
        }

        //get response
        $responseData = $this->loginAction($user,  array('user'), $isRegistred);

        return $responseData;
    }

    /**
     * @param $id
     * @param $token
     * @param $token_secret
     * @return mixed
     */
    private function getTwitterData($id, $token, $token_secret)
    {
        $consumer_key = $this->getParameter('twitter_client_id'); //$response->getResourceOwner()->getOption('client_id');
        $consumer_secret = $this->getParameter('twitter_client_secret'); //$response->getResourceOwner()->getOption('client_secret');

        $host = 'api.twitter.com';
        $method = 'GET';
        $path = '/1.1/users/show.json'; // api call path

        $query = array(
            'user_id' => $id,
        );

        $oauth = array(
            'oauth_consumer_key' => $consumer_key,
            'oauth_token' => $token,
            'oauth_nonce' => (string)mt_rand(), // a stronger nonce is recommended
            'oauth_timestamp' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_version' => '1.0'
        );

        $oauth = array_map("rawurlencode", $oauth); // must be encoded before sorting
        $query = array_map("rawurlencode", $query);

        $arr = array_merge($oauth, $query); // combine the values THEN sort

        asort($arr); // secondary sort (value)
        ksort($arr); // primary sort (key)

        // http_build_query automatically encodes, but our parameters
        // are already encoded, and must be by this point, so we undo
        // the encoding step
        $querystring = urldecode(http_build_query($arr, '', '&'));

        $url = "https://$host$path";

        // mash everything together for the text to hash
        $base_string = $method."&".rawurlencode($url)."&".rawurlencode($querystring);

        // same with the key
        $key = rawurlencode($consumer_secret)."&".rawurlencode($token_secret);

        // generate the hash
        $signature = rawurlencode(base64_encode(hash_hmac('sha1', $base_string, $key, true)));

        // this time we're using a normal GET query, and we're only encoding the query params
        // (without the oauth params)
        $url .= "?".http_build_query($query);
        $url=str_replace("&amp;","&",$url); //Patch by @Frewuill

        $oauth['oauth_signature'] = $signature; // don't want to abandon all that work!
        ksort($oauth); // probably not necessary, but twitter's demo does it

        // also not necessary, but twitter's demo does this too
        // function add_quotes($str) { return '"'.$str.'"'; }
        $oauth = array_map(function ($str) { return '"'.$str.'"'; }, $oauth);

        // this is the full value of the Authorization line
        $auth = "OAuth " . urldecode(http_build_query($oauth, '', ', '));

        // if you're doing post, you need to skip the GET building above
        // and instead supply query parameters to CURLOPT_POSTFIELDS
        $options = array( CURLOPT_HTTPHEADER => array("Authorization: $auth"),
            //CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_HEADER => false,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false);

        // do our business
        $feed = curl_init();
        curl_setopt_array($feed, $options);
        $json = curl_exec($feed);
        curl_close($feed);

        return json_decode($json);
    }

    /**
     * This function is used to check is user with such email registered
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to check is user with such email registered",
     *  statusCodes={
     *         200="Returned when status changed",
     *         404="User not found"
     *     },
     * )
     *
     * @Rest\View()
     * @param $email
     * @return array
     */
    public function getRegisteredAction($email)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("ApplicationUserBundle:User")->findOneBy(array('email' => $email));

        if($user){

            return array(
                'registered' => true,
                'image_path' => $user->getPhotoLink()
            );
        }

        return array(
            'registered' => false
        );
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to reset password",
     *  statusCodes={
     *         204="Returned when all ok",
     *         404="User not found"
     *     },
     * )
     *
     * @Rest\View()
     * @param $email
     * @return array|JsonResponse
     */
    public function getResetAction($email)
    {
        $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($email);
        $trans = $this->get('translator');

        if (null === $user) {
            return new JsonResponse(['user' => $trans->trans('resetting.user_not_found', [], 'FOSUserBundle')], Response::HTTP_NOT_FOUND);
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return new JsonResponse(['expires' => $trans->trans('resetting.password_already_requested', [], 'FOSUserBundle')], Response::HTTP_BAD_REQUEST);
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);

        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);

        return new JsonResponse('', Response::HTTP_NO_CONTENT);
    }

    /**
     * This function is used to get current users
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to to get current user",
     *  statusCodes={
     *         200="Returned when status changed",
     *         401="Access allowed only for registered users"
     *     },
     * )
     * @Rest\View(serializerGroups={"user", "completed_profile", "image_info", "badge"})
     * @Rest\Get("/user/{uid}", name="get_user", defaults={"uid" = null}, options={"method_prefix"=false})
     * @Secure(roles="ROLE_USER")
     * @param $uid
     * @return array
     */
    public function getAction(Request $request, $uid = null)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        $liipManager = $this->get('liip_imagine.cache.manager');

        //get current user
        $currentUser = $uid?$em->getRepository('ApplicationUserBundle:User')->findOneBy(array('uId'=>$uid)) :$this->get('security.token_storage')->getToken()->getUser();

        if(!$request->query->get('mobileAppPlatform')){
            // get drafts
            $em->getRepository("AppBundle:Goal")->findMyIdeasCount($currentUser);
            $goalFriendsCount = 0;
            $em->getRepository("AppBundle:Goal")->findRandomGoalFriends($currentUser->getId(), null, $goalFriendsCount, true);
            $currentUser->setGoalFriendsCount($goalFriendsCount);
        } else {
            // get drafts
            $em->getRepository("AppBundle:Goal")->findMyDraftsAndFriendsCount($currentUser);
        }

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        $states = $currentUser->getStats();

        $states['created'] = $em->getRepository('AppBundle:Goal')->findOwnedGoalsCount($currentUser->getId(), false);

        $currentUser->setStats($states);

        if($currentUser->getImagePath()){
            $currentUser->setCachedImage($liipManager->getBrowserPath($currentUser->getImagePath(), 'user_image'));
        }

        return $currentUser;
    }

    /**
     * @param $value
     * @return bool
     */
    private function toBool($value){
        if ($value === 'true' || $value === true || $value === 1){
            return true;
        }

        return false;
    }

    /**
     * This function is used to get apps string for mobile
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to get apps string for mobile",
     *  statusCodes={
     *         200="OK",
     *         204="No content"
     *     },
     * )
     * @Rest\View()
     */
    public function getAppStringAction()
    {
        //get file directory
        $rootDir= __DIR__ . '/../../../../../bin/appString.txt';

        if(file_exists($rootDir) || is_file($rootDir)) {

            //open file
            $file = fopen($rootDir ,'r');

            //get string in file
            $string = fread($file, filesize($rootDir));

            //close file
            fclose($file);
        }
        else{
            return new Response('File with app string not exist', Response::HTTP_NO_CONTENT);
        }

        return $string;
    }

    /**
     * This function is used to create apps string for mobile
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to create apps string for mobile",
     *  statusCodes={
     *         200="OK",
     *         400="Bad request"
     *     },
     * )
     * @Rest\View()
     */
    public function postAppStringAction($string)
    {
        if(!$string) {
            return new Response('Invalid string parameters', Response::HTTP_BAD_REQUEST);
        }

        //get file directory
        $rootDir= __DIR__ . '/../../../../../bin/appString.txt';

        //open file
        $file = fopen($rootDir ,'w+');

        //write string code in file
        fwrite($file, $string);

        //close file
        fclose($file);

        return new Response('', Response::HTTP_OK);

    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to get User`s register id",
     *  statusCodes={
     *         204="There is no information to send back",
     *         401="Access allowed only for registered users",
     *         400="Bad request"
     *     },
     * parameters={
     *      {"name"="registrationId", "dataType"="string", "required"=true, "description"="Device Id"},
     *      {"name"="mobileOc", "dataType"="string", "required"=true, "description"="Mobile OC"},
     *      {"name"="version", "dataType"="string", "required"=false, "description"="App Version"},
     * }
     * )
     *
     * @param Request $request
     * @return JsonResponse
     * @Secure(roles="ROLE_USER")
     */
    public function putDeviceIdAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();

        $data            = $request->request->all();
        $registrationId = array_key_exists('registrationId', $data) ? $data['registrationId'] : null;
        $registrationId = preg_replace('/\|ID\|\d\|:/', '', $registrationId);
        $mobileOc        = array_key_exists('mobileOc', $data) ? $data['mobileOc'] : null;

        if(!$registrationId || !$mobileOc || !in_array($mobileOc, ['ios', 'android'])){
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Empty parameters value");
        }

        $regData = $currentUser->getRegistrationIds();

        if(array_key_exists($mobileOc, $regData)){
            $device = $regData[$mobileOc];
            if(!in_array($registrationId, $device)){
                array_push($device, $registrationId);
                $this->cleanRegIds($registrationId, $mobileOc);
            }

            $regData[$mobileOc] = $device;
        }
        else {
            $regData[$mobileOc][] =  $registrationId;
            $this->cleanRegIds($registrationId, $mobileOc);
        }

        $currentUser->setRegistrationIds($regData);
        $version = array_key_exists('version', $data) ? $data['version'] : null;
        if ($version){
            $setterName = 'set' . ucfirst($mobileOc) . 'Version';
            $currentUser->$setterName($version);
        }

        $em->persist($currentUser);
        $em->flush();

        return new JsonResponse(Response::HTTP_NO_CONTENT);
    }

//    this function clean duplicate registration ids from old users
    private function cleanRegIds($registrationId, $mobileOc){
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('ApplicationUserBundle:User')->findWithRelationsIds($registrationId);
        foreach ($users as $user){
            $userRegData = $user->getRegistrationIds();
            $userDevice = $userRegData[$mobileOc];
            $key = array_search($registrationId, $userDevice);
            if(!($key === false)){
                unset($userDevice[$key]);
                $userRegData[$mobileOc] = $userDevice;
                $user->setRegistrationIds($userRegData);
            }

        }
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to remove User`s register id",
     *  statusCodes={
     *         204="There is no information to send back",
     *         401="Access allowed only for registered users",
     *         400="Bad request"
     *     },
     * parameters={
     *      {"name"="registrationId", "dataType"="string", "required"=true, "description"="Device Id"},
     *      {"name"="mobileOc", "dataType"="string", "required"=true, "description"="Mobile OC"},
     *      {"name"="version", "dataType"="string", "required"=false, "description"="App Version"},
     * }
     * )
     *
     * @param Request $request
     * @return JsonResponse
     * @Secure(roles="ROLE_USER")
     */
    public function deleteDeviceIdAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();

        $registrationId = $request->get('registrationId', null);
        $registrationId = preg_replace('/\|ID\|\d\|:/', '', $registrationId);
        $mobileOc       = $request->get('mobileOc', null);

        if(!$registrationId || !$mobileOc || !in_array($mobileOc, ['ios', 'android'])){
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Empty parameters value");
        }

        $regData = $currentUser->getRegistrationIds();

        if(array_key_exists($mobileOc, $regData)){
            $device = $regData[$mobileOc];
            if(($key = array_search($registrationId, $device)) !== false){
                unset($device[$key]);
                $regData[$mobileOc] = $device;
            }
            else {
                throw new HttpException(Response::HTTP_BAD_REQUEST, "The user hasn't such deviceId");
            }
        }
        else {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "The user hasn't such OS deviceIds");
        }

        $currentUser->setRegistrationIds($regData);
        $em->flush();

        return new JsonResponse(Response::HTTP_NO_CONTENT);
    }

    /**
     * This function is used to get user states
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to get user states",
     *  statusCodes={
     *         200="OK",
     *         400="Bad request"
     *     },
     * )
     * @Rest\View()
     */
    public function getStatesAction($id)
    {
        if(!$id) {
            return new Response('Invalid id parameters', Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();

        $states = $em->getRepository("ApplicationUserBundle:User")->findUserStats($id);
        

        return new JsonResponse($states);

    }

    /**
     * This function is used to create following users
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to create following users",
     *  statusCodes={
     *         204="Not content",
     *         404="Not found"
     *     },
     * )
     * @ParamConverter("user", class="ApplicationUserBundle:User")
     * @Secure(roles="ROLE_USER")
     * @param User $user
     * @return JsonResponse|Response
     */
    public function postToggleFollowingAction(User $user)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get current user
        $currentUser = $this->getUser();

        $goalFriends = $currentUser->getFollowings();

        if($goalFriends->contains($user)){
            $currentUser->removeFollowing($user);
        }else{
            $currentUser->addFollowing($user);

        }

        $em->persist($currentUser);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

//    /**
//     * This function is used to delete following users
//     *
//     * @ApiDoc(
//     *  resource=true,
//     *  section="User",
//     *  description="This function is used to delete following users",
//     *  statusCodes={
//     *         204="Not content",
//     *         404="Not found"
//     *     },
//     * )
//     * @ParamConverter("user", class="ApplicationUserBundle:User")
//     * @Secure(roles="ROLE_USER")
//     * @param User $user
//     * @return JsonResponse|Response
//     */
//    public function deleteFollowingAction(User $user)
//    {
//        // get entity manager
//        $em = $this->getDoctrine()->getManager();
//
//        // get current user
//        $currentUser = $this->getUser();
//        $currentUser->removeFollowing($user);
//        $em->persist($currentUser);
//        $em->flush();
//
//        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
//    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to remove User profile",
     *  statusCodes={
     *         204="There is no information to send back",
     *         401="Access allowed only for registered users",
     *         400="Bad request"
     *     },
     * )
     *
     * @param Request $request
     * @return JsonResponse
     * @Secure(roles="ROLE_USER")
     */
    public function putDeleteProfileAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();

        if(!$currentUser){
            throw new HttpException(Response::HTTP_BAD_REQUEST, "User Not Found");
        }

        //check if request content type is json
        if ($request->getContentType() == 'application/json' || $request->getContentType() == 'json') {

            //get content and add it in request after json decode
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        $password = $request->request->get('password');
        $reason = $request->request->get('reasone');

        if ($password) {
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($currentUser);

            $isRight = $encoder->isPasswordValid($currentUser->getPassword(),$password,$currentUser->getSalt());

            if(!$isRight) {
                return new JsonResponse('Password Not Valid', Response::HTTP_BAD_REQUEST);
            }
        } else {
            if (!$currentUser->getTwitterId() && !$currentUser->getFacebookId() && !$currentUser->getGoogleId()) {
                return new JsonResponse('Password Not Valid', Response::HTTP_BAD_REQUEST);
            }
        }
        
        $currentUser->setDeleteReason($reason);
        $currentUser->setEnabled(false);

        $em->flush();

        return new JsonResponse(Response::HTTP_NO_CONTENT);
    }
    /**
     * This function is used to get my followings users
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to get my followings users",
     *  statusCodes={
     *         200="ok",
     *     },
     * )
     * @Secure(roles="ROLE_USER")
     * @Rest\View(serializerGroups={"tiny_user"})
     * @return JsonResponse|Response
     */
    public function getFollowingsAction()
    {
        // get current user
        $currentUser = $this->getUser();
        $goalFriends = $currentUser->getFollowings();

        return $goalFriends;
    }

    /**
     * This function is used to to post file for current user
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to to post file for current user",
     *  statusCodes={
     *         204="No content",
     *         400="Bad Request",
     *         401="Access allowed only for registered users"
     *     },
     *  parameters={
     *      {"name"="file", "dataType"="file", "required"=true, "description"="File for current user"}
     * }
     * )
     * @Rest\View()
     * @Rest\Post("/user/upload-file", name="application_user_rest_user_postuploadfile", options={"method_prefix"=false})
     * @Secure(roles="ROLE_USER")
     */
    public function postUploadFileAction(Request $request)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();
        $liipManager = $this->get('liip_imagine.cache.manager');
        
        //get current user
        $currentUser = $this->getUser();

        //get file
        $file = $request->files->get('file');

        if(!$file) {
            return new JsonResponse('File not found', Response::HTTP_BAD_REQUEST);
        }

        $currentUser->setFile($file);

        //get validator
        $validator = $this->get('validator');


        //get errors
        $errors = $validator->validate($currentUser, null, ['File']);

        $returnResult = [];

        if(count($errors) > 0){

            foreach($errors as $error)
            {
                $returnResult[$error->getPropertyPath()] = $error->getMessage();
            }

            return new JsonResponse($returnResult, Response::HTTP_BAD_REQUEST);
        }

        //get uploadFile service
        $this->get('bl_service')->uploadFile($currentUser);
        $imagePath = $liipManager->getBrowserPath($currentUser->getImagePath(), 'user_image');
        $currentUser->setCachedImage($imagePath);
        
        $em->persist($currentUser);
        $em->flush();

        return new Response($imagePath, Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to create new password",
     *  statusCodes={
     *         204="Returned when all ok",
     *         404="User not found",
     *         400="Bad request"
     *     },
     * )
     *
     * @Rest\View(serializerGroups={"user", "completed_profile", "image_info"})
     */
    public function postNewPasswordAction(Request $request)
    {
        //check if request content type is json
        if ($request->getContentType() == 'application/json' || $request->getContentType() == 'json') {

            //get content and add it in request after json decode
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        //get post data in request
        $token = $request->request->get('token');
        $password = $request->request->get('password');
        $plainPassword = $request->request->get('plainPassword');

        //get user by token
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        //check if user not exist
        if(!$user){
            return new JsonResponse("The user with confirmation token does not exist for value $token", Response::HTTP_NOT_FOUND);
        }

        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }


        if($password && $plainPassword && $password == $plainPassword) {
            $user->setPlainPassword($plainPassword);
            $user->setConfirmationToken(null);
            $user->setPasswordRequestedAt(null);
            $user->setEnabled(true);
            $this->container->get('fos_user.user_manager')->updateUser($user);
        }else{
            return new JsonResponse(['password' => 'Passwords is not equals'] , Response::HTTP_BAD_REQUEST);
        }

       $response = $this->loginAction($user, ['user']);

        return $response;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to check reset password token",
     *  statusCodes={
     *         204="Returned when all ok",
     *         404="User not found"
     *     },
     * )
     *
     * @Rest\View()
     * @Rest\Get("/user/check/reset-token/{token}", name="application_user_rest_user_checkresettoken_1", options={"method_prefix"=false})
     * @param $token
     * @return array|JsonResponse
     */
    public function checkResetTokenAction($token)
    {
        //get user by token
        $user = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        $t = $this->container->getParameter('fos_user.resetting.token_ttl');

        if (!$user || (!$user->isPasswordRequestNonExpired($t))) {
            return new JsonResponse(['email_token' => 'Invalid email token for this user'], Response::HTTP_BAD_REQUEST);
        }

        return  ['confirm' => true];
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to check reset password token",
     *  statusCodes={
     *         204="Returned when all ok",
     *         404="User not found"
     *     },
     * )
     *
     * @Rest\View()
     * @Rest\Post("/user/check/registration-token", name="application_user_rest_user_checkregistrationtoken", options={"method_prefix"=false})
     * @return array|JsonResponse
     */
    public function checkRegistrationTokenAction(Request $request)
    {
        //check if request content type is json
        if ($request->getContentType() == 'application/json' || $request->getContentType() == 'json') {

            //get content and add it in request after json decode
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        $userId = $request->request->get('id');

        $em = $this->getDoctrine()->getManager();

        //get user by token
        $user = $em->getRepository('ApplicationUserBundle:User')->find($userId);

        if(!$user) {
            new JsonResponse('User not found', Response::HTTP_NOT_FOUND);
        }

        $registrationToken = $user->getRegistrationToken();

        if($registrationToken) {
            $tokeExist = true;
        }else{
            $tokeExist = false;
        }

        return  ['confirm' => $tokeExist];
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to confirm user registartion",
     *  statusCodes={
     *         204="Returned when all ok",
     *         404="User not found"
     *     },
     * )
     *
     * @Rest\View(serializerGroups={"user", "completed_profile", "image_info"})
     * @Rest\Post("/user/confirm", name="application_user_rest_user_confirmregistration", options={"method_prefix"=false})
     * @return array|JsonResponse
     */
    public function confirmRegistrationAction(Request $request)
    {
        //check if request content type is json
        if ($request->getContentType() == 'application/json' || $request->getContentType() == 'json') {

            //get content and add it in request after json decode
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        $token = $request->request->get('token');

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('ApplicationUserBundle:User')->findOneBy(['registrationToken'=>$token]);

        if (!$user) {
            return new JsonResponse(['user_confirm' => "The user with confirmation token $token does not exist"], Response::HTTP_NOT_FOUND);
        }

        //set user data
        $user->setRegistrationToken(null);
        $user->setEnabled(true);
        $user->setLastLogin(new \DateTime());
        $em->persist($user);
        $em->flush();

        $response = $this->loginAction($user, ['user']);

        return $response;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to check reset password token",
     *  statusCodes={
     *         204="Returned when all ok",
     *         404="User not found",
     *         400="Bad request"
     *     },
     * )
     *
     * @Rest\View()
     * @Rest\Get("/user/activation-email/{emailToken}/{email}", name="application_user_rest_user_activationuseremails", options={"method_prefix"=false})
     * @Secure(roles="ROLE_USER")
     */
    public function activationUserEmailsAction($emailToken, $email)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $user = $this->getUser();

        //check if user not exist
        if (!$user) {
            return new JsonResponse('User not found', Response::HTTP_BAD_REQUEST);
        }

        //get user emails
        $userEmails = $user->getUserEmails();

        //check new email not exist in user emails
        if(!array_key_exists($email, $userEmails)) {
            return new JsonResponse('User not found', Response::HTTP_BAD_REQUEST);
        }

        //get current email data
        $data = $userEmails[$email];

        //get userEmail value in array
        $currentEmailToken = $data['token'];

        //check if tokens is equal
        if ($currentEmailToken == $emailToken) {

            //set token null in userEmails by key
            $userEmails[$email]['token'] = null;

            //set activation email token null
            $user->setUserEmails($userEmails);

            if ($user->getSocialFakeEmail() == $user->getEmail()){
                $user->primary = $email;
            }
        }
        else {
           return new JsonResponse('Invalid email token for this user', Response::HTTP_BAD_REQUEST);
        }

        $em->persist($user);
        $em->flush($user);

       return new JsonResponse('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to update confirm registration email",
     *  statusCodes={
     *         400="Bad request",
     *         204="Np content"
     *     },
     * )
     * @Rest\View()
     * @Rest\Post("/user/update/activation-email", name="application_user_rest_user_postupdateconfirmemail", options={"method_prefix"=false})
     * @Secure(roles="ROLE_USER")
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function postUpdateConfirmEmailAction(Request $request)
    {
        if ($request->getContentType() == 'application/json' || $request->getContentType() == 'json') {

            //get content and add it in request after json decode
            $content = $request->getContent();
            $request->request->add(json_decode($content, true));
        }

        //get email
        $email = $request->request->get('email');
        $user = $this->getUser();

        //get registration token
        $regToken = $user->getRegistrationToken();

        if(!$regToken) {
            new JsonResponse('Bad request', Response::HTTP_BAD_REQUEST);
        }

        //check if email not exist
        if(!$email) {
            $email = $user->getEmail();
        }

        $em = $this->getDoctrine()->getManager();

        $token = md5(microtime());
        $user->setRegistrationToken($token);

        $em->persist($user);
        $em->flush();

        $this->container->get('bl.email.sender')->sendConfirmEmail($email, $token, $user->getFirstName());

        return $email;
    }
}

