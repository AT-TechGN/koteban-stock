<?php

namespace App\Service;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class StatisticsCalculator
{
    private $entityManager;
    private $orderRepository;

    public function __construct(EntityManagerInterface $entityManager, OrderRepository $orderRepository)
    {
        $this->entityManager = $entityManager;
        $this->orderRepository = $orderRepository;
    }

    public function getTotalSales(): float
    {
        $qb = $this->orderRepository->createQueryBuilder('o')
            ->select('SUM(o.totalAmount)')
            ->where('o.status = :status')
            ->setParameter('status', 'delivered')
            ->setMaxResults(1);
        $result = $qb->getQuery()->getSingleScalarResult();
        return (float) ($result ?? 0.0);
    }

    public function getTotalRevenue(): float
    {
        $qb = $this->orderRepository->createQueryBuilder('o')
            ->select('SUM(o.totalAmount)')
            ->where('o.status IN (:statuses)')
            ->setParameter('statuses', ['delivered', 'pending'])
            ->setMaxResults(1);
        $result = $qb->getQuery()->getSingleScalarResult();
        return (float) ($result ?? 0.0);
    }

    public function getTopProducts(int $limit = 5): array
    {
        return []; // À implémenter selon vos besoins
    }

    public function getStockValue(): float
    {
        return 0.0; // À implémenter selon vos besoins
    }
}