<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CancellationRepository")
 */
class Cancellation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Excursion", inversedBy="cancellation", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $excursion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getExcursion(): ?Excursion
    {
        return $this->excursion;
    }

    public function setExcursion(Excursion $excursion): self
    {
        $this->excursion = $excursion;

        return $this;
    }
}
