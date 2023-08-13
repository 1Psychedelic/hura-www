<?php

namespace VCD2\Applications\Service;

use Monolog\Logger;
use Nextras\Dbal\Connection;
use VCD2\Applications\Application;
use VCD2\Applications\Invoice;
use VCD2\Applications\InvoiceItem;
use VCD2\Orm;

class Invoices {

    /** @var Orm */
    private $orm;

    /** @var Connection */
    private $connection;

    /** @var Logger */
    private $logger;
    
    function __construct(Orm $orm, Connection $connection, Logger $logger) {
        $this->orm = $orm;
        $this->connection = $connection;
        $this->logger = $logger->withName('vcd.invoices');
    }

    function createInvoiceToBePaid(Application $application) {
        if($application->invoice !== NULL || !$application->isPayingOnInvoice) {
            return;
        }

        $this->connection->transactional(function() use ($application) {
            $lastIdThisYear = $this->getLastIdThisYear();

            $invoice = Invoice::createFromApplication(++$lastIdThisYear, $application);
            $this->orm->persist($invoice);
            foreach($application->createInvoiceItems() as $itemDTO) {
                $item = new InvoiceItem($invoice, $itemDTO->name, $itemDTO->basePrice, $itemDTO->amount, $itemDTO->totalPrice);
                $this->orm->persist($item);
            }

            $application->hasInvoice = true;

            $this->logger->info(sprintf('Generuji neuhrazenou fakturu %s pro přihlášku %s.', $invoice, $application));

            $this->orm->flush();
        });
    }
    
    function createInvoices() {
        return $this->connection->transactional(function() {

            $lastIdThisYear = $this->getLastIdThisYear();

            /** @var Application[] $applications */
            $applications = $this->orm->applications->findBy([
                'isApplied' => true,
                'isAccepted' => true,
                'isCanceled' => false,
                'isRejected' => false,
                'hasInvoice' => false,
            ])->fetchAll();

            usort($applications, function(Application $application, Application $other) {
                return $application->paidAt <=> $other->paidAt;
            });

            $cnt = 0;
            foreach($applications as $application) {
                if($application->invoice === NULL && $application->isEligibleForInvoice) {
                    $invoice = Invoice::createFromApplication(++$lastIdThisYear, $application);
                    $this->orm->persist($invoice);
                    foreach($application->createInvoiceItems() as $itemDTO) {
                        $item = new InvoiceItem($invoice, $itemDTO->name, $itemDTO->basePrice, $itemDTO->amount, $itemDTO->totalPrice);
                        $this->orm->persist($item);
                    }

                    $application->hasInvoice = true;
                    $this->orm->persist($application);

                    $cnt++;
                    $this->logger->info(sprintf('Generuji uhrazenou fakturu %s pro přihlášku %s.', $invoice, $application));
                }
            }

            $this->orm->flush();

            return $cnt;

        });
    }

    function createInvoice(Application $application)
    {
        if ($application->invoice !== null || !$application->isEligibleForInvoice) {
            return;
        }

        return $this->connection->transactional(function() use ($application) {

            $lastIdThisYear = $this->getLastIdThisYear();

            $invoice = Invoice::createFromApplication(++$lastIdThisYear, $application);
            $this->orm->persist($invoice);
            foreach($application->createInvoiceItems() as $itemDTO) {
                $item = new InvoiceItem($invoice, $itemDTO->name, $itemDTO->basePrice, $itemDTO->amount, $itemDTO->totalPrice);
                $this->orm->persist($item);
            }

            $application->hasInvoice = true;
            $this->orm->persist($application);

            $this->logger->info(sprintf('Generuji uhrazenou fakturu %s pro přihlášku %s.', $invoice, $application));

            $this->orm->flush();
        });
    }

    private function getLastIdThisYear() {
        return $this->orm->invoices->getCountForYear((new \DateTimeImmutable)->format('Y'));
    }

}
