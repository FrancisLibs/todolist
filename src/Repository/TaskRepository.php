<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return Tasks[]
     */
    public function findAdminTasks($user): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.user = :val')
            ->setParameter('val', $user)
            ->orWhere('t.user is NULL')
            ->andWhere('t.isDone = FALSE')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Tasks[]
     */
    public function findAdminDoneTasks($user): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.user = :val')
            ->setParameter('val', $user)
            ->orWhere('t.user is NULL')
            ->andWhere('t.isDone = TRUE')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Task[] Returns an array of Task objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Task
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
