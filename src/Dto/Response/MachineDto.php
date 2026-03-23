<?php

declare(strict_types=1);

namespace App\Dto\Response;

use App\Entity\Machine;

final readonly class MachineDto
{
    /**
     * @param list<ProcessRequirementsDto> $processes
     */
    public function __construct(
        public int $id,
        public int $totalMemory,
        public int $totalCpu,
        public int $processNum,
        public int $usedMemory,
        public int $usedCpu,
        public int $freeMemory,
        public int $freeCpu,
        public array $processes,
    ) {}

    public function toJson(): array
    {
        return [
            'id' => $this->id,
            'totalMemory' => $this->totalMemory,
            'totalCpu' => $this->totalCpu,
            'processNum' => $this->processNum,
            'usedMemory' => $this->usedMemory,
            'usedCpu' => $this->usedCpu,
            'freeMemory' => $this->freeMemory,
            'freeCpu' => $this->freeCpu,
            'processes' => array_map(static fn (ProcessRequirementsDto $p) => $p->toJson(), $this->processes),
        ];
    }

    /**
     * @param array{
     *     machine: Machine,
     *     processes: iterable,
     *     process_num: int,
     *     used_memory: int,
     *     used_cpu: int,
     *     free_memory: int,
     *     free_cpu: int
     * } $machineData
     */
    public static function fromLoadData(array $machineData): self
    {
        $m = $machineData['machine'];
        $procs = [];
        foreach ($machineData['processes'] as $p) {
            $procs[] = ProcessRequirementsDto::fromEntity($p);
        }

        return new self(
            id: (int) $m->getId(),
            totalMemory: $m->getTotalMemory(),
            totalCpu: $m->getTotalCpu(),
            processNum: $machineData['process_num'],
            usedMemory: $machineData['used_memory'],
            usedCpu: $machineData['used_cpu'],
            freeMemory: $machineData['free_memory'],
            freeCpu: $machineData['free_cpu'],
            processes: $procs,
        );
    }
}
