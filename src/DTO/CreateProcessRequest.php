<?php

namespace App\DTO;

use Symfony\Components\Validator\Constraints as Assert;

class CreateProcessRequest
{
    #[Assert\NotNull (message: 'Field requiredMemory is required')]
    #[Assert\Type(type: 'integer', message: 'Field requiredMemory must be an integer')]
    #[Assert\Positive(message: 'Field requiredMemory must be a positive number')]
    public int $requiredMemory;

    #[Assert\NotNull (message: 'Field requiredCpu is required')]
    #[Assert\Type(type: 'integer', message: 'Field requiredCpu must be an integer')]
    #[Assert\Positive(message: 'Field requiredCpu must be a positive number')]
    public int $requiredCpu;
    
    public function __construct(int $requiredMemory, int $requiredCpu)
    {
        $this->requiredMemory = $requiredMemory;
        $this->requiredCpu = $requiredCpu;
    }
}