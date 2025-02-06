<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication Route
Route::prefix('auth')->group(function(){
    Route::get('/init', [
        'uses' => 'API\AuthController@init',
        'as' => 'auth.init'
    ])->middleware('auth:api');

    Route::post('/login', [
        'uses' => 'API\AuthController@login',
        'as' => 'auth.login'
    ]);

    Route::post('/register', [
        'uses' => 'API/AuthController@register',
        'as' => 'auth.register'
    ]);

    Route::get('/logout', [
        'uses' => 'API\AuthController@logout',
        'as' => 'auth.logout'
    ])->middleware('auth:api');
});

// User Routes
Route::group(['prefix' => 'user', 'middleware' => ['auth:api', 'user.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\UserController@index',
        'as' => 'user.index',
    ]);

    Route::get('/create', [
        'uses' => 'API\UserController@create',
        'as' => 'user.create',
    ]);

    Route::post('/store', [
        'uses' => 'API\UserController@store',
        'as' => 'user.store',
    ]);

    Route::get('/edit/{id}', [
        'uses' => 'API\UserController@edit',
        'as' => 'user.edit',
    ]);

    Route::post('/update/{id}', [
        'uses' => 'API\UserController@update',
        'as' => 'user.update',
    ]);

    Route::post('/update_profile/{id}', [
        'uses' => 'API\UserController@update_profile',
        'as' => 'user.update_profile',
    ]);

    Route::post('/delete', [
        'uses' => 'API\UserController@delete',
        'as' => 'user.delete',
    ]);

    Route::get('/roles_permissions', [
        'uses' => 'API\UserController@userRolesPermissions',
        'as' => 'user.roles_permissions',
    ]);

});

// Product Routes
Route::group(['prefix' => 'product', 'middleware' => ['auth:api', 'product.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\ProductController@index',
        'as' => 'product.index',
    ]);
    
    Route::post('/list/view', [
        'uses' => 'API\ProductController@list_view',
        'as' => 'product.list.view',
    ]);

    Route::post('/scanned_products', [
        'uses' => 'API\ProductController@scanned_products',
        'as' => 'scanned.products',
    ]);

    Route::get('/create', [
        'uses' => 'API\ProductController@create',
        'as' => 'product.create',
    ]);

    Route::post('/store', [
        'uses' => 'API\ProductController@store',
        'as' => 'product.store',
    ]);

    Route::get('/edit/{id}', [
        'uses' => 'API\ProductController@edit',
        'as' => 'product.edit',
    ]);

    Route::post('/update/{id}', [
        'uses' => 'API\ProductController@update',
        'as' => 'product.update',
    ]);

    Route::post('/delete', [
        'uses' => 'API\ProductController@delete',
        'as' => 'product.delete',
    ]);

    Route::post('/import/{id}', [
        'uses' => 'API\ProductController@import',
        'as' => 'product.import'
    ]);

    Route::post('/search_brand', [
        'uses' => 'API\ProductController@search_brand',
        'as' => 'product.search_brand',
    ]);

    Route::post('/search_model', [
        'uses' => 'API\ProductController@search_model',
        'as' => 'product.search_model',
    ]);

    Route::post('/search_category', [
        'uses' => 'API\ProductController@search_category',
        'as' => 'product.search_category',
    ]);

    Route::post('/search_serial', [
        'uses' => 'API\ProductController@search_serial',
        'as' => 'product.search_serial',
    ]);
    
    Route::post('/serial_number_details', [
        'uses' => 'API\ProductController@serial_number_details',
        'as' => 'serial.number.details'
    ]);

    Route::post('/inventory_on_hand', [
        'uses' => 'API\ProductController@inventory_on_hand',
        'as' => 'inventory.on.hand'
    ]);

    Route::get('/sync_item_master_data', [
        'uses' => 'API\ProductController@sync_item_master_data',
        'as' => 'sync.item.master.data'
    ]);

    // Product Export Route
    Route::post('/export', [
        'uses' => 'API\ProductController@export',
        'as' => 'product.export',
    ]);

    // Product Export Route
    Route::post('/export_merged_template', [
        'uses' => 'API\ProductController@export_merged_template',
        'as' => 'product.export_merged_template',
    ]);

    // Product Export Route
    Route::post('/template/download', [
        'uses' => 'API\ProductController@template_download',
        'as' => 'product.template.download',
    ]);

    // Inventory On Hand Export Route
    Route::post('/export_inventory_on_hand', [
        'uses' => 'API\ProductController@export_inventory_on_hand',
        'as' => 'export.inventory.on.hand',
    ]);



});

