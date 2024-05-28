<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/27/16
 * Time: 1:52 PM
 */
namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Routing\Route;

class InstantArticleService
{
    protected $templating;

    protected $entityManager;

    protected $pageToken;

    protected $pageId;

    public function __construct(TwigEngine $templating, EntityManager $entityManager, $pageToken, $pageId)
    {
        $this->templating    = $templating;
        $this->entityManager = $entityManager;
        $this->pageToken     = $pageToken;
        $this->pageId        = $pageId;
    }

    public function createInstanceArticle($goalSlug)
    {
        $goal = $this->entityManager->getRepository('AppBundle:Goal')->findBySlugWithTinyRelations(['slug' => $goalSlug]);

        $content = $this->templating->render('AppBundle:InstantArticle:index.html.twig', ['goal' => $goal]);

        $postData = [
            'access_token' => $this->pageToken,
            'html_source' => $content,
            'published' => 'false',
            'development_mode' => 'true'
        ];

        $url = 'https://graph.facebook.com/' . $this->pageId . '/instant_articles';

        $ch       = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
//        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
//        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;

    }
}