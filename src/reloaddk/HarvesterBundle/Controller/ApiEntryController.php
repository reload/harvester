<?php

namespace reloaddk\HarvesterBundle\Controller;

use DateTime;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use reloaddk\HarvesterBundle\Entity\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @RouteResource("Entry")
 */
class ApiEntryController extends FOSRestController
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
        $entry = $this->container->get('doctrine.orm.entity_manager')->getRepository('reloaddkHarvesterBundle:Entry')->findOneBy(array(
            'id' => $entry_id,
            'status' => 1,
        ));

        $view = $this->view($entry, $entry ? 200 : 404);

        return $this->handleView($view);
    }

    /**
     * @Get("/entries")
     * @QueryParam(name="group", requirements="user | tasks | project", description="Group entries.")
     * @QueryParam(name="from", requirements="\d+", description="Date range from (yyyymmdd). (Overwrites month, year if set).")
     * @QueryParam(name="to", requirements="\d+", description="Date range to (yyyymmdd). (Overwrites month, year if set).")
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
     *
     * @param Request $request
     * @param ParamFetcher $paramFetcher
     * @return Annotation\View
     */
    public function getEntriesAction(Request $request, ParamFetcher $paramFetcher)
    {
        $token_response = null;

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
        $date_from = new DateTime('first day of this month');
        $date_to = new DateTime('yesterday');
        $date_today = new DateTime('today');

        // If: it's the first day of the month.
        // Or: we're within the first 3 days of the month and the current day isn't saturday/sunday/monday.
        if ((date('j') == 1 || (date('j') < 4 && in_array(date('w'), array(6,7,1))))) {
            // Then we're showing the previous month.
            $date_from = new DateTime('first day of last month');
            $date_to = new DateTime('last day of last month');
        }

        // If the month and year params is set.
        // And: the request isn't the beginning of the current month,
        //      when we don't have any data yet.
        if (($month && $year)
        && !(((date('j') == 1 ||
               (date('j') < 4 && in_array(date('w'), array(6,7,1)))) &&
            ((strtolower($month) == strtolower(date('M')) ||
              strtolower($month) == strtolower(date('F'))) && $year == date('Y'))))) {
            // Set the range to the requested month.
            $date_from = new DateTime('first day of ' . $month . ' ' . $year);
            $date_to = new DateTime('last day of ' . $month . ' ' . $year);

            // If the given month / year is equal to the current month / year.
            if ($date_to->format('Ym') === $date_today->format('Ym')) {
                // Then we set "to", to be the current date instead of the end of the month.
                $date_to = new DateTime('yesterday');
            }
        }

        // If from/to is set, it overwrites month/year
        if ($from == true) {
            $date_from = DateTime::createFromFormat('Ymd', $from);
        }

        // If from/to is set, it overwrites month/year
        if ($to == true) {
            $date_to = Datetime::createFromFormat('Ymd', $to);

            // If "date_to" equals the current day.
            if ($date_to->format('Ymd') === $date_today->format('Ymd')) {
                // Subtract 1 day.
                $date_to->modify('-1 day');
            }
        }

        // Start query from entry table.
        $repository = $this->container->get('doctrine.orm.entity_manager')->getRepository('reloaddkHarvesterBundle:Entry');

        $query = $repository->createQueryBuilder('e');

        // Limit the query to a date span.
        $query
            ->where('e.spentAt >= :date_from AND e.spentAt <= :date_to AND e.status = :status')
            ->setParameter('date_from', $date_from, \Doctrine\DBAL\Types\Type::DATE)
            ->setParameter('date_to', $date_to, \Doctrine\DBAL\Types\Type::DATE)
            ->setParameter('status', 1);

        // If group GET param is set, generate a Entry repository function name.
        if ($group) {
            $repository_function = 'groupBy' . ucfirst($group);
        }

        // Call the custom 'group' repository function.
        $result = $repository->$repository_function($query, $this->container->getParameter('default_hours_per_day'), $token_response);

        $callback = $request->get('callback'); // Check to see if callback parameter is in URL

        $response = new JsonResponse(); // Construct a new JSON response
        $response->setStatusCode($result ? 200 : 404);

        if (isset($callback)) {
            $response->setCallback($callback); // Set callback function to variable passed in callback
        }

        $response->setData($result);

        return $response;
    }
}
