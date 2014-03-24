<?php

namespace Harvester\FetchBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class HarvesterFetchTasksCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('harvester:fetchtasks');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = $this->getContainer()->get('harvest_app_reports')->getApi();
        $doctrine = $this->getContainer()->get('doctrine');

        $harvest_tasks = $api->getTasks();

        $doctrine->getManager()->getRepository('HarvesterFetchBundle:Task')
            ->registerTask($harvest_tasks, $output);
    }
}