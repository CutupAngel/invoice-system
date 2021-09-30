<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//API V1
Route::group(['namespace' => 'Api\v1', 'prefix' => 'v1', 'middleware' => 'api'], function () {
    Route::get('test', 'TestController@test');

    //Packages
    Route::group(['prefix' => 'package', 'middleware' => 'api'], function () {
        Route::post('group/create', 'PackageController@createGroup');
        Route::post('group/update', 'PackageController@updateGroup');
        Route::get('group/lists', 'PackageController@listsGroup');
        Route::get('group/get', 'PackageController@getGroup');
        Route::post('group/delete', 'PackageController@deleteGroup');
        Route::post('create', 'PackageController@createPackage');
        Route::post('update', 'PackageController@updatePackage');
        Route::get('show', 'PackageController@showPackage');
        Route::get('lists', 'PackageController@listsPackage');
        Route::get('get', 'PackageController@getPackage');
        Route::post('delete', 'PackageController@deletePackage');
        Route::post('option/create', 'PackageController@createPackageOption');
        Route::post('option/update', 'PackageController@updatePackageOption');
        Route::get('option/lists', 'PackageController@listsPackageOption');
        Route::get('option/get', 'PackageController@getPackageOption');
        Route::post('option/delete', 'PackageController@deletePackageOption');
        Route::get('get-by-customer', 'PackageController@getPackageByCustomer');
    });

    //Country
    Route::group(['prefix' => 'country', 'middleware' => 'api'], function () {
        Route::get('lists', 'CountryController@listsCountry');
        Route::get('get', 'CountryController@getCountry');
    });

    //County
    Route::group(['prefix' => 'county', 'middleware' => 'api'], function () {
        Route::get('lists-by-country', 'CountyController@listsCountiesByCountry');
    });

    //Customers
    Route::group(['prefix' => 'customer', 'middleware' => 'api'], function () {
        Route::post('check', 'CustomerController@checkByUsernameAndPassword');
        Route::post('create', 'CustomerController@createCustomer');
        Route::post('update', 'CustomerController@updateCustomer');
        Route::get('lists', 'CustomerController@listsCustomer');
        Route::get('get', 'CustomerController@getCustomer');
        Route::post('delete', 'CustomerController@deleteCustomer');
        Route::get('reset-password', 'CustomerController@resetPasswordCustomer');
        Route::post('add-credit', 'CustomerController@addCreditCustomer');
        Route::get('get-credit', 'CustomerController@getCreditCustomer');
        Route::post('delete-pay-method', 'CustomerController@deletePayMethodCustomer');
        Route::post('add-note', 'CustomerController@addNoteCustomer');
    });

    //Invoices
    Route::group(['prefix' => 'invoice', 'middleware' => 'api'], function () {
        Route::post('create-quote', 'InvoiceController@createQuote');
        Route::post('accept-quote', 'InvoiceController@acceptQuote');
        Route::post('delete-quote', 'InvoiceController@deleteQuote');
        Route::get('lists', 'InvoiceController@listsInvoice');
        Route::post('create-invoice', 'InvoiceController@createInvoice');
        Route::post('delete-invoice', 'InvoiceController@deleteInvoice');
        Route::post('update-invoice', 'InvoiceController@updateInvoice');
        Route::post('send-invoice', 'InvoiceController@sendInvoice');
        Route::post('send-invoice-reminder', 'InvoiceController@sendInvoiceReminder');
        Route::post('capture-payment', 'InvoiceController@capturePaymentInvoice');
        Route::get('get-payment-method', 'InvoiceController@getPaymentMethodInvoice');
        Route::get('get-transactions', 'InvoiceController@getTransactionsInvoice');
        Route::post('update-transaction', 'InvoiceController@updateTransactionInvoice');
    });

    //Orders
    Route::group(['prefix' => 'order', 'middleware' => 'api'], function () {
        Route::post('accept-order', 'OrderController@acceptOrder');
        Route::post('add-order', 'OrderController@addOrder');
        Route::post('cancel-order', 'OrderController@cancelOrder');
        Route::post('delete-order', 'OrderController@deleteOrder');
        Route::post('fraud-order', 'OrderController@fraudOrder');
        Route::get('get-orders', 'OrderController@getOrders');
        Route::get('get-orders-by-status', 'OrderController@getOrdersByStatus');
        Route::get('check-fraud', 'OrderController@checkFraudOrder');
        Route::post('pending-order', 'OrderController@pendingOrder');
    });

    //Module
    Route::group(['prefix' => 'module', 'middleware' => 'api'], function () {
        Route::post('activate-deactivate', 'ModuleController@activateDeactivateModule');
        Route::get('get-module-configuration', 'ModuleController@getModuleConfiguration');
    });

    //Report
    Route::group(['prefix' => 'report', 'middleware' => 'api'], function () {
        Route::get('annual-sales', 'ReportController@getAnnualSalesReport');
        Route::get('sales-by-staff', 'ReportController@getSalesByStaffReport');
        Route::get('sales-by-customer', 'ReportController@getSalesByCustomerReport');
        Route::get('login-history', 'ReportController@getloginHistoryReport');
        Route::get('revenue-trend', 'ReportController@getRevenueTrendReport');
        Route::get('package-leaderboard', 'ReportController@getPackageLeaderboardReport');
        Route::get('customer-receipt', 'ReportController@getCustomerReceiptReport');
        Route::get('customer-credit', 'ReportController@getCustomerCreditReport');
        Route::get('customer-invoice', 'ReportController@getCustomerInvoiceReport');
        Route::get('customer-debt', 'ReportController@getCustomerDebtReport');
    });

    //Marketing
    Route::group(['prefix' => 'marketing', 'middleware' => 'api'], function () {
        Route::post('create-discount', 'MarketingController@createDiscount');
        Route::post('update-discount', 'MarketingController@updateDiscount');
        Route::post('delete-discount', 'MarketingController@deleteDiscount');
        Route::get('get-discount', 'MarketingController@getDiscountById');
        Route::get('lists', 'MarketingController@listsDiscount');
    });

    //Setting
    Route::group(['prefix' => 'setting', 'middleware' => 'api'], function () {
        Route::post('create-staff', 'SettingController@createStaff');
        Route::post('update-staff', 'SettingController@updateStaff');
        Route::get('lists-staff', 'SettingController@listsStaff');
        Route::get('get-staff', 'SettingController@getStaffById');
        Route::post('delete-staff', 'SettingController@deleteStaff');
        Route::post('invoice', 'SettingController@invoiceSetting');
        Route::get('invoice', 'SettingController@getInvoiceSetting');
        Route::post('create-tax-zone', 'SettingController@createTaxZone');
        Route::post('update-tax-zone', 'SettingController@updateTaxZone');
        Route::post('delete-tax-zone', 'SettingController@deleteTaxZone');
        Route::get('lists-tax-zone', 'SettingController@listsTaxZone');
        Route::get('get-tax-zone', 'SettingController@getTaxZoneById');
        Route::post('create-tax-class', 'SettingController@createTaxClass');
        Route::post('update-tax-class', 'SettingController@updateTaxClass');
        Route::post('delete-tax-class', 'SettingController@deleteTaxClass');
        Route::get('lists-tax-class', 'SettingController@listsTaxClass');
        Route::get('get-tax-class', 'SettingController@getTaxClassById');
    });

    Route::group(['prefix' => 'checkout', 'middleware' => 'api'], function () {
        Route::post('create', 'CheckoutController@create');
    });
});
