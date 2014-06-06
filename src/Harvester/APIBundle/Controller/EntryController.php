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
use Harvester\FetchBundle\Entity\UserRepository;

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
     * @QueryParam(name="month", requirements="\w+", description="Month (january, february ect.)")
     * @QueryParam(name="year", requirements="\d+", description="Year (2013, 2014)")
     * @QueryParam(name="token", description="An authenticated user token")
     * @ApiDoc(
     *   section="Entry",
     *   description="Returns a collection of Entry",
     *   statusCodes={
     *     200="Returned when successful",
     *     401="Unauthorized - The token is invalid",
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

        // Get GET Params from URL.
        $group = $paramFetcher->get('group');
        $from = $paramFetcher->get('from');
        $to = $paramFetcher->get('to');
        $month = $paramFetcher->get('month');
        $year = $paramFetcher->get('year');
        $token = $paramFetcher->get('token');

        // Validate user token, if set.
        if (isset($token)) {
            // token response is either an response object or a user id.
            $token_response = UserRepository::validateToken($this, $token);
            if (is_object($token_response) && $token_response->getStatusCode() == 401) {
                return $token_response;
            }
        }

        // Set the default from / to dates.
        $date_from = new DateTime('first day of this month 00:00:00');
        $date_to = new DateTime('last day of this month 23:59:59');

        // If we're within the first 3 days of the month, and the first day is monday.
        if (date("w") == 1 && date("j") < 4) {
            // Then we're showing the last month.
            $date_from = new DateTime('first day of last month 00:00:00');
            $date_to = new DateTime('last day of last month 23:59:59');
        }

        // If the month and year params is set.
        if ($month && $year) {
            $date_from = new DateTime('first day of ' . $month . ' ' . $year);
            $date_to = new DateTime('last day of ' . $month . ' ' . $year . '23:59:59');
        }

        // If from/to is set, it overwrites month/year
        if ($from == true) {
            $date_from = DateTime::createFromFormat('U', $from);
        }
        if ($to == true) {
            $date_to = Datetime::createFromFormat('U', $to);
        }

        // Start query from entry table.
        $repository = $this->container->get('doctrine.orm.entity_manager')->getRepository('HarvesterFetchBundle:Entry');

        $query = $repository->createQueryBuilder('e');

        // Limit the query to a date span.
        $query
            ->where('e.spentAt >= :date_from AND e.spentAt < :date_to')
            ->setParameter('date_from', $date_from, \Doctrine\DBAL\Types\Type::DATETIME)
            ->setParameter('date_to', $date_to, \Doctrine\DBAL\Types\Type::DATETIME);

        // Limit query to user if token_response is set.
        if ($token_response) {
            $query->andWhere('e.user = :user_id')
                ->setParameter('user_id', $token_response);
        }

        // Generate the query.
        $query_result = $query->getQuery()->getResult();

        // If group GET param is set, generate a Entry repository function name.
        if ($group) {
            $repository_function = 'groupBy' . ucfirst($group);
        }

        // Call the custom 'group' repository function.
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
