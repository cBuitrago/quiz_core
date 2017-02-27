<?php

namespace DoctrineProxies\__CG__\com\novaconcept\entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class Quiz extends \com\novaconcept\entity\Quiz implements \Doctrine\ORM\Proxy\Proxy
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
            return array('__isInitialized__', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'id', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'quizId', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'lockedOnCompletion', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'timeToComplete', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'quizData', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'isUserCanDisplayChart', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'isUserCanDisplayQa', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'isEnabled', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'isUserSeeGoodAnswer', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'answerJson', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'quizAuthorizationCollection', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'quizResultsCollection');
        }

        return array('__isInitialized__', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'id', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'quizId', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'lockedOnCompletion', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'timeToComplete', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'quizData', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'isUserCanDisplayChart', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'isUserCanDisplayQa', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'isEnabled', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'isUserSeeGoodAnswer', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'answerJson', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'quizAuthorizationCollection', '' . "\0" . 'com\\novaconcept\\entity\\Quiz' . "\0" . 'quizResultsCollection');
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (Quiz $proxy) {
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
    public function setQuizID($quizId)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setQuizID', array($quizId));

        return parent::setQuizID($quizId);
    }

    /**
     * {@inheritDoc}
     */
    public function getQuizID()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getQuizID', array());

        return parent::getQuizID();
    }

    /**
     * {@inheritDoc}
     */
    public function setLockedOnCompletion($lockedOnCompletion)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLockedOnCompletion', array($lockedOnCompletion));

        return parent::setLockedOnCompletion($lockedOnCompletion);
    }

    /**
     * {@inheritDoc}
     */
    public function getLockedOnCompletion()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLockedOnCompletion', array());

        return parent::getLockedOnCompletion();
    }

    /**
     * {@inheritDoc}
     */
    public function setTimeToComplete($timeToComplete)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setTimeToComplete', array($timeToComplete));

        return parent::setTimeToComplete($timeToComplete);
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeToComplete()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTimeToComplete', array());

        return parent::getTimeToComplete();
    }

    /**
     * {@inheritDoc}
     */
    public function setQuizData($quizData)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setQuizData', array($quizData));

        return parent::setQuizData($quizData);
    }

    /**
     * {@inheritDoc}
     */
    public function getQuizData()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getQuizData', array());

        return parent::getQuizData();
    }

    /**
     * {@inheritDoc}
     */
    public function setIsUserCanDisplayChart($isUserCanDisplayChart)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setIsUserCanDisplayChart', array($isUserCanDisplayChart));

        return parent::setIsUserCanDisplayChart($isUserCanDisplayChart);
    }

    /**
     * {@inheritDoc}
     */
    public function getIsUserCanDisplayChart()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getIsUserCanDisplayChart', array());

        return parent::getIsUserCanDisplayChart();
    }

    /**
     * {@inheritDoc}
     */
    public function setIsUserCanDisplayQa($isUserCanDisplayQa)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setIsUserCanDisplayQa', array($isUserCanDisplayQa));

        return parent::setIsUserCanDisplayQa($isUserCanDisplayQa);
    }

    /**
     * {@inheritDoc}
     */
    public function getIsUserCanDisplayQa()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getIsUserCanDisplayQa', array());

        return parent::getIsUserCanDisplayQa();
    }

    /**
     * {@inheritDoc}
     */
    public function setIsEnabled($isEnabled)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setIsEnabled', array($isEnabled));

        return parent::setIsEnabled($isEnabled);
    }

    /**
     * {@inheritDoc}
     */
    public function getIsEnabled()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getIsEnabled', array());

        return parent::getIsEnabled();
    }

    /**
     * {@inheritDoc}
     */
    public function setIsUserSeeGoodAnswer($isUserSeeGoodAnswer)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setIsUserSeeGoodAnswer', array($isUserSeeGoodAnswer));

        return parent::setIsUserSeeGoodAnswer($isUserSeeGoodAnswer);
    }

    /**
     * {@inheritDoc}
     */
    public function getIsUserSeeGoodAnswer()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getIsUserSeeGoodAnswer', array());

        return parent::getIsUserSeeGoodAnswer();
    }

    /**
     * {@inheritDoc}
     */
    public function setAnswerJson($answerJson)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setAnswerJson', array($answerJson));

        return parent::setAnswerJson($answerJson);
    }

    /**
     * {@inheritDoc}
     */
    public function getAnswerJson()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAnswerJson', array());

        return parent::getAnswerJson();
    }

    /**
     * {@inheritDoc}
     */
    public function getQuizAuthorizationCollection()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getQuizAuthorizationCollection', array());

        return parent::getQuizAuthorizationCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function getQuizResultsCollection()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getQuizResultsCollection', array());

        return parent::getQuizResultsCollection();
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
    public function onPreUpdate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'onPreUpdate', array());

        return parent::onPreUpdate();
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
    public function getDataArray($includes = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDataArray', array($includes));

        return parent::getDataArray($includes);
    }

}