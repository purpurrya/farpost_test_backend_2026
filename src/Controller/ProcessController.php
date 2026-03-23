<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CreateProcessDto;
use App\Dto\Response\ProcessRequirementsDto;
use App\Dto\Response\ProcessWithMachineDto;
use App\Dto\Response\QueueDto;
use App\Service\ProcessManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/processes', name: 'api_processes_')]
class ProcessController extends AbstractController
{
    public function __construct(
        private ProcessManager $processManager,
    ) {}

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        CreateProcessDto $dto,
    ): JsonResponse {
        try {
            $process = $this->processManager->createProcess($dto->requiredMemory, $dto->requiredCpu);

            return $this->json([
                'success' => true,
                'process' => ProcessWithMachineDto::fromEntity($process)->toJson(),
            ], 201);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
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
                'message' => "Process {$id} deleted",
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $rows = [];
        foreach ($this->processManager->getAllProcesses() as $process) {
            $rows[] = ProcessWithMachineDto::fromEntity($process)->toJson();
        }

        return $this->json($rows);
    }

    #[Route('/unallocated', name: 'unallocated', methods: ['GET'])]
    public function unallocated(): JsonResponse
    {
        $unallocated = $this->processManager->getUnallocatedProcesses();
        $dtos = array_map(
            static fn ($p) => ProcessRequirementsDto::fromEntity($p),
            $unallocated,
        );

        return $this->json((new QueueDto(count($dtos), $dtos))->toJson());
    }
}
