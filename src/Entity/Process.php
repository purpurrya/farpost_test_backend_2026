<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProcessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProcessRepository::class)]
class Process
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: false)]
    private ?int $requiredMemory = null;

    #[ORM\Column(nullable: false)]
    private ?int $requiredCpu = null;

    #[ORM\ManyToOne(inversedBy: 'processes')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Machine $machine = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequiredMemory(): ?int
    {
        return $this->requiredMemory;
    }

    public function setRequiredMemory(int $requiredMemory): static
    {
        $this->requiredMemory = $requiredMemory;

        return $this;
    }

    public function getRequiredCpu(): ?int
    {
        return $this->requiredCpu;
    }

    public function setRequiredCpu(int $requiredCpu): static
    {
        $this->requiredCpu = $requiredCpu;

        return $this;
    }

    public function getMachine(): ?Machine
    {
        return $this->machine;
    }

    public function setMachine(?Machine $machine): static
    {
        $this->machine = $machine;

        return $this;
    }
}
