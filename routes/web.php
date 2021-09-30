<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['module' => 'auth', 'prefix' => 'auth'], function () {
	Route::Group(['middleware' => 'guest'], function () {
		Route::get('/', 'AuthController@getLogin')->name('login');
		Route::get('/login', 'AuthController@getLogin')->name('login');
		Route::post('/login', 'AuthController@postLogin');

		Route::get('/2fa', 'AuthController@get2fa');
		Route::post('/2fa', 'AuthController@post2fa');

		Route::get('/register', 'AuthController@getRegister');
		Route::post('/register', 'AuthController@postRegister');

		Route::get('token/{token}', 'AuthController@tokenLogin');
	});

	// Password Reset Routes...
	Route::get('password/reset', 'PasswordController@showResetEmailForm')->name('password.reset.email');
	Route::get('password/reset/{token?}', 'PasswordController@showResetForm')->name('password.reset');
	Route::post('password/email', 'PasswordController@sendResetLinkEmail');
	Route::post('password/reset', 'PasswordController@reset');

	Route::get('/logout', 'AuthController@getLogout')->middleware(['auth']);
});

Route::group(['module' => 'Checkout', 'prefix' => 'checkout'], function () {
	Route::get('/', 'CheckoutController@getCheckout');
	Route::post('/', 'CheckoutController@postCheckout');
	Route::middleware('checkout')->get('{key}/form', 'CheckoutController@getFormCheckout')->name('checkout.form');
	Route::post('/callback', 'CheckoutController@callback');
	Route::get('/addCustomer', 'CheckoutController@addCustomer');
	Route::get('/getCustomer', 'CheckoutController@getCustomer');
	Route::get('/getOrAddCustomerStripe', 'CheckoutController@getOrAddCustomerStripe');
	Route::get('/getPaymentIntentSavedCardStripe', 'CheckoutController@getPaymentIntentSavedCardStripe');
	Route::get('/createSetupIntentStripe', 'CheckoutController@createSetupIntentStripe');
	Route::get('/saveCard', 'CheckoutController@saveCard');
	Route::get('/check_fraud', 'CheckoutController@checkFraud');
	Route::get('/check_outofstock', 'CheckoutController@checkOutofstock');
	Route::post('/regions', 'CheckoutController@postCheckoutGetRegions');
	Route::post('/check/username', 'CheckoutController@postCheckoutCheckUsername');
	Route::post('/check/email', 'CheckoutController@postCheckoutCheckEmail');
	Route::post('/status', 'CheckoutController@postCheckoutOrderStatus');
  Route::any('/receipt', 'CheckoutController@getReceipt');
  Route::post('/tax', 'CheckoutController@postTaxRates');
  Route::post('/discountCode', 'CheckoutController@postDiscountCode');
	Route::get('/payment_intents', 'CheckoutController@paymentItents')->name('checkout.itent');
	Route::get('/validate_vat', 'CheckoutController@validateVat');
	Route::get('/check_vat/{vat}', 'CheckoutController@checkVat');

	//TESTING ONLY
	Route::get('/testpaypalpro', 'CheckoutController@testpaypalpro');
});

Route::group(['module' => 'Common', 'prefix' => '/'], function () {
	Route::post('/viewcart/delete', 'CommonController@updateBascket')->name('update-item');
	Route::get('/viewcart', 'CommonController@getViewCart')->name('view-cart');
	Route::get('/currency/{id}', 'CommonController@getNewCurrency');
	Route::post('/viewcart', 'CommonController@postAddToCart')->name('add-to-cart');
	Route::get('/fraud', 'CommonController@getFraud')->name('get-fraud');
});

// Normally there will be prefix here for the module, but this is the common module (overall) so we won't prefix it.
Route::group(['module' => 'Common'], function () {});

Route::group(['module' => 'API', 'prefix' => 'api'], function () {
	Route::put('migrate', 'CommonAPIController@installDatabase');
	Route::post('createUser', 'CommonAPIController@createUser');
	Route::post('settings', 'CommonAPIController@setSetting');
	Route::post('login', 'CommonAPIController@login');
});

