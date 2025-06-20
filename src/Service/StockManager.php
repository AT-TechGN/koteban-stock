<?php

namespace App\Service;

use App\Entity\Variant;
use Doctrine\ORM\EntityManagerInterface;

class StockManager
{
    private $entityManager;
    private $lowStockThreshold;

    public function __construct(EntityManagerInterface $entityManager, int $lowStockThreshold)
    {
        $this->entityManager = $entityManager;
        $this->lowStockThreshold = $lowStockThreshold;
    }

    public function updateStock(Variant $variant, int $quantity, string $type = 'add'): void
    {
        $currentQuantity = $variant->getStockQuantity();
        if ($type === 'add') {
            $variant->setStockQuantity($currentQuantity + $quantity);
        } elseif ($type === 'remove') {
            $newQuantity = max(0, $currentQuantity - $quantity);
            $variant->setStockQuantity($newQuantity);
        }

        $this->entityManager->persist($variant);
        $this->entityManager->flush();
    }

    public function getLowStockVariants(): array
    {
        return $this->entityManager->getRepository(Variant::class)->findByLowStock($this->lowStockThreshold);
    }
}