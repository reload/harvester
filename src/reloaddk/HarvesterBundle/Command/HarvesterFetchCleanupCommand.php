<?php

namespace reloaddk\HarvesterBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use DateTime;
use DateInterval;
use Harvest_Range;
use Harvest_Exception;

class HarvesterFetchCleanupCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $date_from = new DateTime('01-01-1970');
        $date_to = new DateTime('now');

        $this
            ->setName('harvester:fetch:cleanup')
            ->setDescription('Update status of user-entries within a given range.')
            ->addArgument(
                'from-date',
                InputArgument::OPTIONAL,
                "'From' date. (YYYYMMDD).",
                $date_from->format('Ymd'))
            ->addArgument(
                'to-date',
                InputArgument::OPTIONAL,
                "'To' date. (YYYYMMDD).",
                $date_to->format('Ymd'))
            ->addOption(
                'days',
                null,
                InputOption::VALUE_REQUIRED,
                'How many days do you wish to go back and repopulate the records?
This will overwrite and values given to "from-date" and "to-date".')
            ->addOption(
                'all-users',
                null,
                InputOption::VALUE_NONE,
                'If set, both active and inactive users will be fetched.')
            ->addOption(
                'preserve-roles',
                null,
                InputOption::VALUE_NONE,
                'If set, preserve the admin roles set on the user, add role to new users.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get containers.
        $harvest_api = $this->getContainer()->get('harvest_app_reports')->getApi();
        $extended_api = $this->getContainer()->get('harvest_adapter')->getApi();

        // Get doctrine.
        $doctrine = $this->getContainer()->get('doctrine');

        // Fetch users from Harvest and check if we have a valid connection to the API.
        $users = $input->getOption('all-users') ? $harvest_api->getUsers() : $harvest_api->getActiveUsers();
        if (!$users->isSuccess()) {
            throw new Harvest_Exception($users->get('data'));
        }

        // Get date arguments from command.
        $from_date = Datetime::createFromFormat('Ymd', $input->getArgument('from-date'));
        $to_date = Datetime::createFromFormat('Ymd', $input->getArgument('to-date'));

        // Get the current date.
        $date_today = new DateTime('now');

        // If the "days" option is provided, format the range.
        // This will overwrite the "from" and "to" dates if provided.
        if (is_numeric($input->getOption('days'))) {
            // Get the amount of days the user want to go back and clear.
            $interval = new DateInterval('P' . $input->getOption('days') . 'D');

            // Set the "from" date to go "X" amount of days back.
            $from_date = Datetime::createFromFormat('Ymd', date('Ymd', time()))->sub($interval);

            // Set the "to" date, to the current date.
            $to_date = $date_today;
        }

        // Load the repositories that we wish to work with inside the loop.
        $entry_repository = $doctrine->getManager()->getRepository('reloaddkHarvesterBundle:Entry');

        // Loop through each users.
        foreach ($users->get('data') as $user) {
            // Output the current user to the terminal.
            $output->writeln('<info>Fetching ' . $user->first_name . ' ' . $user->last_name . ' [' . $user->id . ']</info>');
            $output->writeln($user->notes);

            // Set range for the Harvest data.
            $range = new Harvest_Range($from_date->format('Ymd'), $to_date->format('Ymd'));

            // Fetch user entries from Harvest and create an array of IDs.
            $harvest_entries = $extended_api->getUserEntries($user->id, $range);

            // Save the latest entries so we don't compare with an outdated dataset.
            if ($harvest_entries->isSuccess() && count($harvest_entries->get('data'))) {
                $entry_repository->saveEntries($harvest_entries, $output, $harvest_api);
            }

            // Prepare array of Harvests id's to compare them with our DB later.
            $harvest_entries_ids = [];
            foreach ($harvest_entries->get('data') as $entry) {
                $harvest_entries_ids[] = intval($entry->id);
            }

            // Fetch user entries from the DB.
            $query = $entry_repository->createQueryBuilder('e');
            $query
                ->where('e.user = :uid AND e.spentAt >= :date_from AND e.spentAt < :date_to')
                ->setParameter('uid', $user->id)
                ->setParameter('date_from', $from_date->format('Y-m-d'))
                ->setParameter('date_to', $to_date->format('Y-m-d'));
            $db_entries = $query->getQuery()->getResult();

            // Prepare array of our id's to compare them with the Harvest response.
            $db_entries_ids = [];
            foreach ($db_entries as $entry) {
                $db_entries_ids[] = intval($entry->getId());
            }

            // Find the difference of the two id arrays. The difference are deleted entries.
            $diff = array_diff($db_entries_ids, $harvest_entries_ids);

            // Update the status of all the entries in the diff array.
            foreach ($diff as $entry_id) {
              $entry_repository->updateEntryStatus($entry_id, $output);
            }
        }
    }
}