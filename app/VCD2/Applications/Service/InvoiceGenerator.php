<?php

namespace VCD2\Applications\Service;

use Hafo\DI\Container;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Nette\Application\UI\ITemplateFactory;
use Nette\Database\Context;
use VCD2\Applications\Application;
use VCD2\Applications\Invoice;

class InvoiceGenerator {

    /** @var ITemplateFactory */
    private $templateFactory;

    /** @var Context */
    private $database;

    /** @var Container */
    private $container;

    function __construct(ITemplateFactory $templateFactory, Context $database, Container $container) {
        $this->templateFactory = $templateFactory;
        $this->database = $database;
        $this->container = $container;
    }

    function generate(Invoice $invoice) {
        if ($invoice->customFile !== null) {
            $dir = $this->container->get('invoices');
            $filename = $dir . '/' . $invoice->customFile;
            if (file_exists($filename)) {
                return file_get_contents($filename);
            }
        }

        $website = $this->database->table('system_website')->fetch();

        $template = $this->templateFactory->createTemplate();
        $template->setFile(__DIR__ . '/invoice.latte');
        $template->invoice = $invoice;
        $template->bankAccount = $website['bank_account'];

        //$template->render();die;

        ob_start();
        $template->render();
        $html = ob_get_clean();

        $phone = str_replace('-', ' ', str_replace('+420', '', $website['phone']));

        $footer = <<<FOOTER
<table border="0" cellpadding="10" cellspacing="0" width="100%" style="text-align: center">
    <tr>
        <td colspan="3" style="color:#151515">
            Spolek je zapsaný ve Spolkovém rejstříku u Krajského soudu v Brně.
        </td>
    </tr>
    <tr style="background-color: #393d72;">
        <td style="width:33%;color:#ffffff;"><img src="images/icons/invoice/phone.png" style="height: 30px;margin-bottom: -10px"> {$phone}</td>
        <td style="width:34%;color:#ffffff"><img src="images/icons/invoice/email.png" style="height: 30px;margin-bottom: -10px"> {$website['email']}</td>
        <td style="width:33%;color:#ffffff"><img src="images/icons/invoice/web.png" style="height: 30px;margin-bottom: -10px"> www.hura-tabory.cz</td>
    </tr>
</table>
FOOTER;

        $defaultConfig = (new ConfigVariables)->getDefaults();
        $defaultFontData = (new FontVariables)->getDefaults()['fontdata'];

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
            'fontDir' => array_merge($defaultConfig['fontDir'], [$this->container->get('www') . '/fonts']),
            'fontdata' => $defaultFontData + [
                'ballotamma2' => [
                    'R' => 'BalooTamma2-Regular.ttf',
                    'B' => 'BalooTamma2-Bold.ttf',
                ],
            ],
            'default_font' => 'ballotamma2',
        ]);
        $mpdf->defaultheaderline = 0;
        $mpdf->defaultfooterline = 0;
        $mpdf->setFooter($footer);
        $mpdf->WriteHTML($html);
        return $mpdf->Output(NULL, Destination::STRING_RETURN);
    }

}
