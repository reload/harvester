<?php

namespace Harvester\FetchBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Harvester\FetchBundle\Entity\EntryRepository;

class HarvesterFetchProjectsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('harvester:fetchprojects');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = $this->getContainer()->get('harvest_app_reports')->getApi();

        // Check if we have a valid connection to the API.
        if (!$api->getUsers()->isSuccess()) {
            throw new \Harvest_Exception($api->getUsers()->get('data'));
        }

        $doctrine = $this->getContainer()->get('doctrine');
        $projects = $api->getProjects();
        if ($projects->isSuccess())
        {
            foreach ($projects->data as $project)
            {
                $doctrine->getManager()->getRepository('HarvesterFetchBundle:Project')
                    ->registerProject($project, $output);
            }
        }
    }
}