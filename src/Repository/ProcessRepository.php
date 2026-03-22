<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Process;
use App\Entity\Machine;
use App\Repository\Interface\ProcessRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Process>
 */
class ProcessRepository extends ServiceEntityRepository implements ProcessRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Process::class);
    }

    public function save(Process $process): void
    {
        $this->getEntityManager()->persist($process);
        $this->getEntityManager()->flush();
    }

    public function remove(Process $process): void 
    {
        $this->getEntityManager()->remove($process);
        $this->getEntityManager()->flush();
    }

    public function findByMachine(Machine $machine): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.machine = :machine')
            ->setParameter('machine', $machine)
            ->getQuery()
            ->getResult();
    }

    public function getUnallocatedProcesses(): array 
    {
        return $this->createQueryBuilder('p')
            ->where('p.machine IS NULL')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Process[] Returns an array of Process objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Process
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
