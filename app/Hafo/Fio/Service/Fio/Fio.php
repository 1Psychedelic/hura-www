<?php

namespace Hafo\Fio\Service\Fio;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Hafo\Fio\FioException;
use Hafo\Fio\Payment;
use Hafo\Fio\Repository\PaymentRepository;
use Hafo\Logger\ExceptionLogger;
use Monolog\Logger;
use Nette\Utils\Arrays;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class Fio implements \Hafo\Fio\Service\Fio {

    const API_URL = 'https://www.fio.cz/ib_api/rest';

    private $token;

    private $paymentRepository;

    /** @var Logger */
    private $logger;

    private $client;

    function __construct($token, PaymentRepository $paymentRepository, Logger $logger, Client $client) {
        $this->token = $token;
        $this->paymentRepository = $paymentRepository;
        $this->client = $client;
        $this->logger = $logger->withName('fio');
    }

    /**
     * @param \DateTimeInterface $since
     * @param \DateTimeInterface $till
     * @return Payment[]
     * @throws FioException
     */
    function getTransactionsByPeriod(\DateTimeInterface $since, \DateTimeInterface $till) {
        $url = sprintf('%s/periods/%s/%s/%s/transactions.json',
            self::API_URL,
            $this->token,
            $since->format('Y-m-d'),
            $till->format('Y-m-d')
        );
        $this->logger->info(sprintf('Volám Fio API na URL %s', $url));
        try {
            $response = $this->client->get($url);
        } catch (ClientException $e) {
            (new ExceptionLogger($this->logger))->log($e);
            throw new FioException(sprintf('Fio returned status code %s.', $e->getCode()), 0, $e);
        } catch (ConnectException $e) {
            (new ExceptionLogger($this->logger))->log($e);
            throw new FioException(sprintf('Fio returned status code %s.', $e->getCode()), 0, $e);
        }

        if($response->getStatusCode() !== 200) {
            $e = new FioException(sprintf('Fio returned status code %s.', $response->getStatusCode()), $response->getStatusCode());
            (new ExceptionLogger($this->logger))->log($e);
            throw $e;
        }

        try {
            $contents = $response->getBody()->getContents();
        } catch (\RuntimeException $e) {
            (new ExceptionLogger($this->logger))->log($e);
            throw new FioException('Error while reading response from Fio.', 0, $e);
        }

        try {
            $data = Json::decode($contents, Json::FORCE_ARRAY);
        } catch (JsonException $e) {
            (new ExceptionLogger($this->logger))->log($e);
            throw new FioException('Response is invalid.', 0, $e);
        }

        if(!isset($data['accountStatement'])) {
            $this->logger->critical('Odpověď od Fio API neobsahuje [accountStatement].');
            throw new FioException('Response is invalid.');
        }

        if(!isset($data['accountStatement']['transactionList'])) {
            $this->logger->critical('Odpověď od Fio API neobsahuje [accountStatement][transactionList].');
            throw new FioException('Response is invalid.');
        }

        if(!isset($data['accountStatement']['transactionList']['transaction'])) {
            $this->logger->critical('Odpověď od Fio API neobsahuje [accountStatement][transactionList][transaction].');
            throw new FioException('Response is invalid.');
        }

        $newPayments = [];
        foreach($data['accountStatement']['transactionList']['transaction'] as $transaction) {
            $normalizedTransaction = [];
            foreach($transaction as $column) {
                if($column !== NULL) {
                    $normalizedTransaction[$column['name']] = $column['value'];
                }
            }
            if(!isset($normalizedTransaction['ID pohybu'])) {
                $this->logger->info('Odpověď od Fio API obsahuje transakci bez ID pohybu.');
                continue;
            }
            if(!isset($normalizedTransaction['Objem'])) {
                $this->logger->info('Odpověď od Fio API obsahuje transakci bez částky.');
                continue;
            }

            $existingPayment = $this->paymentRepository->getBy(['fioId' => $normalizedTransaction['ID pohybu']]);
            if($existingPayment !== NULL) {
                $this->logger->info(sprintf('Přeskakuji Fio platbu %s, protože už je v databázi zaznamenaná.', $normalizedTransaction['ID pohybu']));
                continue;
            }
            $newPayment = new Payment(
                $normalizedTransaction['ID pohybu'],
                $normalizedTransaction['Objem'],
                Arrays::get($normalizedTransaction, 'VS', NULL),
                Arrays::get($normalizedTransaction, 'Komentář', NULL),
                Arrays::get($normalizedTransaction, 'Zpráva pro příjemce', NULL)
            );

            $this->logger->info(sprintf('Ukládám do databáze novou Fio platbu%s.', isset($normalizedTransaction['VS']) ? ' s variabilním symbolem ' . $normalizedTransaction['VS'] : ''));

            $this->paymentRepository->persist($newPayment);
            $newPayments[] = $newPayment;
        }

        return $newPayments;
    }

}