Route::get('/sync_item_master_data', [
    'uses' => 'API\ProductController@sync_item_master_data',
    'as' => 'sync_item_master_data'
]);

// Product Template Download Route
// Route::get('/product/template/download/{branch_id}', [
//     'uses' => 'API\ProductController@template_download',
//     'as' => 'product.template_download',
// ])->middleware(['product.maintenance']);

// // Product Export Route
// Route::get('/product/export/{branch_id}/{user_id}', [
//     'uses' => 'API\ProductController@export',
//     'as' => 'product.export',
// ])->middleware(['product.maintenance']);

// Inventory Reconciliation Route
Route::group(['prefix' => 'inventory_reconciliation', 'middleware' => ['auth:api', 'inventory_reconciliation.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\InventoryReconciliationController@index',
        'as' => 'inventory_reconciliation.index',
    ]);

    Route::get('/create', [
        'uses' => 'API\InventoryReconciliationController@create',
        'as' => 'inventory_reconciliation.create',
    ]);

    Route::post('/store', [
        'uses' => 'API\InventoryReconciliationController@store',
        'as' => 'inventory_reconciliation.store',
    ]);

    Route::get('/edit/{id}', [
        'uses' => 'API\InventoryReconciliationController@edit',
        'as' => 'inventory_reconciliation.edit',
    ]);

    Route::post('/update/{id}', [
        'uses' => 'API\InventoryReconciliationController@update',
        'as' => 'inventory_reconciliation.update',
    ]);

    Route::post('/delete', [
        'uses' => 'API\InventoryReconciliationController@delete',
        'as' => 'inventory_reconciliation.delete',
    ]);

    Route::get('/discrepancy/{id}', [
        'uses' => 'API\InventoryReconciliationController@discrepancy',
        'as' => 'inventory_reconciliation.discrepancy',
    ]);
    Route::post('/breakdown', [
        'uses' => 'API\InventoryReconciliationController@breakdown',
        'as' => 'inventory_reconciliation.breakdown',
    ]);

    Route::post('/import', [
        'uses' => 'API\InventoryReconciliationController@import',
        'as' => 'inventory_reconciliation.import',
    ]);

    Route::post('/sync', [
        'uses' => 'API\InventoryReconciliationController@sync_inventory_recon',
        'as' => 'inventory_reconciliation.sync',
    ]);

    Route::get('/export_discrepancy/{id}', [
        'uses' => 'API\InventoryReconciliationController@export_discrepancy',
        'as' => 'inventory_reconciliation.export_discrepancy',
    ]);

    Route::post('/export_breakdown', [
        'uses' => 'API\InventoryReconciliationController@export_breakdown',
        'as' => 'inventory_reconciliation.export_breakdown',
    ]);

    Route::post('/unreconcile/list', [
        'uses' => 'API\InventoryReconciliationController@unreconciled_list',
        'as' => 'inventory_reconciliation.unreconciled_list',
    ]);

    Route::post('/reconcile', [
        'uses' => 'API\InventoryReconciliationController@reconcile',
        'as' => 'inventory_reconciliation.reconcile',
    ]);
});

