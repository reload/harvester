<?php

namespace Harvester\FetchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * Entry
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Harvester\FetchBundle\Entity\EntryRepository")
 * @ExclusionPolicy("all")
 */
class Entry
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
     * @ORM\Column(name="notes", type="text")
     * @Expose
     */
    private $notes;

    /**
     * @var float
     *
     * @ORM\Column(name="hours", type="float")
     * @Expose
     */
    private $hours;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_closed", type="boolean")
     */
    private $isClosed;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_billed", type="boolean")
     */
    private $isBilled;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="spent_at", type="date")
     */
    private $spentAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timer_started_at", type="datetime")
     */
    private $timerStartedAt;

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
     * @ORM\ManyToOne(targetEntity="Harvester\FetchBundle\Entity\User", inversedBy="entries")
     * @ORM\JoinColumn(name="fk_user_id", referencedColumnName="id")
     * @Expose
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Harvester\FetchBundle\Entity\Project", inversedBy="entries")
     * @ORM\JoinColumn(name="fk_project_id", referencedColumnName="id")
     * @Expose
     */
    protected $project;

    /**
     * @ORM\ManyToOne(targetEntity="Harvester\FetchBundle\Entity\Task", inversedBy="entries")
     * @ORM\JoinColumn(name="fk_task_id", referencedColumnName="id")
     * @Expose
     */
    protected $tasks;

    /**
     * Set id
     *
     * @param integer $id
     * @return Entry
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
     * Set userId
     *
     * @param integer $userId
     * @return Entry
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set notes
     *
     * @param string $notes
     * @return Entry
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
     * Set hours
     *
     * @param float $hours
     * @return Entry
     */
    public function setHours($hours)
    {
        $this->hours = $hours;

        return $this;
    }

    /**
     * Get hours
     *
     * @return float 
     */
    public function getHours()
    {
        return $this->hours;
    }

    /**
     * Set isClosed
     *
     * @param boolean $isClosed
     * @return Entry
     */
    public function setIsClosed($isClosed)
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    /**
     * Get isClosed
     *
     * @return boolean 
     */
    public function getIsClosed()
    {
        return $this->isClosed;
    }

    /**
     * Set isBilled
     *
     * @param boolean $isBilled
     * @return Entry
     */
    public function setIsBilled($isBilled)
    {
        $this->isBilled = $isBilled;

        return $this;
    }

    /**
     * Get isBilled
     *
     * @return boolean 
     */
    public function getIsBilled()
    {
        return $this->isBilled;
    }

    /**
     * Set spentAt
     *
     * @param \DateTime $spentAt
     * @return Entry
     */
    public function setSpentAt($spentAt)
    {
        $this->spentAt = $spentAt;

        return $this;
    }

    /**
     * Get spentAt
     *
     * @return \DateTime 
     */
    public function getSpentAt()
    {
        return $this->spentAt;
    }

    /**
     * Set timerStartedAt
     *
     * @param \DateTime $timerStartedAt
     * @return Entry
     */
    public function setTimerStartedAt($timerStartedAt)
    {
        $this->timerStartedAt = $timerStartedAt;

        return $this;
    }

    /**
     * Get timerStartedAt
     *
     * @return \DateTime 
     */
    public function getTimerStartedAt()
    {
        return $this->timerStartedAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Entry
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Entry
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
     * Set user
     *
     * @param \Harvester\FetchBundle\Entity\User $user
     * @return Entry
     */
    public function setUser(\Harvester\FetchBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Harvester\FetchBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set project
     *
     * @param \Harvester\FetchBundle\Entity\Project $project
     * @return Entry
     */
    public function setProject(\Harvester\FetchBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \Harvester\FetchBundle\Entity\Project 
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set tasks
     *
     * @param \Harvester\FetchBundle\Entity\Task $tasks
     * @return Entry
     */
    public function setTasks(\Harvester\FetchBundle\Entity\Task $tasks = null)
    {
        $this->tasks = $tasks;

        return $this;
    }

    /**
     * Get tasks
     *
     * @return \Harvester\FetchBundle\Entity\Task 
     */
    public function getTasks()
    {
        return $this->tasks;
    }
}
