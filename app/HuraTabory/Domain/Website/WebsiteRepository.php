<?php
declare(strict_types=1);

namespace HuraTabory\Domain\Website;

use PDO;

class WebsiteRepository
{
    /** @var PDO */
    private $pdo;

    /** @var WebsiteConfig|null */
    private $cache;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getWebsiteConfig(): WebsiteConfig
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $result = $this->pdo->query('SELECT * FROM system_website LIMIT 1');

        $row = $result->fetch();

        $config = new WebsiteConfig();
        $config->setName($row['name']);
        $config->setTitle($row['title']);
        $config->setHeading($row['heading']);
        $config->setSlogan($row['slogan']);
        $config->setDescription($row['description']);
        $config->setKeywords($row['keywords']);
        $config->setEmail($row['email']);
        $config->setPhone($row['phone']);
        $config->setBankAccount($row['bank_account']);
        $config->setFacebookLink($row['facebook_link']);
        $config->setInstagramLink($row['instagram_link']);
        $config->setPinterestLink($row['pinterest_link']);
        $config->setAddress($row['address']);
        $config->setTermsAndConditions($row['terms_and_conditions']);
        $config->setGdpr($row['gdpr']);
        $config->setRules($row['rules']);
        $config->setContactPerson($row['contact_person']);
        $config->setIco($row['ico']);
        $config->setBankName($row['bank_name']);
        $config->setOrgDescription($row['org_description']);
        $config->setGoogleConfig($this->getGoogleConfig());
        $config->setFacebookConfig($this->getFacebookConfig());
        $config->setMenuCollection($this->getMenuCollection());
        $config->setCustomJavascripts($this->getCustomJavascripts());

        $this->cache = $config;

        return $config;
    }

    private function getGoogleConfig(): GoogleConfig
    {
        $result = $this->pdo->query('SELECT * FROM google_config LIMIT 1');

        $row = $result->fetch();

        $config = new GoogleConfig();
        $config->setAppId($row['app_id']);

        return $config;
    }

    private function getFacebookConfig(): FacebookConfig
    {
        $result = $this->pdo->query('SELECT * FROM facebook_config LIMIT 1');

        $row = $result->fetch();

        $config = new FacebookConfig();
        $config->setAppId($row['app_id']);

        return $config;
    }

    private function getMenuCollection(): MenuCollection
    {
        $sql = <<<SQL
SELECT i.id AS id, i.url AS url, i.text AS text, m.key AS `key`, i.is_external AS is_external
FROM vcd_menu_item i
LEFT JOIN vcd_menu m ON m.id = i.menu
WHERE i.visible = 1
ORDER BY position ASC
SQL;
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $collection = new MenuCollection();
        foreach ($result as $row) {
            $collection->fetchMenu((string)$row['key'])->addItem(new MenuItem((int)$row['id'], (string)$row['url'], (string)$row['text'], (bool)$row['is_external']));
        }

        return $collection;
    }

    private function getCustomJavascripts(): array
    {
        $stmt = $this->pdo->query('SELECT id, code, visible FROM vcd_web_code WHERE visible > 0 ORDER BY position ASC');
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $javascripts = [];
        foreach ($result as $row) {
            $javascripts[] = new CustomJavascript((int)$row['id'], (string)$row['code'], (int)$row['visible']);
        }

        return $javascripts;
    }
}
