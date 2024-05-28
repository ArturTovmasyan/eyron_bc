<?php

namespace AppBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class MailChimpService
 * @package AppBundle\Services
 */
class MailChimpService
{

    /**
     * @var
     */
    protected  $apiKey;


    /**
     * @var
     */
    protected $mailChimpUrl;


    /**
     * @var Container
     */
    protected $container;


    /**
     * @param Container $container
     * @param $apiKey
     */
    public function __construct(Container $container, $apiKey)
    {
        $this->container = $container;

        $this->apiKey = $apiKey;

        // get API kay prefix
        $dataCenter = substr($apiKey, strpos($apiKey, '-') + 1);

        // set mail chimp api link
        $this->mailChimpUrl = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/';
    }

    /**
     * @return array
     */
    public function getMailChimpList()
    {

        // check data
        if($result = $this->myCUrl($this->mailChimpUrl . 'lists')){

            return $result['lists']; // return lists
        }

        // return empty data
        return array();

    }

    /**
     * @param $id
     * @return array
     */
    public function getMailChimpListById($id)
    {
        // check data
        if($result = $this->myCUrl($this->mailChimpUrl . 'lists/' . $id)){

            return $result; // return list
        }

        // return empty data
        return array();

    }

    /**
     * @param $id
     * @return array
     */
    public function getMailChimpMembersByList($id)
    {
        // check data
        if($result = $this->myCUrl($this->mailChimpUrl . 'lists/' . $id . '/members')){

            return $result['members']; // return list
        }

        // return empty data
        return array();

    }

    /**
     * @param $data
     * @param $listId
     * @return bool|mixed
     */
    public function addSubscriber($data, $listId)
    {
        //hashing user email for security
        $memberId = md5(strtolower($data['email']));

        // create connection url
        $url = $this->mailChimpUrl . 'lists/' . $listId . '/members/' . $memberId;

        // create content an json
        $json = json_encode([
            'email_address' => $data['email'],
            'status' => 'subscribed', // "subscribed","unsubscribed","cleaned","pending"
            'merge_fields' => [
                'FNAME' => $data['firstName'],
                'LNAME' => $data['lastName'],
            ]
        ]);

        $result = $this->myCUrl($url, "PUT", $json);

        return $result;
    }

    /**
     * @param $url
     * @param string $method
     * @param null $postFields
     * @param bool|true $jsonDecode
     * @return bool|mixed
     */
    private function myCUrl($url, $method = "GET", $postFields = null, $jsonDecode = true)
    {
        // open connection
        $ch = curl_init($url);

        // login by apiKay and PUT date
        curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $this->apiKey);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        // check result
        $result = curl_exec($ch);

        // check result status code
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // close init
        curl_close($ch);

        // check status code
        if($httpCode != Response::HTTP_OK){
            $monoLog = $this->container->get("monolog.logger.mailchimp");

            $monoLog->error($result);
            return false;
        }

        return $jsonDecode ? json_decode($result, true) : $result;
    }

}