//Employee Master Data Routes
Route::group(['prefix' => 'employee_master_data', 'middleware' => ['auth:api', 'employee.master.data.maintenance']], function() {
    Route::get('/index', [
        'uses' => 'API\EmployeeMasterDataController@index',
        'as' => 'employee.master.data.index'
    ]);

    Route::post('/store', [
        'uses' => 'API\EmployeeMasterDataController@store',
        'as' => 'employee.master.data.store'
    ]); 

    Route::post('/update/{id}', [
        'uses' => 'API\EmployeeMasterDataController@update',
        'as' => 'employee.master.data.update'
    ]); 

    Route::post('/delete', [
        'uses' => 'API\EmployeeMasterDataController@delete',
        'as' => 'employee.master.data.delete'
    ]); 

    Route::post('/import', [
        'uses' => 'API\EmployeeMasterDataController@import',
        'as' => 'employee.master.data.import'
    ]); 

    Route::post('/export', [
        'uses' => 'API\EmployeeMasterDataController@export',
        'as' => 'employee.master.data.export'
    ]); 

    Route::post('/file_upload/{id}', [
        'uses' => 'API\EmployeeMasterDataController@file_upload',
        'as' => 'employee.master.data.file_upload'
    ]); 

    Route::post('/file_delete', [
        'uses' => 'API\EmployeeMasterDataController@file_delete',
        'as' => 'employee.master.data.file_delete'
    ]); 

    Route::post('/file_download', [
        'uses' => 'API\EmployeeMasterDataController@file_download',
        'as' => 'employee.master.data.file_download'
    ]); 
});

//Employee Master Data Key Performance Routes
Route::group(['prefix' => 'employee_master_data/key_performance', 'middleware' => ['auth:api', 'employee.key.performance.maintenance']], function() {
    Route::get('/index', [
        'uses' => 'API\EmployeeKeyPerformanceController@index',
        'as' => 'employee.key.performance.index'
    ]);

    Route::post('/store', [
        'uses' => 'API\EmployeeKeyPerformanceController@store',
        'as' => 'employee.key.performance.store'
    ]); 

    Route::post('/update/{id}', [
        'uses' => 'API\EmployeeKeyPerformanceController@update',
        'as' => 'employee.key.performance.update'
    ]); 

    Route::post('/delete', [
        'uses' => 'API\EmployeeKeyPerformanceController@delete',
        'as' => 'employee.key.performance.delete'
    ]); 
});

// Employee Routes
Route::group(['prefix' => 'employee', 'middleware' => ['auth:api', 'employee.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\EmployeeController@index',
        'as' => 'employee.index',
    ]);

    Route::post('/list/view', [
        'uses' => 'API\EmployeeController@list_view',
        'as' => 'employee.list.view',
    ]);

    Route::get('/create', [
        'uses' => 'API\EmployeeController@create',
        'as' => 'employee.create',
    ]);

    Route::post('/store', [
        'uses' => 'API\EmployeeController@store',
        'as' => 'employee.store',
    ]);

    Route::get('/edit/{id}', [
        'uses' => 'API\EmployeeController@edit',
        'as' => 'employee.edit',
    ]);

    Route::post('/update/{id}', [
        'uses' => 'API\EmployeeController@update',
        'as' => 'employee.update',
    ]);

    Route::post('/import_employee/{id}', [
        'uses' => 'API\EmployeeController@import_employee',
        'as' => 'employee.import'
    ]);

    Route::post('/delete', [
        'uses' => 'API\EmployeeController@delete',
        'as' => 'employee.delete'
    ]);

    Route::post('/export_employee', [
        'uses' => 'API\EmployeeController@export_employee',
        'as' => 'employee.export',
    ]);

});

// Employee Export Route
// Route::get('/employee/export_employee/{id}', [
//     'uses' => 'API\EmployeeController@export_employee',
//     'as' => 'employee.export',
// ])->middleware(['employee.maintenance']);

