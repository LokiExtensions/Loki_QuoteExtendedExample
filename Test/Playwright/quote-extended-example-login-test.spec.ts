import {Field} from '@loki-checkout/checkout-objects';
import {setupCheckout} from '@loki/setup-checkout';
import {test, expect} from '@loki/test';
import merge from '@loki/util/merge';
import type {Page} from '@playwright/test';
import moduleConfig, {themes, withTheme} from './config/config';

const FIELD_ID = 'loki-checkout-customer-example-field-field';
const PREVIEW_ID = 'loki-checkout-sidebar-preview-quote-details';
const PREVIEW_LABEL = 'Example field:';

const createCustomer = (themeName: string) => ({
    email: `quote-extended-${themeName.replace(/\W/g, '').toLowerCase()}-${Date.now()}@example.com`,
    password: moduleConfig.customer.password,
});

const loginViaCheckoutModal = async (page: Page, email: string, password: string) => {
    await page.locator('#loki-checkout-login-form-button').click();

    const modal = page.locator('#loki-checkout-login-form-modal');
    await expect(modal).toBeVisible();

    await page.locator('#loki-checkout-login-form-inner-email').fill(email);
    await page.locator('#loki-checkout-login-form-inner-password').fill(password);

    const button = page.locator('#loki-checkout-login-form-inner-button');
    await expect(button).toBeEnabled();
    await button.click();

    await expect(page.locator('#loki-checkout-login')).toContainText(/You are logged in as/);
};

test.describe('Loki_QuoteExtendedExample login during checkout', () => {
    for (const theme of themes) {
        test(`keeps the guest value after logging in as a customer without a cart (${theme.name})`, async ({page, context}) => {
            const customer = createCustomer(theme.name);
            await setupCheckout(page, context, withTheme(merge(moduleConfig, {customer}), theme));

            await new Field(page, 'customer.email').fill(customer.email);
            await new Field(page, FIELD_ID).fill('Guest value');
            await expect(page.getById(PREVIEW_ID)).toContainText(`${PREVIEW_LABEL} Guest value`);

            await loginViaCheckoutModal(page, customer.email, customer.password);

            await page.reload();
            await expect(page.locator('#loki-checkout-login')).toContainText(/You are logged in as/);
            await new Field(page, FIELD_ID).expectValue('Guest value');
            await expect(page.getById(PREVIEW_ID)).toContainText(`${PREVIEW_LABEL} Guest value`);
        });

        test(`keeps the guest value after logging in as a customer with an existing cart (${theme.name})`, async ({page, context, browser}) => {
            const customer = createCustomer(theme.name);
            const customerConfig = withTheme(merge(moduleConfig, {customer}), theme);

            const customerContext = await browser.newContext({baseURL: process.env.TEST_URL, ignoreHTTPSErrors: true});
            try {
                const newPage = await customerContext.newPage();
                const customerPage = Object.assign(newPage, {
                    getById: (id: string) => newPage.locator(`#${id}`),
                    setCatchErrors: () => {},
                });
                await setupCheckout(customerPage as any, customerContext, merge(customerConfig, {customer: {login: true}}));
                await new Field(customerPage, FIELD_ID).fill('Customer value');
                await new Field(customerPage, FIELD_ID).expectValue('Customer value');
                await expect(customerPage.locator(`#${PREVIEW_ID}`)).toContainText(`${PREVIEW_LABEL} Customer value`);
            } finally {
                await customerContext.close();
            }

            await setupCheckout(page, context, customerConfig);

            await new Field(page, 'customer.email').fill(customer.email);
            await new Field(page, FIELD_ID).fill('Guest value');
            await expect(page.getById(PREVIEW_ID)).toContainText(`${PREVIEW_LABEL} Guest value`);

            await loginViaCheckoutModal(page, customer.email, customer.password);

            await page.reload();
            await expect(page.locator('#loki-checkout-login')).toContainText(/You are logged in as/);
            await new Field(page, FIELD_ID).expectValue('Guest value');
            await expect(page.getById(PREVIEW_ID)).toContainText(`${PREVIEW_LABEL} Guest value`);
        });
    }
});
