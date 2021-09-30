<?php

Route::group(['module' => 'Paypal', 'prefix' => 'paypal'], function () {
	Route::any('/ipn/{invoiceId?}', 'PaypalController@handleIPN');
	Route::any('/tax/', 'PaypalController@handleTaxRequest');
});
