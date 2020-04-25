<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;

abstract class TransactionFunctionalTestCase extends FunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    protected $em;

    protected function setUp(): void
    {
        $this->em = $this->getTestContainer()->get(EntityManagerDecorator::class);

        parent::setUp();

        $this->em->beginTransaction();
        $this->em->getConnection()->setAutoCommit(false);
    }

    protected function tearDown(): void
    {
        $this->em->rollback();

        parent::tearDown();
    }
}
