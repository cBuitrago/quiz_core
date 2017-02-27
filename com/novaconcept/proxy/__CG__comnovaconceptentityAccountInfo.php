<?php

namespace DoctrineProxies\__CG__\com\novaconcept\entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class AccountInfo extends \com\novaconcept\entity\AccountInfo implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = array();



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return array('__isInitialized__', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'id', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'isActive', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'name', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'description', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'accountBilling', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'accountAppSettings', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'accountConfig', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'createdOn', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'accountContactCollection', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'departmentInfoCollection', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'userAuthorizationCollection', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'clientAuthorizationCollection', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'clientAccountCollection', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'userAccountCollection', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'clientInfoCollection', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'userInfoCollection');
        }

        return array('__isInitialized__', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'id', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'isActive', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'name', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'description', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'accountBilling', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'accountAppSettings', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'accountConfig', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'createdOn', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'accountContactCollection', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'departmentInfoCollection', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'userAuthorizationCollection', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'clientAuthorizationCollection', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'clientAccountCollection', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'userAccountCollection', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'clientInfoCollection', '' . "\0" . 'com\\novaconcept\\entity\\AccountInfo' . "\0" . 'userInfoCollection');
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (AccountInfo $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', array());
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', array());
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int)  parent::getId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', array());

        return parent::getId();
    }

    /**
     * {@inheritDoc}
     */
    public function setIsActive($isActive)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setIsActive', array($isActive));

        return parent::setIsActive($isActive);
    }

    /**
     * {@inheritDoc}
     */
    public function getIsActive()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getIsActive', array());

        return parent::getIsActive();
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setName', array($name));

        return parent::setName($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getName', array());

        return parent::getName();
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription($description)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDescription', array($description));

        return parent::setDescription($description);
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDescription', array());

        return parent::getDescription();
    }

    /**
     * {@inheritDoc}
     */
    public function setAccountBilling($accountBilling)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setAccountBilling', array($accountBilling));

        return parent::setAccountBilling($accountBilling);
    }

    /**
     * {@inheritDoc}
     */
    public function getAccountBilling()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAccountBilling', array());

        return parent::getAccountBilling();
    }

    /**
     * {@inheritDoc}
     */
    public function setAccountAppSettings($accountAppSettings)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setAccountAppSettings', array($accountAppSettings));

        return parent::setAccountAppSettings($accountAppSettings);
    }

    /**
     * {@inheritDoc}
     */
    public function getAccountAppSettings()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAccountAppSettings', array());

        return parent::getAccountAppSettings();
    }

    /**
     * {@inheritDoc}
     */
    public function setAccountConfig($accountConfig)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setAccountConfig', array($accountConfig));

        return parent::setAccountConfig($accountConfig);
    }

    /**
     * {@inheritDoc}
     */
    public function getAccountConfig()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAccountConfig', array());

        return parent::getAccountConfig();
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedOn($createdOn)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCreatedOn', array($createdOn));

        return parent::setCreatedOn($createdOn);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedOn()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCreatedOn', array());

        return parent::getCreatedOn();
    }

    /**
     * {@inheritDoc}
     */
    public function getAccountContactCollection()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAccountContactCollection', array());

        return parent::getAccountContactCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function getDepartmentInfoCollection()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDepartmentInfoCollection', array());

        return parent::getDepartmentInfoCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function getClientInfoCollection()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getClientInfoCollection', array());

        return parent::getClientInfoCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function getUserAccountCollection()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUserAccountCollection', array());

        return parent::getUserAccountCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function getClientAccountCollection()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getClientAccountCollection', array());

        return parent::getClientAccountCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInfoCollection()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUserInfoCollection', array());

        return parent::getUserInfoCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function getUserAuthorizationCollection()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUserAuthorizationCollection', array());

        return parent::getUserAuthorizationCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function getClientAuthorizationCollection()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getClientAuthorizationCollection', array());

        return parent::getClientAuthorizationCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function onPrePersist()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'onPrePersist', array());

        return parent::onPrePersist();
    }

    /**
     * {@inheritDoc}
     */
    public function mapPostData($requestData)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'mapPostData', array($requestData));

        return parent::mapPostData($requestData);
    }

    /**
     * {@inheritDoc}
     */
    public function mergePostData($requestData)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'mergePostData', array($requestData));

        return parent::mergePostData($requestData);
    }

    /**
     * {@inheritDoc}
     */
    public function getData($includes = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getData', array($includes));

        return parent::getData($includes);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, '__toString', array());

        return parent::__toString();
    }

}