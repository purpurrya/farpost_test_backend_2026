<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Machine;
use Doctrine\DBAL\LockMode;

interface MachineRepositoryInterface
{
    /**
     * @return Machine|null
     */
    public function find(mixed $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null);

    /**
     * @return Machine[]
     */
    public function findAll(): array;

    public function create(int $totalMemory, int $totalCpu): Machine;

    public function save(Machine $machine): void;

    public function remove(Machine $machine): void;
}