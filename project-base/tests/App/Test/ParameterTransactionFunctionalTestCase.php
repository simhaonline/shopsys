<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ParameterTransactionFunctionalTestCase extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade
     */
    protected $parameterFacade;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parameterFacade = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade::class);
    }

    /**
     * @param string $parameterValueNameId
     * @return int
     */
    protected function getParameterValueIdForFirstDomain(string $parameterValueNameId): int
    {
        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        $parameterValueTranslatedName = t($parameterValueNameId, [], 'dataFixtures', $firstDomainLocale);

        return $this->parameterFacade->getParameterValueByValueTextAndLocale($parameterValueTranslatedName, $firstDomainLocale)->getId();
    }
}