Route::group(['module' => 'Customers', 'middleware' => ['auth', 'isStaff']], function () {
	Route::resource('customers', 'CustomerController');
	Route::get('customers/delete/{id}', 'CustomerController@destroy');
	Route::post('customers/save-note', 'CustomerController@saveNote');
	Route::post('customers/{customers}/credit', ['as' => 'customers.editCredit', 'uses' => 'CustomerController@editCredit']);
	Route::post('customers/{customers}/merge', ['as' => 'customers.merge', 'uses' => 'CustomerController@merge']);
	Route::put('customers/{customer}/restore', ['as' => 'customers.restore', 'uses' => 'CustomerController@restore']);

	Route::get('customers/{customers}/impersonate', 'CustomerController@impersonate');
	Route::get('customers/{customers}/set_fraudlabs_status/{status}', 'CustomerController@setFraudLabsStatus')->name('set_customer_fraudlabs_status');
});

Route::group(['module' => 'Dashboard', 'prefix' => '/', 'middleware' => 'auth'], function () {
	Route::get('', 'DashboardController@dashboard');
	Route::post('/todo', 'DashboardController@addTodolist');
	Route::put('/todo', 'DashboardController@updateTodolist');
	Route::delete('/todo', 'DashboardController@deleteTodolist');
});

Route::group(['module' => 'Home', 'prefix' => 'home'], function () {
	Route::get('/', 'HomeController@getFrontendHome');
	Route::get('create-ticket', 'HomeController@ticketForm');
	Route::post('create-ticket', 'HomeController@ticketFormPost');
});

Route::group(['module' => 'Invoices', 'middleware' => ['auth']], function () {
	Route::get('admin/invoices/create/{id}', 'InvoicesController@create');
	Route::get('admin/invoices/view/{type?}', 'InvoicesController@index');
	Route::post('admin/invoices/taxRates', 'InvoicesController@postTaxRates');
	Route::post('admin/invoices/list', 'InvoicesController@list');
	Route::resource('admin/invoices', 'InvoicesController');
	Route::get('admin/invoices/{id}/pdf', 'InvoicesController@renderInvoicePdf');
	Route::get('admin/invoices/{id}/send_invoice_email', 'InvoicesController@sendInvoiceEmail');
});

Route::group(['module' => 'Transactions'], function () {
    Route::post('admin/invoices/add-manual-payment', 'TransactionsController@addPayment')->name('add_manual_payment');
});

Route::group(['module' => 'Invoices', 'middleware' => ['auth', 'isCustomer']], function () {
	Route::get('invoices/view/{type?}', 'InvoicesCustomerController@index');
	Route::resource('invoices', 'InvoicesCustomerController');
	Route::get('invoices/{id}/pay/{authhash?}', 'InvoicesCustomerController@pay');
});

Route::get('helper/counties/{country_id}', 'HelperController@getCounties');
Route::get('helper/search', 'HelperController@getSearch')->middleware(['auth']);
Route::get('helper/customers', 'HelperController@getCustomers')->middleware(['auth']);

Route::group(['module' => 'Marketing', 'prefix' => 'marketing', 'middleware' => ['auth', 'isStaff']], function () {
	Route::get('/', 'MarketingController@index');
	Route::resource('fixed-discounts', 'FixedController');
	Route::resource('discount-codes', 'CodesController');
});

Route::group(['module' => 'Orders', 'prefix' => 'orders', 'middleware' => ['auth', 'isStaff']], function () {
	Route::get('/', 'OrdersClientController@getPackages');
	Route::get('/options', 'OrdersClientController@getOptionsList');

	Route::put('/toggle/{id}', 'OrdersClientController@toggle');

	Route::post('/options/delete', 'OrdersClientController@postDeleteOptionData');
	Route::post('/options/save', 'OrdersClientController@postSaveOptionData');
	Route::get('/options/get/{type}/{id}/{id2?}', 'OrdersClientController@getOptionDataJson');

	Route::post('/delete/{orderid}/{packageid?}', 'OrdersClientController@delete');

	Route::post('/integration/{integration}', 'OrdersClientController@getIntegrationForm');

	// These have to to at the bottom other wise, they will override every route.
	Route::post('/delete_image', 'OrdersClientController@deletePackageImage');
	Route::get('/{group}', 'OrdersClientController@getGroup');
	Route::post('/{group}', 'OrdersClientController@saveGroup');
	Route::get('/{group}/{package}', 'OrdersClientController@getPackage');
	Route::post('/{group}/{package}', 'OrdersClientController@savePackage');
});

