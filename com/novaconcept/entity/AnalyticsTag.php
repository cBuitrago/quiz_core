<?php

namespace com\novaconcept\entity;

use com\novaconcept\entity\Analytics;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use stdClass;

/**
 * AnalyticsTag
 * @ORM\Table(name="analytics_tag")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class AnalyticsTag extends AbstractEntity
{
    /**
     * @var integer
     * @ORM\Column(name="PK_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="tag", type="string", length=128, nullable=false, unique=true)
     */
    private $tag;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    private $createdOn;
    
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Analytics", mappedBy="analyticsTag", cascade={"all"})
     **/
    private $analyticsCollection;
    
    public function __construct() 
    {
        $this->analyticsCollection = new ArrayCollection();
    }

    public function getId() { return $this->id; }
    public function setTag($tag) { $this->tag = $tag; return $this; }
    public function getTag() { return $this->tag; }
    public function getCreatedOn() { return $this->createdOn; }
    
    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->createdOn = new DateTime();
        $this->modifiedOn = new DateTime();
    }
    
    public function mapPostData($requestData)
    {
        $this->tag = $requestData['nameTag'];
        
        return $this;
    }
    public function mergePostData($requestData)
    {
        return $this;
    }

    public function getData($includes = NULL)
    {
        if ( $includes === NULL )
            $includes = array();
        $data = new stdClass();
        
        $data->tag = $this->tag;
        $data->createdOn = $this->createdOn->getTimestamp();
        
        if ( array_search('analytics', $includes) !== FALSE )
        {
            $analytics = array();
            $this->analyticsCollection->first();
            while($this->analyticsCollection->current() != NULL)
            {
                array_push( $analytics, $this->analyticsCollection->current()->getData() );
                $this->analyticsCollection->next();
            }
            $data->analytics = $analytics;
        }
        
        return $data;
    }
    
    public function __toString()
    {
       return strval($this->id);
    }
}
