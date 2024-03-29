<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class  User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=13)
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Messages", mappedBy="admin", orphanRemoval=true)
     */
    private $messages;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Benefits", mappedBy="users")
     */
    private $benefits;

    /**
     * @ORM\Column(type="integer")
     */
    private $freeDays;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Demands", mappedBy="employee")
     */
    private $demands;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    public function __construct()
    {
        $this->messages = new ArrayCollection();

        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        $this -> freeDays = 21;
        $this->benefits = new ArrayCollection();
        $this->demands = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }


    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }
    public function setRoles($role = "ROLE_USER") {
        array_push($this->roles, $role);
    }

    /**
     * @see UserInterface
     */
    public function getRoles() {
        $roles = $this->roles;
        return array_unique($roles);
    }

    public function deleteRole($role) {
        for($i = 0; $i < count($this->roles); $i++)
            if ($this->roles[$i] == $role)
                unset($this->roles[$i]);
    }

    /**
     * @return Collection|Messages[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Messages $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setAdmin($this);
        }

        return $this;
    }

    public function removeMessage(Messages $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getAdmin() === $this) {
                $message->setAdmin(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Benefits[]
     */
    public function getBenefits(): Collection
    {
        return $this->benefits;
    }

    public function addBenefit(Benefits $benefit): self
    {
        if (!$this->benefits->contains($benefit)) {
            $this->benefits[] = $benefit;
            $benefit->addUser($this);
        }

        return $this;
    }

    public function removeBenefit(Benefits $benefit): self
    {
        if ($this->benefits->contains($benefit)) {
            $this->benefits->removeElement($benefit);
            $benefit->removeUser($this);
        }

        return $this;
    }

    public function getFreeDays(): ?int
    {
        return $this->freeDays;
    }

    public function setFreeDays(int $freeDays): self
    {
        $this->freeDays = $freeDays;

        return $this;
    }

    /**
     * @return Collection|Demands[]
     */
    public function getDemands(): Collection
    {
        return $this->demands;
    }

    public function addDemand(Demands $demand): self
    {
        if (!$this->demands->contains($demand)) {
            $this->demands[] = $demand;
            $demand->setEmployee($this);
        }

        return $this;
    }

    public function removeDemand(Demands $demand): self
    {
        if ($this->demands->contains($demand)) {
            $this->demands->removeElement($demand);
            // set the owning side to null (unless already changed)
            if ($demand->getEmployee() === $this) {
                $demand->setEmployee(null);
            }
        }

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }
}
