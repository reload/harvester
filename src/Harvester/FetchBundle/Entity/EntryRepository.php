<?php

namespace Harvester\FetchBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Harvester\FetchBundle\Entity\User;
use Harvester\FetchBundle\Entity\Project;
use Harvest_Result;
use Harvest_DayEntry;
use HarvestReports;
use DateTime;
use Symfony\Component\Console\Formatter\OutputFormatter;
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
                $entry = new Entry();
                $this->saveEntry($entry, $user_entry, $api);
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

        $workingdays_to_now = $this->calcWorkingDaysInRange($date_from->getValue()->format('U')+7200, time());

        $hours = 0;
        $hours_in_range = null;
        $hours_to_today = null;
        $ranking = array();
        $old_user = [];

        foreach ($query_result as $row) {
            if ($row->getUser()->getIsActive() && !$row->getUser()->getIsContractor()) {
                if (!array_key_exists($row->getUser()->getId(), $old_user) || count($old_user) == 0) {
                    $working_hours = $row->getUser()->getWorkingHours() != 0 ? $row->getUser()->getWorkingHours() : 7.5;
                    $hours_in_range += $working_hours * $this->calcWorkingDaysInRange($date_from->getValue()->format('U'), $date_to->getValue()->format('U'));
                    $hours_to_today += $working_hours * $this->calcWorkingDaysInRange($date_from->getValue()->format('U'), time());
                    $old_user[$row->getUser()->getId()] = true;
                }
                $hours += $row->getHours();
                $user_entries[$row->getUser()->getId()][] = $row;
            }
        }

        foreach ($user_entries as $user) {
            $ranking[] = $this->parseRanking($user, $workingdays_to_now, $working_hours_per_day, $token);
        }

        return array(
            'succes' => ($query_result ? true : false),
            'ranking' => $ranking,
            'date_start' => $date_from->getValue()->format('U'),
            'date_end' => $date_to->getValue()->format('U'),
            'hours_until_today' => $hours_to_today,
            'hours_total_month' => $hours_in_range,
            'hours_total_registered' => $hours,
        );
    }

    /**
     * Find amount of working days in range.
     *
     * @param int $from
     * @param int $to
     * @return int
     */
    public function calcWorkingDaysInRange($from, $to)
    {
        $work_days = 0;
        for ($i = $from; $i < $to; $i += 86400) {
            $tmp_day = Datetime::createFromFormat('U', $i);
            if ($tmp_day->format('N') < 6) {
                ++$work_days;
            }
        }
        // Don't want to calculate today, since data isn't at 100%.
        return $work_days-1;
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
     * @return array
     */
    public function parseRanking(Array $user_entries, $workingdays_to_now, $user_working_hours = null, $token = null)
    {
        $hours = $billable = $education = $holiday = $vacation = false;
        $illness['normal'] = $illness['child'] = false;
        $billability['raw'] = $billability['calculated'] = false;

        // Loop through all user entries and calculate.
        foreach ($user_entries as $entry) {
            if ($token == $entry->getUser()->getId() || 1) {
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
                $hours += $entry->getHours();
            }
        }

        // Get default working hours per day for the user.
        if ($entry->getUser()->getWorkingHours() > 0) {
            $user_working_hours = $entry->getUser()->getWorkingHours();
        }

        // Get the normed hours for this month.
        $hours_goal = $workingdays_to_now * $user_working_hours;

        // Get the actual hours the user is working.
        $working_hours = $hours - $vacation - $holiday;

        // Calculate billability percent from the actual working hours.
        $billability['calculated'] = round($billable/$working_hours * 100, 2);

        // Calculate billability percent from total amount of hours.
        $billability['raw'] = round($billable/$hours * 100, 2);

        // Split user id, used for creating path to user avatar from Harvest!
        $user_id = str_pad($entry->getUser()->getId(), 9, 0, STR_PAD_LEFT);
        $split_user_id = str_split($user_id, 3);

        // If no illness is registered, minimize output.
        if ($illness['normal'] == false && $illness['child'] == false) {
            $illness = false;
        }

        return array(
            'name' => $entry->getUser()->getFirstName() . ' ' . $entry->getUser()->getLastName(),
            'group' => $this->determineRankingGroup($hours, $hours_goal),
            'hours_goal' => $hours_goal,
            'hours_registered' => $hours,
            'user_id_first_part' => $split_user_id[0],
            'user_id_second_part' => $split_user_id[1],
            'user_id_third_part' => $split_user_id[2],
            'extra' => array(
                'billable' => $billable,
                'billability' => $billability,
                'holiday' => $holiday,
                'education' => $education,
                'vacation' => $vacation,
                'illness' => $illness,
            ),
        );
    }
}
