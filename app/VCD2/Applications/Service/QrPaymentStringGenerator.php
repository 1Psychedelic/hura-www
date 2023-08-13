<?php
declare(strict_types=1);

namespace VCD2\Applications\Service;


use PDO;

class QrPaymentStringGenerator
{
    private const TEMPLATE = 'SPD*1.0*ACC:%s*AM:%s*CC:%s*X-VS:%s';

    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function generatePaymentString(float $price, string $variableSymbol): string
    {
        $iban = (string)$this->pdo->query('SELECT iban FROM system_website LIMIT 1')->fetchColumn();

        return sprintf(self::TEMPLATE, $iban, $this->formatPrice($price), 'CZK', $variableSymbol);
    }

    private function formatPrice(float $price): string
    {
        return number_format($price, 2, '.', '');
    }
}
