<?php

declare(strict_types=1);

namespace App\Dto\Response;

use App\Entity\Machine;

final readonly class MachinesDto
{
    public function __construct(
        public int $id,
        public int $totalMemory,
        public int $totalCpu,
        public int $processNum,
        public int $usedMemory,
        public int $usedCpu,
        public int $freeMemory,
        public int $freeCpu,
        public float $memoryOccup,
        public float $cpuOccup,
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
            'memoryOccup' => $this->memoryOccup,
            'cpuOccup' => $this->cpuOccup,
        ];
    }

    /**
     * @param array{
     *     machine: Machine,
     *     process_num: int,
     *     used_memory: int,
     *     used_cpu: int,
     *     free_memory: int,
     *     free_cpu: int,
     *     memory_occup: float,
     *     cpu_occup: float
     * } $item
     */
    public static function fromLoadRow(array $item): self
    {
        $m = $item['machine'];

        return new self(
            id: (int) $m->getId(),
            totalMemory: $m->getTotalMemory(),
            totalCpu: $m->getTotalCpu(),
            processNum: $item['process_num'],
            usedMemory: $item['used_memory'],
            usedCpu: $item['used_cpu'],
            freeMemory: $item['free_memory'],
            freeCpu: $item['free_cpu'],
            memoryOccup: $item['memory_occup'],
            cpuOccup: $item['cpu_occup'],
        );
    }
}
