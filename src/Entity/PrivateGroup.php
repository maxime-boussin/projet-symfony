<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PrivateGroupRepository")
 */
class PrivateGroup
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="privateGroups")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupMaster;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User")
     */
    private $group_member;

    public function __construct()
    {
        $this->group_member = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupMaster(): ?User
    {
        return $this->groupMaster;
    }

    public function setGroupMaster(?User $groupMaster): self
    {
        $this->groupMaster = $groupMaster;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getGroupMember(): Collection
    {
        return $this->group_member;
    }

    public function addGroupMember(User $groupMember): self
    {
        if (!$this->group_member->contains($groupMember)) {
            $this->group_member[] = $groupMember;
        }

        return $this;
    }

    public function removeGroupMember(User $groupMember): self
    {
        if ($this->group_member->contains($groupMember)) {
            $this->group_member->removeElement($groupMember);
        }

        return $this;
    }
}
