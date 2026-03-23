<?php

declare(strict_types=1);

namespace App\Dto\Response;

final readonly class StatusSummaryDto
{
    public function __construct(
        public int $totalMachines,
        public int $totalProcesses,
        public int $unallocatedProcesses,
        public int $waitingQueueCount,
    ) {}

    public function toJson(): array
    {
        return [
            'total_machines' => $this->totalMachines,
            'total_processes' => $this->totalProcesses,
            'unallocated_processes' => $this->unallocatedProcesses,
            'waiting_queue_count' => $this->waitingQueueCount,
        ];
    }

    /** @param array{total_machines:int,total_processes:int,unallocated_processes:int,waiting_queue_count:int} $stats */
    public static function fromStatsArray(array $stats): self
    {
        return new self(
            $stats['total_machines'],
            $stats['total_processes'],
            $stats['unallocated_processes'],
            $stats['waiting_queue_count'],
        );
    }
}
