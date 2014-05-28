<?php

namespace Harvester\AdminBundle\Controller;

use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/admin/")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/admin/users/{userid}")
     * * @Template()
     */
    public function usersAction(Request $request, $userid = null)
    {
        $rendered_form = false;
        $doctrine = $this->container->get('doctrine.orm.entity_manager');

        if ($userid == true) {
            $user = $doctrine->getRepository('HarvesterFetchBundle:User')->findOneById($userid);

            $form = $this->createFormBuilder($user)
                ->add('workingHours', 'text', array('attr' => array('class' => 'form-control')))
                ->add('save', 'submit', array('attr' => array('class' => 'btn btn-default')))
                ->getForm();

            $form->handleRequest($request);

            if ($request->isMethod('POST')) {
                if ($form->isValid()) {
                    $doctrine->persist($user);
                    $doctrine->flush();

                    return $this->redirect('/app_dev.php/admin/users');
                }
            }

            $rendered_form = $form->createView();
        }


        $users = $doctrine->getRepository('HarvesterFetchBundle:User')->findAll();

        return array(
            'users' => $users,
            'form' => $rendered_form,
        );
    }
}
