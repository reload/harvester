<?php

namespace Harvester\APIBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;

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
     * @QueryParam(name="group", requirements="user | tasks | project", description="Group entries.")
     * @QueryParam(name="from", requirements="\d+", description="Date range from (timestamp)")
     * @QueryParam(name="to", requirements="\d+", description="Date range to (timestamp)")
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
     * @param ParamFetcher $paramFetcher
     * @return Annotation\View
     */
    public function getEntriesAction(ParamFetcher $paramFetcher)
    {
        $group = $paramFetcher->get('group');
        $from = $paramFetcher->get('from');
        $to = $paramFetcher->get('to');

        $date_from = new DateTime('first day of this month 00:00:00');
        $date_to = new DateTime('last day of this month 23:59:59');

        if ($from == true) {
            $date_from = DateTime::createFromFormat('U', $from);
        }
        if ($to == true) {
            $date_to = Datetime::createFromFormat('U', $to);
        }

        $repository = $this->container->get('doctrine.orm.entity_manager')->getRepository('HarvesterFetchBundle:Entry');

        $query = $repository->createQueryBuilder('e');

        $query
            ->where('e.spentAt >= :date_from AND e.spentAt < :date_to')
            ->setParameter('date_from', $date_from, \Doctrine\DBAL\Types\Type::DATETIME)
            ->setParameter('date_to', $date_to, \Doctrine\DBAL\Types\Type::DATETIME);


        if ($group) {
            $repository_function = 'groupBy' . ucfirst($group);
        }

        $query_result = $query->getQuery()->getResult();

        $result = $repository->$repository_function($query, $query_result, $this->container->getParameter('default_hours_per_day'));

        $callback = $this->getRequest()->get('callback'); // Check to see if callback parameter is in URL

        $response = new JsonResponse(); // Construct a new JSON response
        $response->setStatusCode($result ? 200 : 404);

        if (isset($callback)) {
            $response->setCallback($callback); // Set callback function to variable passed in callback
        }

        $response->setData($result);

        return $response;
    }

}
