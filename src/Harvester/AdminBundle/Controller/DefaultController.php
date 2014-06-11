<?php

namespace Harvester\AdminBundle\Controller;

use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Swift_Message;
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
     * @Route("/admin/users/{user_id}/generate_password", name="_generatepassword")
     * @param $user_id
     */
    public function generatePasswordAction($user_id = null)
    {
        if ($user_id !== null) {
            $doctrine = $this->container->get('doctrine.orm.entity_manager');
            $generator = new UriSafeTokenGenerator();
            $token = $generator->generateToken();
            $user_password = substr($token, 0, 6);

            // Change user password.
            $user = $doctrine->getRepository('HarvesterFetchBundle:User')->findOneById($user_id);
            $user->setPassword($user_password);
            $doctrine->persist($user);
            $doctrine->flush();

            // Mail the user with new password.
            $message = Swift_Message::newInstance()
                ->setSubject('Hello Email')
                ->setFrom('harvest@reload.dk')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'HarvesterAdminBundle:Default:email.html.twig',
                        array(
                            'name' => $user->getFirstname(),
                            'password' => $user_password,
                        )
                    )
                );
            $this->get('mailer')->send($message);

            $this->get('session')->getFlashBag()->add(
                'success',
                'New password for <strong>' . $user->getFirstname() . ' ' . $user->getLastname() . '</strong> is generated.'
            );
        }
        return $this->redirect('/app_dev.php/admin/users');

    }

    /**
     * @Route("/admin/users/{user_id}", name="_useredit")
     * * @Template()
     */
    public function usersAction(Request $request, $user_id = null)
    {
        $is_contractor = $request->query->get('contractor') ?: 0;
        $is_active = $request->query->get('active') ?: 0;
        $is_admin = $request->query->get('admin') ?: false;

        $rendered_form = false;
        $doctrine = $this->container->get('doctrine.orm.entity_manager');

        if ($user_id == true) {
            $user = $doctrine->getRepository('HarvesterFetchBundle:User')->findOneById($user_id);

            $form = $this->createFormBuilder($user)
                ->add('workingHours', 'text', array(
                    'attr' => array(
                        'placeholder' => 'Eg. 7.5',
                        'class' => 'form-control',
                )))
                ->add('password', 'text', array(
                    'attr' => array(
                        'value' => null,
                        'class' => 'form-control',
                )))
                ->add('save', 'submit', array(
                    'validation_groups' => false,
                    'attr' => array(
                    'class' => 'btn btn-default',
                )))
                ->getForm();

            $form->handleRequest($request);

            if ($request->isMethod('POST')) {
                if ($form->isValid()) {

                    $doctrine->persist($user);
                    $doctrine->flush();

                    $this->get('session')->getFlashBag()->add(
                        'success',
                        '<strong>' . $user->getFirstname() . ' ' . $user->getLastname() . '</strong> profile is updated.'
                    );

                    return $this->redirect('/app_dev.php/admin/users');
                }
            }

            $rendered_form = $form->createView();
        }

        $query = $doctrine->getRepository('HarvesterFetchBundle:User')
            ->createQueryBuilder('u');

        $query->where('u.isContractor = :is_contractor')
            ->andWhere('u.isActive = :is_active')
            ->setParameters(array(
                'is_contractor' => $is_contractor,
                'is_active' => $is_active,
            ));

        if ($is_admin) {
            $query->andWhere('u.isAdmin = :is_admin')
                ->setParameter('is_admin', $is_admin);
        }

        $users = $query
            ->getQuery()
            ->getResult();

        return array(
            'users' => $users,
            'form' => $rendered_form,
        );
    }
}
