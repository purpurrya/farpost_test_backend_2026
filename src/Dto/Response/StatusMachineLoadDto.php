<?php

declare(strict_types=1);

namespace App\Dto\Response;

use App\Entity\Machine;

final readonly class StatusMachineLoadDto
{
    public function __construct(
        public int $machineId,
        public int $processNum,
        public int $memoryUsed,
        public int $memoryTotal,
        public float $memoryPercent,
        public int $cpuUsed,
        public int $cpuTotal,
        public float $cpuPercent,
    ) {}

    public function toJson(): array
    {
        return [
            'machine_id' => $this->machineId,
            'process_num' => $this->processNum,
            'memory_used' => $this->memoryUsed,
            'memory_total' => $this->memoryTotal,
            'memory_percent' => $this->memoryPercent,
            'cpu_used' => $this->cpuUsed,
            'cpu_total' => $this->cpuTotal,
            'cpu_percent' => $this->cpuPercent,
        ];
    }

    /**
     * @param array{machine: Machine, process_num: int, used_memory: int, used_cpu: int} $item
     */
    public static function fromLoadRow(array $item): self
    {
        $m = $item['machine'];
        $memT = $m->getTotalMemory();
        $cpuT = $m->getTotalCpu();
        $um = $item['used_memory'];
        $uc = $item['used_cpu'];

        return new self(
            machineId: (int) $m->getId(),
            processNum: $item['process_num'],
            memoryUsed: $um,
            memoryTotal: $memT,
            memoryPercent: $memT > 0 ? round(($um / $memT) * 100, 2) : 0.0,
            cpuUsed: $uc,
            cpuTotal: $cpuT,
            cpuPercent: $cpuT > 0 ? round(($uc / $cpuT) * 100, 2) : 0.0,
        );
    }
}
