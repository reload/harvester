<?php

namespace reloaddk\HarvesterBundle\Entity;

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
     * Register the new Entries.
     *
     * @param Harvest_Result $user_entries
     * @param $output
     * @param HarvestReports $api
     */
    public function saveEntries(Harvest_Result $user_entries, OutputInterface $output, HarvestReports $api)
    {
        // We loop through each entry and prepare them to be written, while also getting
        // getting information about how many entries are ready to be updated or created.
        $count_new_entries = $count_updated_entries = 0;
        foreach ($user_entries->get('data') as $user_entry) {

            $entry = $this->getEntityManager()->getRepository('reloaddkHarvesterBundle:Entry')->findOneById($user_entry->get('id'));

            if (!$entry) {
                $this->queueEntry(new Entry(), $user_entry, $api);
                ++$count_new_entries;
                if (!$count_new_entries) {
                    $output->writeln('<comment>--> Entries queued for insertion.</comment>');
                    ++$count_new_entries;
                }
            }
            else {
                $entry_last_update = new DateTime($user_entry->get('updated-at'));

                if ($entry->getUpdatedAt()->getTimestamp() < $entry_last_update->getTimestamp()-7200) {
                    $this->queueEntry($entry, $user_entry, $api);
                    ++$count_updated_entries;
                    if (!$count_updated_entries) {
                        $output->writeln('<comment>--> Entries queued for update.</comment>');
                        ++$count_updated_entries;
                    }
                }
            }
        }

        // Output feedback to screen.
        if ($count_updated_entries || $count_new_entries) {
            $output->writeln('<comment>--> Entries: </comment>');
            if ($count_new_entries) {
                $output->writeln('<info>    Insert: ' . $count_new_entries . ' queued.</info>');
            }
            if ($count_updated_entries) {
                $output->writeln('<info>    Update: ' . $count_updated_entries . ' queued.</info>');
            }

            // Write all the queued entries to the database.
            try {
                $this->getEntityManager()->flush();
                $output->writeln('<comment>--> Wrote queued entries.</comment>');
            } catch (\Doctrine\ORM\OptimisticLockException $e) {
                $output->writeln('<error>' . $e->getMessage() . '</error>');
            }
        }
    }

    /**
     * Delete Entries based on user id.
     *
     * @param object $user
     * @param string $from_date in Y-m-d format
     * @param string $to_date in Y-m-d format
     * @param $output
     */
    public function deleteEntries($user, $from_date, $to_date, OutputInterface $output)
    {
        // Get the Doctrine Entity Manager and delete all rows for a user between a scope.
        $em = $this->getEntityManager();
        $query = $em->createQuery('DELETE reloaddkHarvesterBundle:entry e WHERE e.user = :user AND e.spentAt >= :from_date AND e.spentAt <= :to_date')
            ->setParameter('user', $user->id)
            ->setParameter('from_date', $from_date . ' 00:00:00')
            ->setParameter('to_date', $to_date . ' 23:59:59');
        $result = $query->execute();

        // Output.
        if ($result) {
            $output->writeln('<comment>--> Entries deleted.</comment>');
        }
    }

    /**
     * Queue Entry to database.
     *
     * @param Entry $entry
     * @param Harvest_DayEntry $harvest_entry
     * @param HarvestReports $api
     */
    public function queueEntry(Entry $entry, Harvest_DayEntry $harvest_entry, HarvestReports $api)
    {
        $user = $this->getEntityManager()->getRepository('reloaddkHarvesterBundle:User')->findOneById($harvest_entry->get('user-id'));
        $project = $this->getEntityManager()->getRepository('reloaddkHarvesterBundle:Project')->findOneById($harvest_entry->get('project-id'));
        $task = $this->getEntityManager()->getRepository('reloaddkHarvesterBundle:Task')->findOneById($harvest_entry->get('task-id'));

        // If the project doesn't exist in db, create it.
        if (!$project) {
            $harvest_project = $api->getProject($harvest_entry->get('project-id'));
            $project = $this->getEntityManager()->getRepository('reloaddkHarvesterBundle:Project')
                ->registerProject($harvest_project->get('data'), new ConsoleOutput(), $api);
        }

        // If the task doesn't exist in db, create it.
        if (!$task) {
            $harvest_task = $api->getTask($harvest_entry->get('task-id'));
            $task = $this->getEntityManager()->getRepository('reloaddkHarvesterBundle:Task')
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
        $entry->setStatus(1);

        // Notify the "Unit Of Work" of this entry for the next flush.
        $em = $this->getEntityManager();
        $em->persist($entry);
    }

    /**
     * Save Entry to database.
     *
     * @param Entry $entry
     * @param Harvest_DayEntry $harvest_entry
     * @param HarvestReports $api
     * @param OutputInterface $output
     *
     * @see queueEntry
     */
    public function saveEntry(Entry $entry, Harvest_DayEntry $harvest_entry, HarvestReports $api, OutputInterface $output)
    {
        // Queue the Entry.
        $this->queueEntry($entry, $harvest_entry, $api);

        // Write to the database.
        try {
            $this->getEntityManager()->flush();
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }

    /**
     * Set the status of an entry to 0.
     *
     * @param $entry_id
     * @param OutputInterface $output
     */
    public function updateEntryStatus($entry_id, OutputInterface $output)
    {
        // Get the requested entry.
        $em = $this->getEntityManager();
        $entry = $em->getRepository('reloaddkHarvesterBundle:Entry')->findOneBy(array(
            'id' => $entry_id,
        ));

        // set the status and save the change.
        $entry->setStatus(0);
        $em->flush();

        // Provide feedback to the user.
        if ($entry) {
            $output->writeln('<comment>--> Entry [' . $entry_id . '] status updated.</comment>');
        }
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
        // Fetch entries from the query.
        $entries = $query->getQuery()->getResult();
        // Fetch date range from query.
        $date_to = $query->getQuery()->getParameter('date_to');
        $date_from = $query->getQuery()->getParameter('date_from');

        // Define default values.
        $hours_registered = 0;
        $hours_in_range = $hours_to_today = null;
        $users = $old_user = $user_entries = [];

        // Categorise entries by user if they are active company employees.
        foreach ($entries as $entry) {
            // If the user is active and isn't a contractor.
            if ($entry->getUser()->getIsActive() && !$entry->getUser()->getIsContractor()) {
                // Categorise the entry by user id.
                $user_entries[$entry->getUser()->getId()][] = $entry;
            }
        }

        // Get total working days in range for calculations when parsing users.
        $working_days_in_range = $this->calcWorkingDaysInRange($date_from->getValue()->format('Ymd'), $date_to->getValue()->format('Ymd'));

        // Format user-data and calculate total sums of data like "hours in range"
        // and "hours registered".
        foreach ($user_entries as $user) {
            // Format and calculate the user categorised entries.
            $parsed_user = $this->parseUser($user, $working_days_in_range, $working_hours_per_day, $token);
            // Array of all the parsed users we return.
            $users[] = $parsed_user;
            // Add this users data to the sums.
            $hours_in_range += $parsed_user['hours_goal'];
            $hours_registered += $parsed_user['hours_registered'];
        }

        // Get the first registered entry.
        $first_entry_object = $this->getEntityManager()->getRepository('reloaddkHarvesterBundle:Entry')->findOneBy(array(), array(
            'spentAt' => 'ASC',
        ));

        // Return the final response.
        return array(
            'success' => ($entries ? true : false),
            'users' => $users,
            'date_start' => $date_from->getValue()->format('Ymd'),
            'date_end' => $date_to->getValue()->format('Ymd'),
            'hours_in_range' => $hours_in_range,
            'hours_total_registered' => $hours_registered,
            'misc' => array(
                'working_days_in_range' => $working_days_in_range,
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
        }
        else if ($performance < 110 && $performance >= 98) {
            $group = "B-goalie";
        }
        else if ($performance < 98 && $performance >= 80) {
            $group = "C-karmauser";
        }
        else {
            $group = "D-slacker";
        }

        return $group;
    }


    /**
     * Parse the user ranking into an array.
     *
     * @param $user_entries
     * @param int $workingdays_in_range
     * @param float $user_working_hours
     * @param string $token
     * @return array
     */
    public function parseUser(Array $user_entries, $workingdays_in_range, $user_working_hours = null, $token = null)
    {
        $hours = $billable_hours = $education = $holiday = $time_off = $vacation = 0;
        $illness['normal'] = $illness['child'] = 0;
        $billability['raw'] = $billability['calculated'] = 0;
        $extra = $admin = [];
        $user = false;

        if ($token) {
            $user = $this->getEntityManager()->getRepository('reloaddkHarvesterBundle:User')->findOneById($token);
        }

        // Loop through all user entries and calculate.
        foreach ($user_entries as $entry) {
            if ($token == $entry->getUser()->getId() || (is_object($user) && $user->hasRole('ROLE_ADMIN'))) {
                if ($entry->getTasks()->getName() == 'Helligdag') {
                    $holiday += $entry->getHours();
                }
                else if ($entry->getTasks()->getName() == 'Ferie') {
                    $vacation += $entry->getHours();
                }
                else if ($entry->getTasks()->getName() == 'Holder fri') {
                    $time_off += $entry->getHours();
                }
                else if ($entry->getTasks()->getName() == 'Sygdom' || $entry->getTasks()->getName() == 'Barns første sygedag') {
                    if ($entry->getTasks()->getName() == 'Sygdom') {
                        $illness['normal'] += $entry->getHours();
                    }
                    else {
                        $illness['child'] += $entry->getHours();
                    }
                }
                else if ($entry->getTasks()->getName() == 'Uddannelse/Kursus') {
                    $education += $entry->getHours();
                }
                if ($entry->getTasks()->getBillableByDefault() && $entry->getProject()->getBillable()) {
                    $billable_hours += $entry->getHours();
                }
            }
            $hours += $entry->getHours();
        }

        // Get default working hours per day for the user.
        if ($entry->getUser()->getWorkingHours() > 0) {
            $user_working_hours = $entry->getUser()->getWorkingHours();
        }

        // Get the normed hours for this month.
        $hours_goal = $workingdays_in_range * $user_working_hours;

        // Split user id, used for creating path to user avatar from Harvest!
        $user_id = str_pad($entry->getUser()->getId(), 9, 0, STR_PAD_LEFT);
        $split_user_id = str_split($user_id, 3);

        // Get the actual hours the user is working.
        $working_hours = $hours - $vacation - $holiday - $time_off - $education;

        if ($token == $entry->getUser()->getId() || (is_object($user) && $user->hasRole('ROLE_ADMIN'))) {
            if ($billable_hours && $working_hours) {
                // Calculate billability percent from the actual working hours.
                $billability['calculated'] = round($billable_hours / $working_hours * 100, 2);

                // Calculate billability percent from total amount of hours.
                $billability['raw'] = round($billable_hours / $hours * 100, 2);

                // Provide admin-only data like "performance" which calculates the percentage
                // a user is from an admin provided billable goal.
                if ($user->hasRole('ROLE_ADMIN')) {
                    // Provide a default value for "billable hours goal per day (75),
                    // if no specifics have been provided.
                    // @TODO: Find a way to fetch the value from app/config/parameters.yml.
                    $goal = $entry->getUser()->getBillabilityGoal() != NULL ? $entry->getUser()->getBillabilityGoal() : 75;
                    // Calculate the billable hours to reach compared to the goal.
                    $billable_hours_to_reach = ($goal / 100) * $working_hours;
                    // Find the current performance compared to the provided goal.
                    // Ex: 75 billable_hours / 100 billable_hours_to_reach = 75% performance.
                    $calculated_goal = ($billable_hours / $billable_hours_to_reach) * 100;

                    $admin = array(
                        'billability' => array(
                            'billable_hours_to_reach' => round($billable_hours_to_reach, 2),
                            'goal' => round($goal, 2),
                            'performance' => round($calculated_goal, 2),
                        ),
                    );
                }
            }

            // If no illness is registered, minimize output.
            if ($illness['normal'] == false && $illness['child'] == false) {
                $illness = false;
            }

            // Extra information for admins and logged in users.
            $extra = array(
                'billable_hours' => $billable_hours,
                'billability' => array(
                    'of_total_hours' => $billability['raw'],
                    'of_working_hours' => $billability['calculated'],
                    'hours_pr_day' => round($billable_hours / $workingdays_in_range, 2),
                ),
                'holiday' => $holiday,
                'time_off' => $time_off,
                'education' => $education,
                'vacation' => $vacation,
                'illness' => $illness,
                'working_hours' => $working_hours,
                'working_days' => $workingdays_in_range,
            );
        }

        return array(
            'id' => $entry->getUser()->getId(),
            'first_name' => $entry->getUser()->getFirstName(),
            'last_name' => $entry->getUser()->getLastName(),
            'full_name' => $entry->getUser()->getFirstName() . ' ' . $entry->getUser()->getLastName(),
            'hours_goal' => $hours_goal,
            'hours_registered' => $hours,
            'extra' => $extra,
            'admin' => $admin,
        );
    }
}
