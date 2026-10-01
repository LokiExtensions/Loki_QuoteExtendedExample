<?php

declare(strict_types=1);

namespace Loki\QuoteExtendedExample\Test\Integration\Checkout;

use Loki\Components\Util\Controller\LayoutLoader;
use Loki\Components\Util\IdConvertor;
use Loki\QuoteExtended\Component\QuoteExtendedField\QuoteExtendedFieldRepository;
use Loki\QuoteExtended\Test\Integration\Trait\QuoteExtendedTestTrait;
use LokiCheckout\Core\Config\Config;
use LokiCheckout\Core\Test\Integration\LokiCheckoutTestCase;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;

#[
    DbIsolation(true),
    AppArea('frontend'),
    DataFixture(ProductFixture::class, as: 'product'),
    DataFixture(GuestCartFixture::class, as: 'cart'),
    DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
]
class ExampleFieldTest extends LokiCheckoutTestCase
{
    use QuoteExtendedTestTrait;

    private const BLOCK_NAME = 'loki-checkout.customer.example_field';
    private const GENDER_BLOCK_NAME = 'loki-checkout.customer.customer_gender';
    private const SIDEBAR_LABEL = '<span>Example field</span>:';

    #[ConfigFixture('customer/address/gender_show', 'opt', 'store', 'default')]
    #[ConfigFixture('customer/address/gender_show', 'opt')]
    public function testFieldIsPlacedAfterGender(): void
    {
        $this->getCart();
        $layout = $this->getObjectManager()->get(LayoutLoader::class)->load($this->getCheckoutHandles());

        $childNames = array_values($layout->getChildNames('loki-checkout.customer'));
        $genderPosition = array_search(self::GENDER_BLOCK_NAME, $childNames, true);
        $fieldPosition = array_search(self::BLOCK_NAME, $childNames, true);

        $this->assertIsInt($genderPosition, 'Gender block not found');
        $this->assertIsInt($fieldPosition, 'Example field block not found');
        $this->assertSame($genderPosition + 1, $fieldPosition);
    }

    public function testComponentSavesToQuoteExtendedTable(): void
    {
        $cart = $this->getCart();
        $component = $this->getCheckoutComponent(self::BLOCK_NAME);

        $this->assertInstanceOf(QuoteExtendedFieldRepository::class, $component->getRepository());
        $this->assertFalse($component->getViewModel()->isRequired());

        $this->assertRepositorySave($component, 'foobar');

        $row = $this->getQuoteExtendedRepository()->getByQuoteId((int)$cart->getId());
        $this->assertSame('foobar', $row->getData('example_field'));
    }

    #[ConfigFixture('customer/address/gender_show', 'opt', 'store', 'default')]
    #[ConfigFixture('customer/address/gender_show', 'opt')]
    public function testCheckoutShowsFieldAndSidebarValue(): void
    {
        $cart = $this->getCart();
        $this->saveQuoteExtended((int)$cart->getId(), ['example_field' => 'Hello <b>world</b>']);

        $this->dispatchToCheckout($cart);
        $body = $this->getResponse()->getBody();

        $idConvertor = $this->getObjectManager()->get(IdConvertor::class);
        $genderId = $idConvertor->toElementId(self::GENDER_BLOCK_NAME);
        $fieldId = $idConvertor->toElementId(self::BLOCK_NAME);

        $this->assertStringContainsString('id="' . $fieldId . '"', $body);
        $this->assertLessThan(
            strpos($body, 'id="' . $fieldId . '"'),
            strpos($body, 'id="' . $genderId . '"')
        );
        $this->assertStringContainsString('maxlength="255"', $this->getFieldInputHtml($body));

        $this->assertStringContainsString(self::SIDEBAR_LABEL, $body);
        $this->assertStringContainsString('Hello &lt;b&gt;world&lt;/b&gt;', $body);
        $this->assertStringNotContainsString('Hello <b>world</b>', $body);
    }

    public function testSidebarHidesEmptyValue(): void
    {
        $this->dispatchToCheckout();

        $this->assertStringNotContainsString(self::SIDEBAR_LABEL, $this->getResponse()->getBody());
    }

    private function getFieldInputHtml(string $body): string
    {
        $this->assertSame(1, preg_match('/<input[^>]*name="example_field"[^>]*>/', $body, $match));

        return $match[0];
    }

    private function getCheckoutHandles(): array
    {
        return [
            'default',
            'loki_components',
            'loki_field_components',
            'loki_checkout',
            'loki_checkout_skin_' . $this->getObjectManager()->get(Config::class)->getSkin(),
        ];
    }
}
