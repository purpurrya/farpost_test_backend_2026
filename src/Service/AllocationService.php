<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Process;
use App\Repository\Interface\ProcessRepositoryInterface;
use App\Repository\Interface\MachineRepositoryInterface;

class AllocationService
{
    public function __construct(
        private ProcessRepositoryInterface $processRepository,
        private MachineRepositoryInterface $machineRepository
    ) {}

    public function rebalance(): void
    {
        $machines = $this->machineRepository->findAll();
        $allProcesses = $this->processRepository->findAll();

        if (empty($machines)) {
            foreach ($allProcesses as $process) {
                $process->setMachine(null);
                $this->processRepository->save($process);
            }
            return;
        }

        $usedMemoryByMachineId = [];
        $usedCpuByMachineId = [];
        foreach ($machines as $machine) {
            $usedMemoryByMachineId[$machine->getId()] = 0;
            $usedCpuByMachineId[$machine->getId()] = 0;
        }

        foreach ($allProcesses as $process) {
            $process->setMachine(null);
        }

        usort($allProcesses, function (Process $a, Process $b) {
            return ($b->getRequiredMemory() + $b->getRequiredCpu()) <=> ($a->getRequiredMemory() + $a->getRequiredCpu());
        });

        foreach ($allProcesses as $process) {
            $this->allocateProcess($process, $machines, $usedMemoryByMachineId, $usedCpuByMachineId);
            $this->processRepository->save($process);
        }
    }

    private function allocateProcess(Process $process, array $machines, array &$usedMemoryByMachineId, array &$usedCpuByMachineId): void {
        $bestMachine = null;
        $bestCount = -INF;

        foreach ($machines as $machine) {
            $machineId = $machine->getId();
            $freeMemory = $machine->getTotalMemory() - $usedMemoryByMachineId[$machineId];
            $freeCpu = $machine->getTotalCpu() - $usedCpuByMachineId[$machineId];

            if ($freeMemory < $process->getRequiredMemory() || $freeCpu < $process->getRequiredCpu()) {
                continue;
            }

            $freeMemoryPercent = $machine->getTotalMemory() > 0
                ? ($freeMemory / $machine->getTotalMemory())
                : 0;

            $freeCpuPercent = $machine->getTotalCpu() > 0
                ? ($freeCpu / $machine->getTotalCpu())
                : 0;

            $score = ($freeMemoryPercent + $freeCpuPercent) / 2;

            if ($score > $bestCount) {
                $bestCount = $score;
                $bestMachine = $machine;
            }
        }

        if ($bestMachine !== null) {
            $process->setMachine($bestMachine);
            $bestId = $bestMachine->getId();
            $usedMemoryByMachineId[$bestId] += $process->getRequiredMemory();
            $usedCpuByMachineId[$bestId] += $process->getRequiredCpu();
        } else {
            $process->setMachine(null);
        }
    }

    public function getWaitingQueue(): array
    {
        return $this->processRepository->getUnallocatedProcesses();
    }

    public function getStats(): array
    {
        $machines = $this->machineRepository->findAll();
        $unallocatedProcesses = $this->processRepository->getUnallocatedProcesses();
        $allProcesses = $this->processRepository->findAll();

        return [
            'total_machines' => count($machines),
            'total_processes' => count($allProcesses),
            'unallocated_processes' => count($unallocatedProcesses),
            'waiting_queue_count' => count($unallocatedProcesses),
        ];
    }
}
