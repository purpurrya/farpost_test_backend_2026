<?php

declare(strict_types=1);

namespace App\Dto\Response;

use App\Entity\Machine;

final readonly class MachineSummaryDto
{
    public function __construct(
        public int $id,
        public int $totalMemory,
        public int $totalCpu,
    ) {}

    public function toJson(): array
    {
        return [
            'id' => $this->id,
            'totalMemory' => $this->totalMemory,
            'totalCpu' => $this->totalCpu,
        ];
    }

    public static function fromEntity(Machine $machine): self
    {
        return new self(
            (int) $machine->getId(),
            $machine->getTotalMemory(),
            $machine->getTotalCpu(),
        );
    }
}
