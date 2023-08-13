<?php

namespace Hafo\Ares\Ares;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Hafo\Ares\AresException;
use Hafo\Ares\Subject;
use Hafo\Logger\ExceptionLogger;
use Monolog\Logger;

class Ares implements \Hafo\Ares\Ares
{
    private $client;

    private $logger;

    public function __construct(Client $client, Logger $logger)
    {
        $this->client = $client;
        $this->logger = $logger->withName('ares');
    }

    /**
     * @param string $ico
     * @return Subject|NULL
     * @throws AresException
     */
    public function getSubjectByIco($ico)
    {
        try {
            $response = $this->client->get('https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=' . $ico);
        } catch (RequestException $e) {
            (new ExceptionLogger($this->logger))->log($e);

            throw new AresException(sprintf('ARES returned status code %s.', $e->getCode()), 0, $e);
        }

        if ($response->getStatusCode() !== 200) {
            $e = new AresException(sprintf('ARES returned status code %s.', $response->getStatusCode()), $response->getStatusCode());
            (new ExceptionLogger($this->logger))->log($e);

            throw $e;
        }

        try {
            $contents = $response->getBody()->getContents();
        } catch (\RuntimeException $e) {
            (new ExceptionLogger($this->logger))->log($e);

            throw new AresException('Error while reading response from ARES.', 0, $e);
        }

        $xml = simplexml_load_string($contents);
        $ns = $xml->getDocNamespaces();
        $data = $xml->children($ns['are']);
        $el = $data->children($ns['D'])->VBAS;
        if (strval($el->ICO) === $ico) {
            $data = [
                'ico' => (string)$el->ICO,
                'dic' => (string)$el->DIC,
                'name' => (string)$el->OF,
                'street' => (string)$el->AA->NU . ' ' . (($el->AA->CO == '') ? $el->AA->CD : $el->AA->CD . '/' . $el->AA->CO),
                'city' => (string)$el->AA->N,
                'zip' => (string)$el->AA->PSC,
            ];

            return Subject::createFromArray($data);
        }

        return null;
    }
}
