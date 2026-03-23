<?php

declare(strict_types=1);

namespace App\Dto\Response;

use App\Entity\Process;

final readonly class StatusProcessRowDto
{
    public function __construct(
        public int $id,
        public int $requiredMemory,
        public int $requiredCpu,
        public bool $allocated,
        public ?MachineSummaryDto $machine,
    ) {}

    public function toJson(): array
    {
        return [
            'id' => $this->id,
            'requiredMemory' => $this->requiredMemory,
            'requiredCpu' => $this->requiredCpu,
            'allocated' => $this->allocated,
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
            $m !== null,
            $m ? MachineSummaryDto::fromEntity($m) : null,
        );
    }
}
