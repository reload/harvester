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
use Harvest_Exception;

class HarvesterRefreshCommand extends ContainerAwareCommand
{
  protected function configure()
  {
    $date_from = new DateTime('01-01-1970');
    $date_to = new DateTime('now');

    $this
      ->setName('harvester:refresh')
      ->setDescription('Clear user-entries from the database within a range and refill.')
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

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // Get the "real" reports HarvestAPI.
    $api = $this->getContainer()->get('harvest_app_reports')->getApi();
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

    // If the "days" option is provided, format the range.
    // This will overwrite the "from" and "to" dates if provided.
    if (is_numeric($input->getOption('days'))) {
        // Get the amount of days the user want to go back and clear.
        $interval = new DateInterval('P' . $input->getOption('days') . 'D');
        // Set the "from" date to go "X" amount of days back.
        $from_date = Datetime::createFromFormat('Ymd', date('Ymd', time()))
            ->sub($interval);
        // Set the "to" date, to the current date.
        $to_date = $date_today;
    }

    // If we have a valid callback from Harvest.
    if ($api_users->isSuccess()) {
        // Load the repositories that we wish to work with inside the loop.
        $entry_repository = $doctrine->getManager()->getRepository('reloaddkHarvesterBundle:Entry');

        // Inform the terminal that we're clearing old records.
        $output->writeln('<question>|----------------------------------------------|</question>');
        $output->writeln('<question>|------------ Clearing old records ------------|</question>');
        $output->writeln('<question>|----------------------------------------------|</question>');

        // Loop through each users.
        foreach ($api_users->get('data') as $user_id => $api_user) {
            // Output the current user to the terminal.
            $output->writeln('<info>' . $api_user->first_name . ' ' . $api_user->last_name . ' [' . $api_user->id . ']</info>');
            $output->writeln($api_user->notes);

            // Clear the previous records for the user.
            $entry_repository->deleteEntries($api_user, $from_date->format('Y-m-d'), $to_date->format('Y-m-d'), $output);
        }
    }
    // Error message, if the callback was invalid.
    else {
        $output->writeln('<error>The request to Harvest was invalid.</error>');
    }


    // Fetch new data by executing the "harvester:fetch" command.
    $output->writeln('<question>|----------------------------------------------|</question>');
    $output->writeln('<question>|------------ Fetching new records ------------|</question>');
    $output->writeln('<question>|----------------------------------------------|</question>');

    // Execute the "harvester:fetch" command.
    $command = $this->getApplication()->find('harvester:fetch');

    // Set arguments for the command.
    $arguments = array(
        'command'   => 'harvester:fetch',
        'from-date' => $from_date->format('Ymd'),
        'to-date'   => $to_date->format('Ymd')
    );
    if ($input->getOption('all-users')) {
        $arguments['--all-users'] = true;
    }
    if ($input->getOption('preserve-roles')) {
        $arguments['--preserve-roles'] = true;
    }

    // Execute "harvester:fetch".
    $input = new ArrayInput($arguments);
    $command->run($input, $output);
  }
}