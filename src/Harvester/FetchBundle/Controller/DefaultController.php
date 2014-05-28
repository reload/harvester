<?php

namespace Harvester\FetchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/{user_id}")
     * @Template()
     */
    public function indexAction($user_id = NULL)
    {
        $entries = $user = array();

        if (!empty($user_id))
        {
            $user = $this->getDoctrine()->getRepository('HarvesterFetchBundle:User')->find($user_id);

            $entries = $user->getEntries();
        }
        return array(
            'user' => $user,
            'entries' =>$entries,
        );
    }
}