// Employee Loans Routes
Route::group(['prefix' => 'employee_loans', 'middleware' => ['auth:api', 'employee.loans.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\EmployeeLoansController@index',
        'as' => 'employee.loans.index',
    ]);

    Route::post('/list/view', [
        'uses' => 'API\EmployeeLoansController@list_view',
        'as' => 'employee.loans.list.view',
    ]);

    Route::get('/create', [
        'uses' => 'API\EmployeeLoansController@create',
        'as' => 'employee.loans.create',
    ]);

    Route::post('/store', [
        'uses' => 'API\EmployeeLoansController@store',
        'as' => 'employee.loans.store',
    ]);

    Route::get('/edit/{id}', [
        'uses' => 'API\EmployeeLoansController@edit',
        'as' => 'employee.loans.edit',
    ]);

    Route::post('/update/{id}', [
        'uses' => 'API\EmployeeLoansController@update',
        'as' => 'employee.loans.update',
    ]);

    Route::post('/import_loans/{id}', [
        'uses' => 'API\EmployeeLoansController@import_loans',
        'as' => 'employee.loans.import'
    ]);

    Route::post('/delete', [
        'uses' => 'API\EmployeeLoansController@delete',
        'as' => 'employee.loans.delete'
    ]);

    Route::post('/export_loans', [
        'uses' => 'API\EmployeeLoansController@export_loans',
        'as' => 'employee.loan.export',
    ]);

});

// // Employee Loans Export Route
// Route::get('/employee_loans/export_loans/{id}', [
//     'uses' => 'API\EmployeeLoansController@export_loans',
//     'as' => 'employee.loan.export',
// ])->middleware(['employee.loans.maintenance']);

// Employee Premiums Routes
Route::group(['prefix' => 'employee_premiums', 'middleware' => ['auth:api', 'employee.premiums.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\EmployeePremiumsController@index',
        'as' => 'employee.premiums.index',
    ]);

    Route::post('/list/view', [
        'uses' => 'API\EmployeePremiumsController@list_view',
        'as' => 'employee.premiums.list.view',
    ]);

    Route::get('/create', [
        'uses' => 'API\EmployeePremiumsController@create',
        'as' => 'employee.premiums.create',
    ]);

    Route::post('/store', [
        'uses' => 'API\EmployeePremiumsController@store',
        'as' => 'employee.premiums.store',
    ]);

    Route::get('/edit/{id}', [
        'uses' => 'API\EmployeePremiumsController@edit',
        'as' => 'employee.premiums.edit',
    ]);

    Route::post('/update/{id}', [
        'uses' => 'API\EmployeePremiumsController@update',
        'as' => 'employee.premiums.update',
    ]);

    Route::post('/import_premiums/{id}', [
        'uses' => 'API\EmployeePremiumsController@import_premiums',
        'as' => 'employee.premiums.import'
    ]);

    Route::post('/delete', [
        'uses' => 'API\EmployeePremiumsController@delete',
        'as' => 'employee.premiums.delete'
    ]);

    Route::post('/export_premiums', [
        'uses' => 'API\EmployeePremiumsController@export_premiums',
        'as' => 'employee.premiums.export',
    ]);

});

// // Employee Premiums Export Route
// Route::get('/employee_premiums/export_premiums/{id}', [
//     'uses' => 'API\EmployeePremiumsController@export_premiums',
//     'as' => 'employee.premiums.export',
// ])->middleware(['employee.premiums.maintenance']);

