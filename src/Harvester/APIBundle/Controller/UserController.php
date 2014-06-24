<?php

namespace Harvester\APIBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

class UserController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *   section="Users",
     *   description="Returns a collection of User",
     *   statusCodes={
     *     200="Returned when successful",
     *     404="Returned when no data available",
     *   },
     *   requirements={
     *     {
     *       "name": "_format",
     *       "dataType": "integer",
     *       "requirement": "json|xml",
     *     }
     *   }
     * )
     */
    public function cgetAction()
    {
        $users = $this->container->get('doctrine.orm.entity_manager')->getRepository('HarvesterFetchBundle:User')->findAll();

        $view = $this->view($users, $users ? 200 : 404);

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *   section="Users",
     *   description="Authenticate user",
     *   statusCodes={
     *     200="Returned when authenticated",
     *     401="Returned when authentication failed",
     *   },
     *   requirements={
     *     {
     *       "name": "email",
     *       "dataType": "string",
     *       "requirement": "valid email",
     *       "description": "The email address"
     *     },
     *     {
     *       "name": "password",
     *       "dataType": "string",
     *       "requirement": "Hashed password",
     *       "description": "Password"
     *     },
     *     {
     *       "name": "_format",
     *       "dataType": "integer",
     *       "requirement": "json|xml",
     *     }
     *   }
     * )
     */
    public function postLoginAction()
    {
        $repository = $this->getDoctrine()
            ->getRepository('HarvesterFetchBundle:User');

        $query = $repository->createQueryBuilder('u')
            ->where('u.email = :email')
            ->andWhere('u.password = :password')
            ->setParameter('email', $this->get('request')->request->get('email'))
            ->setParameter('password', $this->get('request')->request->get('password'))
            ->getQuery();

        $user = $query->getResult();

        $view = $this->view($user, $user ? 200 : 401);

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *   section="Users",
     *   description="Returns a User",
     *   statusCodes={
     *     200="Returned when successful",
     *     404="Returned when the user is not found",
     *   },
     *   requirements={
     *     {
     *       "name": "user_id",
     *       "dataType": "integer",
     *       "requirement": "\d+",
     *       "description": "The Harvest User Id"
     *     },
     *     {
     *       "name": "_format",
     *       "dataType": "integer",
     *       "requirement": "json|xml",
     *     }
     *   }
     * )
     */
    public function getAction($user_id)
    {
        $user = $this->container->get('doctrine.orm.entity_manager')->getRepository('HarvesterFetchBundle:User')->findOneById($user_id);

        $view = $this->view($user, $user ? 200 : 404);

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *   section="Users",
     *   description="Returns a collection of user entries",
     *   statusCodes={
     *     200="Returned when successful",
     *     404="Returned when no data is available",
     *   },
     *   requirements={
     *     {
     *       "name": "user_id",
     *       "dataTYpe": "integer",
     *       "requirement": "\d+",
     *       "description": "The Harvest User Id"
     *     },
     *     {
     *       "name": "_format",
     *       "dataType": "integer",
     *       "requirement": "json|xml",
     *     }
     *   }
     * )
     */
    public function getEntriesAction($user_id)
    {
        $entries = $this->container->get('doctrine.orm.entity_manager')->getRepository('HarvesterFetchBundle:Entry')->findByUser($user_id);

        $view = $this->view($entries, $entries ? 200 : 404);

        return $this->handleView($view);
    }

    /**
     */
    public function getTimekingAction()
    {
        $data = array(
            'succes' => 'true',
            'hours_total_registered' => 0,
            'hours_total_month' => 0,
            'hours_until_today' => 0,
            'date_start' => 0,
            'date_end' => 0,
            'timestamp' => time(),
        );

        $users = $this->container->get('doctrine.orm.entity_manager')->getRepository('HarvesterFetchBundle:User')->findAll();

        foreach ($users as $user) {
            $hours = 0;
            foreach ($user->getEntries() as $entry) {
                $hours+=$entry->getHours();
            }
            $data['ranking'][] = array(
                'user_id_first_part' => '0',
                'user_id_second_part' => '0',
                'user_id_third_part' => '0',
                'name' => $user->getFirstName(),
                'hours_registered' => $hours ,
                'hours_goal' => '0',
                'performance' => '0',
                'group' => '0',
            );

        }
        $view = $this->view($data, 200);

        return $this->handleView($view);

    }
}
