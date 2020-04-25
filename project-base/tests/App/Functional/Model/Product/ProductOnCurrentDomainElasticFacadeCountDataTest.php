<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;

class ProductOnCurrentDomainElasticFacadeCountDataTest extends ProductOnCurrentDomainFacadeCountDataTest
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    public function getProductOnCurrentDomainFacade(): ProductOnCurrentDomainFacadeInterface
    {
        return $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade::class);
    }
}