// Employee Attlog Routes
Route::group(['prefix' => 'employee_attlog', 'middleware' => ['auth:api', 'employee.attlog.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\EmployeeAttlogController@index',
        'as' => 'employee.attlog.index',
    ]);

    Route::post('/list/view', [
        'uses' => 'API\EmployeeAttlogController@list_view',
        'as' => 'employee.attlog.list.view',
    ]);

    Route::get('/create', [
        'uses' => 'API\EmployeeAttlogController@create',
        'as' => 'employee.attlog.create',
    ]);

    Route::post('/store', [
        'uses' => 'API\EmployeeAttlogController@store',
        'as' => 'employee.attlog.store',
    ]);

    Route::get('/edit/{id}', [
        'uses' => 'API\EmployeeAttlogController@edit',
        'as' => 'employee.attlog.edit',
    ]);

    Route::post('/update/{id}', [
        'uses' => 'API\EmployeeAttlogController@update',
        'as' => 'employee.attlog.update',
    ]);

    Route::post('/import_attlog/{id}', [
        'uses' => 'API\EmployeeAttlogController@import_attlog',
        'as' => 'employee.attlog.import'
    ]);

    Route::post('/delete', [
        'uses' => 'API\EmployeeAttlogController@delete',
        'as' => 'employee.attlog.delete'
    ]);

    Route::post('/export_attlog', [
        'uses' => 'API\EmployeeAttlogController@export_attlog',
        'as' => 'employee.attlog.export',
    ]);
    
    // Employee Attlog File Download Route
    Route::post('/file/download', [
        'uses' => 'API\EmployeeAttlogController@download',
        'as' => 'employee.attlog.file.download',
    ]);

});

// Employee Attlog Export Route
// Route::get('/employee_attlog/export_attlog/{id}', [
//     'uses' => 'API\EmployeeAttlogController@export_attlog',
//     'as' => 'employee.attlog.export',
// ])->middleware(['employee.attlog.maintenance']);

// // Employee Attlog File Download Route
// Route::get('/employee_attlog/file/download', [
//     'uses' => 'API\EmployeeAttlogController@download',
//     'as' => 'employee.attlog.file.download',
// ])->middleware(['employee.attlog.maintenance']);

// Training Files Route
Route::group(['prefix' => 'training', 'middleware' => ['auth:api', 'training_file.maintenance']], function(){
    Route::get('/files', [
        'uses' => 'API\TrainingController@files',
        'as' => 'training.files'
    ]);

    Route::get('/user_files', [
        'uses' => 'API\TrainingController@user_files',
        'as' => 'training.user_files'
    ]);

    Route::post('/file/upload', [
        'uses' => 'API\TrainingController@file_upload',
        'as' => 'training.file.upload'
    ]);

    Route::post('/file/update/{id}', [
        'uses' => 'API\TrainingController@update',
        'as' => 'training.file.update'
    ]);

    Route::post('/file/delete', [
        'uses' => 'API\TrainingController@delete',
        'as' => 'training.file.delete'
    ]);

    // Route::get('/file/download', [
    //     'uses' => 'API\TrainingController@download',
    //     'as' => 'training.file.download'
    // ]);
});

// File Download Route
Route::get('/training/file/download', [
    'uses' => 'API\TrainingController@download',
    'as' => 'training.file.download',
])->middleware(['training_file.maintenance']);

//Permissions
Route::group(['prefix' => 'permission', 'middleware' => ['auth:api', 'permission.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\PermissionController@index',
        'as' => 'permission.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\PermissionController@create',
        'as' => 'permission.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\PermissionController@store',
        'as' => 'permission.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\PermissionController@edit',
        'as' => 'permission.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\PermissionController@update',
        'as' => 'permission.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\PermissionController@delete',
        'as' => 'permission.delete',
    ]);

});

//Roles
Route::group(['prefix' => 'role', 'middleware' => ['auth:api', 'role.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\RoleController@index',
        'as' => 'role.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\RoleController@create',
        'as' => 'role.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\RoleController@store',
        'as' => 'role.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\RoleController@edit',
        'as' => 'role.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\RoleController@update',
        'as' => 'role.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\RoleController@delete',
        'as' => 'role.delete',
    ]);

});

// Brand Routes
Route::group(['prefix' => 'brand', 'middleware' => ['auth:api', 'brand.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\BrandController@index',
        'as' => 'brand.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\BrandController@create',
        'as' => 'brand.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\BrandController@store',
        'as' => 'brand.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\BrandController@edit',
        'as' => 'brand.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\BrandController@update',
        'as' => 'brand.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\BrandController@delete',
        'as' => 'brand.delete',
    ]);

});

