<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateMachineRequest
{
    #[Assert\NotNull (message: 'Field totalMemory is required')]
    #[Assert\Type(type: 'integer', message: 'Field totalMemory must be an integer')]
    #[Assert\Positive(message: 'Field totalMemory must be a positive number')]
    public int $totalMemory;

    #[Assert\NotNull (message: 'Field totalCpu is required')]
    #[Assert\Type(type: 'integer', message: 'Field totalCpu must be an integer')]
    #[Assert\Positive(message: 'Field totalCpu must be a positive number')]
    public int $totalCpu;

    public function __construct(int $totalMemory, int $totalCpu)
    {
        $this->totalMemory = $totalMemory;
        $this->totalCpu = $totalCpu;
    }
}