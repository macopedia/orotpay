# Tpay & OroCommerce payment gateway integration
<br>
<p>
<img src="./.github/tpay.svg" width="250"  alt="Tpay trusted payments"/> &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <img src="https://oroinc.com/wp-content/themes/oroinc/images/redesign/OroCommerce-Logo.svg?x64248" width="400"  alt="OroCommerce"/>
</p>
<br>

The **OroTpay bundle** is a free and official Tpay extension for OroCommerce  that enables seamless payment processing for your OroCommerce application. Bundle provides a secure, frictionless checkout experience by integrating one of Poland’s leading payment gateways directly into your store.

## Key features

* **Native checkout experience:** Support for BLIK, VISA Mobile, Credit Cards, Online Transfers (PBL), and PragmaPay directly on your store.
* **Digital wallets:** Ability to configure Apple Pay and Google Pay on store checkout
* **Classic gateway support:** "classic" payment method of redirect to the Tpay payment gateway.
* **Flexible payment logic:** Support for payment retries, transactions statuses and logs to troubleshoot potential payment problems, possibility for full refunds directly from the OroCommerce admin panel (v1.0 supports full refunds only)
* **Testing environment:** You can use it with Tpay Sandbox mode for safe development and staging.

## Technical requirements

Please verify your environment meets the following criteria:

| Component                  | Requirement                                                                                                                                                                                                                                                                                                             |
|:---------------------------|:------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **OroCommerce**            | Version 6.x                                                                                                                                                                                                                                                                                                             |
| **PHP Version**            | >= PHP 8.4                                                                                                                                                                                                                                                                                                              |
| **Tpay Account**           | Active production or Tpay sandbox account (testing only)                                                                                                                                                                                                                                                                |
| **Apple/Goole MerchantID** | To offer digital wallets (Apple Pay/Google Pay) to your customers, ensure your Merchant ID is correctly configured in the integration settings. Note on Apple Pay: to enable Apple Pay, you must verify your store domain within the Tpay Merchant Panel. This involves uploading a verification file provided by Apple |

# Installation & Setup

Follow these steps to integrate Tpay into your OroCommerce instance:

### 1. Install via Composer
Execute the following command in your project root:
```
composer req macopedia/orotpay
```
### 2. Run database migrations & load translations
-  Execute migrations
```
php bin/console oro:platform:update --force
```

- Clear the application cache
```
php bin/console cache:clear
```

### 3. Configuration

1. **Parameters:** If necessary, customize the default parameters found in:  
   [Resources/config/parameters.yml](./Resources/config/parameters.yml)
2. **Deployment:** Deploy the changes to your OroCommerce instance (**remember: run migrations on first deployment!**)
3. **Go to ORO admin panel:**
    * Navigate to the OroCommerce admin panel.
    * Go to **System > Integrations > Manage Integrations**.
    * Create a new integration, select **Tpay**, provide your API credentials and select production or sandbox mode
    * Configure your desired payment methods within **System > Payment Rules**.

### 4. Documentation
Documentation for the extension is available on Tpay's website in:
1. [Polish](https://docs-api.tpay.com/pl/orocommerce/)
2. [English](https://docs-api.tpay.com/en/orocommerce/)

## License

This plugin's source code is completely free and released under the terms of the MIT license.

## About extension
This extension is a joint initiative developed by [Macopedia](https://macopedia.com/) in official partnership with [Tpay](https://tpay.com).

### About Tpay

[Tpay](https://tpay.com) is one of Poland’s leading online payment operators, providing secure and convenient payment solutions for thousands of e-commerce businesses.

### About Macopedia
[Macopedia](https://macopedia.com/) is a software development house and digital commerce partner with strong expertise in PIM and e-commerce, supporting B2B and B2C companies worldwide.
Since 2006, we’ve been helping manufacturers, importers, and distributors scale their digital sales by bridging business needs with robust, modern technology.

We specialize in building and maintaining solutions based on proven platforms such as OroCommerce, Adobe Commerce (Magento), Shopware, Akeneo, Pimcore, and Odoo, as well as custom integrations across complex system landscapes.
Our teams deliver end-to-end solutions, from architecture and development to integrations with ERP, CRM, and payment systems. We build scalable, headless, and traditional web applications using TYPO3 and modern frameworks.

Beyond project delivery, we actively share knowledge and educate the market through initiatives like the [B2B MasterClass podcast](https://www.youtube.com/watch?v=bKcTa6id7Xs&list=PLPCr2EIkIgImMjKrm1bAI6UY0KWiG8r8y) and [PIM Academy](https://www.youtube.com/watch?v=6pbpuklMXyI&list=PLPCr2EIkIgIkJxKQtUrwbFpTS1R6Q9Pzs), supporting the growth of the digital commerce community.

## [Contact us](https://macopedia.com/contact)
