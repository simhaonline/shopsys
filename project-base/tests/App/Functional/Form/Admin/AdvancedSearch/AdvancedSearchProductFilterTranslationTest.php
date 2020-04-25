<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form\Admin\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchProductFilterTranslation;
use Tests\App\Test\FunctionalTestCase;

class AdvancedSearchProductFilterTranslationTest extends FunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchConfig
     */
    private $advancedSearchConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchProductFilterTranslation
     */
    private $advancedSearchProductFilterTranslation;

    public function setUp(): void
    {
        parent::setUp();
        $this->advancedSearchConfig = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchConfig::class);
        $this->advancedSearchProductFilterTranslation = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchProductFilterTranslation::class);
    }

    public function testTranslateFilterName()
    {
        foreach ($this->advancedSearchConfig->getAllFilters() as $filter) {
            $this->assertNotEmpty($this->advancedSearchProductFilterTranslation->translateFilterName($filter->getName()));
        }
    }

    public function testTranslateFilterNameNotFoundException()
    {
        $advancedSearchTranslator = new AdvancedSearchProductFilterTranslation();

        $this->expectException(\Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchTranslationNotFoundException::class);
        $advancedSearchTranslator->translateFilterName('nonexistingFilterName');
    }
}
