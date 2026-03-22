<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CreateMachineDto;
use App\Service\AllocationService;
use App\Service\MachineManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/machines', name: 'api_machines_')]
class MachineController extends AbstractController
{
    public function __construct(
        private MachineManager $machineManager,
        private AllocationService $allocationService,
    ) {}

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        CreateMachineDto $dto,
    ): JsonResponse {
        try {
            $machine = $this->machineManager->createMachine($dto->totalMemory, $dto->totalCpu);
            $this->allocationService->rebalance();

            return $this->json([
                'success' => true,
                'machine' => [
                    'id' => $machine->getId(),
                    'totalMemory' => $machine->getTotalMemory(),
                    'totalCpu' => $machine->getTotalCpu()
                ]
            ], 201);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->machineManager->deleteMachine($id);

            return $this->json([
                'success' => true,
                'message' => "Machine {$id} deleted"
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $machineData = $this->machineManager->getMachineWithLoad($id);

        if (!$machineData) {
            return $this->json([
                'success' => false,
                'error' => "Machine {$id} not found"
            ], 404);
        }

        $processes = [];
        foreach ($machineData['processes'] as $process) {
            $processes[] = [
                'id' => $process->getId(),
                'requiredMemory' => $process->getRequiredMemory(),
                'requiredCpu' => $process->getRequiredCpu()
            ];
        }

        return $this->json([
            'id' => $machineData['machine']->getId(),
            'totalMemory' => $machineData['machine']->getTotalMemory(),
            'totalCpu' => $machineData['machine']->getTotalCpu(),
            'processNum' => $machineData['process_num'],
            'usedMemory' => $machineData['used_memory'],
            'usedCpu' => $machineData['used_cpu'],
            'freeMemory' => $machineData['free_memory'],
            'freeCpu' => $machineData['free_cpu'],
            'processes' => $processes
        ]);
    }
}
