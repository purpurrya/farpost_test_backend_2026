<?php

namespace App\Controller;

use App\Service\AllocationService;
use App\Service\MachineManager;
use App\Service\ProcessManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/status', name: 'api_status_')]
class StatusController extends AbstractController
{
    public function __construct(
        private AllocationService $allocationService,
        private MachineManager $machineManager,
        private ProcessManager $processManager
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $stats = $this->allocationService->getStats();
        $waitingQueue = $this->allocationService->getWaitingQueue();

        $machinesData = [];
        foreach ($this->machineManager->getAllMachinesLoad() as $item) {
            $machine = $item['machine'];
            $machinesData[] = [
                'machine_id' => $machine->getId(),
                'process_num' => $item['process_num'],
                'memory_used' => $item['used_memory'],
                'memory_total' => $machine->getTotalMemory(),
                'memory_percent' => $machine->getTotalMemory() > 0
                    ? round(($item['used_memory'] / $machine->getTotalMemory()) * 100, 2)
                    : 0,
                'cpu_used' => $item['used_cpu'],
                'cpu_total' => $machine->getTotalCpu(),
                'cpu_percent' => $machine->getTotalCpu() > 0
                    ? round(($item['used_cpu'] / $machine->getTotalCpu()) * 100, 2)
                    : 0,
            ];
        }

        $waitingData = [];
        foreach ($waitingQueue as $process) {
            $waitingData[] = [
                'id' => $process->getId(),
                'requiredMemory' => $process->getRequiredMemory(),
                'requiredCpu' => $process->getRequiredCpu()
            ];
        }

        return $this->json([
            'summary' => [
                'total_machines' => $stats['total_machines'],
                'total_processes' => $stats['total_processes'],
                'unallocated_processes' => $stats['unallocated_processes'],
                'waiting_queue_count' => $stats['waiting_queue_count']
            ],
            'machines' => $machinesData,
            'waiting_queue' => $waitingData
        ]);
    }

    #[Route('/machines', name: 'machines', methods: ['GET'])]
    public function machines(): JsonResponse
    {
        $data = [];
        foreach ($this->machineManager->getAllMachinesLoad() as $item) {
            $data[] = [
                'id' => $item['machine']->getId(),
                'totalMemory' => $item['machine']->getTotalMemory(),
                'totalCpu' => $item['machine']->getTotalCpu(),
                'processNum' => $item['process_num'],
                'usedMemory' => $item['used_memory'],
                'usedCpu' => $item['used_cpu'],
                'freeMemory' => $item['free_memory'],
                'freeCpu' => $item['free_cpu'],
                'memoryOccup' => $item['memory_occup'],
                'cpuOccup' => $item['cpu_occup']
            ];
        }

        return $this->json($data);
    }

    #[Route('/processes', name: 'processes', methods: ['GET'])]
    public function processes(): JsonResponse
    {
        $processes = $this->processManager->getAllProcesses();

        $data = [];
        foreach ($processes as $process) {
            $machine = $process->getMachine();
            $data[] = [
                'id' => $process->getId(),
                'requiredMemory' => $process->getRequiredMemory(),
                'requiredCpu' => $process->getRequiredCpu(),
                'allocated' => $machine !== null,
                'machine' => $machine ? [
                    'id' => $machine->getId(),
                    'totalMemory' => $machine->getTotalMemory(),
                    'totalCpu' => $machine->getTotalCpu()
                ] : null
            ];
        }

        return $this->json($data);
    }
}
