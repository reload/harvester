<?php

namespace Harvester\FetchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Task
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Harvester\FetchBundle\Entity\TaskRepository")
 */
class Task
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_default", type="boolean")
     */
    private $isDefault;

    /**
     * @var float
     *
     * @ORM\Column(name="default_hourly_rate", type="float")
     */
    private $defaultHourlyRate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="billable_by_default", type="boolean")
     */
    private $billableByDefault;

    /**
     * @var boolean
     *
     * @ORM\Column(name="deactivated", type="boolean")
     */
    private $deactivated;

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
     * @ORM\OneToMany(targetEntity="Harvester\FetchBundle\Entity\Task", mappedBy="user")
     */
    protected $tasks;

    /**
     * Set id
     *
     * @param string $id
     * @return Task
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
     * @return Task
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
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return Task
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean 
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set defaultHourlyRate
     *
     * @param float $defaultHourlyRate
     * @return Task
     */
    public function setDefaultHourlyRate($defaultHourlyRate)
    {
        $this->defaultHourlyRate = $defaultHourlyRate;

        return $this;
    }

    /**
     * Get defaultHourlyRate
     *
     * @return float 
     */
    public function getDefaultHourlyRate()
    {
        return $this->defaultHourlyRate;
    }

    /**
     * Set billableByDefault
     *
     * @param boolean $billableByDefault
     * @return Task
     */
    public function setBillableByDefault($billableByDefault)
    {
        $this->billableByDefault = $billableByDefault;

        return $this;
    }

    /**
     * Get billableByDefault
     *
     * @return boolean 
     */
    public function getBillableByDefault()
    {
        return $this->billableByDefault;
    }

    /**
     * Set deactivated
     *
     * @param boolean $deactivated
     * @return Task
     */
    public function setDeactivated($deactivated)
    {
        $this->deactivated = $deactivated;

        return $this;
    }

    /**
     * Get deactivated
     *
     * @return boolean 
     */
    public function getDeactivated()
    {
        return $this->deactivated;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Task
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
     * @return Task
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
     * Constructor
     */
    public function __construct()
    {
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add tasks
     *
     * @param \Harvester\FetchBundle\Entity\Task $tasks
     * @return Task
     */
    public function addTask(\Harvester\FetchBundle\Entity\Task $tasks)
    {
        $this->tasks[] = $tasks;

        return $this;
    }

    /**
     * Remove tasks
     *
     * @param \Harvester\FetchBundle\Entity\Task $tasks
     */
    public function removeTask(\Harvester\FetchBundle\Entity\Task $tasks)
    {
        $this->tasks->removeElement($tasks);
    }

    /**
     * Get tasks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTasks()
    {
        return $this->tasks;
    }
}
