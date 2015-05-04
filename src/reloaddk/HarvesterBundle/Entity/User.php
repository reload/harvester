<?php

namespace reloaddk\HarvesterBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="reloaddk\HarvesterBundle\Entity\UserRepository")
 * @ExclusionPolicy("all")
 */
class User implements AdvancedUserInterface
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
     * @ORM\Column(name="first_name", type="string", length=255)
     * @Expose
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255)
     * @Expose
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     * @Expose
     */
    private $email;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_admin", type="boolean")
     */
    private $isAdmin;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_contractor", type="boolean")
     */
    private $isContractor;

    /**
     * @var float
     *
     * @ORM\Column(name="working_hours", type="float", nullable=true, options={"default": 0})
     * @Expose
     */
    private $workingHours;

    /**
     * @var float
     *
     * @ORM\Column(name="billable_hours_goal", type="float", nullable=true, options={"default": null})
     * @Expose
     */
    private $billableHoursGoal;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", nullable=true, options={"default": null})
     */
    private $password;

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
     * @ORM\OneToMany(targetEntity="reloaddk\HarvesterBundle\Entity\Entry", mappedBy="user")
     */
    protected $entries;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
     * @ORM\JoinTable(name="User_Roles")
     * @Expose
     */
    protected $userRoles;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->getEmail();
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials() {}

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked() {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired() {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled() {
        return true;
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return User
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
     * Set firstName
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set isAdmin
     *
     * @param boolean $isAdmin
     * @return User
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    /**
     * Get isAdmin
     *
     * @return boolean
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * Set isContractor
     *
     * @param boolean $isContractor
     * @return User
     */
    public function setIsContractor($isContractor)
    {
        $this->isContractor = $isContractor;

        return $this;
    }

    /**
     * Get isContractor
     *
     * @return boolean
     */
    public function getIsContractor()
    {
        return $this->isContractor;
    }

    /**
     * Set workingHours.
     *
     * @param $workingHours
     * @return $this
     */
    public function setWorkingHours($workingHours)
    {
        $this->workingHours = $workingHours;

        return $this;
    }

    /**
     * Get workingHours.
     *
     * @return float
     */
    public function getWorkingHours()
    {
        return $this->workingHours;
    }

    /**
     * Set billableHoursGoal.
     *
     * @param $billableHoursGoal
     * @return $this
     */
    public function setBillableHoursGoal($billableHoursGoal)
    {
        $this->billableHoursGoal = $billableHoursGoal;

        return $this;
    }

    /**
     * Get billableHoursGoal.
     *
     * @return float
     */
    public function getBillableHoursGoal()
    {
        return $this->billableHoursGoal;
    }

    /**
     * Set password.
     *
     * @param $password
     * @return $this
     */
    public function setPassword($password)
    {
        if ($password !== null) {
            $options = ['salt' => md5('ReloadGotTime')];
            $this->password = password_hash($password, PASSWORD_DEFAULT, $options);

            return $this;
        }
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return User
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
     * @return User
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
     * Add entries
     *
     * @param \reloaddk\HarvesterBundle\Entity\Entry $entries
     * @return User
     */
    public function addEntry(\reloaddk\HarvesterBundle\Entity\Entry $entries)
    {
        $this->entries[] = $entries;

        return $this;
    }

    /**
     * Remove entries
     *
     * @param \reloaddk\HarvesterBundle\Entity\Entry $entries
     */
    public function removeEntry(\reloaddk\HarvesterBundle\Entity\Entry $entries)
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

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRoles()
    {
        // Set default role for an authenticated user.
        $roles = ['ROLE_USER'];

        foreach ($this->userRoles as $role) {
            $roles[] = $role->getName();
        }

        return $roles;
    }

    /**
     * Check if user has role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role = null)
    {
        foreach ($this->getRoles() as $userRole) {
            if ($userRole == $role) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add userRoles
     *
     * @param \reloaddk\HarvesterBundle\Entity\Role $userRoles
     * @return User
     */
    public function addUserRole(\reloaddk\HarvesterBundle\Entity\Role $userRoles)
    {
        $this->userRoles[] = $userRoles;

        return $this;
    }

    /**
     * Remove userRoles
     *
     * @param \reloaddk\HarvesterBundle\Entity\Role $userRoles
     */
    public function removeUserRole(\reloaddk\HarvesterBundle\Entity\Role $userRoles)
    {
        $this->userRoles->removeElement($userRoles);
    }

    /**
     * Get userRoles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserRoles()
    {
        return $this->userRoles;
    }
}
