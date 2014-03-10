<?php

namespace Harvester\FetchBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Harvester\FetchBundle\Entity\EntryRepository;

class HarvesterFetchEntriesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('harvester:fetchentries');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = $this->getContainer()->get('harvest_app_reports')->getApi();
        $doctrine = $this->getContainer()->get('doctrine');

        $repository = $this->getContainer()->get('doctrine')
            ->getRepository('HarvesterFetchBundle:User');

        $users = $repository->createQueryBuilder('u')
            ->where('u.isActive = 1')
            ->getQuery()
            ->getResult();

        foreach ($users as $user)
        {
            $entries = $api->getUserEntries($user->getId(), new \Harvest_Range('20140101', '20140131'));
            $doctrine->getManager()->getRepository('HarvesterFetchBundle:Entry')
                ->registerEntry($entries, $output);
        }

    }
}