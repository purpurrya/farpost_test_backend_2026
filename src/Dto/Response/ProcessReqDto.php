<?php

declare(strict_types=1);

namespace App\Dto\Response;

final readonly class ProcessReqDto
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

    public function fromEnity(Process $process): self
    {
        return new self(
            $process->getId(),
            $process->getRequiredMemory(),
            $process->getRequiredCpu()
        );
    }
}