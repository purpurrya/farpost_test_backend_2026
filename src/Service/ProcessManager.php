<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Process;
use App\Repository\Interface\ProcessRepositoryInterface;

class ProcessManager
{
    public function __construct(
        private ProcessRepositoryInterface $processRepository,
        private AllocationService $allocationService
    ) {}

    public function createProcess(int $requiredMemory, int $requiredCpu): Process
    {
        $process = new Process();
        $process->setRequiredMemory($requiredMemory);
        $process->setRequiredCpu($requiredCpu);

        $this->processRepository->save($process);

        $this->allocationService->rebalance();

        return $process;
    }

    public function deleteProcess(int $processId): void
    {
        $process = $this->processRepository->find($processId);

        if (!$process) {
            throw new \Exception("Process {$processId} not found.");
        }

        $this->processRepository->remove($process);

        $this->allocationService->rebalance();
    }

    public function getAllProcesses(): array
    {
        return $this->processRepository->findAll();
    }

    public function getUnallocatedProcesses(): array
    {
        return $this->processRepository->getUnallocatedProcesses();
    }
}
