<?php

namespace App\Controller;

use App\Service\ProcessManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/processes', name: 'api_processes_')]
class ProcessController extends AbstractController
{
    public function __construct(
        private ProcessManager $processManager,
    ) {}

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            if (!is_array($data)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Invalid request data'
                ], 400);
            }

            $requiredMemory = $data['requiredMemory'] ?? null;
            $requiredCpu = $data['requiredCpu'] ?? null;

            if (!is_int($requiredMemory) || $requiredMemory <= 0 || !is_int($requiredCpu) || $requiredCpu <= 0) {
                return $this->json([
                    'success' => false,
                    'error' => 'requiredMemory and requiredCpu must be int > 0'
                ], 400);
            }

            $process = $this->processManager->createProcess($requiredMemory, $requiredCpu);

            $machine = $process->getMachine();

            return $this->json([
                'success' => true,
                'process' => [
                    'id' => $process->getId(),
                    'requiredMemory' => $process->getRequiredMemory(),
                    'requiredCpu' => $process->getRequiredCpu(),
                    'machine' => $machine ? [
                        'id' => $machine->getId(),
                        'totalMemory' => $machine->getTotalMemory(),
                        'totalCpu' => $machine->getTotalCpu()
                    ] : null
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
            $this->processManager->deleteProcess($id);

            return $this->json([
                'success' => true,
                'message' => "Process {$id} deleted"
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $processes = $this->processManager->getAllProcesses();

        $data = [];
        foreach ($processes as $process) {
            $machine = $process->getMachine();
            $data[] = [
                'id' => $process->getId(),
                'requiredMemory' => $process->getRequiredMemory(),
                'requiredCpu' => $process->getRequiredCpu(),
                'machine' => $machine ? [
                    'id' => $machine->getId(),
                    'totalMemory' => $machine->getTotalMemory(),
                    'totalCpu' => $machine->getTotalCpu()
                ] : null
            ];
        }

        return $this->json($data);
    }

    #[Route('/unallocated', name: 'unallocated', methods: ['GET'])]
    public function unallocated(): JsonResponse
    {
        $unallocated = $this->processManager->getUnallocatedProcesses();

        $data = [];
        foreach ($unallocated as $process) {
            $data[] = [
                'id' => $process->getId(),
                'requiredMemory' => $process->getRequiredMemory(),
                'requiredCpu' => $process->getRequiredCpu()
            ];
        }

        return $this->json([
            'count' => count($data),
            'processes' => $data
        ]);
    }
}
