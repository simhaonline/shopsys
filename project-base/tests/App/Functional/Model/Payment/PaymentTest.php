<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Payment;

use App\Model\Payment\Payment;
use App\Model\Transport\Transport;
use Tests\App\Test\TransactionFunctionalTestCase;

class PaymentTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface
     */
    private $paymentDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface
     */
    private $transportDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     */
    private $transportFacade;

    public function setUp(): void
    {
        parent::setUp();
        $this->transportDataFactory = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface::class);
        $this->paymentDataFactory = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface::class);
        $this->transportFacade = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Transport\TransportFacade::class);
    }

    public function testRemoveTransportFromPaymentAfterDelete()
    {
        $transportData = $this->transportDataFactory->create();
        $transportData->name['cs'] = 'name';
        $transport = new Transport($transportData);

        $paymentData = $this->paymentDataFactory->create();
        $paymentData->name['cs'] = 'name';

        $payment = new Payment($paymentData);
        $payment->addTransport($transport);

        $this->em->persist($transport);
        $this->em->persist($payment);
        $this->em->flush();

        $this->transportFacade->deleteById($transport->getId());

        $this->assertNotContains($transport, $payment->getTransports());
    }
}
