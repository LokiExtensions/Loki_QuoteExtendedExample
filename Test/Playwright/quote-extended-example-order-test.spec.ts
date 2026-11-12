import {Field, PaymentMethod, PlaceOrderButton, ShippingMethod, SuccessPage} from '@loki-checkout/checkout-objects';
import {setupCheckout} from '@loki/setup-checkout';
import {test, expect} from '@loki/test';
import merge from '@loki/util/merge';
import type {Page} from '@playwright/test';
import moduleConfig, {themes, withTheme} from './config/config';

const FIELD_ID = 'loki-checkout-customer-example-field-field';
const ORDER_FIELD_SELECTOR = '[data-role="loki-quote-extended-example-field"]';

const fillFields = async (page: Page, fields: Record<string, string>) => {
    for (const [fieldName, fieldValue] of Object.entries(fields)) {
        const field = new Field(page, fieldName);
        await field.fill(fieldValue);
        await field.expectValue(fieldValue);
    }
};

const placeOrder = async (page: Page) => {
    await (new ShippingMethod(page, 'flatrate_flatrate')).select();
    await (new PaymentMethod(page, 'checkmo')).select();
    await (new PlaceOrderButton(page)).click();

    await new SuccessPage(page).expectToBeLoaded();
};

test.describe('Loki_QuoteExtendedExample order pages', () => {
    for (const theme of themes) {
        test(`shows the example field on the success page (${theme.name})`, async ({page, context}) => {
            await setupCheckout(page, context, withTheme(moduleConfig, theme));

            await fillFields(page, {
                'customer.email': 'jane@example.com',
                'shipping.firstname': 'Jane',
                'shipping.lastname': 'Doe',
                'shipping.city': 'Baarn',
                'shipping.street0': 'Amalialaan',
                'shipping.street1': '126',
                'shipping.telephone': '0123456789',
                [FIELD_ID]: 'Success value',
            });

            await placeOrder(page);

            const orderField = page.locator(ORDER_FIELD_SELECTOR);
            await expect(orderField).toHaveCount(1);
            await expect(orderField).toBeVisible();
            await expect(orderField).toContainText('Example field');
            await expect(orderField).toContainText('Success value');
        });

        test(`shows the example field on the customer order view (${theme.name})`, async ({page, context}) => {
            await setupCheckout(page, context, withTheme(merge(moduleConfig, {customer: {login: true}}), theme));

            await fillFields(page, {[FIELD_ID]: 'Account value'});

            await placeOrder(page);

            await expect(page.locator(ORDER_FIELD_SELECTOR)).toContainText('Account value');

            const orderLink = page.locator('a[href*="sales/order/view/order_id"]').first();
            await expect(orderLink).toBeAttached();
            await page.goto((await orderLink.getAttribute('href')) as string);
            await expect(page).toHaveURL(/sales\/order\/view\/order_id/);

            const orderField = page.locator(ORDER_FIELD_SELECTOR);
            await expect(orderField).toHaveCount(1);
            await expect(orderField).toBeVisible();
            await expect(orderField).toContainText('Example field');
            await expect(orderField).toContainText('Account value');
        });
    }
});
