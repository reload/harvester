<?php

namespace Harvester\FetchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * Client
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Harvester\FetchBundle\Entity\ClientRepository")
 * @ExclusionPolicy("all")
 */
class Client
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @Expose
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Expose
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=255)
     */
    private $currency;

    /**
     * @var string
     *
     * @ORM\Column(name="currency_symbol", type="string", length=255)
     */
    private $currencySymbol;

    /**
     * @var string
     *
     * @ORM\Column(name="details", type="text")
     */
    private $details;

    /**
     * @var string
     *
     * @ORM\Column(name="last_invoice_kind", type="string", length=255)
     */
    private $lastInvoiceKind;

    /**
     * @var string
     *
     * @ORM\Column(name="default_invoice_timeframe", type="string", length=255)
     */
    private $defaultInvoiceTimeframe;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="Harvester\FetchBundle\Entity\Project", mappedBy="client")
     */
    protected $projects;

    /**
     * Set id
     *
     * @param string $id
     * @return Client
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Client
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Client
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Client
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string 
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set currencySymbol
     *
     * @param string $currencySymbol
     * @return Client
     */
    public function setCurrencySymbol($currencySymbol)
    {
        $this->currencySymbol = $currencySymbol;

        return $this;
    }

    /**
     * Get currencySymbol
     *
     * @return string 
     */
    public function getCurrencySymbol()
    {
        return $this->currencySymbol;
    }

    /**
     * Set details
     *
     * @param string $details
     * @return Client
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get details
     *
     * @return string 
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set lastInvoiceKind
     *
     * @param string $lastInvoiceKind
     * @return Client
     */
    public function setLastInvoiceKind($lastInvoiceKind)
    {
        $this->lastInvoiceKind = $lastInvoiceKind;

        return $this;
    }

    /**
     * Get lastInvoiceKind
     *
     * @return string 
     */
    public function getLastInvoiceKind()
    {
        return $this->lastInvoiceKind;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Client
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Client
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set defaultInvoiceTimeframe
     *
     * @param string $defaultInvoiceTimeframe
     * @return Client
     */
    public function setDefaultInvoiceTimeframe($defaultInvoiceTimeframe)
    {
        $this->defaultInvoiceTimeframe = $defaultInvoiceTimeframe;

        return $this;
    }

    /**
     * Get defaultInvoiceTimeframe
     *
     * @return string 
     */
    public function getDefaultInvoiceTimeframe()
    {
        return $this->defaultInvoiceTimeframe;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->projects = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add projects
     *
     * @param \Harvester\FetchBundle\Entity\Project $projects
     * @return Client
     */
    public function addProject(\Harvester\FetchBundle\Entity\Project $projects)
    {
        $this->projects[] = $projects;

        return $this;
    }

    /**
     * Remove projects
     *
     * @param \Harvester\FetchBundle\Entity\Project $projects
     */
    public function removeProject(\Harvester\FetchBundle\Entity\Project $projects)
    {
        $this->projects->removeElement($projects);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProjects()
    {
        return $this->projects;
    }
}
