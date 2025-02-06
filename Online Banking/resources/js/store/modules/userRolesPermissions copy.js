import axios from 'axios';
import router from '../../router';

const state = {
  userPermissions: {
    user_list: false,
    user_create: false,
    user_edit: false,
    user_delete: false,
    brand_list: false,
    brand_create: false,
    brand_edit: false,
    brand_delete: false,
    branch_list: false,
    branch_create: false,
    branch_edit: false,
    branch_delete: false,
    company_list: false,
    company_create: false,
    company_edit: false,
    company_delete: false,
    position_list: false,
    position_create: false,
    position_edit: false,
    position_delete: false,
    department_list: false,
    department_create: false,
    department_edit: false,
    department_delete: false,
    product_category_list: false,
    product_category_create: false,
    product_category_edit: false,
    product_category_delete: false,
    product_model_list: false,
    product_model_create: false,
    product_model_edit: false,
    product_model_delete: false,
    product_list: false,
    product_list_all: false,
    product_create: false,
    product_edit: false,
    product_delete: false,
    product_clear_list: false,
    product_reconcile: false,
    product_export: false,
    serial_number_details: false,
    sync_item_master_data: false,
    inventory_recon_list: false,
    inventory_recon_list_all: false,
    inventory_recon_create: false,
    inventory_recon_sync: false,
    inventory_recon_edit: false,
    inventory_recon_delete: false,
    inventory_recon_audit: false,
    employee_list: false,
    employee_list_all: false,
    employee_create: false,
    employee_edit: false,
    employee_delete: false,
    employee_list_import: false,
    employee_list_export: false,
    employee_clear_list: false,
    employee_attlog_list: false,
    employee_attlog_list_all: false,
    employee_attlog_create: false,
    employee_attlog_edit: false,
    employee_attlog_delete: false,
    employee_attlog_import: false,
    employee_attlog_export: false,
    employee_attlog_clear_list: false,
    employee_resigned_list: false,
    employee_resigned_list_all: false,
    employee_resigned_create: false,
    employee_resigned_edit: false,
    employee_resigned_delete: false,
    employee_resigned_import: false,
    employee_resigned_export: false,
    employee_resigned_clear_list: false,
    employee_payroll_list: false,
    employee_payroll_list_all: false,
    employee_payroll_create: false,
    employee_payroll_edit: false,
    employee_payroll_delete: false,
    employee_payroll_import: false,
    employee_payroll_export: false,
    employee_payroll_clear_list: false,
    employee_absences_list: false,
    employee_absences_list_all: false,
    employee_absences_create: false,
    employee_absences_edit: false,
    employee_absences_delete: false,
    employee_absences_import: false,
    employee_absences_export: false,
    employee_absences_clear_list: false,
    employee_overtime_list: false,
    employee_overtime_list_all: false,
    employee_overtime_create: false,
    employee_overtime_edit: false,
    employee_overtime_delete: false,
    employee_overtime_import: false,
    employee_overtime_export: false,
    employee_overtime_clear_list: false,
    employee_holiday_pay_list: false,
    employee_holiday_pay_list_all: false,
    employee_holiday_create: false,
    employee_holiday_edit: false,
    employee_holiday_delete: false,
    employee_holiday_pay_import: false,
    employee_holiday_pay_export: false,
    employee_holiday_pay_clear_list: false,
    employee_loans_list: false,
    employee_loans_list_all: false,
    employee_loans_create: false,
    employee_loans_edit: false,
    employee_loans_delete: false,
    employee_loans_import: false,
    employee_loans_export: false,
    employee_loans_clear_list: false,
    employee_premiums_list: false,
    employee_premiums_list_all: false,
    employee_premiums_create: false,
    employee_premiums_edit: false,
    employee_premiums_delete: false,
    employee_premiums_import: false,
    employee_premiums_export: false,
    employee_premiums_clear_list: false,
    file_list: false,
    file_create: false,
    file_edit: false,
    file_delete: false,
    user_files: false,
    expense_particular_list: false,
    expense_particular_create: false,
    expense_particular_edit: false,
    expense_particular_delete: false,
    tactical_requisition_list: false,
    tactical_requisition_create: false,
    tactical_requisition_edit: false,
    tactical_requisition_delete: false,
    tactical_requisition_approve: false,
    tactical_attachment_upload: false,
    tactical_attachment_delete: false,
    marketing_event_list: false,
    marketing_event_create: false,
    marketing_event_edit: false,
    marketing_event_delete: false,
    access_chart_list: false,
    access_chart_create: false,
    access_chart_edit: false,
    access_chart_delete: false,
    access_module_list: false,
    access_module_create: false,
    access_module_edit: false,
    access_module_delete: false,
    access_level_edit: false,
    approving_officer_list: false,
    approving_officer_create: false,
    approving_officer_edit: false,
    approving_officer_delete: false,
    role_list: false,
    role_create: false,
    role_edit: false,
    role_delete: false,
    permission_list: false,
    permission_create: false,
    permission_edit: false,
    permission_delete: false,
    sap_database_list: false,
    sap_database_create: false,
    sap_database_edit: false,
    sap_database_delete: false,
    activity_logs: false,
  },
  userRoles: {
    administrator: false,
    hr_admin: false,
    inventory_admin: false,
    inventory_branch: false,
    audit_admin: false,
    mss: false,
  },
  userRolesPermissionsIsLoaded: false,
};

