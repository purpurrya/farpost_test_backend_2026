<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Machine;
use App\Repository\Interface\MachineRepositoryInterface;
use App\Repository\Interface\ProcessRepositoryInterface;

class MachineManager
{
    public function __construct(
        private MachineRepositoryInterface $machineRepository,
        private ProcessRepositoryInterface $processRepository,
        private AllocationService $allocationService
    ) {}

    public function createMachine(int $totalMemory, int $totalCpu): Machine
    {
        $machine = new Machine();
        $machine->setTotalMemory($totalMemory);
        $machine->setTotalCpu($totalCpu);

        $this->machineRepository->save($machine);

        return $machine;
    }

    public function deleteMachine(int $machineId): void
    {
        $machine = $this->machineRepository->find($machineId);

        if (!$machine) {
            throw new \Exception("Machine {$machineId} not found.");
        }

        $this->machineRepository->remove($machine);
        $this->allocationService->rebalance();
    }

    public function getAllMachinesLoad(): array
    {
        $machines = $this->machineRepository->findAll();
        $result = [];

        foreach ($machines as $machine) {
            $processes = $this->processRepository->findByMachine($machine);

            $usedMemory = 0;
            $usedCpu = 0;

            foreach ($processes as $process) {
                $usedMemory += $process->getRequiredMemory();
                $usedCpu += $process->getRequiredCpu();
            }

            $memoryPercent = $machine->getTotalMemory() > 0
                ? round(($usedMemory / $machine->getTotalMemory()) * 100, 2)
                : 0;

            $cpuPercent = $machine->getTotalCpu() > 0
                ? round(($usedCpu / $machine->getTotalCpu()) * 100, 2)
                : 0;

            $result[] = [
                'machine' => $machine,
                'processes' => $processes,
                'process_num' => count($processes),
                'used_memory' => $usedMemory,
                'used_cpu' => $usedCpu,
                'free_memory' => $machine->getTotalMemory() - $usedMemory,
                'free_cpu' => $machine->getTotalCpu() - $usedCpu,
                'memory_occup' => $memoryPercent,
                'cpu_occup' => $cpuPercent
            ];
        }

        return $result;
    }

    public function getMachineWithLoad(int $machineId): ?array
    {
        $machine = $this->machineRepository->find($machineId);

        if (!$machine) {
            return null;
        }

        $processes = $this->processRepository->findByMachine($machine);

        $usedMemory = 0;
        $usedCpu = 0;

        foreach ($processes as $process) {
            $usedMemory += $process->getRequiredMemory();
            $usedCpu += $process->getRequiredCpu();
        }

        return [
            'machine' => $machine,
            'processes' => $processes,
            'process_num' => count($processes),
            'used_memory' => $usedMemory,
            'used_cpu' => $usedCpu,
            'free_memory' => $machine->getTotalMemory() - $usedMemory,
            'free_cpu' => $machine->getTotalCpu() - $usedCpu
        ];
    }
}
