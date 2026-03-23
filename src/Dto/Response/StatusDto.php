<?php

declare(strict_types=1);

namespace App\Dto\Response;

use App\Entity\Process;

final readonly class StatusDto
{
    /**
     * @param list<StatusMachineLoadDto> $machines
     * @param list<ProcessRequirementsDto> $waitingQueue
     */
    public function __construct(
        public StatusSummaryDto $summary,
        public array $machines,
        public array $waitingQueue,
    ) {}

    public function toJson(): array
    {
        return [
            'summary' => $this->summary->toJson(),
            'machines' => array_map(static fn (StatusMachineLoadDto $m) => $m->toJson(), $this->machines),
            'waiting_queue' => array_map(static fn (ProcessRequirementsDto $p) => $p->toJson(), $this->waitingQueue),
        ];
    }

    /**
     * @param array{total_machines:int,total_processes:int,unallocated_processes:int,waiting_queue_count:int} $stats
     * @param iterable<int, array> $loadRows
     * @param list<Process> $waitingProcesses
     */
    public static function build(array $stats, iterable $loadRows, array $waitingProcesses): self
    {
        $machines = [];
        foreach ($loadRows as $row) {
            $machines[] = StatusMachineLoadDto::fromLoadRow($row);
        }

        $waiting = array_map(
            static fn (Process $p) => ProcessRequirementsDto::fromEntity($p),
            $waitingProcesses,
        );

        return new self(
            StatusSummaryDto::fromStatsArray($stats),
            $machines,
            $waiting,
        );
    }
}
