<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'WelcomeController@index');

Route::get('home', 'HomeController@index');
/*
Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
*/
Route::post('admin/login', 'LoginController@loginverify');

// ADMIN COMPANY ROUTERS
Route::get('admin/account', 'AccountController@listData');
Route::get('admin/account/list', 'AccountController@listData');
Route::post('admin/account/add', 'AccountController@addData');
Route::get('admin/account/edit/{id}', 'AccountController@GetData');
Route::post('admin/account/save', 'AccountController@SaveData');
Route::post('admin/account/delete', 'AccountController@DeleteData');

// ADMIN STAFF ROUTERS
Route::get('admin/staff', 'StaffController@index');
Route::post('admin/staffAdd', 'StaffController@add');
Route::post('admin/staffEdit', 'StaffController@edit');
Route::post('admin/staffDelete', 'StaffController@delete');
Route::post('admin/staffDetail', 'StaffController@detail');
Route::get('admin/StaffEdit/{id}', 'StaffController@detail');

// ADMIN NOTE ROUTERS
Route::post('admin/staff/note', 'StaffController@note');
Route::post('admin/staff/noteDelete', 'StaffController@noteDelete');
Route::post('admin/staff/noteAdd', 'StaffController@noteAdd');
Route::post('admin/staff/noteEdit', 'StaffController@noteEdit');
Route::post('admin/staff/noteDetail', 'StaffController@notedetail');

// ADMIN TIME OFF ROUTERS
Route::post('admin/staff/timeoff', 'StaffController@timeoff');
Route::post('admin/staff/timeoffDelete', 'StaffController@timeoffDelete');
Route::post('admin/staff/timeoffAdd', 'StaffController@timeoffAdd');
Route::post('admin/staff/timeoffEdit', 'StaffController@timeoffEdit');
Route::post('admin/staff/timeoffDetail', 'StaffController@timeoffdetail');


// ADMIN Vendor ROUTERS
Route::get('admin/vendor', 'VendorController@index');
Route::post('admin/vendorDelete', 'VendorController@delete');
Route::post('admin/vendorAdd', 'VendorController@add');
Route::post('admin/vendorEdit', 'VendorController@edit');
Route::post('admin/vendorDetail', 'VendorController@detail');
Route::get('admin/VendorEdit/{id}', 'VendorController@detail');

// COMMON CONTROLLER 
Route::get('common/getAdminRoles', 'CommonController@getAdminRoles');
Route::get('common/type/{type}', 'CommonController@type');
Route::get('common/staffRole', 'CommonController@getStaffRoles');
Route::get('common/checkemail/{email}', 'CommonController@checkemailExist');
Route::get('auth/session', 'LoginController@check_session');
Route::get('auth/logout', 'LoginController@logout');




