<?php

namespace Harvester\FetchBundle\Command;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Harvester\FetchBundle\Entity\EntryRepository;
use Harvest_Range;
use DateTime;

class HarvesterFetchEntriesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $date_from = new DateTime('01-01-1970');
        $date_to = new DateTime('now');
        $this
            ->setName('harvester:fetchentries')
            ->setDescription('Fetch Harvest entries')
            ->addArgument(
                'from-date',
                InputArgument::OPTIONAL,
                "'From' date. (yyyymmdd)",
                $date_from->format('Y-m-d')
            )
            ->addArgument(
                'to-date',
                InputArgument::OPTIONAL,
                "'To' date. (yyyymmdd)",
                $date_to->format('Y-m-d')
            );
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

        $from_date = $input->getArgument('from-date');
        $to_date = $input->getArgument('to-date');

        $repository = $this->getContainer()->get('doctrine')
            ->getRepository('HarvesterFetchBundle:User');

        $users = $repository->createQueryBuilder('u')
            ->where('u.isActive = 1')
            ->getQuery()
            ->getResult();

        foreach ($users as $user) {
            $entries = $api->getUserEntries($user->getId(), new Harvest_Range($from_date, $to_date));
            if ($entries->isSuccess()) {
                $doctrine->getManager()->getRepository('HarvesterFetchBundle:Entry')
                    ->registerEntry($entries, $output);
            }
        }
    }
}