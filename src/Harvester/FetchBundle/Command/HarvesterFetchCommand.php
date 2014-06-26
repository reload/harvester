<?php

namespace Harvester\FetchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use DateTime;
use Harvest_Range;
use Harvest_Exception;

class HarvesterFetchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $date_from = new DateTime('01-01-1970');
        $date_to = new DateTime('now');

        $this
            ->setName('harvester:fetch')
            ->setDescription('Fetch Harvest data')
            ->addArgument(
                'from-date',
                InputArgument::OPTIONAL,
                "'From' date. (yyyymmdd)",
                $date_from->format('Ymd'))
            ->addArgument(
                'to-date',
                InputArgument::OPTIONAL,
                "'To' date. (yyyymmdd)",
                $date_to->format('Ymd'))
            ->addOption(
                'all-users',
                null,
                InputOption::VALUE_NONE,
                'If set, both active and inactive users will be fetched');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = $this->getContainer()->get('harvest_app_reports')->getApi();
        $doctrine = $this->getContainer()->get('doctrine');

        // Check if we have a valid connection to the API.
        if (!$api->getUsers()->isSuccess()) {
            throw new Harvest_Exception($api->getUsers()->get('data'));
        }

        // Fetch users from HarvestAPI.
        $api_users = $input->getOption('all-users') ? $api->getUsers() : $api->getActiveUsers();

        // Get date arguments from command.
        $from_date = $input->getArgument('from-date');
        $to_date = $input->getArgument('to-date');

        // If we have a valid callback from Harvest.
        if ($api_users->isSuccess()) {
            foreach ($api_users->get('data') as $user_id => $api_user) {
                $output->writeln('<info>' . $api_user->first_name . ' ' . $api_user->last_name . '</info>');

                $doctrine->getManager()->getRepository('HarvesterFetchBundle:User')
                    ->registerUser($api_user, $output);

                // Fetch user entries updated within range.
                $range = new Harvest_Range($from_date, $to_date);
                $user_entries = $api->getUserEntries($user_id, $range);

                // Save users entries if any is available.
                if ($user_entries->isSuccess() && count($user_entries->get('data'))) {
                    $doctrine->getManager()->getRepository('HarvesterFetchBundle:Entry')
                        ->registerEntry($user_entries, $output, $api);
                }
            }
        }
    }
}