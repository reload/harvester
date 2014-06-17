<?php

namespace Harvester\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Harvester\AdminBundle\AdminMailer;

/**
 * @Route(service="admin_controller")
 */
class AdminController extends Controller
{
    protected $doctrine;
    protected $form_factory;
    protected $session;
    protected $mailer;

    public function __construct(ManagerRegistry $doctrine, FormFactoryInterface $form_factory, SessionInterface $session, AdminMailer $mailer)
    {
        $this->doctrine = $doctrine;
        $this->form_factory = $form_factory;
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
        $is_contractor = $request->query->get('contractor') ?: 0;
        $is_active = $request->query->get('active') ?: 0;
        $is_admin = $request->query->get('admin') ?: false;

        $rendered_form = false;

        if ($user_id == true) {
            $user = $this->doctrine->getRepository('HarvesterFetchBundle:User')->findOneById($user_id);

            $form = $this->form_factory->createBuilder('form', $user)
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

                    $this->doctrine->getManager()->persist($user);
                    $this->doctrine->getManager()->flush();

                    $this->session->getFlashBag()->add(
                        'success',
                        '<strong>' . $user->getFirstname() . ' ' . $user->getLastname() . '</strong> profile is updated.'
                    );

                    return $this->redirect('/app_dev.php/admin/users');
                }
            }

            $rendered_form = $form->createView();
        }

        $query = $this->doctrine->getRepository('HarvesterFetchBundle:User')
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
