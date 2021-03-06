<?php

namespace reloaddk\HarvesterBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Harvest_Client;
use HarvestReports;
use DateTime;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ClientRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ClientRepository extends EntityRepository
{

    /**
     * Register the client.
     *
     * @param Harvest_Client $harvest_client
     * @param ConsoleOutput $output
     * @return Client
     */
    public function registerClient(Harvest_Client $harvest_client, ConsoleOutput $output)
    {
        $client = $this->getEntityManager()->getRepository('reloaddkHarvesterBundle:Client')->findOneById($harvest_client->get('id'));

        if (!$client) {
            // If the client doesn't exist locally, create it.
            $client = new Client();
            $this->saveClient($client, $harvest_client);
            $output->writeln('<info>' . $harvest_client->get('name') . '<info> <comment>created.</comment>');
        }
        else {
            // If the client exists and it's been updated, update it.
            $client_last_update = new DateTime($harvest_client->get('updated-at'));

            if ($client->getUpdatedAt()->getTimestamp() < $client_last_update->getTimestamp()-3600) {
                $this->saveClient($client, $harvest_client);
                $output->writeln('<info>'.$harvest_client->name. ' have been updated.</info>');
            }
            else {
                $output->writeln('<comment>'.$harvest_client->name . ' is up to date.</comment>');
            }

        }
        return $client;
    }

    /**
     * Save the client to db.
     *
     * @param Client $client
     * @param Harvest_Client $harvest_client
     * @return Client
     */
    public function saveClient(Client $client, Harvest_Client $harvest_client)
    {
        $client->setId($harvest_client->get('id'));
        $client->setName($harvest_client->get('name'));
        $client->setActive($harvest_client->get('active') == 'false' ? 0 : 1);
        $client->setCurrency($harvest_client->get('currency'));
        $client->setCurrencySymbol($harvest_client->get('currency-symbol'));
        $client->setDetails($harvest_client->get('details'));
        $client->setLastInvoiceKind($harvest_client->get('last-invoice-kind'));
        $client->setUpdatedAt(new DateTime($harvest_client->get('updated-at')));
        $client->setCreatedAt(new DateTime($harvest_client->get('created-at')));
        $client->setDefaultInvoiceTimeframe($harvest_client->get('default-invoice-timeframe'));

        $em = $this->getEntityManager();
        $em->persist($client);
        $em->flush();

        return $client;
    }
}
