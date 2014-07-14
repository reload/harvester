<?php

namespace Harvester\FetchBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Harvest_Result;
use Harvest_DayEntry;
use HarvestReports;
use DateTime;
use DatePeriod;
use DateInterval;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * EntryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EntryRepository extends EntityRepository
{

    /**
     * Register the new Entry.
     *
     * @param Harvest_Result $user_entries
     * @param $output
     * @param HarvestReports $api
     */
    public function registerEntry(Harvest_Result $user_entries, OutputInterface $output, HarvestReports $api)
    {
        $count_new_entries = $count_updated_entries = 0;
        foreach ($user_entries->get('data') as $user_entry) {

            $entry = $this->getEntityManager()->getRepository('HarvesterFetchBundle:Entry')->findOneById($user_entry->get('id'));

            if (!$entry) {
                $this->saveEntry(new Entry(), $user_entry, $api);
                if (!$count_new_entries) {
                    $output->writeln('<info>--> Entries created.</info>');
                    ++$count_new_entries;
                }
            }
            else {
                $entry_last_update = new DateTime($user_entry->get('updated-at'));

                if ($entry->getUpdatedAt()->getTimestamp() < $entry_last_update->getTimestamp()-7200) {
                    $this->saveEntry($entry, $user_entry, $api);
                    if (!$count_updated_entries) {
                        $output->writeln('<info>--> Entries updated.</info>');
                        ++$count_updated_entries;
                    }
                }
            }
        }
    }

    /**
     * Save Entry to database.
     *
     * @param Entry $entry
     * @param Harvest_DayEntry $harvest_entry
     * @param HarvestReports $api
     */
    public function saveEntry(Entry $entry, Harvest_DayEntry $harvest_entry, HarvestReports $api)
    {
        $user = $this->getEntityManager()->getRepository('HarvesterFetchBundle:User')->findOneById($harvest_entry->get('user-id'));
        $project = $this->getEntityManager()->getRepository('HarvesterFetchBundle:Project')->findOneById($harvest_entry->get('project-id'));
        $task = $this->getEntityManager()->getRepository('HarvesterFetchBundle:Task')->findOneById($harvest_entry->get('task-id'));

        // If the project doesn't exist in db, create it.
        if (!$project) {
            $harvest_project = $api->getProject($harvest_entry->get('project-id'));
            $project = $this->getEntityManager()->getRepository('HarvesterFetchBundle:Project')
                ->registerProject($harvest_project->get('data'), new ConsoleOutput(), $api);
        }

        // If the task doesn't exist in db, create it.
        if (!$task) {
            $harvest_task = $api->getTask($harvest_entry->get('task-id'));
            $task = $this->getEntityManager()->getRepository('HarvesterFetchBundle:Task')
                ->registerTask($harvest_task->get('data'), new ConsoleOutput(), $api);
        }

        // Create the entry.
        $entry->setId($harvest_entry->get('id'));
        $entry->setUser($user);
        $entry->setProject($project);
        $entry->setTasks($task);
        $entry->setNotes($harvest_entry->get('notes'));
        $entry->setHours($harvest_entry->get('hours'));
        $entry->setIsClosed($harvest_entry->get('is-closed') == 'true' ? 1 : 0);
        $entry->setIsBilled($harvest_entry->get('is-billed') == 'true' ? 1 : 0);
        $entry->setSpentAt(new DateTime($harvest_entry->get('spent-at')));
        $entry->setTimerStartedAt(new DateTime($harvest_entry->get('timer-started-at')));
        $entry->setUpdatedAt(new DateTime($harvest_entry->get('updated-at')));
        $entry->setCreatedAt(new DateTime($harvest_entry->get('created-at')));

        // Save it to db.
        $em = $this->getEntityManager();
        $em->persist($entry);
        $em->flush();
    }

    /**
     * Group doctrine data by user.
     *
     * @param \Doctrine\ORM\QueryBuilder $query
     * @param float $working_hours_per_day
     * @param string $token
     * @return array
     */
    public function groupByUser(\Doctrine\ORM\QueryBuilder $query, $working_hours_per_day = null, $token = null)
    {
        $query_result = $query->getQuery()->getResult();

        // Fetch date range from query.
        $date_to = $query->getQuery()->getParameter('date_to');
        $date_from = $query->getQuery()->getParameter('date_from');

        $hours = 0;
        $hours_in_range = null;
        $hours_to_today = null;
        $ranking = array();
        $old_user = [];

        foreach ($query_result as $row) {
            if ($row->getUser()->getIsActive() && !$row->getUser()->getIsContractor()) {
                if (!array_key_exists($row->getUser()->getId(), $old_user) || count($old_user) == 0) {
                    $working_hours = $row->getUser()->getWorkingHours() != 0 ? $row->getUser()->getWorkingHours() : 7.5;
                    $hours_in_range += $working_hours * $this->calcWorkingDaysInRange($date_from->getValue()->format('Ymd'), $date_to->getValue()->format('Ymd'));
                    $old_user[$row->getUser()->getId()] = true;
                }
                $hours += $row->getHours();
                $user_entries[$row->getUser()->getId()][] = $row;
            }
        }

        $workingdays_to_now = $this->calcWorkingDaysInRange($date_from->getValue()->format('Ymd'), date('Ymd', time()));

        if (date('Ymd', time()) !== $date_to->getValue()->format('Ymd')) {
            $workingdays_to_now = $this->calcWorkingDaysInRange($date_from->getValue()->format('Ymd'), $date_to->getValue()->format('Ymd'));
        }

        foreach ($user_entries as $user) {
            $ranking[] = $this->parseRanking($user, $workingdays_to_now, $working_hours_per_day, $token);
        }

        // Get the first registered entry.
        $first_entry_object = $this->getEntityManager()->getRepository('HarvesterFetchBundle:Entry')->findOneBy(array(), array(
            'spentAt' => 'ASC',
        ));

        return array(
            'success' => ($query_result ? true : false),
            'ranking' => $ranking,
            'date_start' => $date_from->getValue()->format('Ymd'),
            'date_end' => $date_to->getValue()->format('Ymd'),
            'hours_in_range' => $hours_in_range,
            'hours_total_registered' => $hours,
            'misc' => array(
                'first_entry' => array(
                    'year' => $first_entry_object->getSpentAt()->format('Y'),
                    'day' => $first_entry_object->getSpentAt()->format('d'),
                    'month' => $first_entry_object->getSpentAt()->format('m'),
                ),
            ),
        );
    }

    /**
     * Find amount of working days in range by "Ymd" format.
     *
     * @param int $from
     * @param int $to
     * @return int
     */
    public function calcWorkingDaysInRange($from, $to)
    {
        // If we only want to fetch one day.
        if ($from === $to) {
            return 1;
        }

        // Create an instance of the range.
        $from = Datetime::createFromFormat('Ymd', $from);
        $to = Datetime::createFromFormat('Ymd', $to);
        $today = Datetime::createFromFormat('Ymd', date('Ymd', time()));

        // If: "to" is the same month / year as the current month / year.
        // Or: "to" is greater than the current month / year.
        if (($to->format('Ym') === $today->format('Ym')) OR ($to->format('Ym') > $today->format('Ym'))) {
            // If "to" is greater than or equal to the current date.
            if ($to >= $today) {
                // Set "to", to the current date.
                $to = Datetime::createFromFormat('Ymd', date('Ymd', time()));
            }
            // Else: include the end date to the period.
            else {
              $to->modify('+1 day');
            }
        }
        // Else: include the end date to the period.
        else {
            $to->modify('+1 day');
        }
        // Set the date interval to be "1 day" (P1D means: Period = 1 Day).
        $interval = new DateInterval('P1D');
        // Get the period from the "from" date to the "to" date,
        // based on 1 day periods.
        $periods = new DatePeriod($from, $interval, $to);

        // Find amount of work days.
        $work_days = 0;
        // We loop through each period/day and find each "N" that's between
        // 1 to 5 (mon - fri).
        foreach ($periods as $period) {
            // If the day is from 1 to 5 (mon-fri).
            if ($period->format('N') < 6) {
                // Add a day to "amount of work days".
                $work_days++;
            }
        }

        return $work_days;
    }

    /**
     * Find a raking group calc'ed from work performance.
     *
     * @param float $hours_registered
     * @param float $hours_goal
     * @return string
     */
    public function determineRankingGroup($hours_registered, $hours_goal) {
        $performance = round($hours_registered/$hours_goal*100);

        if($performance >= 110) {
            $group = "A-karmahunter";
        } elseif ($performance < 110 && $performance >= 98) {
            $group = "B-goalie";
        } elseif ($performance < 98 && $performance >= 80) {
            $group = "C-karmauser";
        } else {
            $group = "D-slacker";
        }
        return $group;
    }


    /**
     * Parse the user ranking into an array.
     *
     * @param $user_entries
     * @param int $workingdays_to_now
     * @param float $user_working_hours
     * @param string $token
     * @return array
     */
    public function parseRanking(Array $user_entries, $workingdays_to_now, $user_working_hours = null, $token = null)
    {
        $hours = $billable = $education = $holiday = $vacation = 0;
        $illness['normal'] = $illness['child'] = 0;
        $billability['raw'] = $billability['calculated'] = 0;
        $extra = [];
        $user = false;

        if ($token) {
            $user = $this->getEntityManager()->getRepository('HarvesterFetchBundle:User')->findOneById($token);
        }

        // Loop through all user entries and calculate.
        foreach ($user_entries as $entry) {
            if ($token == $entry->getUser()->getId() || (is_object($user) && $user->hasRole('ROLE_ADMIN'))) {
                if ($entry->getTasks()->getName() == 'Helligdag') {
                    $holiday += $entry->getHours();
                }
                if ($entry->getTasks()->getName() == 'Ferie') {
                    $vacation += $entry->getHours();
                }
                if ($entry->getTasks()->getName() == 'Sygdom' || $entry->getTasks()->getName() == 'Barns første sygedag') {
                    if ($entry->getTasks()->getName() == 'Sygdom') {
                        $illness['normal'] += $entry->getHours();
                    }
                    else {
                        $illness['child'] += $entry->getHours();
                    }
                }
                if ($entry->getTasks()->getName() == 'Uddannelse/Kursus') {
                    $education += $entry->getHours();
                }
                if ($entry->getTasks()->getBillableByDefault() && $entry->getProject()->getBillable()) {
                    $billable += $entry->getHours();
                }
            }
            $hours += $entry->getHours();
        }

        // Get default working hours per day for the user.
        if ($entry->getUser()->getWorkingHours() > 0) {
            $user_working_hours = $entry->getUser()->getWorkingHours();
        }

        // Get the normed hours for this month.
        $hours_goal = $workingdays_to_now * $user_working_hours;

        // Split user id, used for creating path to user avatar from Harvest!
        $user_id = str_pad($entry->getUser()->getId(), 9, 0, STR_PAD_LEFT);
        $split_user_id = str_split($user_id, 3);

        // Get the actual hours the user is working.
        $working_hours = $hours - $vacation - $holiday;

        if ($token == $entry->getUser()->getId() || (is_object($user) && $user->hasRole('ROLE_ADMIN'))) {
            if ($billable && $working_hours) {
                // Calculate billability percent from the actual working hours.
                $billability['calculated'] = round($billable / $working_hours * 100, 2);

                // Calculate billability percent from total amount of hours.
                $billability['raw'] = round($billable / $hours * 100, 2);
            }

            // If no illness is registered, minimize output.
            if ($illness['normal'] == false && $illness['child'] == false) {
                $illness = false;
            }

            $extra = array(
                'billable' => $billable,
                'billability' => $billability,
                'holiday' => $holiday,
                'education' => $education,
                'vacation' => $vacation,
                'illness' => $illness,
            );
        }

        return array(
            'first_name' => $entry->getUser()->getFirstName(),
            'last_name' => $entry->getUser()->getLastName(),
            'full_name' => $entry->getUser()->getFirstName() . ' ' . $entry->getUser()->getLastName(),
            'group' => $this->determineRankingGroup($hours, $hours_goal),
            'hours_goal' => $hours_goal,
            'hours_registered' => $hours,
            'converted_user_id' => implode('/', $split_user_id),
            'extra' => $extra,
        );
    }
}
