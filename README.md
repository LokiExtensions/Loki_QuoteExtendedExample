# Loki_QuoteExtendedExample

<!-- badges.specs.start -->
<div class="github-specs-badges">

![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/loki-checkout/magento2-quote-extended-example)

</div>
<!-- badges.specs.end -->


## Installation
Install this package via composer:
```bash
composer require loki-checkout/magento2-quote-extended-example
```

Next, enable this module:
```bash
bin/magento module:enable Loki_QuoteExtendedExample
```


## Usage
Example module for `Loki_QuoteExtended`. It adds a column `example_field` to the table `loki_quote_extended` and shows it:

- as an optional field in the Loki Checkout, below the customer gender;
- in the checkout sidebar (when filled in);
- in the order, invoice, shipment and credit memo emails (this module overrides the 8 core templates `sales_email_order_template`, `sales_email_order_guest_template`, `sales_email_invoice_template`, `sales_email_invoice_guest_template`, `sales_email_shipment_template`, `sales_email_shipment_guest_template`, `sales_email_creditmemo_template` and `sales_email_creditmemo_guest_template`; these are copies of the Magento 2.4.9 templates, so they need to be kept in sync on upgrades and they replace any theme overrides of these templates);
- on the checkout success page and the customer account order view;
- on the admin order, invoice, shipment and credit memo pages, via the generic labelled display of `Loki_QuoteExtended` (see `etc/adminhtml/di.xml`).


## Current status

<!-- badges.test.start -->
<div class="github-pipeline-badges">

![Static Tests](https://img.shields.io/github/actions/workflow/status/LokiExtensions/Loki_QuoteExtendedExample/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/LokiExtensions/Loki_QuoteExtendedExample/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/LokiExtensions/Loki_QuoteExtendedExample/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/LokiExtensions/Loki_QuoteExtendedExample/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/LokiExtensions/Loki_QuoteExtendedExample/compile.yml?label=compile)

</div>
<!-- badges.test.end -->
