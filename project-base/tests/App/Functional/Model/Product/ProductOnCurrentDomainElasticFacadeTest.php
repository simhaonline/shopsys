<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;

class ProductOnCurrentDomainElasticFacadeTest extends ProductOnCurrentDomainFacadeTest
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    public function getProductOnCurrentDomainFacade(): ProductOnCurrentDomainFacadeInterface
    {
        return $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade::class);
    }
}
