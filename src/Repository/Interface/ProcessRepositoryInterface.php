<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Process;
use App\Entity\Machine;
use Doctrine\DBAL\LockMode;

interface ProcessRepositoryInterface
{
    /**
     * @return Process|null
     */
    public function find(mixed $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null);

    /**
     * @return Process[]
     */
    public function findAll(): array;

    public function save(Process $process): void;

    public function remove(Process $process): void;

    /**
     * @return Process[]
     */
    public function findByMachine(Machine $machine): array;

    /**
     * @return Process[]
     */
    public function getUnallocatedProcesses(): array;
}