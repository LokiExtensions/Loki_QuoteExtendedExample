import {Field, PaymentMethod, PlaceOrderButton, ShippingMethod, SuccessPage} from '@loki-checkout/checkout-objects';
import {setupCheckout} from '@loki/setup-checkout';
import {test, expect} from '@loki/test';
import moduleConfig, {themes, withTheme} from './config/config';

const FIELD_ID = 'loki-checkout-customer-example-field-field';
const FIELD_COMPONENT_ID = 'loki-checkout-customer-example-field';
const GENDER_COMPONENT_ID = 'loki-checkout-customer-customer-gender';
const PREVIEW_ID = 'loki-checkout-sidebar-preview-quote-details';
const PREVIEW_LABEL = 'Example field:';

test.describe('Loki_QuoteExtendedExample test', () => {
    for (const theme of themes) {
        test(`shows the example field after the gender field (${theme.name})`, async ({page, context}) => {
            await setupCheckout(page, context, withTheme(moduleConfig, theme));

            const input = page.getById(FIELD_ID);
            await expect(input).toBeVisible();
            await expect(input).toHaveAttribute('maxlength', '255');
            await expect(input).not.toHaveAttribute('required');
            await expect(page.locator(`label[for="${FIELD_ID}"]`)).toContainText('Example field');

            const isAfterGender = await page.evaluate(([genderId, fieldId]) => {
                const gender = document.getElementById(genderId);
                const field = document.getElementById(fieldId);
                if (!gender || !field) {
                    return false;
                }

                return (gender.compareDocumentPosition(field) & Node.DOCUMENT_POSITION_FOLLOWING) > 0;
            }, [GENDER_COMPONENT_ID, FIELD_COMPONENT_ID]);
            expect(isAfterGender).toBe(true);
        });

        test(`shows the saved value in the sidebar (${theme.name})`, async ({page, context}) => {
            await setupCheckout(page, context, withTheme(moduleConfig, theme));

            const preview = page.getById(PREVIEW_ID);
            await expect(preview).not.toContainText(PREVIEW_LABEL);

            const field = new Field(page, FIELD_ID);
            await field.fill('Hello world');
            await field.expectValue('Hello world');

            await expect(preview).toContainText(`${PREVIEW_LABEL} Hello world`);
        });

        test(`keeps the value after reloading the checkout (${theme.name})`, async ({page, context}) => {
            await setupCheckout(page, context, withTheme(moduleConfig, theme));

            await new Field(page, FIELD_ID).fill('Persisted value');

            await page.reload();

            await new Field(page, FIELD_ID).expectValue('Persisted value');
            await expect(page.getById(PREVIEW_ID)).toContainText(`${PREVIEW_LABEL} Persisted value`);
        });

        test(`hides the sidebar value when the field is emptied (${theme.name})`, async ({page, context}) => {
            await setupCheckout(page, context, withTheme(moduleConfig, theme));

            const field = new Field(page, FIELD_ID);
            const preview = page.getById(PREVIEW_ID);

            await field.fill('Temporary value');
            await expect(preview).toContainText(PREVIEW_LABEL);

            await field.fill('');
            await expect(preview).not.toContainText(PREVIEW_LABEL);
        });

        test(`escapes HTML in the sidebar value (${theme.name})`, async ({page, context}) => {
            await setupCheckout(page, context, withTheme(moduleConfig, theme));

            await new Field(page, FIELD_ID).fill('<b>bold</b>');

            const preview = page.getById(PREVIEW_ID);
            await expect(preview).toContainText(PREVIEW_LABEL);
            await expect(preview.locator('b', {hasText: 'bold'})).toHaveCount(0);
        });

        test(`places an order with the example field (${theme.name})`, async ({page, context}) => {
            await setupCheckout(page, context, withTheme(moduleConfig, theme));

            const fields = {
                'customer.email': 'jane@example.com',
                'shipping.firstname': 'Jane',
                'shipping.lastname': 'Doe',
                'shipping.city': 'Baarn',
                'shipping.street0': 'Amalialaan',
                'shipping.street1': '126',
                'shipping.telephone': '0123456789',
                [FIELD_ID]: 'Order value',
            };

            for (const [fieldName, fieldValue] of Object.entries(fields)) {
                const field = new Field(page, fieldName);
                await field.fill(fieldValue);
                await field.expectValue(fieldValue);
            }

            await (new ShippingMethod(page, 'flatrate_flatrate')).select();
            await (new PaymentMethod(page, 'checkmo')).select();
            await (new PlaceOrderButton(page)).click();

            await new SuccessPage(page).expectToBeLoaded();
        });
    }
});