Route::group(['module' => 'Orders', 'middleware' => ['auth', 'isStaff']], function() {
	Route::resource('customers/order', 'OrderController');
	Route::put('customers/order/{order}/command', 'OrderController@processCommand');
});

Route::group(['module' => 'Orders', 'prefix' => 'products-ordered'], function () {
	Route::get('/', 'OrderController@index');
	Route::get('order/{id}', 'OrderController@show');
	Route::get('order/download/{id}', 'OrderController@downloadPngImage');
});

Route::group(['module' => 'Orders', 'prefix' => 'order'], function () {
	Route::get('/', 'OrdersCustomerController@displayOrder');
	Route::get('/get_packages_from_group/{id}', 'OrdersCustomerController@getPackageFromGroup');
	Route::get('/get_package_featured', 'OrdersCustomerController@getPackageFeatured');
	Route::get('/dl/{fileid}', 'OrderCustomerController@getFile');

	Route::get('orders/', 'OrderController@index');
	Route::get('order/{id}', 'OrderController@show');

	Route::get('/{group}/{packageid}', 'OrdersCustomerController@getPackage')->name('order.group.package');
	Route::get('/{group}', 'OrdersCustomerController@getGroup');
});

Route::group(['module' => 'Plans', 'prefix' => 'plans', 'middleware' => ['auth', 'isSuperAdmin']], function () {
	Route::get('/', 'PlansAdminController@getPlans');

	// Route::post('/options/delete', 'Admin@postDeleteOptionData');
	// Route::post('/options/save', 'Admin@postSaveOptionData');
	// Route::get('/options/get/{type}/{id}/{id2?}', 'Admin@getOptionDataJson');

	Route::post('/delete/{planid}', 'PlansAdminController@delete');

	// These have to to at the bottom other wise, they will override every route.
	Route::get('/{plan}', 'PlansAdminController@getPlan');
	Route::post('/{plan}', 'PlansAdminController@savePlan');

});

// Route::group(['module' => 'Plans', 'prefix' => 'plan'], function () {
// 	Route::get('/{planid}', 'Client@getPlan');
// });

// Normally there will be prefix here for the module, but this is the common module (overall) so we won't prefix it.
Route::group(['module' => 'Reports', 'prefix' => 'reports', 'middleware' => ['auth', 'isStaff']], function () {
	Route::get('loginhistory', 'ReportsClientController@loginHistory');
	Route::get('annual-sales', 'ReportsClientController@annualSales');
	Route::get('debt-sheet', 'ReportsClientController@debtSheet');
	Route::get('customer-invoice-report', 'ReportsClientController@customerInvoiceReport');
	Route::get('revenue-trend', 'ReportsClientController@revenueTrend');
	Route::get('customer-trend', 'ReportsClientController@customerTrend');
	Route::get('package-leaderboard', 'ReportsClientController@packageLeaderboard');
	Route::get('customer-receipts-report', 'ReportsClientController@customerReceipts');
	Route::get('customer-credit-report', 'ReportsClientController@customerCredit');

	Route::get('sales-by-staff/{type?}/{date?}', 'ReportsClientController@salesByStaff');
	Route::get('sales-by-customers/{type?}/{date?}', 'ReportsClientController@salesByCustomers');
});

