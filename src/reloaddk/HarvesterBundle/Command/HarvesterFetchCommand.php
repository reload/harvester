<?php

namespace reloaddk\HarvesterBundle\Command;

use DateTime;
use Harvest_Range;
use Harvest_Exception;
use DateInterval;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HarvesterFetchCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $date_from = new DateTime('01-01-1970');
        $date_to = new DateTime('now');

        $this
            ->setName('harvester:fetch')
            ->setDescription('Fetch Harvest data.')
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
                'all-users',
                null,
                InputOption::VALUE_NONE,
                'If set, both active and inactive users will be fetched.')
            ->addOption(
                'preserve-roles',
                null,
                InputOption::VALUE_NONE,
                'If set, preserve the admin roles set on the user, add role to new users.')
            ->addOption(
                'updated',
                null,
                InputOption::VALUE_REQUIRED,
                'How many days do you want to go back and look for updated entries?')
            ->addOption(
                'updated-yesterday',
                null,
                InputOption::VALUE_NONE,
                'If set, get the entries that have been updated since yesterday.')
            ->addOption(
                'updated-week',
                null,
                InputOption::VALUE_NONE,
                'If set, get the entries that have been updated within the last 7 days.')
            ->addOption(
                'updated-month',
                null,
                InputOption::VALUE_NONE,
                'If set, get the entries that have been updated within the last 30 days.')
            ->addOption(
                'updated-year',
                null,
                InputOption::VALUE_NONE,
                'If set, get the entries that have been updated this year.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get the "real" reports HarvestAPI.
        $api = $this->getContainer()->get('harvest_app_reports')->getApi();

        // Get our own, extended API, that only supports "API", not "Reports".
        $extended_api = $this->getContainer()->get('harvest_adapter')->getApi();

        // Get doctrine.
        $doctrine = $this->getContainer()->get('doctrine');

        // Check if we have a valid connection to the API.
        if (!$api->getUsers()->isSuccess()) {
            throw new Harvest_Exception($api->getUsers()->get('data'));
        }

        // Fetch users from HarvestAPI.
        $api_users = $input->getOption('all-users') ? $api->getUsers() : $api->getActiveUsers();

        // Get date arguments from command.
        $from_date = Datetime::createFromFormat('Ymd', $input->getArgument('from-date'));
        $to_date = Datetime::createFromFormat('Ymd', $input->getArgument('to-date'));

        // Get the current date.
        $date_today = new DateTime('now');

        // Updated: custom value.
        if ($input->getOption('updated')) {
            // If 1 is greater than the requested amount of days.
            if ($input->getOption('updated') <= 0) {
                $output->writeln('<error>The value for the "updated" argument, must be greater than 0</error>');
                return;
            }

            // Set the period to days and deduced "1" from the current date;
            $interval = new DateInterval('P' . $input->getOption('updated') . 'D');
            $updated_since = $date_today->sub($interval)->format('Y-m-d');
        }

        // Updated: Yesterday.
        if ($input->getOption('updated-yesterday')) {
            // Set the period to days and deduced "1" from the current date;
            $interval = new DateInterval('P1D');
            $updated_since = $date_today->sub($interval)->format('Y-m-d');
        }

        // Updated: within 7 days.
        if ($input->getOption('updated-week')) {
            // Set the period to days and deduced "7" from the current date;
            $interval = new DateInterval('P7D');
            $updated_since = $date_today->sub($interval)->format('Y-m-d');
        }

        // Updated: within 30 days.
        if ($input->getOption('updated-month')) {
            // Set the period to days and deduced "30" from the current date;
            $interval = new DateInterval('P30D');
            $updated_since = $date_today->sub($interval)->format('Y-m-d');
        }

        // Updated: this year. Will go back to the first of january.
        if ($input->getOption('updated-year')) {
            $updated_since = $date_today->format('Y') . '-01-01';
        }

        // Set "updated since" to "null" if no argument were provided.
        $updated_since = isset($updated_since) ? $updated_since : null;

        // Load the repositories that we wish to work with inside the loop.
        $user_repository = $doctrine->getManager()->getRepository('reloaddkHarvesterBundle:User');
        $entry_repository = $doctrine->getManager()->getRepository('reloaddkHarvesterBundle:Entry');

        // If we have a valid callback from Harvest.
        if ($api_users->isSuccess()) {
            // Loop through each users.
            foreach ($api_users->get('data') as $user_id => $api_user) {
                // Output the current user to the terminal.
                $output->writeln('<info>Fetching ' . $api_user->first_name . ' ' . $api_user->last_name . ' [' . $api_user->id . ']</info>');
                $output->writeln($api_user->notes);

                // Register or update user.
                $user_repository->registerUser($api_user, $input, $output);

                // Set range for the Harvest data.
                $range = new Harvest_Range($from_date->format('Ymd'), $to_date->format('Ymd'));

                // Fetch user entries (the 3. argument is null since we don't want to use a project ID).
                $user_entries = $extended_api->getUserEntries($user_id, $range, null, $updated_since);

                // Save users entries if any is available.
                if ($user_entries->isSuccess() && count($user_entries->get('data'))) {
                    $entry_repository->registerEntry($user_entries, $output, $api);
                }
            }
        }
    }
}