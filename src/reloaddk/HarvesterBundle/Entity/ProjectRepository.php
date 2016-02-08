<?php

namespace reloaddk\HarvesterBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Harvest_Project;
use HarvestReports;
use Symfony\Component\Console\Output\ConsoleOutput;
use DateTime;
/**
 * ProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectRepository extends EntityRepository
{
    /**
     * Register the project.
     *
     * @param Harvest_Project $harvest_project
     * @param ConsoleOutput $output
     * @param $api
     * @return Project
     */
    public function registerProject(Harvest_Project $harvest_project, ConsoleOutput $output, HarvestReports $api)
    {
        $project = $this->getEntityManager()->getRepository('reloaddkHarvesterBundle:Project')->findOneById($harvest_project->id);

        if (!$project) {
            $project = new Project();
            $this->saveProject($project, $harvest_project, $api);
            $output->writeln('<info>' . $harvest_project->get('name') . ' created.</info>');
        }
        else {

            $project_last_update = new DateTime($harvest_project->get('updated-at'));

            if ($project->getUpdatedAt()->getTimestamp() < $project_last_update->getTimestamp()-3600) {
                $this->saveProject($project, $harvest_project, $api);
                $output->writeln('<info>'.$harvest_project->get('name') .  ' have been updated.</info>');
            }
            else {
                $output->writeln('<comment>'.$harvest_project->get('name') .  ' is up to date.</comment>');
            }
        }

        return $project;
    }

    /**
     * Save multiple projects.
     *
     * @param Array $projects
     * @param $output
     * @param HarvestReports $api
     */
    public function saveProjects(Array $projects, ConsoleOutput $output, HarvestReports $api) {

        foreach ($projects as $projectId) {
            $harvest_project = $api->getProject($projectId);
            $this->registerProject($harvest_project->get('data'), $output, $api);
        }
    }

    /**
     * Save the project to db.
     *
     * @param Project $project
     * @param Harvest_Project $harvest_project
     * @param HarvestReports $api
     * @return Project
     */
    public function saveProject(Project $project, Harvest_Project $harvest_project, HarvestReports $api)
    {
        $client = $this->getEntityManager()->getRepository('reloaddkHarvesterBundle:Client')->findOneById($harvest_project->get('client-id'));

        // If the client doesn't exist in db, create it.
        if (!$client) {
            $harvest_client = $api->getClient($harvest_project->get('client-id'));
            $client = $this->getEntityManager()->getRepository('reloaddkHarvesterBundle:Client')
                ->registerClient($harvest_client->get('data'), new ConsoleOutput(), $api);
        }
        // Create project object.
        $project->setId($harvest_project->get('id'));
        $project->setClientId($harvest_project->get('client-id'));
        $project->setClient($client);
        $project->setName($harvest_project->get('name'));
        $project->setCode($harvest_project->get('code'));
        $project->setActive($harvest_project->get('active') == 'true' ? 1 : 0);
        $project->setNotes($harvest_project->get('notes'));
        $project->setBillable($harvest_project->get('billable') == 'true' ? 1 : 0);
        $project->setBillBy($harvest_project->get('bill-by'));
        $project->setCostBudget($harvest_project->get('cost-budget'));
        $project->setCostBudgetIncludeExpenses($harvest_project->get('cost-budget-include-expenses') == 'true' ? 1 : 0);
        $project->setHourlyRate($harvest_project->get('hourly-rate'));
        $project->setBudget($harvest_project->get('budget'));
        $project->setBudgetBy($harvest_project->get('budget-by'));
        $project->setNotifyWhenOverBudget($harvest_project->get('notify-when-over-budget') == 'true' ? 1 : 0);
        $project->setOverBudgetNotificationPercentage($harvest_project->get('over-budget-notification-percentage'));
        $project->setOverBudgetNotifiedAt(new DateTime($harvest_project->get('over-budget-notified-at')));
        $project->setShowBudgetToAll($harvest_project->get('show-budget-to-all') == 'true' ? 1 : 0);
        $project->setCreatedAt(new DateTime($harvest_project->get('created-at')));
        $project->setUpdatedAt(new DateTime($harvest_project->get('updated-at')));
        $project->setEstimate($harvest_project->get('estimate'));
        $project->setEstimateBy($harvest_project->get('estimate-by'));
        $project->setHintEarliestRecordAt(new DateTime($harvest_project->get('hint-earliest-record-at')));
        $project->setHintLatestRecordAt(new DateTime($harvest_project->get('hint-latest-record-at')));

        // Save the project to db.
        $em = $this->getEntityManager();
        $em->persist($project);
        $em->flush();

        return $project;
    }
}
