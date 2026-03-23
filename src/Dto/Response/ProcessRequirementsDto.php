<?php

declare(strict_types=1);

namespace App\Dto\Response;

use App\Entity\Process;

final readonly class ProcessRequirementsDto
{
    public function __construct(
        public int $id,
        public int $requiredMemory,
        public int $requiredCpu,
    ) {}

    public function toJson(): array
    {
        return [
            'id' => $this->id,
            'requiredMemory' => $this->requiredMemory,
            'requiredCpu' => $this->requiredCpu,
        ];
    }

    public static function fromEntity(Process $process): self
    {
        return new self(
            (int) $process->getId(),
            $process->getRequiredMemory(),
            $process->getRequiredCpu(),
        );
    }
}
