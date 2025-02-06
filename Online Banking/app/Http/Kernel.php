<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // 'throttle:120,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'user.maintenance' => \App\Http\Middleware\UserMaintenance::class,
        'product.maintenance' => \App\Http\Middleware\ProductMaintenance::class,
        'brand.maintenance' => \App\Http\Middleware\BrandMaintenance::class,
        'branch.maintenance' => \App\Http\Middleware\BranchMaintenance::class,
        'company.maintenance' => \App\Http\Middleware\CompanyMaintenance::class,
        'rank.maintenance' => \App\Http\Middleware\RankMaintenance::class,
        'position.maintenance' => \App\Http\Middleware\PositionMaintenance::class,
        'department.maintenance' => \App\Http\Middleware\DepartmentMaintenance::class,
        'division.maintenance' => \App\Http\Middleware\DivisionMaintenance::class,
        'employee.master.data.maintenance' => \App\Http\Middleware\EmployeeMasterDataMaintenance::class,
        'employee.key.performance.maintenance' => \App\Http\Middleware\EmployeeKeyPerformanceMaintenance::class,
        'employee.maintenance' => \App\Http\Middleware\EmployeeMaintenance::class,
        'employee.loans.maintenance' => \App\Http\Middleware\EmployeeLoansMaintenance::class,
        'employee.premiums.maintenance' => \App\Http\Middleware\EmployeePremiumsMaintenance::class,
        'employee.attlog.maintenance' => \App\Http\Middleware\EmployeeAttlogMaintenance::class,
        'permission.maintenance' => \App\Http\Middleware\PermissionMaintenance::class,
        'role.maintenance' => \App\Http\Middleware\RoleMaintenance::class,
        'product.maintenance' => \App\Http\Middleware\ProductMaintenance::class,
        'product_model.maintenance' => \App\Http\Middleware\ProductModelMaintenance::class,
        'product_category.maintenance' => \App\Http\Middleware\ProductCategoryMaintenance::class,
        'expense_particular.maintenance' => \App\Http\Middleware\ExpenseParticularMaintenance::class,
        'inventory_reconciliation.maintenance' => \App\Http\Middleware\InventoryReconciliationMaintenance::class,
        'tactical_requisition.maintenance' => \App\Http\Middleware\TacticalRequisitionMaintenance::class,
        'marketing_event.maintenance' => \App\Http\Middleware\MarketingEventMaintenance::class,
        'marketing_event_user_map.maintenance' => \App\Http\Middleware\MarketingEventUserMapMaintenance::class,
        'access_module.maintenance' => \App\Http\Middleware\AccessModuleMaintenance::class,
        'access_chart.maintenance' => \App\Http\Middleware\AccessChartMaintenance::class,
        'access_chart_user_map.maintenance' => \App\Http\Middleware\AccessChartUserMapMaintenance::class,
        'training_file.maintenance' => \App\Http\Middleware\TrainingFileMaintenance::class,
        'sap_database.maintenance' => \App\Http\Middleware\SAPDatabaseMaintenance::class,
        'activity.logs' => \App\Http\Middleware\ActivityLogs::class,
        'online_banking.maintenance' => \App\Http\Middleware\OnlineBankingMaintenance::class, //for additional online banking
    ];
}
