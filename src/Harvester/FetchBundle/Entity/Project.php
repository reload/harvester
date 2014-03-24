<?php

namespace Harvester\FetchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Project
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Harvester\FetchBundle\Entity\ProjectRepository")
 */
class Project
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="client_id", type="integer")
     */
    private $clientId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text")
     */
    private $notes;

    /**
     * @var boolean
     *
     * @ORM\Column(name="billable", type="boolean")
     */
    private $billable;

    /**
     * @var string
     *
     * @ORM\Column(name="bill_by", type="string", length=255)
     */
    private $billBy;

    /**
     * @var float
     *
     * @ORM\Column(name="cost_budget", type="float")
     */
    private $costBudget;

    /**
     * @var boolean
     *
     * @ORM\Column(name="cost_budget_include_expenses", type="boolean")
     */
    private $costBudgetIncludeExpenses;

    /**
     * @var float
     *
     * @ORM\Column(name="hourly_rate", type="float")
     */
    private $hourlyRate;

    /**
     * @var float
     *
     * @ORM\Column(name="budget", type="float")
     */
    private $budget;

    /**
     * @var string
     *
     * @ORM\Column(name="budget_by", type="string", length=255)
     */
    private $budgetBy;

    /**
     * @var boolean
     *
     * @ORM\Column(name="notify_when_over_budget", type="boolean")
     */
    private $notifyWhenOverBudget;

    /**
     * @var float
     *
     * @ORM\Column(name="over_budget_notification_percentage", type="float")
     */
    private $overBudgetNotificationPercentage;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="over_budget_notified_at", type="date")
     */
    private $overBudgetNotifiedAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="show_budget_to_all", type="boolean")
     */
    private $showBudgetToAll;

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
     * @var float
     *
     * @ORM\Column(name="estimate", type="float")
     */
    private $estimate;

    /**
     * @var string
     *
     * @ORM\Column(name="estimate_by", type="string", length=255)
     */
    private $estimateBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="hint_earliest_record_at", type="date")
     */
    private $hintEarliestRecordAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="hint_latest_record_at", type="date")
     */
    private $hintLatestRecordAt;

    /**
     * @ORM\OneToMany(targetEntity="Harvester\FetchBundle\Entity\Entry", mappedBy="project")
     */
    protected $entries;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }


    /**
     * Set id
     *
     * @param integer $id
     * @return Project
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
     * Set clientId
     *
     * @param integer $clientId
     * @return Project
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get clientId
     *
     * @return integer 
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Project
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
     * Set code
     *
     * @param string $code
     * @return Project
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Project
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
     * Set notes
     *
     * @param string $notes
     * @return Project
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string 
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set billable
     *
     * @param boolean $billable
     * @return Project
     */
    public function setBillable($billable)
    {
        $this->billable = $billable;

        return $this;
    }

    /**
     * Get billable
     *
     * @return boolean 
     */
    public function getBillable()
    {
        return $this->billable;
    }

    /**
     * Set billBy
     *
     * @param string $billBy
     * @return Project
     */
    public function setBillBy($billBy)
    {
        $this->billBy = $billBy;

        return $this;
    }

    /**
     * Get billBy
     *
     * @return string 
     */
    public function getBillBy()
    {
        return $this->billBy;
    }

    /**
     * Set costBudget
     *
     * @param float $costBudget
     * @return Project
     */
    public function setCostBudget($costBudget)
    {
        $this->costBudget = $costBudget;

        return $this;
    }

    /**
     * Get costBudget
     *
     * @return float 
     */
    public function getCostBudget()
    {
        return $this->costBudget;
    }

    /**
     * Set costBudgetIncludeExpenses
     *
     * @param boolean $costBudgetIncludeExpenses
     * @return Project
     */
    public function setCostBudgetIncludeExpenses($costBudgetIncludeExpenses)
    {
        $this->costBudgetIncludeExpenses = $costBudgetIncludeExpenses;

        return $this;
    }

    /**
     * Get costBudgetIncludeExpenses
     *
     * @return boolean 
     */
    public function getCostBudgetIncludeExpenses()
    {
        return $this->costBudgetIncludeExpenses;
    }

    /**
     * Set hourlyRate
     *
     * @param float $hourlyRate
     * @return Project
     */
    public function setHourlyRate($hourlyRate)
    {
        $this->hourlyRate = $hourlyRate;

        return $this;
    }

    /**
     * Get hourlyRate
     *
     * @return float 
     */
    public function getHourlyRate()
    {
        return $this->hourlyRate;
    }

    /**
     * Set budget
     *
     * @param float $budget
     * @return Project
     */
    public function setBudget($budget)
    {
        $this->budget = $budget;

        return $this;
    }

    /**
     * Get budget
     *
     * @return float 
     */
    public function getBudget()
    {
        return $this->budget;
    }

    /**
     * Set budgetBy
     *
     * @param string $budgetBy
     * @return Project
     */
    public function setBudgetBy($budgetBy)
    {
        $this->budgetBy = $budgetBy;

        return $this;
    }

    /**
     * Get budgetBy
     *
     * @return string 
     */
    public function getBudgetBy()
    {
        return $this->budgetBy;
    }

    /**
     * Set notifyWhenOverBudget
     *
     * @param boolean $notifyWhenOverBudget
     * @return Project
     */
    public function setNotifyWhenOverBudget($notifyWhenOverBudget)
    {
        $this->notifyWhenOverBudget = $notifyWhenOverBudget;

        return $this;
    }

    /**
     * Get notifyWhenOverBudget
     *
     * @return boolean 
     */
    public function getNotifyWhenOverBudget()
    {
        return $this->notifyWhenOverBudget;
    }

    /**
     * Set overBudgetNotificationPercentage
     *
     * @param float $overBudgetNotificationPercentage
     * @return Project
     */
    public function setOverBudgetNotificationPercentage($overBudgetNotificationPercentage)
    {
        $this->overBudgetNotificationPercentage = $overBudgetNotificationPercentage;

        return $this;
    }

    /**
     * Get overBudgetNotificationPercentage
     *
     * @return float 
     */
    public function getOverBudgetNotificationPercentage()
    {
        return $this->overBudgetNotificationPercentage;
    }

    /**
     * Set overBudgetNotifiedAt
     *
     * @param \DateTime $overBudgetNotifiedAt
     * @return Project
     */
    public function setOverBudgetNotifiedAt($overBudgetNotifiedAt)
    {
        $this->overBudgetNotifiedAt = $overBudgetNotifiedAt;

        return $this;
    }

    /**
     * Get overBudgetNotifiedAt
     *
     * @return \DateTime 
     */
    public function getOverBudgetNotifiedAt()
    {
        return $this->overBudgetNotifiedAt;
    }

    /**
     * Set showBudgetToAll
     *
     * @param boolean $showBudgetToAll
     * @return Project
     */
    public function setShowBudgetToAll($showBudgetToAll)
    {
        $this->showBudgetToAll = $showBudgetToAll;

        return $this;
    }

    /**
     * Get showBudgetToAll
     *
     * @return boolean 
     */
    public function getShowBudgetToAll()
    {
        return $this->showBudgetToAll;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Project
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
     * @return Project
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
     * Set estimate
     *
     * @param float $estimate
     * @return Project
     */
    public function setEstimate($estimate)
    {
        $this->estimate = $estimate;

        return $this;
    }

    /**
     * Get estimate
     *
     * @return float 
     */
    public function getEstimate()
    {
        return $this->estimate;
    }

    /**
     * Set estimateBy
     *
     * @param string $estimateBy
     * @return Project
     */
    public function setEstimateBy($estimateBy)
    {
        $this->estimateBy = $estimateBy;

        return $this;
    }

    /**
     * Get estimateBy
     *
     * @return string 
     */
    public function getEstimateBy()
    {
        return $this->estimateBy;
    }

    /**
     * Set hintEarliestRecordAt
     *
     * @param \DateTime $hintEarliestRecordAt
     * @return Project
     */
    public function setHintEarliestRecordAt($hintEarliestRecordAt)
    {
        $this->hintEarliestRecordAt = $hintEarliestRecordAt;

        return $this;
    }

    /**
     * Get hintEarliestRecordAt
     *
     * @return \DateTime 
     */
    public function getHintEarliestRecordAt()
    {
        return $this->hintEarliestRecordAt;
    }

    /**
     * Set hintLatestRecordAt
     *
     * @param \DateTime $hintLatestRecordAt
     * @return Project
     */
    public function setHintLatestRecordAt($hintLatestRecordAt)
    {
        $this->hintLatestRecordAt = $hintLatestRecordAt;

        return $this;
    }

    /**
     * Get hintLatestRecordAt
     *
     * @return \DateTime 
     */
    public function getHintLatestRecordAt()
    {
        return $this->hintLatestRecordAt;
    }

    /**
     * Add entries
     *
     * @param \Harvester\FetchBundle\Entity\Entry $entries
     * @return Project
     */
    public function addEntry(\Harvester\FetchBundle\Entity\Entry $entries)
    {
        $this->entries[] = $entries;

        return $this;
    }

    /**
     * Remove entries
     *
     * @param \Harvester\FetchBundle\Entity\Entry $entries
     */
    public function removeEntry(\Harvester\FetchBundle\Entity\Entry $entries)
    {
        $this->entries->removeElement($entries);
    }

    /**
     * Get entries
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEntries()
    {
        return $this->entries;
    }
}
