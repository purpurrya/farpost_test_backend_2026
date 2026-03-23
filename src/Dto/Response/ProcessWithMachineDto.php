<?php

declare(strict_types=1);

namespace App\Dto\Response;

use App\Entity\Process;

final readonly class ProcessWithMachineDto
{
    public function __construct(
        public int $id,
        public int $requiredMemory,
        public int $requiredCpu,
        public ?MachineSummaryDto $machine,
    ) {}

    public function toJson(): array
    {
        return [
            'id' => $this->id,
            'requiredMemory' => $this->requiredMemory,
            'requiredCpu' => $this->requiredCpu,
            'machine' => $this->machine?->toJson(),
        ];
    }

    public static function fromEntity(Process $process): self
    {
        $m = $process->getMachine();

        return new self(
            (int) $process->getId(),
            $process->getRequiredMemory(),
            $process->getRequiredCpu(),
            $m ? MachineSummaryDto::fromEntity($m) : null,
        );
    }
}
