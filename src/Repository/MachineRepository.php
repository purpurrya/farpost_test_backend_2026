<?php

namespace App\Repository;

use App\Entity\Machine;
use App\Repository\Interface\MachineRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Machine>
 */
class MachineRepository extends ServiceEntityRepository implements MachineRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Machine::class);
    }

    public function save(Machine $machine): void
    {
        $this->getEntityManager()->persist($machine);
        $this->getEntityManager()->flush();
    }

    public function remove(Machine $machine): void
    {
        $this->getEntityManager()->remove($machine);
        $this->getEntityManager()->flush();
    }
}
