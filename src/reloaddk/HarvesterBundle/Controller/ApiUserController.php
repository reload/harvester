<?php

namespace reloaddk\HarvesterBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
/**
 * @RouteResource("User")
 */
class ApiUserController extends FOSRestController implements ClassResourceInterface
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

}