// Product Model Routes
Route::group(['prefix' => 'product_model', 'middleware' => ['auth:api', 'product_model.maintenance']], function(){
    Route::post('/index', [
        'uses' => 'API\ProductModelController@index',
        'as' => 'product_model.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\ProductModelController@create',
        'as' => 'product_model.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\ProductModelController@store',
        'as' => 'product_model.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\ProductModelController@edit',
        'as' => 'product_model.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\ProductModelController@update',
        'as' => 'product_model.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\ProductModelController@delete',
        'as' => 'product_model.delete',
    ]);

});

// Product Category Routes
Route::group(['prefix' => 'product_category', 'middleware' => ['auth:api', 'product_category.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\ProductCategoryController@index',
        'as' => 'product_category.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\ProductCategoryController@create',
        'as' => 'product_category.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\ProductCategoryController@store',
        'as' => 'product_category.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\ProductCategoryController@edit',
        'as' => 'product_category.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\ProductCategoryController@update',
        'as' => 'product_category.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\ProductCategoryController@delete',
        'as' => 'product_category.delete',
    ]);

});

// Expense Particular Routes
Route::group(['prefix' => 'expense_particular', 'middleware' => ['auth:api', 'expense_particular.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\ExpenseParticularController@index',
        'as' => 'expense_particular.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\ExpenseParticularController@create',
        'as' => 'expense_particular.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\ExpenseParticularController@store',
        'as' => 'expense_particular.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\ExpenseParticularController@edit',
        'as' => 'expense_particular.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\ExpenseParticularController@update',
        'as' => 'expense_particular.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\ExpenseParticularController@delete',
        'as' => 'expense_particular.delete',
    ]);

});

// Tactical Requisition Routes
Route::group(['prefix' => 'tactical_requisition', 'middleware' => ['auth:api', 'tactical_requisition.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\TacticalRequisitionController@index',
        'as' => 'tactical_requisition.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\TacticalRequisitionController@create',
        'as' => 'tactical_requisition.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\TacticalRequisitionController@store',
        'as' => 'tactical_requisition.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\TacticalRequisitionController@edit',
        'as' => 'tactical_requisition.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\TacticalRequisitionController@update',
        'as' => 'tactical_requisition.update',
    ]);
    Route::post('/approve', [
        'uses' => 'API\TacticalRequisitionController@approve',
        'as' => 'tactical_requisition.approve',
    ]);
    Route::post('/disapprove', [
        'uses' => 'API\TacticalRequisitionController@disapprove',
        'as' => 'tactical_requisition.disapprove',
    ]);
    Route::post('/delete', [
        'uses' => 'API\TacticalRequisitionController@delete',
        'as' => 'tactical_requisition.delete',
    ]);
    Route::post('/cancel', [
        'uses' => 'API\TacticalRequisitionController@cancel',
        'as' => 'tactical_requisition.cancel',
    ]);
    Route::post('/add_file/{id}', [
        'uses' => 'API\TacticalRequisitionController@add_file',
        'as' => 'tactical_requisition.add_file',
    ]);
    Route::post('/delete_file', [
        'uses' => 'API\TacticalRequisitionController@delete_file',
        'as' => 'tactical_requisition.delete_file',
    ]);

});

// Tactical Requisition File Download Route
Route::get('/tactical_requisition/attachment/download', [
    'uses' => 'API\TacticalRequisitionController@download',
    'as' => 'tactical_requisition.file.download',
])->middleware(['tactical_requisition.maintenance']);


// Marketing Event Routes
Route::group(['prefix' => 'marketing_event', 'middleware' => ['auth:api', 'marketing_event.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\MarketingEventController@index',
        'as' => 'marketing_event.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\MarketingEventController@create',
        'as' => 'marketing_event.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\MarketingEventController@store',
        'as' => 'marketing_event.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\MarketingEventController@edit',
        'as' => 'marketing_event.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\MarketingEventController@update',
        'as' => 'marketing_event.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\MarketingEventController@delete',
        'as' => 'marketing_event.delete',
    ]);

});