Route::group(['module' => 'Settings', 'prefix' => 'settings', 'middleware' => ['auth']], function () {
    Route::get('/my-account/edit', 'SettingsController@getMyAccountForm');
    Route::post('/my-account/edit', 'SettingsController@postMyAccountForm');
    Route::get('/my-account', 'SettingsController@getMyAccount');
    Route::post('/my-account', 'SettingsController@postMyAccount');
    Route::post('/my-account/generate-api-key', 'SettingsController@generateApiKey');

    Route::resource('/staff', 'StaffController');

    Route::get('/paymentgateway/clear/{gateway?}', 'PaymentGatewayController@clear');
    Route::get('/paymentgateways/{gateway?}', 'PaymentGatewayController@get');
    Route::post('/paymentgateways/{gateway?}', 'PaymentGatewayController@post');
    Route::post('/integrations/import_customers/{app?}', 'IntegrationController@importCustomers');
    Route::get('/integrations/import_customers/{app?}/step_1/{id}', 'IntegrationController@importCustomersStep1');
    Route::get('/integrations/import_customers/{id}', 'IntegrationController@importCustomersResult');
    Route::get('/integrations/{app?}', 'IntegrationController@get');
    Route::post('/integrations/{app?}', 'IntegrationController@post');
    Route::put('/integrations/', 'IntegrationController@toggle');
    Route::put('/integrations/{app?}/{id?}', 'IntegrationController@update');
    Route::delete('/integrations/{app?}/{id?}', 'IntegrationController@destroy');

    Route::post('/change-plan', 'SettingsController@postChangePlan');

    Route::get('invoice-settings', 'InvoiceSettingsController@view');
    Route::post('invoice-settings', 'InvoiceSettingsController@save');

    Route::get('/invoice-settings/tax-rates', 'SettingsController@getTaxRates');
    Route::get('/invoice-settings/tax-classes', 'SettingsController@getTaxClasses');
    Route::get('/invoice-settings/tax-zones', 'SettingsController@getTaxZones');

    Route::post('/invoice-settings/tax-zones/regions', 'SettingsController@postGetRegionsOfCountry');
    Route::post('/invoice-settings/tax-zones/zone/delete', 'SettingsController@postDeleteZone');
    Route::post('/invoice-settings/tax-zones/zone/save', 'SettingsController@postSaveZone');
    Route::post('/invoice-settings/tax-zones/zone', 'SettingsController@postGetSavedZone');
    Route::post('/invoice-settings/tax-zones/class/delete', 'SettingsController@postDeleteClass');
    Route::post('/invoice-settings/tax-zones/class/save', 'SettingsController@postSaveClass');
    Route::post('/invoice-settings/tax-zones/class', 'SettingsController@postGetSavedClass');

    Route::get('/theme-settings/frontend', 'SettingsController@getFrontEndTheme');
    Route::get('/theme-settings/invoices', 'SettingsController@getInvoicesTheme');

    Route::get('/design-settings', 'SettingsController@getDesignSettings');
    Route::post('/design-settings', 'SettingsController@postDesignSettings');

		Route::view('/hc/directadmin/import', 'Integrations.directadminImport');
});

// Route::group(['module' => 'Settings', 'prefix' => 'settings', 'middleware' => ['auth','isCustomer']], function () {
Route::group(['module' => 'Settings', 'prefix' => 'settings', 'middleware' => ['auth']], function () {
    Route::get('/myaccount', 'SettingsController@getMyAccountCustomer');
    Route::post('/myaccount', 'SettingsController@postMyAccountCustomer');
    Route::get('/myaccount/edit', 'SettingsController@getMyAccountFormCustomer');
    Route::post('/myaccount/edit', 'SettingsController@postMyAccountFormCustomer');
    Route::get('/myaccount/gdpr-download', 'SettingsController@postGDPRDownload');
});

Route::group(['module' => 'Support', 'prefix' => 'support', 'middleware' => ['auth']], function () {
    Route::get('tickets', 'SupportController@getTickets')->name('tickets.index');
    Route::get('tickets/datatables', 'SupportController@getDatatables')->name('tickets.datatables');
    Route::post('tickets', 'SupportController@storeTicket')->name('tickets.store');
    Route::get('tickets/create', 'SupportController@createTicket')->name('tickets.create');
    Route::get('tickets/{id}/edit', 'SupportController@editTicket')->name('tickets.edit');
    Route::put('tickets/{id}', 'SupportController@updateTicket')->name('tickets.update');
    Route::post('tickets/{id}/reply', 'SupportController@replyTicket')->name('tickets.reply');
});

/* TEST PAYMENT API IFRAME */
Route::get('/payment-api-test', 'TestController@paymentApiTest');

Route::get('/stopImpersonating', 'DashboardController@stopImpersonating');

Route::get('/{group}/{packageUrl}', 'OrdersCustomerController@getPackageByUrl');
Route::get('/{group}', 'OrdersCustomerController@getGroup');
