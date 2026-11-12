<?php

declare(strict_types=1);

namespace Loki\QuoteExtendedExample\Test\Integration\Adminhtml;

use Loki\QuoteExtended\Test\Integration\Trait\QuoteExtendedTestTrait;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Test\Fixture\Creditmemo as CreditmemoFixture;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\TestCase\AbstractBackendController;

class SalesViewTest extends AbstractBackendController
{
    use QuoteExtendedTestTrait;

    private const ROW_PATTERN = '#<tr>\s*<th>Example field</th>\s*<td>Hello &lt;b&gt;world&lt;/b&gt;</td>\s*</tr>#';

    #[
        DbIsolation(true),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], as: 'order'),
    ]
    public function testOrderViewShowsValue(): void
    {
        $order = $this->getOrderWithValue();

        $this->dispatch('backend/sales/order/view/order_id/' . (int)$order->getEntityId());

        $this->assertMatchesRegularExpression(self::ROW_PATTERN, $this->getResponse()->getBody());
    }

    #[
        DbIsolation(true),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], as: 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], as: 'invoice'),
    ]
    public function testInvoiceViewShowsValue(): void
    {
        $this->getOrderWithValue();
        $invoice = DataFixtureStorageManager::getStorage()->get('invoice');

        $this->dispatch('backend/sales/invoice/view/invoice_id/' . (int)$invoice->getId());

        $this->assertMatchesRegularExpression(self::ROW_PATTERN, $this->getResponse()->getBody());
    }

    #[
        DbIsolation(true),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], as: 'order'),
        DataFixture(ShipmentFixture::class, ['order_id' => '$order.id$'], as: 'shipment'),
    ]
    public function testShipmentViewShowsValue(): void
    {
        $this->getOrderWithValue();
        $shipment = DataFixtureStorageManager::getStorage()->get('shipment');

        $this->dispatch('backend/admin/order_shipment/view/shipment_id/' . (int)$shipment->getId());

        $this->assertMatchesRegularExpression(self::ROW_PATTERN, $this->getResponse()->getBody());
    }

    #[
        DbIsolation(true),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], as: 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], as: 'invoice'),
        DataFixture(CreditmemoFixture::class, ['order_id' => '$order.id$'], as: 'creditmemo'),
    ]
    public function testCreditmemoViewShowsValue(): void
    {
        $this->getOrderWithValue();
        $creditmemo = DataFixtureStorageManager::getStorage()->get('creditmemo');

        $this->dispatch('backend/sales/creditmemo/view/creditmemo_id/' . (int)$creditmemo->getId());

        $this->assertMatchesRegularExpression(self::ROW_PATTERN, $this->getResponse()->getBody());
    }

    #[
        DbIsolation(true),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], as: 'order'),
    ]
    public function testOrderViewHidesEmptyValue(): void
    {
        $order = DataFixtureStorageManager::getStorage()->get('order');

        $this->dispatch('backend/sales/order/view/order_id/' . (int)$order->getEntityId());

        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('Account Information', $body);
        $this->assertStringNotContainsString('<th>Example field</th>', $body);
    }

    private function getOrderWithValue(): OrderInterface
    {
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $this->saveQuoteExtended((int)$order->getQuoteId(), [
            'order_id' => (int)$order->getEntityId(),
            'example_field' => 'Hello <b>world</b>',
        ]);

        return $order;
    }
}