// Marketing Event User Map Routes
Route::group(['prefix' => 'marketing_event_user_map', 'middleware' => ['auth:api', 'marketing_event_user_map.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\MarketingEventUserMapController@index',
        'as' => 'marketing_event_user_map.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\MarketingEventUserMapController@create',
        'as' => 'marketing_event_user_map.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\MarketingEventUserMapController@store',
        'as' => 'marketing_event_user_map.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\MarketingEventUserMapController@edit',
        'as' => 'marketing_event_user_map.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\MarketingEventUserMapController@update',
        'as' => 'marketing_event_user_map.update',
    ]);
    Route::post('/update_approver_per_level', [
        'uses' => 'API\MarketingEventUserMapController@update_approver_per_level',
        'as' => 'marketing_event_user_map.update_approver_per_level',
    ]);
    Route::post('/delete', [
        'uses' => 'API\MarketingEventUserMapController@delete',
        'as' => 'marketing_event_user_map.delete',
    ]);

});

// Access Module Routes
Route::group(['prefix' => 'access_module', 'middleware' => ['auth:api', 'access_module.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\AccessModuleController@index',
        'as' => 'access_module.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\AccessModuleController@create',
        'as' => 'access_module.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\AccessModuleController@store',
        'as' => 'access_module.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\AccessModuleController@edit',
        'as' => 'access_module.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\AccessModuleController@update',
        'as' => 'access_module.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\AccessModuleController@delete',
        'as' => 'access_module.delete',
    ]);

});

// Access Chart Routes
Route::group(['prefix' => 'access_chart', 'middleware' => ['auth:api', 'access_chart.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\AccessChartController@index',
        'as' => 'access_chart.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\AccessChartController@create',
        'as' => 'access_chart.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\AccessChartController@store',
        'as' => 'access_chart.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\AccessChartController@edit',
        'as' => 'access_chart.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\AccessChartController@update',
        'as' => 'access_chart.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\AccessChartController@delete',
        'as' => 'access_chart.delete',
    ]);
    Route::get('/get_access_level', [
        'uses' => 'API\AccessChartController@get_access_level',
        'as' => 'get_access_level',
    ]);
    Route::post('/update_access_level/{id}', [
        'uses' => 'API\AccessChartController@update_access_level',
        'as' => 'update_access_level',
    ]);

});

// Access Chart User Map Routes
Route::group(['prefix' => 'access_chart_user_map', 'middleware' => ['auth:api', 'access_chart_user_map.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\AccessChartUserMapController@index',
        'as' => 'access_chart_user_map.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\AccessChartUserMapController@create',
        'as' => 'access_chart_user_map.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\AccessChartUserMapController@store',
        'as' => 'access_chart_user_map.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\AccessChartUserMapController@edit',
        'as' => 'access_chart_user_map.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\AccessChartUserMapController@update',
        'as' => 'access_chart_user_map.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\AccessChartUserMapController@delete',
        'as' => 'access_chart_user_map.delete',
    ]);

});

// Branch Routes
Route::group(['prefix' => 'branch', 'middleware' => ['auth:api', 'branch.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\BranchController@index',
        'as' => 'branch.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\BranchController@create',
        'as' => 'branch.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\BranchController@store',
        'as' => 'branch.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\BranchController@edit',
        'as' => 'branch.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\BranchController@update',
        'as' => 'branch.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\BranchController@delete',
        'as' => 'branch.delete',
    ]);

});

// Company Routes
Route::group(['prefix' => 'company', 'middleware' => ['auth:api', 'company.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\CompanyController@index',
        'as' => 'company.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\CompanyController@create',
        'as' => 'company.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\CompanyController@store',
        'as' => 'company.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\CompanyController@edit',
        'as' => 'company.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\CompanyController@update',
        'as' => 'company.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\CompanyController@delete',
        'as' => 'company.delete',
    ]);

});

