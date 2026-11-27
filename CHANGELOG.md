# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.0.1] - 27 November 2026
### Added
- Example field in the Loki Checkout customer step and sidebar preview, stored in a new `example_field` column of `loki_quote_extended`
- Example field shown on admin order, invoice, shipment and credit memo pages
- Example field shown on the frontend order view page and checkout success page
- Example field included in order, invoice, shipment and credit memo emails (customer and guest)
- README documentation

### Fixed
- `maxlength` rendered as a proper HTML attribute on the example field
- Dependency on `loki-checkout/magento2-quote-extended` now allows 1.x
- Removed unneeded `Magento_Shipping` dependency
