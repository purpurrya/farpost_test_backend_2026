<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateMachineDto
{
    public function __construct(
        #[Assert\Positive(message: 'totalMemory must be int > 0')]
        public int $totalMemory,
        #[Assert\Positive(message: 'totalCpu must be int > 0')]
        public int $totalCpu,
    ) {}
}