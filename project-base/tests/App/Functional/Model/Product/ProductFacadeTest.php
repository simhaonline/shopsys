<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use App\DataFixtures\Demo\AvailabilityDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use ReflectionClass;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler
     */
    private $productPriceRecalculationScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     */
    private $vatFacade;

    public function setUp(): void
    {
        parent::setUp();
        $this->productFacade = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Product\ProductFacade::class);
        $this->productPriceRecalculationScheduler = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler::class);
        $this->productDataFactory = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface::class);
        $this->vatFacade = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade::class);
    }

    /**
     * @dataProvider getTestHandleOutOfStockStateDataProvider
     * @param mixed $hidden
     * @param mixed $sellingDenied
     * @param mixed $stockQuantity
     * @param mixed $outOfStockAction
     * @param mixed $calculatedHidden
     * @param mixed $calculatedSellingDenied
     */
    public function testHandleOutOfStockState(
        $hidden,
        $sellingDenied,
        $stockQuantity,
        $outOfStockAction,
        $calculatedHidden,
        $calculatedSellingDenied
    ) {
        $productData = $this->productDataFactory->create();
        $productData->hidden = $hidden;
        $productData->sellingDenied = $sellingDenied;
        $productData->stockQuantity = $stockQuantity;
        $productData->outOfStockAction = $outOfStockAction;
        $productData->usingStock = true;
        $productData->availability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_IN_STOCK);
        $productData->outOfStockAvailability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK);
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $this->setVats($productData);

        $product = $this->productFacade->create($productData);

        $this->em->clear();

        $productFromDb = $this->productFacade->getById($product->getId());

        $this->assertSame($productFromDb->getCalculatedHidden(), $calculatedHidden);
        $this->assertSame($calculatedSellingDenied, $productFromDb->getCalculatedSellingDenied());
    }

    public function getTestHandleOutOfStockStateDataProvider()
    {
        return [
            [
                'hidden' => true,
                'sellingDenied' => true,
                'stockQuantity' => 0,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY,
                'calculatedHidden' => true,
                'calculatedSellingDenied' => true,
            ],
            [
                'hidden' => false,
                'sellingDenied' => false,
                'stockQuantity' => 0,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY,
                'calculatedHidden' => false,
                'calculatedSellingDenied' => false,
            ],
            [
                'hidden' => true,
                'sellingDenied' => false,
                'stockQuantity' => 0,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY,
                'calculatedHidden' => true,
                'calculatedSellingDenied' => false,
            ],
            [
                'hidden' => false,
                'sellingDenied' => true,
                'stockQuantity' => 0,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY,
                'calculatedHidden' => false,
                'calculatedSellingDenied' => true,
            ],
            [
                'hidden' => false,
                'sellingDenied' => false,
                'stockQuantity' => 0,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_EXCLUDE_FROM_SALE,
                'calculatedHidden' => false,
                'calculatedSellingDenied' => true,
            ],
            [
                'hidden' => false,
                'sellingDenied' => false,
                'stockQuantity' => 0,
                'outOfStockAction' => Product::OUT_OF_STOCK_ACTION_HIDE,
                'calculatedHidden' => true,
                'calculatedSellingDenied' => false,
            ],
        ];
    }

    public function testEditMarkProductForVisibilityRecalculation()
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        $reflectionClass = new ReflectionClass(Product::class);
        $reflectionPropertyRecalculateVisibility = $reflectionClass->getProperty('recalculateVisibility');
        $reflectionPropertyRecalculateVisibility->setAccessible(true);
        $reflectionPropertyRecalculateVisibility->setValue($product, false);

        $this->productFacade->edit($product->getId(), $this->productDataFactory->createFromProduct($product));

        $this->assertSame(true, $reflectionPropertyRecalculateVisibility->getValue($product));
    }

    public function testEditSchedulesPriceRecalculation()
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $productId = $product->getId();

        $productData = $this->productDataFactory->create();
        $this->setVats($productData);

        $this->productFacade->edit($productId, $productData);

        $this->assertArrayHasKey($productId, $this->productPriceRecalculationScheduler->getProductsForImmediateRecalculation());
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function setVats(ProductData $productData): void
    {
        $productVatsIndexedByDomainId = [];
        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
    }
}
