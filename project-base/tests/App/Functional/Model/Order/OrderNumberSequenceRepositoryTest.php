<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order;

use Tests\App\Test\TransactionFunctionalTestCase;

class OrderNumberSequenceRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository
     */
    private $orderNumberSequenceRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->orderNumberSequenceRepository = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository::class);
    }

    public function testGetNextNumber()
    {
        $numbers = [];
        for ($i = 0; $i < 10; $i++) {
            $numbers[] = $this->orderNumberSequenceRepository->getNextNumber();
        }

        $uniqueNumbers = array_unique($numbers);

        $this->assertSame(count($numbers), count($uniqueNumbers));
    }
}