// Rank Routes
Route::group(['prefix' => 'rank', 'middleware' => ['auth:api', 'rank.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\RankController@index',
        'as' => 'rank.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\RankController@create',
        'as' => 'rank.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\RankController@store',
        'as' => 'rank.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\RankController@edit',
        'as' => 'rank.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\RankController@update',
        'as' => 'rank.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\RankController@delete',
        'as' => 'rank.delete',
    ]);

});

// Position Routes
Route::group(['prefix' => 'position', 'middleware' => ['auth:api', 'position.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\PositionController@index',
        'as' => 'position.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\PositionController@create',
        'as' => 'position.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\PositionController@store',
        'as' => 'position.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\PositionController@edit',
        'as' => 'position.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\PositionController@update',
        'as' => 'position.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\PositionController@delete',
        'as' => 'position.delete',
    ]);

});


// Department Routes
Route::group(['prefix' => 'department', 'middleware' => ['auth:api', 'department.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\DepartmentController@index',
        'as' => 'department.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\DepartmentController@create',
        'as' => 'department.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\DepartmentController@store',
        'as' => 'department.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\DepartmentController@edit',
        'as' => 'department.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\DepartmentController@update',
        'as' => 'department.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\DepartmentController@delete',
        'as' => 'department.delete',
    ]);

});


// Division Routes
Route::group(['prefix' => 'division', 'middleware' => ['auth:api', 'division.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\DivisionController@index',
        'as' => 'division.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\DivisionController@create',
        'as' => 'division.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\DivisionController@store',
        'as' => 'division.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\DivisionController@edit',
        'as' => 'division.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\DivisionController@update',
        'as' => 'division.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\DivisionController@delete',
        'as' => 'division.delete',
    ]);

});


// SAP Database Routes
Route::group(['prefix' => 'sap_database', 'middleware' => ['auth:api', 'sap_database.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\SAPDatabaseController@index',
        'as' => 'sap.database.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\SAPDatabaseController@create',
        'as' => 'sap.database.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\SAPDatabaseController@store',
        'as' => 'sap.database.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\SAPDatabaseController@edit',
        'as' => 'sap.database.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\SAPDatabaseController@update',
        'as' => 'sap.database.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\SAPDatabaseController@delete',
        'as' => 'sap.database.delete',
    ]);

});


//Activity Logs
Route::group(['prefix' => 'activity_logs', 'middleware' => ['auth:api', 'activity.logs']], function(){
    Route::get('/index', [
        'uses' => 'API\ActivityLogController@activity_logs',
        'as' => 'activity_logs.index',
    ]);
    
});


// Online Banking Routes
Route::group(['prefix' => 'onlinebanking', 'middleware' => ['auth:api', 'online_banking.maintenance']], function(){
  

    Route::post('/store', [
        'uses' => 'API\OnlineBankingController@store',
        'as' => 'onlinebanking.store',
    ]);


    Route::get('/index', [
        'uses' => 'API\OnlineBankingController@index',
        'as' => 'onlinebanking.index',
    ]);


    Route::post('/approve', [
        'uses' => 'API\OnlineBankingController@approve',
        'as' => 'onlinebanking.approve',
    ]);


    Route::post('/disapprove', [
        'uses' => 'API\OnlineBankingController@disapprove',
        'as' => 'onlinebanking.disapprove',
    ]);


    Route::post('/download', [
        'uses' => 'API\OnlineBankingController@download',
        'as' => 'onlinebanking.download',
    ]);

    Route::post('/delete', [
        'uses' => 'API\OnlineBankingController@delete',
        'as' => 'onlinebanking.delete',
    ]);


    // Route::post('/update/{id}', [
    //     'uses' => 'API\DepartmentController@update',
    //     'as' => 'department.update',
    // ]);

    
    // Route::post('/delete', [
    //     'uses' => 'API\DepartmentController@delete',
    //     'as' => 'department.delete',
    // ]);

});


