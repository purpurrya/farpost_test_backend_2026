<?php

declare(strict_types=1);

namespace App\Dto\Response;

final readonly class QueueDto
{
    /**
     * @param list<ProcessRequirementsDto> $processes
     */
    public function __construct(
        public int $count,
        public array $processes,
    ) {}

    public function toJson(): array
    {
        return [
            'count' => $this->count,
            'processes' => array_map(static fn (ProcessRequirementsDto $p) => $p->toJson(), $this->processes),
        ];
    }
}
