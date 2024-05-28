<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Email;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Http\TransparentPixelResponse;
use Symfony\Component\HttpKernel\KernelEvents;


class EmailController extends Controller
{
    /**
     * @Route("/email.gif", name="open-email")
     */
    public function openAction(Request $request)
    {
        $uuid = $request->query->get('id');

        if (null !== $uuid) {

            $em = $this->getDoctrine()->getManager();
            $email = $em->getRepository('AppBundle:Email')->find($uuid);

            if ($email) {
                $email->setSeen(new \DateTime());

                // get mobile detect
                $md = $this->get('mobile_detect.mobile_detector');
                if ($md->isMobile()) {
                    if ($md->isTablet()) {
                        $email->setDevice(Email::DEVICE_TABLET);
                    } else {
                        $email->setDevice(Email::DEVICE_MOBILE);
                    }
                } else {
                    $email->setDevice(Email::DEVICE_PC);
                }

                $em->persist($email);
                $em->flush();
            }
        }

        return new TransparentPixelResponse();
    }

}
