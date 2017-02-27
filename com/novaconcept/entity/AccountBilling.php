<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\AccountInfo;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * AccountBilling
 * @ORM\Table(name="account_billing", indexes={@ORM\Index(name="FPK_account_info", columns={"FPK_account_info"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class AccountBilling extends AbstractEntity 
{
    /**
     * @var AccountInfo
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AccountInfo", inversedBy="accountBilling")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="FPK_account_info", referencedColumnName="PK_id")
     * })
     */
    private $accountInfo;
    
    /**
     * @var string
     * @ORM\Column(name="company", type="string", length=265, nullable=false)
     */
    private $company;
    
    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=265, nullable=false)
     */
    private $firstName;
    
    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=265, nullable=false)
     */
    private $lastName;
    
    /**
     * @var string
     * @ORM\Column(name="address_1", type="string", length=265, nullable=false)
     */
    private $address1;
    
    /**
     * @var string
     * @ORM\Column(name="address_2", type="string", length=265, nullable=false)
     */
    private $address2;
    
    /**
     * @var string
     * @ORM\Column(name="postal_code", type="string", length=265, nullable=false)
     */
    private $postalCode;
    
    /**
     * @var string
     * @ORM\Column(name="city", type="string", length=265, nullable=false)
     */
    private $city;
    
    /**
     * @var string
     * @ORM\Column(name="province", type="string", length=265, nullable=false)
     */
    private $province;
    
    /**
     * @var string
     * @ORM\Column(name="country", type="string", length=265, nullable=false)
     */
    private $country;
    
    /**
     * @var string
     * @ORM\Column(name="phone", type="string", length=265, nullable=false)
     */
    private $phone;
    
    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=265, nullable=false)
     */
    private $email;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="modified_on", type="datetime", nullable=false)
     */
    private $modifiedOn;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="expires_on", type="datetime", nullable=false)
     */
    private $expiresOn;
    
    public function setAccountInfo($accountInfo) { $this->accountInfo = $accountInfo; return $this; }
    public function getAccountInfo() { return $this->accountInfo; }
    public function setCompany($company) { $this->company = $company; return $this; }
    public function getCompany() { return $this->company; }
    public function setFirstName($firstName) { $this->firstName = $firstName; return $this; }
    public function getFirstName() { return $this->firstName; }
    public function setLastName($lastName) { $this->lastName = $lastName; return $this; }
    public function getLastName() { return $this->lastName; }
    public function setAddress1($adrress1) { $this->address1 = $address1; return $this; }
    public function getAddress1() { return $this->address1; }
    public function setAddress2($adrress2) { $this->address2 = $address2; return $this; }
    public function getAddress2() { return $this->address2; }
    public function setPostalCode($postalCode) { $this->postalCode = $postalCode; return $this; }
    public function getPostalCode() { return $this->postalCode; }
    public function setCity($city) { $this->city = city; return $this; }
    public function getcity() { return $this->city; }
    public function setProvince($province) { $this->province = province; return $this; }
    public function getProvince() { return $this->province; }
    public function setCountry($country) { $this->country = $country; return $this; }
    public function getCountry() { return $this->country; }
    public function setPhone($phone) { $this->phone = $phone; return $this; }
    public function getPhone() { return $this->phone; }
    public function setEmail($email) { $this->email = $email; return $this; }
    public function getEmail() { return $this->email; }
    public function setExpiresOn($expiresOn) { $this->expiresOn = $expiresOn; return $this; }
    public function getExpiresOn() { return $this->expiresOn; }
    
    /** @ORM\PreUpdate */
    public function onPreUpdate()
    {
        $this->modifiedOn = new DateTime();
    }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->modifiedOn = new DateTime();
        $this->createdOn = new DateTime();
    }
    
    public function mapPostData($requestData)
    {
        $this->company = $requestData->company;
        $this->firstName = $requestData->firstName;
        $this->lastName = $requestData->lastName;
        $this->address1 = $requestData->address1;
        $this->address2 = $requestData->address2;
        $this->postalCode = $requestData->postalCode;
        $this->city = $requestData->city;
        $this->province = $requestData->province;
        $this->country = $requestData->country;
        $this->phone = $requestData->phone;
        $this->email = $requestData->email;
        $this->expiresOn = new DateTime();
        $this->expiresOn->setTimestamp($requestData->expiresOn);
        
        return $this;
    }
    
    public function mergePostData($requestData) 
    {
        if (isset($requestData->company))
            $this->company = $requestData->company;
        if (isset($requestData->firstName))
            $this->firstName = $requestData->firstName;
        if (isset($requestData->lastName))
            $this->lastName = $requestData->lastName;
        if (isset($requestData->address1))
            $this->address1 = $requestData->address1;
        if (isset($requestData->address2))
            $this->address2 = $requestData->address2;
        if (isset($requestData->postalCode))
            $this->postalCode = $requestData->postalCode;
        if (isset($requestData->city))
            $this->city = $requestData->city;
        if (isset($requestData->province))
            $this->province = $requestData->province;
        if (isset($requestData->country))
            $this->country = $requestData->country;
        if (isset($requestData->phone))
            $this->phone = $requestData->phone;
        if (isset($requestData->email))
            $this->email = $requestData->email;
        if (isset($requestData->expiresOn))
        {
            $this->expiresOn = new DateTime();
            $this->expiresOn->setTimestamp($requestData->expiresOn);
        }
        return $this;
    }
    
    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
            $includes = array();
        $data = new stdClass();
        
        $data->id = $this->accountInfo->getId();
        $data->company = $this->company;
        $data->firstName = $this->firstName;
        $data->lastName = $this->lastName;
        $data->address1 = $this->address1;
        $data->address2 = $this->address2;
        $data->postalCode = $this->postalCode;
        $data->city = $this->city;
        $data->province = $this->province;
        $data->country = $this->country;
        $data->phone = $this->phone;
        $data->email = $this->email;
        $data->modifiedOn = $this->modifiedOn->getTimestamp();
        $data->createdOn = $this->createdOn->getTimestamp();
        $data->expiresOn = $this->expiresOn->getTimestamp();
        
        if (array_search('account_info', $includes) !== FALSE)
            $data->accountInfo = $this->accountInfo->getData();
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->accountInfo->getId());
    }
}
