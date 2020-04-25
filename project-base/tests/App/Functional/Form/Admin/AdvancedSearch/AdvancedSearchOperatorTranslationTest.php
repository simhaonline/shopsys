<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form\Admin\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation;
use Tests\App\Test\FunctionalTestCase;

class AdvancedSearchOperatorTranslationTest extends FunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchConfig
     */
    private $productAdvancedSearchConfig;

    public function setUp(): void
    {
        parent::setUp();
        $this->productAdvancedSearchConfig = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchConfig::class);
        $this->advancedSearchOperatorTranslation = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation::class);
        $this->orderAdvancedSearchConfig = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\AdvancedSearch\OrderAdvancedSearchConfig::class);
    }

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdvancedSearch\OrderAdvancedSearchConfig
     */
    private $orderAdvancedSearchConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOperatorTranslation
     */
    private $advancedSearchOperatorTranslation;

    public function testTranslateOperator()
    {
        $operators = [];
        foreach ($this->productAdvancedSearchConfig->getAllFilters() as $filter) {
            $operators = array_merge($operators, $filter->getAllowedOperators());
        }
        foreach ($this->orderAdvancedSearchConfig->getAllFilters() as $filter) {
            $operators = array_merge($operators, $filter->getAllowedOperators());
        }

        foreach ($operators as $operator) {
            $this->assertNotEmpty($this->advancedSearchOperatorTranslation->translateOperator($operator));
        }
    }

    public function testTranslateOperatorNotFoundException()
    {
        $advancedSearchTranslator = new AdvancedSearchOperatorTranslation();

        $this->expectException(\Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchTranslationNotFoundException::class);
        $advancedSearchTranslator->translateOperator('nonexistingOperator');
    }
}
