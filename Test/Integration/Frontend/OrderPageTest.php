<?php

declare(strict_types=1);

namespace Loki\QuoteExtendedExample\Test\Integration\Frontend;

use Loki\QuoteExtended\Test\Integration\Trait\QuoteExtendedTestTrait;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\TestCase\AbstractController;

#[
    DbIsolation(true),
    AppArea('frontend'),
    DataFixture(ProductFixture::class, as: 'product'),
    DataFixture(CustomerFixture::class, as: 'customer'),
    DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
    DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]),
    DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
]
class OrderPageTest extends AbstractController
{
    use QuoteExtendedTestTrait;

    private const PATTERN = '#data-role="loki-quote-extended-example-field">\s*<strong>Example field:</strong>\s*'
        . '<span>Hello &lt;b&gt;world&lt;/b&gt;</span>#';

    protected function tearDown(): void
    {
        $this->om()->get(CustomerSession::class)->logout();
        $this->om()->get(CheckoutSession::class)->clearStorage();
        parent::tearDown();
    }

    public function testOrderViewShowsValue(): void
    {
        $order = $this->placeOrderWithValue('Hello <b>world</b>');
        $this->loginCustomer();

        $this->dispatch('sales/order/view/order_id/' . (int)$order->getEntityId());

        $this->assertSame(200, $this->getResponse()->getHttpResponseCode());
        $this->assertMatchesRegularExpression(self::PATTERN, $this->getResponse()->getBody());
    }

    public function testOrderViewHidesEmptyValue(): void
    {
        $order = $this->placeOrderWithValue(null);
        $this->loginCustomer();

        $this->dispatch('sales/order/view/order_id/' . (int)$order->getEntityId());

        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString((string)$order->getIncrementId(), $body);
        $this->assertStringNotContainsString('loki-quote-extended-example-field', $body);
    }

    public function testSuccessPageShowsValue(): void
    {
        $order = $this->placeOrderWithValue('Hello <b>world</b>');
        $checkoutSession = $this->om()->get(CheckoutSession::class);
        $checkoutSession->setLastSuccessQuoteId((int)$order->getQuoteId());
        $checkoutSession->setLastQuoteId((int)$order->getQuoteId());
        $checkoutSession->setLastOrderId((int)$order->getEntityId());
        $checkoutSession->setLastRealOrderId($order->getIncrementId());

        $this->dispatch('checkout/onepage/success');

        $this->assertSame(200, $this->getResponse()->getHttpResponseCode());
        $this->assertMatchesRegularExpression(self::PATTERN, $this->getResponse()->getBody());
    }

    private function placeOrderWithValue(?string $value): OrderInterface
    {
        $cartId = (int)DataFixtureStorageManager::getStorage()->get('cart')->getId();
        if ($value !== null) {
            $this->saveQuoteExtended($cartId, ['example_field' => $value]);
        }

        return $this->placeOrder($cartId);
    }

    private function loginCustomer(): void
    {
        $customerId = (int)DataFixtureStorageManager::getStorage()->get('customer')->getId();
        $this->om()->get(CustomerSession::class)->loginById($customerId);
    }
}
