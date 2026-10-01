<?php

declare(strict_types=1);

namespace Loki\QuoteExtendedExample\Test\Integration;

use Loki\Components\Config\XmlConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsEnabled;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsRegistered;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsRegisteredForReal;

class ModuleTest extends TestCase
{
    use AssertModuleIsEnabled;
    use AssertModuleIsRegistered;
    use AssertModuleIsRegisteredForReal;

    public function testModule(): void
    {
        $this->assertModuleIsEnabled('Loki_QuoteExtendedExample');
        $this->assertModuleIsRegistered('Loki_QuoteExtendedExample');
        $this->assertModuleIsRegisteredForReal('Loki_QuoteExtendedExample');
        $this->assertModuleIsEnabled('Loki_QuoteExtended');
    }

    public function testColumnExists(): void
    {
        $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $columns = $resource->getConnection()->describeTable($resource->getTableName('loki_quote_extended'));

        $this->assertArrayHasKey('example_field', $columns);
        $this->assertSame('varchar', $columns['example_field']['DATA_TYPE']);
        $this->assertSame(255, (int)$columns['example_field']['LENGTH']);
        $this->assertTrue((bool)$columns['example_field']['NULLABLE']);
    }

    public function testComponentUsesQuoteExtendedGroup(): void
    {
        $components = (array)Bootstrap::getObjectManager()->get(XmlConfig::class)->get('components');

        $this->assertArrayHasKey('loki-checkout.customer.example_field', $components);
        $this->assertSame('quoteExtendedFields', $components['loki-checkout.customer.example_field']['group']);
    }
}
