<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Response\MachinesDto;
use App\Dto\Response\StatusDto;
use App\Dto\Response\StatusProcessRowDto;
use App\Service\AllocationService;
use App\Service\MachineManager;
use App\Service\ProcessManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

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
        $dto = StatusDto::build(
            $this->allocationService->getStats(),
            $this->machineManager->getAllMachinesLoad(),
            $this->allocationService->getWaitingQueue(),
        );

        return $this->json($dto->toJson());
    }

    #[Route('/machines', name: 'machines', methods: ['GET'])]
    public function machines(): JsonResponse
    {
        $rows = [];
        foreach ($this->machineManager->getAllMachinesLoad() as $item) {
            $rows[] = MachinesDto::fromLoadRow($item)->toJson();
        }

        return $this->json($rows);
    }

    #[Route('/processes', name: 'processes', methods: ['GET'])]
    public function processes(): JsonResponse
    {
        $rows = [];
        foreach ($this->processManager->getAllProcesses() as $process) {
            $rows[] = StatusProcessRowDto::fromEntity($process)->toJson();
        }

        return $this->json($rows);
    }
}
