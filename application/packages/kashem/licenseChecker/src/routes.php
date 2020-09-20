<?php
Route::group(['middleware' => ['web']], function () {
    Route::get('verify-purchase-code', 'kashem\licenseChecker\ProductVerifyController@verifyPurchaseCode');
    Route::post('post-verify-purchase-code', 'kashem\licenseChecker\ProductVerifyController@postVerifyPurchaseCode');
});