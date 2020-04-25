<?php

declare(strict_types=1);

namespace Tests\App\Smoke;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Tests\App\Test\FunctionalTestCase;

class AllFeedsTest extends FunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Feed\FeedFacade
     */
    private $feedFacade;

    public function setUp(): void
    {
        $this->feedFacade = $this->getTestContainer()->get(\Shopsys\FrameworkBundle\Model\Feed\FeedFacade::class);
    }

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    public function setUp(): void
    {
        $this->filesystem = $this->getTestContainer()->get(\League\Flysystem\FilesystemInterface::class);
    }

    /**
     * @return array
     */
    public function getAllFeedExportCreationData(): array
    {
        // Method setUp is called only before each test, data providers are called even before that
        $this->setUp();

        $data = [];
        foreach ($this->feedFacade->getFeedsInfo() as $feedInfo) {
            foreach ($this->domain->getAll() as $domainConfig) {
                $key = sprintf('feed "%s" on domain "%s"', $feedInfo->getName(), $domainConfig->getName());
                $data[$key] = [$feedInfo, $domainConfig];
            }
        }

        return $data;
    }

    public function testFeedIsExportable(): void
    {
        foreach ($this->getAllFeedExportCreationData() as $dataProvider) {
            /** @var \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface $feedInfo */
            $feedInfo = $dataProvider[0];
            /** @var \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig */
            $domainConfig = $dataProvider[1];

            $this->cleanUp($feedInfo, $domainConfig);

            $this->feedFacade->generateFeed($feedInfo->getName(), $domainConfig);

            $feedFilepath = $this->feedFacade->getFeedFilepath($feedInfo, $domainConfig);
            $this->assertTrue($this->filesystem->has($feedFilepath), 'Exported feed file exists.');

            $this->cleanUp($feedInfo, $domainConfig);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface $feedInfo
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    private function cleanUp(FeedInfoInterface $feedInfo, DomainConfig $domainConfig): void
    {
        $feedFilepath = $this->feedFacade->getFeedFilepath($feedInfo, $domainConfig);

        if ($this->filesystem->has($feedFilepath)) {
            $this->filesystem->delete($feedFilepath);
        }
    }
}
