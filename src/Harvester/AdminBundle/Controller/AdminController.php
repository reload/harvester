<?php

namespace Harvester\AdminBundle\Controller;

use Harvester\AdminBundle\AdminUserForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Doctrine\Common\Persistence\ManagerRegistry;
use Harvester\AdminBundle\AdminMailer;

/**
 * @Route(service="admin_controller")
 */
class AdminController extends Controller
{
    /**
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var \Harvester\AdminBundle\AdminUserForm
     */
    protected $form;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * @var \Harvester\AdminBundle\AdminMailer
     */
    protected $mailer;

    /**
     * @param ManagerRegistry $doctrine
     * @param AdminUserForm $form
     * @param SessionInterface $session
     * @param AdminMailer $mailer
     */
    public function __construct(ManagerRegistry $doctrine, AdminUserForm $form, SessionInterface $session, AdminMailer $mailer)
    {
        $this->doctrine = $doctrine;
        $this->form = $form;
        $this->session = $session;
        $this->mailer = $mailer;
    }

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
            $generator = new UriSafeTokenGenerator();
            $token = $generator->generateToken();
            $user_password = substr($token, 0, 6);

            // Change user password.
            $user = $this->doctrine->getRepository('HarvesterFetchBundle:User')->findOneById($user_id);
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
        return $this->redirect('/app_dev.php/admin/users');
    }

    /**
     * @Route("/admin/users/{user_id}", name="_useredit")
     * * @Template()
     */
    public function usersAction(Request $request, $user_id = null)
    {
        $user = false;
        $is_contractor = $request->query->get('contractor') ?: 0;
        $is_active = $request->query->get('active') ?: 0;
        $is_admin = $request->query->get('admin') ?: false;

        $rendered_form = false;

        if ($user_id == true) {
            $user = $this->doctrine->getRepository('HarvesterFetchBundle:User')->findOneById($user_id);

            $form = $this->form->buildForm($user)->getForm();

            $form->handleRequest($request);

            if ($request->isMethod('POST') && $form->isValid()) {

                    $this->doctrine->getManager()->persist($user);
                    $this->doctrine->getManager()->flush();

                    $this->session->getFlashBag()->add(
                        'success',
                        '<strong>' . $user->getFirstname() . ' ' . $user->getLastname() . '</strong> profile is updated.'
                    );

                    return $this->redirect('/app_dev.php/admin/users');
            }

            $rendered_form = $form->createView();
        }

        $users = $this->doctrine->getRepository('HarvesterFetchBundle:User')
            ->getUserList($is_admin, $is_active, $is_contractor);

        return array(
            'user' => $user,
            'users' => $users,
            'form' => $rendered_form,
        );
    }
}
