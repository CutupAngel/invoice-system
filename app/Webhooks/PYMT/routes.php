<?php

Route::group(['module' => 'PYMT', 'prefix' => 'pymt'], function () {
	Route::any('/callback/{invoiceId?}', 'PYMTController@handleCallback');
});
