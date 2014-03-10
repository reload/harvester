<?php

namespace Harvester\FetchBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use DateTime;
use Harvester\FetchBundle\Entity\User;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class HarvesterFetchUsersCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('harvester:fetchusers');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = $this->getContainer()->get('harvest_app_reports')->getApi();
        $doctrine = $this->getContainer()->get('doctrine');

        $style = new OutputFormatterStyle('red', 'black');
        $output->getFormatter()->setStyle('fire', $style);

        $harvest_users = $api->getActiveUsers();

        foreach ($harvest_users->data as $user)
        {
            $doctrine->getManager()->getRepository('HarvesterFetchBundle:User')
                ->registerUser($user, $output);
        }
    }
}