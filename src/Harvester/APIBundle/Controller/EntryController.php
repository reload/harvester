<?php

namespace Harvester\APIBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations;

use DateTime;

class EntryController extends FOSRestController
{

    /**
     * @Get("/entry/{entry_id}")
     * @ApiDoc(
     *   section="Entry",
     *   description="Returns an Entry",
     *   statusCodes={
     *     200="Returned when successful",
     *     404="Returned when no data available",
     *   },
     *   requirements={
     *     {
     *       "name": "entry_id",
     *       "dataTYpe": "integer",
     *       "requirement": "The Harvest Entry Id",
     *     },
     *     {
     *       "name": "_format",
     *       "dataTYpe": "integer",
     *       "requirement": "json|xml",
     *     }
     *   }
     * )
     */
    public function getEntryAction($entry_id)
    {
        $entry = $this->container->get('doctrine.orm.entity_manager')->getRepository('HarvesterFetchBundle:Entry')->findOneById($entry_id);

        $view = $this->view($entry, $entry ? 200 : 404);

        return $this->handleView($view);
    }

    /**
     * @Get("/entries")
     * @ApiDoc(
     *   section="Entry",
     *   description="Returns a collection of Entry",
     *   statusCodes={
     *     200="Returned when successful",
     *     404="Returned when no data available",
     *   },
     *   requirements={
     *     {
     *       "name": "_format",
     *       "dataType": "integer",
     *       "requirement": "json|xml"
     *     }
     *   }
     * )
     */
    public function getEntriesAction()
    {
        $date_from = new DateTime('first day of this month');
        $date_to = new DateTime('last day of this month');

        $repository = $this->container->get('doctrine.orm.entity_manager')->getRepository('HarvesterFetchBundle:Entry');

        $query = $repository->createQueryBuilder('e');

        $query
            ->where($query->expr()->between('e.spentAt', ':date_from', ':date_to'))
            ->setParameter('date_from', $date_from, \Doctrine\DBAL\Types\Type::DATETIME)
            ->setParameter('date_to', $date_to, \Doctrine\DBAL\Types\Type::DATETIME);

        $result = $query->getQuery()->getResult();

        $view = $this->view($result, 200);

        return $this->handleView($view);

    }

}
