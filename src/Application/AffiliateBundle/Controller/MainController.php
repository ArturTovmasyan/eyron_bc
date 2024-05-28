<?php

namespace Application\AffiliateBundle\Controller;

use AppBundle\Entity\Goal;
use AppBundle\Entity\PlaceType;
use Application\AffiliateBundle\Entity\Affiliate;
use Application\AffiliateBundle\Entity\AffiliateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MainController extends Controller
{
    /**
     * @Route("/affiliates")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $link = $request->get('page_link');
        if (is_null($link)){
            throw new HttpException(Response::HTTP_BAD_REQUEST);
        }

        AffiliateType::$bookingAId = $this->getParameter('booking_aid');

        $em = $this->getDoctrine()->getManager();
        $affiliates = $em->getRepository('ApplicationAffiliateBundle:Affiliate')->getAffiliatesByLink($link);
        
        $htmlContent = '';
        $jsContent   = '';

        foreach($affiliates as $affiliate){
            $htmlContent .= $affiliate->getHtmlContent();
            $jsContent .= $affiliate->getJsContent();
        }

        return [
            'htmlContent' => $htmlContent,
            'jsContent'   => $jsContent
        ];
    }

    /**
     * @Route("/admin/generate-affiliate/{id}", name="generate_affiliate")
     */
    public function generateAffiliate(Request $request, Goal $goal)
    {
        $referer = $request->headers->get('referer');

        if (!$goal->getLat() || !$goal->getLng()){
            $request->getSession()->getFlashBag()->add("sonata_flash_error", $this->get('translator')->trans('admin.flash.empty_location'));

            return $this->redirect($referer);
        }

        $em = $this->getDoctrine()->getManager();
        $googlePlace =  $this->get('app.google_place');
        $routing     = $this->get('router');

        $link = $request->getSchemeAndHttpHost() . $routing->generate('inner_goal', ['slug' => $goal->getSlug()]);
        $affiliate = $em->getRepository('ApplicationAffiliateBundle:Affiliate')->findGoalAffiliateByLink($link);

        if (!is_null($affiliate)){
            $request->getSession()->getFlashBag()->add("sonata_flash_error", $this->get('translator')->trans('admin.flash.already_exists'));

            return $this->redirectToRoute('admin_application_affiliate_affiliate_show', ['id' => $affiliate->getId()]);
        }

        $result = $googlePlace->getPlace($goal->getLat(), $goal->getLng());

        if (!isset($result[PlaceType::TYPE_COUNTRY]) && !isset($result[PlaceType::TYPE_CITY])){
            $request->getSession()->getFlashBag()->add("sonata_flash_error", $this->get('translator')->trans('admin.flash.google_api_error'));

            return $this->redirect($referer);
        }

        $city = isset($result[PlaceType::TYPE_CITY]) ? $result[PlaceType::TYPE_CITY] : '';
        $country = isset($result[PlaceType::TYPE_COUNTRY]) ? $result[PlaceType::TYPE_COUNTRY] : '';
        $searchTerm = urlencode($country . ' ' . $city);

        $ufi = $this->get('application_affiliate.find_ufi')->findUfiBySearchTerm($searchTerm);

        if ($ufi) {
            $affiliateType = $em->getRepository('ApplicationAffiliateBundle:AffiliateType')->findOneByName('DealsFinder');

            if (!$affiliateType){
                $request->getSession()->getFlashBag()->add("sonata_flash_error", $this->get('translator')->trans('admin.flash.deals_finder_type_not_found'));
                return $this->redirect($referer);
            }

            $affiliate = $em->getRepository('ApplicationAffiliateBundle:Affiliate')->findOneByUfi($ufi);
            if (is_null($affiliate)) {
                $affiliate = new Affiliate();
                $affiliate->setName($city ? $city : $country);
                $affiliate->setAffiliateType($affiliateType);
                $affiliate->setUfi($ufi);
                $affiliate->setLinks([$link]);

                $request->getSession()->getFlashBag()->add("sonata_flash_success", $this->get('translator')->trans('admin.flash.created'));
            }
            else {

                $request->getSession()->getFlashBag()->add("sonata_flash_success", $this->get('translator')->trans('admin.flash.updated'));
                $affiliate->addLink($link);
            }

            $em->persist($affiliate);
            $em->flush();

            return $this->redirectToRoute('admin_application_affiliate_affiliate_show', ['id' => $affiliate->getId()]);
        }

        $request->getSession()->getFlashBag()->add("sonata_flash_error", $this->get('translator')->trans('admin.flash.ufi_not_found'));
        return $this->redirect($referer);
    }
}
