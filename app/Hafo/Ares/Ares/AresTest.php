<?php

namespace Hafo\Ares\Ares;

use Hafo\Ares\AresException;
use Hafo\Ares\Subject;

class AresTest implements \Hafo\Ares\Ares
{

    /**
     * @param string $ico
     * @return Subject|NULL
     * @throws AresException
     */
    public function getSubjectByIco($ico) {
        $contents = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<are:Ares_odpovedi xmlns:are="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer_basic/v_1.0.3" xmlns:D="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_datatypes/v_1.0.3" xmlns:U="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/uvis_datatypes/v_1.0.3" odpoved_datum_cas="2018-10-14T17:17:13" odpoved_pocet="1" odpoved_typ="Basic" vystup_format="XML" xslt="klient" validation_XSLT="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_odpovedi.xsl" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer_basic/v_1.0.3 http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer_basic/v_1.0.3/ares_answer_basic_v_1.0.3.xsd" Id="ares">
<are:Odpoved>
<D:PID>0</D:PID>
<D:VH>
<D:K>1</D:K>
</D:VH>
<D:PZA>1</D:PZA>
<D:UVOD>
<D:ND>Výpis z dat Registru ARES - aktuální stav ke dni 2018-10-12</D:ND>
<D:ADB>2018-10-12</D:ADB>
<D:DVY>2018-10-14</D:DVY>
<D:CAS>17:17:13</D:CAS>
<D:Typ_odkazu>0</D:Typ_odkazu>
</D:UVOD>
<D:VBAS>
<D:ICO zdroj="RZP">05154839</D:ICO>
<D:OF zdroj="RZP">Lukáš Klika</D:OF>
<D:DV>2016-06-09</D:DV>
<D:PF zdroj="RZP">
<D:KPF>101</D:KPF>
<D:NPF>Fyzická osoba podnikající dle živnostenského zákona nezapsaná v obchodním rejstříku</D:NPF>
</D:PF>
<D:AD zdroj="ARES">
<D:UC>Na výsluní 2307</D:UC>
<D:PB>10000 Praha</D:PB>
</D:AD>
<D:AA zdroj="ARES">
<D:IDA>419058709</D:IDA>
<D:KS>203</D:KS>
<D:NS>Česká republika</D:NS>
<D:NOK>Hlavní město Praha</D:NOK>
<D:N>Praha</D:N>
<D:NCO>Strašnice</D:NCO>
<D:NMC>Praha 10</D:NMC>
<D:NU>Na výsluní</D:NU>
<D:CD>2307</D:CD>
<D:TCD>1</D:TCD>
<D:CO>20</D:CO>
<D:PSC>10000</D:PSC>
<D:AU>
<U:KOL>19</U:KOL>
<U:KK>19</U:KK>
<U:KOK>3100</U:KOK>
<U:KO>554782</U:KO>
<U:KPO>108</U:KPO>
<U:KSO>108</U:KSO>
<U:KCO>490181</U:KCO>
<U:KMC>500224</U:KMC>
<U:PSC>10000</U:PSC>
<U:KUL>460222</U:KUL>
<U:CD>2307</U:CD>
<U:TCD>1</U:TCD>
<U:CO>20</U:CO>
<U:KA>22626999</U:KA>
<U:KOB>22352121</U:KOB>
<U:PCD>2445044</U:PCD>
</D:AU>
</D:AA>
<D:PSU>NNAANNNNNNNNNNNNNNNNNNNNANNNNN</D:PSU>
<D:RRZ>
<D:ZU>
<D:KZU>310010</D:KZU>
<D:NZU>Úřad městské části Praha 10</D:NZU>
</D:ZU>
<D:FU>
<D:KFU>10</D:KFU>
<D:NFU>Praha 10</D:NFU>
</D:FU>
</D:RRZ>
<D:KPP zdroj="RES">Bez zaměstnanců</D:KPP>
<D:Nace>
<D:NACE zdroj="RES">620</D:NACE>
<D:NACE zdroj="RES">6820</D:NACE>
<D:NACE zdroj="RES">73110</D:NACE>
<D:NACE zdroj="RES">74200</D:NACE>
</D:Nace>
<D:PPI>
<D:PP zdroj="RZP">
<D:T>Výroba, obchod a služby neuvedené v přílohách 1 až 3 živnostenského zákona</D:T>
</D:PP>
</D:PPI>
<D:Obory_cinnosti>
<D:Obor_cinnosti>
<D:K>Z01056</D:K>
<D:T>Poskytování software, poradenství v oblasti informačních technologií, zpracování dat, hostingové a související činnosti a webové portály</D:T>
</D:Obor_cinnosti>
<D:Obor_cinnosti>
<D:K>Z01058</D:K>
<D:T>Realitní činnost, správa a údržba nemovitostí</D:T>
</D:Obor_cinnosti>
<D:Obor_cinnosti>
<D:K>Z01066</D:K>
<D:T>Reklamní činnost, marketing, mediální zastoupení</D:T>
</D:Obor_cinnosti>
<D:Obor_cinnosti>
<D:K>Z01068</D:K>
<D:T>Fotografické služby</D:T>
</D:Obor_cinnosti>
</D:Obory_cinnosti>
</D:VBAS>
</are:Odpoved>
</are:Ares_odpovedi>

XML;


        $xml = simplexml_load_string($contents);
        $ns = $xml->getDocNamespaces();
        $data = $xml->children($ns['are']);
        $el = $data->children($ns['D'])->VBAS;
        if (strval($el->ICO) === $ico) {
            $data = [
                'ico' => (string) $el->ICO,
                'dic' => (string) $el->DIC,
                'name' => (string) $el->OF,
                'street' => (string) $el->AA->NU . ' ' . (($el->AA->CO == '') ? $el->AA->CD : $el->AA->CD . '/' . $el->AA->CO),
                'city' => (string) $el->AA->N,
                'zip' => (string) $el->AA->PSC
            ];

            return Subject::createFromArray($data);
        }

        return NULL;
    }

}
