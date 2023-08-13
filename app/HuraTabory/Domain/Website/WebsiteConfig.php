<?php
declare(strict_types=1);

namespace HuraTabory\Domain\Website;

use Hafo\Http\CacheHeaders;

class WebsiteConfig
{
    /** @var string */
    private $name;

    /** @var string */
    private $title;

    /** @var string */
    private $heading;

    /** @var string */
    private $slogan;

    /** @var string */
    private $description;

    /** @var string */
    private $keywords;

    /** @var string */
    private $email;

    /** @var string */
    private $phone;

    /** @var string */
    private $address;

    /** @var string */
    private $bankAccount;

    /** @var string|null */
    private $facebookLink;

    /** @var string|null */
    private $instagramLink;

    /** @var string|null */
    private $pinterestLink;

    /** @var string */
    private $termsAndConditions;

    /** @var string */
    private $gdpr;

    /** @var string */
    private $rules;

    /** @var string */
    private $contactPerson;

    /** @var string */
    private $ico;

    /** @var string */
    private $bankName;

    /** @var string */
    private $orgDescription;

    /** @var GoogleConfig */
    private $googleConfig;

    /** @var FacebookConfig */
    private $facebookConfig;

    /** @var MenuCollection */
    private $menuCollection;

    /** @var CustomJavascript[] */
    private $customJavascripts = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getHeading(): string
    {
        return $this->heading;
    }

    public function setHeading(string $heading): void
    {
        $this->heading = $heading;
    }

    public function getSlogan(): string
    {
        return $this->slogan;
    }

    public function setSlogan(string $slogan): void
    {
        $this->slogan = $slogan;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }

    public function setKeywords(string $keywords): void
    {
        $this->keywords = $keywords;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getBankAccount(): string
    {
        return $this->bankAccount;
    }

    public function setBankAccount(string $bankAccount): void
    {
        $this->bankAccount = $bankAccount;
    }

    public function getFacebookLink(): ?string
    {
        return $this->facebookLink;
    }

    public function setFacebookLink(?string $facebookLink): void
    {
        $this->facebookLink = $facebookLink;
    }

    public function getInstagramLink(): ?string
    {
        return $this->instagramLink;
    }

    public function setInstagramLink(?string $instagramLink): void
    {
        $this->instagramLink = $instagramLink;
    }

    public function getPinterestLink(): ?string
    {
        return $this->pinterestLink;
    }

    public function setPinterestLink(?string $pinterestLink): void
    {
        $this->pinterestLink = $pinterestLink;
    }

    public function getTermsAndConditions(): string
    {
        return $this->termsAndConditions;
    }

    public function setTermsAndConditions(string $termsAndConditions): void
    {
        $this->termsAndConditions = $termsAndConditions;
    }

    public function getGdpr(): string
    {
        return $this->gdpr;
    }

    public function setGdpr(string $gdpr): void
    {
        $this->gdpr = $gdpr;
    }

    public function getRules(): string
    {
        return $this->rules;
    }

    public function setRules(string $rules): void
    {
        $this->rules = $rules;
    }

    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(string $contactPerson): void
    {
        $this->contactPerson = $contactPerson;
    }

    public function getIco(): string
    {
        return $this->ico;
    }

    public function setIco(string $ico): void
    {
        $this->ico = $ico;
    }

    public function getBankName(): string
    {
        return $this->bankName;
    }

    public function setBankName(string $bankName): void
    {
        $this->bankName = $bankName;
    }

    public function getOrgDescription(): string
    {
        return $this->orgDescription;
    }

    public function setOrgDescription(string $orgDescription): void
    {
        $this->orgDescription = $orgDescription;
    }

    public function getGoogleConfig(): GoogleConfig
    {
        return $this->googleConfig;
    }

    public function setGoogleConfig(GoogleConfig $googleConfig): void
    {
        $this->googleConfig = $googleConfig;
    }

    public function getFacebookConfig(): FacebookConfig
    {
        return $this->facebookConfig;
    }

    public function setFacebookConfig(FacebookConfig $facebookConfig): void
    {
        $this->facebookConfig = $facebookConfig;
    }

    public function getMenuCollection(): MenuCollection
    {
        return $this->menuCollection;
    }

    public function setMenuCollection(MenuCollection $menuCollection): void
    {
        $this->menuCollection = $menuCollection;
    }

    public function getCustomJavascripts(): array
    {
        return $this->customJavascripts;
    }

    public function setCustomJavascripts(array $customJavascripts): void
    {
        $this->customJavascripts = $customJavascripts;
    }

    public function getCacheHeaders(): CacheHeaders
    {
        $etag = md5(
            $this->name
            . $this->title
            . $this->slogan
            . $this->description
            . $this->keywords
            . $this->email
            . $this->phone
            . $this->bankAccount
            . $this->facebookLink
            . $this->instagramLink
            . $this->pinterestLink
            . $this->address
            . $this->termsAndConditions
            . $this->gdpr
            . $this->rules
            . $this->contactPerson
            . $this->ico
            . $this->bankName
            . $this->orgDescription
            . $this->googleConfig->getAppId()
            . $this->facebookConfig->getAppId()
        );

        return new CacheHeaders($etag);
    }
}
