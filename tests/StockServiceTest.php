<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

use App\Repository\StockRepository;
use App\Entity\Stock;

class StockServiceTest extends TestCase
{
    public function testGetStocks()
    {
        // Créer un double de remplacement pour le dépôt (repository)
        $repository = $this->createMock(StockRepository::class);

        // Créer un objet Stock simulé
        $stock1 = new Stock();
        $stock1->setStock(1);
        $stock1->setReste(0);
        $stock1->setLibelle('Stock 1');

        $stock2 = new Stock();
        $stock2->setStock(2);
        $stock2->setReste(0);
        $stock2->setLibelle('Stock 2');

        // Définir le comportement attendu du dépôt
        $repository->expects($this->once())
            ->method('findAll')
            ->willReturn([$stock1, $stock2]);

        // Appeler la méthode à tester
        $stocks = $repository->findAll();

        // Vérifier que le résultat est celui attendu
        $this->assertCount(2, $stocks);
        $this->assertEquals($stock1, $stocks[0]);
        $this->assertEquals($stock2, $stocks[1]);
    }
}
