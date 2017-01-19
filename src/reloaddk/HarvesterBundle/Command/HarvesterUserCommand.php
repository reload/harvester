<?php

namespace reloaddk\HarvesterBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class HarvesterUserCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('harvester:user')
            ->setDescription('Configure a user')
            ->addArgument(
                'user',
                InputArgument::OPTIONAL,
                'Email or Harvest User ID',
                null
            )
            ->addOption(
                'search',
                null,
                InputOption::VALUE_REQUIRED,
                'Search for user by name, email or id'
            )
            ->addOption(
                'admin',
                null,
                InputOption::VALUE_REQUIRED,
                'Set (yes, no) and user will be granted or revoked with administrator role'
            )
            ->addOption(
                'active',
                null,
                InputOption::VALUE_REQUIRED,
                'Set (yes, no) to activate or disable user'
            )
            ->addOption(
                'show',
                null,
                InputOption::VALUE_NONE,
                'Show user data'
            )
            ->addOption(
                'password',
                null,
                InputOption::VALUE_NONE,
                'Use this if password for user should be changed.'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Get doctrine.
        $doctrine = $this->getContainer()->get('doctrine');

        // Load User.
        $user_repository = $doctrine->getManager()->getRepository('reloaddkHarvesterBundle:User');

        // Check if user id or email is argument.
        if (preg_match('/\d+/i', $input->getArgument('user'))) {
            $user = $user_repository->findOneById($input->getArgument('user'));
        }
        else {
            $user = $user_repository->findOneByEmail($input->getArgument('user'));
        }

        // Search for users by first_name, email or id.
        if ($input->getOption('search')) {
            $em = $doctrine->getManager();
            $searchUsers = $em->createQuery(
                "SELECT u
                FROM reloaddkHarvesterBundle:User u
                WHERE u.firstName LIKE :search
                OR u.email LIKE :search
                OR u.id LIKE :search"
            )->setParameter('search', '%' . $input->getOption('search') . '%')
                ->getResult();

            if ($searchUsers) {
                $table = $this->getHelper('table');

                $table->setHeaders(array(
                    'ID',
                    'Full name',
                    'Email'
                ));
                $tableRows = [];
                foreach ($searchUsers as $searchUser) {

                    $tableRows[] = [$searchUser->getId(), $searchUser->getFirstName(), $searchUser->getEmail()];
                }
                $table->setRows($tableRows);

                $table->render($output);
            }
            else {
                $output->writeln('<error>No users found</error>');
            }
        }

        // Show user data.
        if ($input->getOption('show')) {
            $table = $this->getHelper('table');
            $table->setRows(array(
                array('Harvest ID', $user->getId()),
                array('Full name', $user->getFirstName() . ' ' . $user->getLastName()),
                array('Email', $user->getEmail()),
                array('Working hours', $user->getWorkingHours() ? $user->getWorkingHours() : $this->getContainer()->getParameter('default_hours_per_day')),
                array('Admin', $user->getIsAdmin() ? 'Yes' : 'No'),
                array('Active', $user->getIsActive() ? 'Yes' : 'No'),
                array('Created at', $user->getCreatedAt()->format('Y-m-d h:i:s')),
                array('Updated at', $user->getUpdatedAt()->format('Y-m-d h:i:s')),
            ));

            $table->render($output);
        }

        // If user object is set, and we're altering admin or active.
        if ($user) {
            // Option: Admin.
            if ($input->getOption('admin')) {
                // Argument: No.
                if (strtolower($input->getOption('admin')) === 'no') {
                    // Remove the "ROLE_ADMIN" and the admin status.
                    if ($user->getIsAdmin()) {
                        $user->setIsAdmin(0);
                        $userRole = $user_repository->getUserRoleByName('ROLE_ADMIN');
                        $user->removeUserRole($userRole);
                        $output->writeln('<info>Removed "ROLE_ADMIN" from ' . $user->getFirstName() . ' ' . $user->getLastName() . '.</info>');
                    } else {
                        $output->writeln('<error>' . $user->getFirstName() . ' ' . $user->getLastName() . ' is not an admin.</error>');
                    }
                }
                // Argument: Yes.
                else if (strtolower($input->getOption('admin')) === 'yes') {
                    // Provide the "ROLE_ADMIN" and set the user as an admin.
                    if (!$user->getIsAdmin()) {
                        $userRole = $user_repository->getUserRoleByName('ROLE_ADMIN');
                        $user->addUserRole($userRole);
                        $user->setIsAdmin(1);
                        $output->writeln('<info>Added "ROLE_ADMIN" to ' . $user->getFirstName() . ' ' . $user->getLastName() . '.</info>');
                    } else {
                        $output->writeln('<error>' . $user->getFirstName() . ' ' . $user->getLastName() . ' is already an admin.</error>');
                    }
                }
            }

            // Option: Active.
            if ($input->getOption('active')) {
                if (strtolower($input->getOption('active')) === 'no') {
                    $user->setIsActive(null);
                } else if (strtolower($input->getOption('active')) === 'yes') {
                    $user->setIsActive(1);
                }
            }

            if ($input->getOption('password')) {
                $question = new Question('Password: ', '');
                $question->setHidden(true);
                $question->setHiddenFallback(false);

                $helper = $this->getHelper('question');
                $password = $helper->ask($input, $output, $question);

                $user->setPassword($password);
                $output->writeln('<info>Updated password for ' . $user->getFirstName() . ' ' . $user->getLastName() . '.</info>');
            }

            $em = $doctrine->getManager();
            $em->persist($user);
            $em->flush();
        }
    }
}
