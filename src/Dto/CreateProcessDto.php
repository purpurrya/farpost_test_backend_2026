<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateProcessDto
{
    public function __construct(
        #[Assert\Positive(message: 'requiredMemory must be int > 0')]
        public int $requiredMemory,
        #[Assert\Positive(message: 'requiredCpu must be int > 0')]
        public int $requiredCpu,
    ) {}
}