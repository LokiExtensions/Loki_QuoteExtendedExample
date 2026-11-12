<?php

declare(strict_types=1);

namespace Loki\QuoteExtendedExample\Test\Integration\Email;

use Loki\QuoteExtended\Test\Integration\Trait\QuoteExtendedTestTrait;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

#[
    DbIsolation(true),
    AppArea('frontend'),
    DataFixture(ProductFixture::class, as: 'product'),
    DataFixture(GuestCartFixture::class, as: 'cart'),
    DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]),
    DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
]
class SalesEmailTest extends TestCase
{
    use QuoteExtendedTestTrait;

    private const VALUE = 'Hello <b>world</b>';
    private const ESCAPED_VALUE = 'Hello &lt;b&gt;world&lt;/b&gt;';

    public function testOrderEmailShowsValue(): void
    {
        $cartId = $this->getCartId();
        $this->saveQuoteExtended($cartId, ['example_field' => self::VALUE]);
        $order = $this->placeOrder($cartId);

        $body = $this->sendAndGetBody(fn () => $this->get(OrderSender::class)->send($order, true));

        $this->assertStringContainsString('Example field', $body);
        $this->assertStringContainsString(self::ESCAPED_VALUE, $body);
        $this->assertStringNotContainsString(self::VALUE, $body);
    }

    public function testInvoiceEmailShowsValue(): void
    {
        $cartId = $this->getCartId();
        $this->saveQuoteExtended($cartId, ['example_field' => self::VALUE]);
        $invoice = $this->createInvoice($this->placeOrder($cartId));

        $body = $this->sendAndGetBody(fn () => $this->get(InvoiceSender::class)->send($invoice, true));

        $this->assertStringContainsString('Example field', $body);
        $this->assertStringContainsString(self::ESCAPED_VALUE, $body);
    }

    public function testShipmentEmailShowsValue(): void
    {
        $cartId = $this->getCartId();
        $this->saveQuoteExtended($cartId, ['example_field' => self::VALUE]);
        $shipment = $this->createShipment($this->placeOrder($cartId));

        $body = $this->sendAndGetBody(fn () => $this->get(ShipmentSender::class)->send($shipment, true));

        $this->assertStringContainsString('Example field', $body);
        $this->assertStringContainsString(self::ESCAPED_VALUE, $body);
        $this->assertStringNotContainsString(self::VALUE, $body);
    }

    public function testCreditmemoEmailShowsValue(): void
    {
        $cartId = $this->getCartId();
        $this->saveQuoteExtended($cartId, ['example_field' => self::VALUE]);
        $order = $this->placeOrder($cartId);
        $this->createInvoice($order);
        $creditmemo = $this->createCreditmemo($order);

        $body = $this->sendAndGetBody(fn () => $this->get(CreditmemoSender::class)->send($creditmemo, true));

        $this->assertStringContainsString('Example field', $body);
        $this->assertStringContainsString(self::ESCAPED_VALUE, $body);
        $this->assertStringNotContainsString(self::VALUE, $body);
    }

    public function testShipmentEmailHidesEmptyValue(): void
    {
        $shipment = $this->createShipment($this->placeOrder($this->getCartId()));

        $body = $this->sendAndGetBody(fn () => $this->get(ShipmentSender::class)->send($shipment, true));

        $this->assertStringNotContainsString('Example field', $body);
    }

    public function testOrderEmailHidesEmptyValue(): void
    {
        $order = $this->placeOrder($this->getCartId());

        $body = $this->sendAndGetBody(fn () => $this->get(OrderSender::class)->send($order, true));

        $this->assertStringNotContainsString('Example field', $body);
    }

    private function sendAndGetBody(callable $send): string
    {
        $transportBuilder = $this->get(TransportBuilderMock::class);
        $transportBuilder->clean();

        $this->assertTrue($send());

        $message = $transportBuilder->getSentMessage();
        $this->assertNotNull($message, 'No email was sent');

        return quoted_printable_decode($message->getBodyText());
    }

    private function get(string $className): object
    {
        return Bootstrap::getObjectManager()->get($className);
    }

    private function getCartId(): int
    {
        return (int)DataFixtureStorageManager::getStorage()->get('cart')->getId();
    }
}
