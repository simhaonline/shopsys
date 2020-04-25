<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Vat;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Tests\App\Test\TransactionFunctionalTestCase;

class VatFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     */
    private $vatFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     */
    private $transportFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface
     */
    private $transportDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface
     */
    private $paymentDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     */
    private $paymentFacade;

    public function setUp(): void
    {
        parent::setUp();
        $this->vatFacade = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade::class);
        $this->transportFacade = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Transport\TransportFacade::class);
        $this->transportDataFactory = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface::class);
        $this->paymentDataFactory = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface::class);
        $this->paymentFacade = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Payment\PaymentFacade::class);
    }

    public function testDeleteByIdAndReplaceForFirstDomain()
    {
        $vatData = new VatData();
        $vatData->name = 'name';
        $vatData->percent = '10';
        $vatToDelete = $this->vatFacade->create($vatData, Domain::FIRST_DOMAIN_ID);

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatToReplaceWith */
        $vatToReplaceWith = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, Domain::FIRST_DOMAIN_ID);

        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $transportData = $this->transportDataFactory->createFromTransport($transport);

        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CASH);
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);

        $transportData->vatsIndexedByDomainId[Domain::FIRST_DOMAIN_ID] = $vatToDelete;
        $this->transportFacade->edit($transport, $transportData);

        $paymentData->vatsIndexedByDomainId[Domain::FIRST_DOMAIN_ID] = $vatToDelete;
        $this->paymentFacade->edit($payment, $paymentData);

        $this->vatFacade->deleteById($vatToDelete, $vatToReplaceWith);

        $this->em->refresh($transport->getTransportDomain(Domain::FIRST_DOMAIN_ID));
        $this->em->refresh($payment->getPaymentDomain(Domain::FIRST_DOMAIN_ID));

        $this->assertEquals($vatToReplaceWith, $payment->getPaymentDomain(Domain::FIRST_DOMAIN_ID)->getVat());
        $this->assertEquals($vatToReplaceWith, $transport->getTransportDomain(Domain::FIRST_DOMAIN_ID)->getVat());
    }
}