const getters = {};

const actions = {
  async userRolesPermissions({ commit }) {
    let response = await axios.get("/api/user/roles_permissions").then((response) => {

      commit('setUserRoles', response.data.user_roles);
      commit('setUserPermissions', response.data.user_permissions);

    },
      (error) => {
        if (error.response.status == "401") {
          router.push({ name: "unauthorize" });
        }
      });
  },
};

const mutations = {
  setUserRoles(state, roles) {

    let role = state.userRoles;

    role.administrator = roles.includes("Administrator");
    role.hr_admin = roles.includes("HR Admin");
    role.inventory_admin = roles.includes("Inventory Admin");
    role.inventory_branch = roles.includes("Inventory Branch");
    role.audit_admin = roles.includes("Audit Admin");
    role.mss = roles.includes("MSS");
  },
  setUserPermissions(state, permissions) {

    let permission = state.userPermissions;

    // USER RECORD MAINTENANCE PERMISSIONS
    permission.user_list = permissions.includes("user-list");
    permission.user_create = permissions.includes("user-create");
    permission.user_edit = permissions.includes("user-edit");
    permission.user_delete = permissions.includes("user-delete");

    // BRAND RECORD MAINTENANCE PERMISSIONS
    permission.brand_list = permissions.includes("brand-list");
    permission.brand_create = permissions.includes("brand-create");
    permission.brand_edit = permissions.includes("brand-edit");
    permission.brand_delete = permissions.includes("brand-delete");

    // PRODUCT CATEGORY RECORD MAINTENANCE PERMISSIONS
    permission.product_category_list = permissions.includes("product-category-list");
    permission.product_category_create = permissions.includes("product-category-create");
    permission.product_category_edit = permissions.includes("product-category-edit");
    permission.product_category_delete = permissions.includes("product-category-delete");

    // PRODUCT MODEL RECORD MAINTENANCE PERMISSIONS
    permission.product_model_list = permissions.includes("product-model-list");
    permission.product_model_create = permissions.includes("product-model-create");
    permission.product_model_edit = permissions.includes("product-model-edit");
    permission.product_model_delete = permissions.includes("product-model-delete");

    // BRANCH RECORD MAINTENANCE PERMISSIONS
    permission.branch_list = permissions.includes("branch-list");
    permission.branch_create = permissions.includes("branch-create");
    permission.branch_edit = permissions.includes("branch-edit");
    permission.branch_delete = permissions.includes("branch-delete");

    // COMPANY RECORD MAINTENANCE PERMISSIONS
    permission.company_list = permissions.includes("company-list");
    permission.company_create = permissions.includes("company-create");
    permission.company_edit = permissions.includes("company-edit");
    permission.company_delete = permissions.includes("company-delete");

    // POSITION RECORD MAINTENANCE PERMISSIONS
    permission.position_list = permissions.includes("position-list");
    permission.position_create = permissions.includes("position-create");
    permission.position_edit = permissions.includes("position-edit");
    permission.position_delete = permissions.includes("position-delete");

    // DEPARTMENT RECORD MAINTENANCE PERMISSIONS
    permission.department_list = permissions.includes("department-list");
    permission.department_create = permissions.includes("department-create");
    permission.department_edit = permissions.includes("department-edit");
    permission.department_delete = permissions.includes("department-delete");

    // PRODUCT RECORD MAINTENANCE PERMISSIONS
    permission.product_list = permissions.includes("product-list");
    permission.product_list_all = permissions.includes("product-list-all");
    permission.product_create = permissions.includes("product-create");
    permission.product_edit = permissions.includes("product-edit");
    permission.product_delete = permissions.includes("product-delete");
    permission.product_export = permissions.includes("product-export");
    permission.product_clear_list = permissions.includes("product-clear-list");
    permission.product_reconcile = permissions.includes("product-reconcile");
    permission.serial_number_details = permissions.includes("serial-number-details");
    permission.sync_item_master_data = permissions.includes("sync-item-master-data");

    // INVENTORY RECONCILIATION RECORD MAINTENANCE PERMISSIONS
    permission.inventory_recon_list = permissions.includes("inventory-recon-list");
    permission.inventory_recon_list_all = permissions.includes("inventory-recon-list-all");
    permission.inventory_recon_create = permissions.includes("inventory-recon-create");
    permission.inventory_recon_sync = permissions.includes("inventory-recon-sync");
    permission.inventory_recon_edit = permissions.includes("inventory-recon-edit");
    permission.inventory_recon_delete = permissions.includes("inventory-recon-delete");
    permission.inventory_recon_audit = permissions.includes("inventory-recon-audit");

    // EMPLOYEE RECORD MAINTENANCE PERMISSIONS
    permission.employee_list = permissions.includes("employee-list");
    permission.employee_list_all = permissions.includes("employee-list-all");
    permission.employee_create = permissions.includes("employee-create");
    permission.employee_edit = permissions.includes("employee-edit");
    permission.employee_delete = permissions.includes("employee-delete");
    permission.employee_list_import = permissions.includes("employee-list-import");
    permission.employee_list_export = permissions.includes("employee-list-export");
    permission.employee_clear_list = permissions.includes("employee-clear-list");

    // EMPLOYEE ATTLOG RECORD MAINTENANCE PERMISSIONS
    permission.employee_attlog_list = permissions.includes("employee-attlog-list");
    permission.employee_attlog_list_all = permissions.includes("employee-attlog-list-all");
    permission.employee_attlog_create = permissions.includes("employee-attlog-create");
    permission.employee_attlog_edit = permissions.includes("employee-attlog-edit");
    permission.employee_attlog_delete = permissions.includes("employee-attlog-delete");
    permission.employee_attlog_import = permissions.includes("employee-attlog-import");
    permission.employee_attlog_export = permissions.includes("employee-attlog-export");
    permission.employee_attlog_clear_list = permissions.includes("employee-attlog-clear-list");

    // RESIGNED EMPLOYEE RECORD MAINTENANCE PERMISSIONS
    permission.employee_resigned_list = permissions.includes("employee-resigned-list");
    permission.employee_resigned_list_all = permissions.includes("employee-resigned-list-all");
    permission.employee_reqigned_create = permissions.includes("employee-resigned-create");
    permission.employee_reqigned_edit = permissions.includes("employee-resigned-edit");
    permission.employee_reqigned_delete = permissions.includes("employee-resigned-delete");
    permission.employee_resigned_import = permissions.includes("employee-resigned-import");
    permission.employee_resigned_export = permissions.includes("employee-resigned-export");
    permission.employee_resigned_clear_list = permissions.includes("employee-resigned-clear-list");

    // PAYROLL RECORD MAINTENANCE PERMISSIONS
    permission.employee_payroll_list = permissions.includes("employee-payroll-list");
    permission.employee_payroll_list_all = permissions.includes("employee-payroll-list-all");
    permission.employee_payroll_create = permissions.includes("employee-payroll-create");
    permission.employee_payroll_edit = permissions.includes("employee-payroll-edit");
    permission.employee_payroll_delete = permissions.includes("employee-payroll-delete");
    permission.employee_payroll_import = permissions.includes("employee-payroll-import");
    permission.employee_payroll_export = permissions.includes("employee-payroll-export");
    permission.employee_payroll_clear_list = permissions.includes("employee-payroll-clear-list");

    // EMPLOYEE ABSENCES RECORD MAINTENANCE PERMISSIONS
    permission.employee_absences_list = permissions.includes("employee-absences-list");
    permission.employee_absences_list_all = permissions.includes("employee-absences-list-all");
    permission.employee_absences_create = permissions.includes("employee-absences-create");
    permission.employee_absences_edit = permissions.includes("employee-absences-edit");
    permission.employee_absences_delete = permissions.includes("employee-absences-delete");
    permission.employee_absences_import = permissions.includes("employee-absences-import");
    permission.employee_absences_export = permissions.includes("employee-absences-export");
    permission.employee_absences_clear_list = permissions.includes("employee-absences-clear-list");

    // EMPLOYEE OVERTIME RECORD MAINTENANCE PERMISSIONS
    permission.employee_overtime_list = permissions.includes("employee-overtime-list");
    permission.employee_overtime_list_all = permissions.includes("employee-overtime-list-all");
    permission.employee_overtime_create = permissions.includes("employee-overtime-create");
    permission.employee_overtime_edit = permissions.includes("employee-overtime-edit");
    permission.employee_overtime_delete = permissions.includes("employee-overtime-delete");
    permission.employee_overtime_import = permissions.includes("employee-overtime-import");
    permission.employee_overtime_export = permissions.includes("employee-overtime-export");
    permission.employee_overtime_clear_list = permissions.includes("employee-overtime-clear-list");

    // EMPLOYEE HOLIDAY PAY RECORD MAINTENANCE PERMISSIONS
    permission.employee_holiday_pay_list = permissions.includes("employee-holiday-pay-list");
    permission.employee_holiday_pay_list_all = permissions.includes("employee-holiday-pay-list-all");
    permission.employee_holiday_create = permissions.includes("employee-holiday-create");
    permission.employee_holiday_edit = permissions.includes("employee-holiday-edit");
    permission.employee_holiday_delete = permissions.includes("employee-holiday-delete");
    permission.employee_holiday_pay_import = permissions.includes("employee-holiday-pay-import");
    permission.employee_holiday_pay_export = permissions.includes("employee-holiday-pay-export");
    permission.employee_holiday_pay_clear_list = permissions.includes("employee-holiday-pay-clear-list");

    // EMPLOYEE LOANS RECORD MAINTENANCE PERMISSIONS
    permission.employee_loans_list = permissions.includes("employee-loans-list");
    permission.employee_loans_list_all = permissions.includes("employee-loans-list-all");
    permission.employee_loans_create = permissions.includes("employee-loans-create");
    permission.employee_loans_edit = permissions.includes("employee-loans-edit");
    permission.employee_loans_delete = permissions.includes("employee-loans-delete");
    permission.employee_loans_import = permissions.includes("employee-loans-import");
    permission.employee_loans_export = permissions.includes("employee-loans-export");
    permission.employee_loans_clear_list = permissions.includes("employee-loans-clear-list");

    // EMPLOYEE PREMIUMS RECORD MAINTENANCE PERMISSIONS
    permission.employee_premiums_list = permissions.includes("employee-premiums-list");
    permission.employee_premiums_list_all = permissions.includes("employee-premiums-list-all");
    permission.employee_premiums_create = permissions.includes("employee-premiums-create");
    permission.employee_premiums_edit = permissions.includes("employee-premiums-edit");
    permission.employee_premiums_delete = permissions.includes("employee-premiums-delete");
    permission.employee_premiums_import = permissions.includes("employee-premiums-import");
    permission.employee_premiums_export = permissions.includes("employee-premiums-export");
    permission.employee_premiums_clear_list = permissions.includes("employee-premiums-clear-list");

    // TRAINING FILE RECORD MAINTENANCE PERMISSIONS
    permission.file_list = permissions.includes("file-list");
    permission.file_create = permissions.includes("file-create");
    permission.file_edit = permissions.includes("file-edit");
    permission.file_delete = permissions.includes("file-delete");
    permission.user_files = permissions.includes("user-files");

    // EXPENSE PARTICULAR RECORD MAINTENANCE PERMISSIONS
    permission.expense_particular_list = permissions.includes("expense-particular-list");
    permission.expense_particular_create = permissions.includes("expense-particular-create");
    permission.expense_particular_edit = permissions.includes("expense-particular-edit");
    permission.expense_particular_delete = permissions.includes("expense-particular-delete");

    // TACTICAL REQUISITION RECORD MAINTENANCE PERMISSIONS
    permission.tactical_requisition_list = permissions.includes("tactical-requisition-list");
    permission.tactical_requisition_create = permissions.includes("tactical-requisition-create");
    permission.tactical_requisition_edit = permissions.includes("tactical-requisition-edit");
    permission.tactical_requisition_delete = permissions.includes("tactical-requisition-delete");
    permission.tactical_requisition_approve = permissions.includes("tactical-requisition-approve");
    permission.tactical_attachment_upload = permissions.includes("tactical-attachment-upload");
    permission.tactical_attachment_delete = permissions.includes("tactical-attachment-delete");

    // MARKETING EVENT RECORD MAINTENANCE PERMISSIONS
    permission.marketing_event_list = permissions.includes("marketing-event-list");
    permission.marketing_event_create = permissions.includes("marketing-event-create");
    permission.marketing_event_edit = permissions.includes("marketing-event-edit");
    permission.marketing_event_delete = permissions.includes("marketing-event-delete");

    // ACCESS MODULE RECORD MAINTENANCE PERMISSIONS
    permission.access_module_list = permissions.includes("access-module-list");
    permission.access_module_create = permissions.includes("access-module-create");
    permission.access_module_edit = permissions.includes("access-module-edit");
    permission.access_module_delete = permissions.includes("access-module-delete");

    // ACCESS CHART RECORD MAINTENANCE PERMISSIONS
    permission.access_chart_list = permissions.includes("access-chart-list");
    permission.access_chart_create = permissions.includes("access-chart-create");
    permission.access_chart_edit = permissions.includes("access-chart-edit");
    permission.access_chart_delete = permissions.includes("access-chart-delete");
    permission.access_level_edit = permissions.includes("access-level-edit");

    // APPROVING OFFICER RECORD MAINTENANCE PERMISSIONS
    permission.approving_officer_list = permissions.includes("access-chart-list");
    permission.approving_officer_create = permissions.includes("access-chart-create");
    permission.approving_officer_edit = permissions.includes("access-chart-edit");
    permission.approving_officer_delete = permissions.includes("access-chart-delete");

    // PERMISSION RECORD MAINTENANCE PERMISSIONS
    permission.permission_list = permissions.includes("permission-list");
    permission.permission_create = permissions.includes("permission-create");
    permission.permission_edit = permissions.includes("permission-edit");
    permission.permission_delete = permissions.includes("permission-delete");

    // ROLE RECORD MAINTENANCE PERMISSIONS
    permission.role_list = permissions.includes("role-list");
    permission.role_create = permissions.includes("role-create");
    permission.role_edit = permissions.includes("role-edit");
    permission.role_delete = permissions.includes("role-delete");

    // SAP DATABASE RECORD MAINTENANCE PERMISSIONS
    permission.sap_database_list = permissions.includes("sap-database-list");
    permission.sap_database_create = permissions.includes("sap-database-create");
    permission.sap_database_edit = permissions.includes("sap-database-edit");
    permission.sap_database_delete = permissions.includes("sap-database-delete");

    // ACTIVITY LOG PERMISSIONS
    permission.activity_logs = permissions.includes("activity-logs");

    // set true if user roles and permissions value successfully assigned
    state.userRolesPermissionsIsLoaded = true;

  },

};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}