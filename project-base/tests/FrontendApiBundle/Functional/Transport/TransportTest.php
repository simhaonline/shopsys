<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Transport;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class TransportTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     */
    protected $transportFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    protected $transport;

    protected function setUp(): void
    {
        $this->transportFacade = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Transport\TransportFacade::class);
        $this->transport = $this->transportFacade->getById(2);

        parent::setUp();
    }

    public function testTransportNameByUuid(): void
    {
        $query = '
            query {
                transport(uuid: "' . $this->transport->getUuid() . '") {
                    name
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'transport' => [
                    'name' => t('PPL', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
