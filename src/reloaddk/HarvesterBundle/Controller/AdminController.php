<?php

namespace reloaddk\HarvesterBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\Common\Persistence\ManagerRegistry;
use reloaddk\HarvesterBundle\AdminMailer;
use reloaddk\HarvesterBundle\AdminUserForm;
use reloaddk\HarvesterBundle\AdminUtilities;

/**
 * @Route(service="admin_controller")
 */
class AdminController
{
    /**
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var \reloaddk\HarvesterBundle\AdminUserForm
     */
    protected $form;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * @var \reloaddk\HarvesterBundle\AdminMailer
     */
    protected $mailer;

    /**
     * @var \Symfony\Component\Routing\Router
     */
    protected $router;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    protected $templating;

    /**
     * @var \reloaddk\HarvesterBundle\AdminUtilities
     */
    protected $utilities;

    /**
     * @param ManagerRegistry $doctrine
     * @param AdminUserForm $form
     * @param SessionInterface $session
     * @param AdminMailer $mailer
     * @param RouterInterface $router
     * @param EngineInterface $templating
     * @param AdminUtilities $utilities
     */
    public function __construct(ManagerRegistry $doctrine, AdminUserForm $form, SessionInterface $session, AdminMailer $mailer, RouterInterface $router, EngineInterface $templating, AdminUtilities $utilities)
    {
        $this->doctrine = $doctrine;
        $this->form = $form;
        $this->session = $session;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->utilities = $utilities;
    }

    /**
     * @Route("/admin/", name="_admin_index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/login_check", name="_admin_login_check")
     */
    public function loginCheckAction() {}

    /**
     * @Route("/login", name="_admin_login")
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();

        // Get the login error if there is one.
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        }
        else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->templating->renderResponse('reloaddkHarvesterBundle:Admin:login.html.twig', array(
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error
        ));
    }

    /**
     * @Route("/admin/users/{user_id}/generate_password", name="_generatepassword")
     * @param $user_id
     *
     * @return RedirectResponse
     */
    public function generatePasswordAction($user_id = null)
    {
        if ($user_id !== null) {
            // Change user password.
            $user_password = $this->utilities->generatePassword();
            $user = $this->doctrine->getRepository('reloaddkHarvesterBundle:User')->findOneById($user_id);
            $user->setPassword($user_password);
            $this->doctrine->getManager()->persist($user);
            $this->doctrine->getManager()->flush();

            // Mail the user with new password.
            if ($this->mailer->sendMail('harvest@reload.dk', $user->getEmail(), $user->getFirstname(), $user_password, 'Hello Email')) {
                $this->session->getFlashBag()->add(
                    'success',
                    'New password for <strong>' . $user->getFirstname() . ' ' . $user->getLastname() . '</strong> is generated.'
                );
            }
        }

        return new RedirectResponse($this->router->generate('_useredit'));
    }

    /**
     * @Route("/admin/users/{user_id}", name="_useredit")
     * @Template()
     */
    public function usersAction(Request $request, $user_id = null)
    {
        $user = false;
        $is_contractor = $request->query->get('contractor') ?: 0;
        $is_active = $request->query->get('active') ?: 1;
        $is_admin = $request->query->get('admin') ?: false;

        $rendered_form = false;

        if ($user_id == true) {
            $user = $this->doctrine->getRepository('reloaddkHarvesterBundle:User')->findOneById($user_id);

            $form = $this->form->buildForm($user)->getForm();

            $form->handleRequest($request);

            if ($request->isMethod('POST') && $form->isValid()) {
                    $this->doctrine->getManager()->persist($user);
                    $this->doctrine->getManager()->flush();

                    $this->session->getFlashBag()->add(
                        'success',
                        '<strong>' . $user->getFirstname() . ' ' . $user->getLastname() . '</strong> profile is updated.'
                    );

                return new RedirectResponse($this->router->generate('_useredit'));
            }

            $rendered_form = $form->createView();
        }

        $users = $this->doctrine->getRepository('reloaddkHarvesterBundle:User')
            ->getUserList($is_admin, $is_active, $is_contractor);

        return array(
            'user' => $user,
            'users' => $users,
            'form' => $rendered_form,
        );
    }
}
