<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'brand-list',
            'brand-create',
            'brand-edit',
            'brand-delete',
            'branch-list',
            'branch-create',
            'branch-edit',
            'branch-delete',
            'company-list',
            'company-create',
            'company-edit',
            'company-delete',
            'rank-list',
            'rank-create',
            'rank-edit',
            'rank-delete',
            'position-list',
            'position-create',
            'position-edit',
            'position-delete',
            'department-list',
            'department-create',
            'department-edit',
            'department-delete',
            'division-list',
            'division-create',
            'division-edit',
            'division-delete',
            'product-list',
            'product-list-all',
            'product-list-scanned',
            'product-create',
            'product-scan',
            'product-edit',
            'product-delete',
            'product-import',
            'product-export',
            'product-clear-list',
            'product-reconcile',
            'product-template-download',
            'product-model-list',
            'product-model-create',
            'product-model-edit',
            'product-model-delete',
            'product-category-list',
            'product-category-create',
            'product-category-edit',
            'product-category-delete',
            'inventory-recon-list',
            'inventory-recon-create',
            'inventory-recon-sync',
            'inventory-recon-edit',
            'inventory-recon-delete',
            'inventory-recon-audit',
            'employee-master-data-list',
            'employee-master-data-create',
            'employee-master-data-edit',
            'employee-master-data-delete',
            'employee-master-data-import',
            'employee-master-data-export',
            'employee-list',
            'employee-list-all',
            'employee-create',
            'employee-edit',
            'employee-delete',
            'employee-list-import',
            'employee-list-export',
            'employee-clear-list',
            'employee-resigned-list',
            'employee-resigned-all',
            'employee-resigned-import',
            'employee-resigned-export',
            'employee-resigned-clear-list',
            'employee-payroll-list',
            'employee-payroll-list-all',
            'employee-payroll-import',
            'employee-payroll-export',
            'employee-payroll-clear-list',
            'employee-absences-list',
            'employee-absences-list-all',
            'employee-absences-import',
            'employee-absences-export',
            'employee-absences-clear-list',
            'employee-overtime-list',
            'employee-overtime-list-all',
            'employee-overtime-import',
            'employee-overtime-export',
            'employee-overtime-clear-list',
            'employee-holiday-pay-list',
            'employee-holiday-pay-list-all',
            'employee-holiday-pay-import',
            'employee-holiday-pay-export',
            'employee-holiday-pay-clear-list',
            'employee-loans-list',
            'employee-loans-list-all',
            'employee-loans-create',
            'employee-loans-edit',
            'employee-loans-delete',
            'employee-loans-import',
            'employee-loans-export',
            'employee-loans-clear-list',
            'employee-premiums-list',
            'employee-premiums-list-all',
            'employee-premiums-create',
            'employee-premiums-edit',
            'employee-premiums-delete',
            'employee-premiums-import',
            'employee-premiums-export',
            'employee-premiums-clear-list',
            'employee-attlog-list',
            'employee-attlog-list-all',
            'employee-attlog-create',
            'employee-attlog-edit',
            'employee-attlog-delete',
            'employee-attlog-import',
            'employee-attlog-export',
            'employee-attlog-clear-list',
            'file-list',
            'file-create',
            'file-edit',
            'file-delete',
            'user-files',
            'expense-particular-list',
            'expense-particular-create',
            'expense-particular-edit',
            'expense-particular-delete',
            'tactical-requisition-list',
            'tactical-requisition-create',
            'tactical-requisition-edit',
            'tactical-requisition-delete',
            'tactical-requisition-cancel',
            'tactical-requisition-approve',
            'tactical-requisition-calendar',
            'tactical-attachment-upload',
            'tactical-attachment-delete',
            'marketing-event-list',
            'marketing-event-create',
            'marketing-event-edit',
            'marketing-event-delete',
            'access-chart-list',
            'access-chart-create',
            'access-chart-edit',
            'access-chart-delete',
            'access-level-edit',
            'approving-officer-list',
            'approving-officer-create',
            'approving-officer-edit',
            'approving-officer-delete',
            'access-module-list',
            'access-module-create',
            'access-module-edit',
            'access-module-delete',
            'permission-list',
            'permission-create',
            'permission-edit',
            'permission-delete',
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'sap-database-list',
            'sap-database-create',
            'sap-database-edit',
            'sap-database-delete',
            'serial-number-details',
            'inventory-on-hand',
            'sync-item-master-data',
            'activity-logs',
         ];
 
 
         foreach ($permissions as $permission) {
              Permission::firstOrCreate(['name' => $permission]);
         }
    }
}
