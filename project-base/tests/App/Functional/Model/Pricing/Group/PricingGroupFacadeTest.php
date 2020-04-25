<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Pricing\Group;

use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPrice;
use Tests\App\Test\TransactionFunctionalTestCase;

class PricingGroupFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade
     */
    private $pricingGroupFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculator
     */
    private $productPriceRecalculator;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     */
    private $customerUserFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface
     */
    private $customerUserDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface
     */
    private $customerUserUpdateDataFactory;

    public function setUp(): void
    {
        parent::setUp();
        $this->pricingGroupFacade = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade::class);
        $this->productPriceRecalculator = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculator::class);
        $this->customerUserFacade = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade::class);
        $this->customerUserDataFactory = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface::class);
        $this->customerUserUpdateDataFactory = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface::class);
    }

    public function testCreate()
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'pricing_group_name';
        $domainId = Domain::FIRST_DOMAIN_ID;
        $pricingGroup = $this->pricingGroupFacade->create($pricingGroupData, $domainId);
        $this->productPriceRecalculator->runAllScheduledRecalculations();
        $productCalculatedPrice = $this->em->getRepository(ProductCalculatedPrice::class)->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroup,
        ]);

        $this->assertNotNull($productCalculatedPrice);
    }

    public function testDeleteAndReplace()
    {
        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'name';
        $pricingGroupToDelete = $this->pricingGroupFacade->create($pricingGroupData, Domain::FIRST_DOMAIN_ID);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroupToReplaceWith */
        $pricingGroupToReplaceWith = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserFacade->getCustomerUserById(1);

        $customerUserData = $this->customerUserDataFactory->createFromCustomerUser($customerUser);
        $customerUserData->pricingGroup = $pricingGroupToDelete;
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->create();
        $customerUserUpdateData->customerUserData = $customerUserData;
        $this->customerUserFacade->editByAdmin($customerUser->getId(), $customerUserUpdateData);

        $this->pricingGroupFacade->delete($pricingGroupToDelete->getId(), $pricingGroupToReplaceWith->getId());

        $this->em->refresh($customerUser);

        $this->assertEquals($pricingGroupToReplaceWith, $customerUser->getPricingGroup());
    }
}
