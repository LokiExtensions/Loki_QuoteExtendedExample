# Loki_QuoteExtendedExample

<!-- badges.specs.start -->
![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://gitlab.yireo.com/loki-checkout/Loki_QuoteExtendedExample/-/badges/release.svg)
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
[![Static Tests](https://gitlab.yireo.com/loki-checkout/Loki_QuoteExtendedExample/badges/main/pipeline.svg?job=static-tests)](https://gitlab.yireo.com/loki-checkout/Loki_QuoteExtendedExample/-/pipelines)
[![Unit Tests](https://gitlab.yireo.com/loki-checkout/Loki_QuoteExtendedExample/badges/main/pipeline.svg?job=unit-tests)](https://gitlab.yireo.com/loki-checkout/Loki_QuoteExtendedExample/-/pipelines)
[![Integration Tests](https://gitlab.yireo.com/loki-checkout/Loki_QuoteExtendedExample/badges/main/pipeline.svg?job=integration-tests)](https://gitlab.yireo.com/loki-checkout/Loki_QuoteExtendedExample/-/pipelines)
[![Playwright](https://gitlab.yireo.com/loki-checkout/Loki_QuoteExtendedExample/badges/main/pipeline.svg?job=playwright)](https://gitlab.yireo.com/loki-checkout/Loki_QuoteExtendedExample/-/pipelines)
[![DI Compilation](https://gitlab.yireo.com/loki-checkout/Loki_QuoteExtendedExample/badges/main/pipeline.svg?job=compile)](https://gitlab.yireo.com/loki-checkout/Loki_QuoteExtendedExample/-/pipelines)
<!-- badges.test.end -->
