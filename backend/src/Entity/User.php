<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity('email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $password = null;

    #[ORM\Column(nullable: false)]
    private ?bool $isVerified = false;

    #[ORM\Column(type: Types::ARRAY, nullable: false)]
    private array $roles = [];

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?self $createdBy = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?self $updatedBy = null;

    /**
     * @var Collection<int, Purchase>
     */
    #[ORM\OneToMany(targetEntity: Purchase::class, mappedBy: 'customer', orphanRemoval: true)]
    private Collection $purchases;

    /**
     * @var Collection<int, CompletedCourse>
     */
    #[ORM\OneToMany(targetEntity: CompletedCourse::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $completedCourses;

    /**
     * @var Collection<int, CompletedLesson>
     */
    #[ORM\OneToMany(targetEntity: CompletedLesson::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $completedLessons;

    /**
     * @var Collection<int, Certificate>
     */
    #[ORM\OneToMany(targetEntity: Certificate::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $certificates;

    public function __construct()
    {
        $this->purchases = new ArrayCollection();
        $this->completedCourses = new ArrayCollection();
        $this->completedLessons = new ArrayCollection();
        $this->certificates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedBy(): ?self
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?self $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedBy(): ?self
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?self $updatedBy): static
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return Collection<int, Purchase>
     */
    public function getPurchases(): Collection
    {
        return $this->purchases;
    }

    public function addPurchase(Purchase $purchase): static
    {
        if (!$this->purchases->contains($purchase)) {
            $this->purchases->add($purchase);
            $purchase->setCustomer($this);
        }

        return $this;
    }

    public function removePurchase(Purchase $purchase): static
    {
        if ($this->purchases->removeElement($purchase)) {
            // set the owning side to null (unless already changed)
            if ($purchase->getCustomer() === $this) {
                $purchase->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompletedCourse>
     */
    public function getCompletedCourses(): Collection
    {
        return $this->completedCourses;
    }

    public function addCompletedCourse(CompletedCourse $completedCourse): static
    {
        if (!$this->completedCourses->contains($completedCourse)) {
            $this->completedCourses->add($completedCourse);
            $completedCourse->setUser($this);
        }

        return $this;
    }

    public function removeCompletedCourse(CompletedCourse $completedCourse): static
    {
        if ($this->completedCourses->removeElement($completedCourse)) {
            // set the owning side to null (unless already changed)
            if ($completedCourse->getUser() === $this) {
                $completedCourse->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompletedLesson>
     */
    public function getCompletedLessons(): Collection
    {
        return $this->completedLessons;
    }

    public function addCompletedLesson(CompletedLesson $completedLesson): static
    {
        if (!$this->completedLessons->contains($completedLesson)) {
            $this->completedLessons->add($completedLesson);
            $completedLesson->setUser($this);
        }

        return $this;
    }

    public function removeCompletedLesson(CompletedLesson $completedLesson): static
    {
        if ($this->completedLessons->removeElement($completedLesson)) {
            // set the owning side to null (unless already changed)
            if ($completedLesson->getUser() === $this) {
                $completedLesson->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Certificate>
     */
    public function getCertificates(): Collection
    {
        return $this->certificates;
    }

    public function addCertificate(Certificate $certificate): static
    {
        if (!$this->certificates->contains($certificate)) {
            $this->certificates->add($certificate);
            $certificate->setUser($this);
        }

        return $this;
    }

    public function removeCertificate(Certificate $certificate): static
    {
        if ($this->certificates->removeElement($certificate)) {
            // set the owning side to null (unless already changed)
            if ($certificate->getUser() === $this) {
                $certificate->setUser(null);
            }
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        
    }
}
