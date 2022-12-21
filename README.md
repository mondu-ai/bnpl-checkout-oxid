# OXID eShop module for Mondu Payment

### Installation

##### Install using composer

1. Switch to the shop root directory

```
# Install desired version of mondu/bnpl-checkout-oxid module (in example, version 1.0.0)
$ composer require mondu/bnpl-checkout-oxid ^1.0.0
```

2. After installation, Mondu module should be visible in admin dashboard (_Admin Dashboard -> Extensions -> Modules_)
3. Navigate to Mondu module settings tab
    1. Enter API key provided by Mondu
    2. Check 'Sandbox mode' checkbox for testing in sandbox environment
    3. Save
4. Navigate to Mondu module overview tab
5. Click on Activate button to activate the extension
    > NOTE: On module activation, three new payment methods (Mondu Invoice, Mondu SEPA and Mondu Installment) are added and activated
6. Assign desired countries to Mondu Payment methods (_Admin Dashboard -> Shop Settings -> Payment Methods -> <desired Mondu payment method> -> Country -> Assign Countries_)
    > NOTE: In case no country is assigned to payment method, it will not be visible in checkout flow
7. Assign desired payment methods to Shop shipping methods (_Admin Dashboard -> Shop Settings -> Shipping Methods -> <desired shipping method> -> Payment -> Assign Payment Methods_)
    > NOTE: In case payment method is not assigned to any shipping method, it will not be visible in checkout flow
