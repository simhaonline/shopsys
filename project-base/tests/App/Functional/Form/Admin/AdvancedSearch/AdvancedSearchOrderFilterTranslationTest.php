<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form\Admin\AdvancedSearch;

use Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOrderFilterTranslation;
use Tests\App\Test\FunctionalTestCase;

class AdvancedSearchOrderFilterTranslationTest extends FunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\AdvancedSearch\OrderAdvancedSearchConfig
     */
    private $advancedSearchConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOrderFilterTranslation
     */
    private $advancedSearchOrderFilterTranslation;

    public function setUp(): void
    {
        $this->advancedSearchConfig = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\AdvancedSearch\OrderAdvancedSearchConfig::class);
        $this->advancedSearchOrderFilterTranslation = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOrderFilterTranslation::class);
    }

    public function testTranslateFilterName()
    {
        foreach ($this->advancedSearchConfig->getAllFilters() as $filter) {
            $this->assertNotEmpty($this->advancedSearchOrderFilterTranslation->translateFilterName($filter->getName()));
        }
    }

    public function testTranslateFilterNameNotFoundException()
    {
        $advancedSearchTranslator = new AdvancedSearchOrderFilterTranslation();

        $this->expectException(\Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception\AdvancedSearchTranslationNotFoundException::class);
        $advancedSearchTranslator->translateFilterName('nonexistingFilterName');
    }
}
