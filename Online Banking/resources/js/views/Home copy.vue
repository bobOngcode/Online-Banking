<template>
  <v-app>
    <!-- Navbar -->
    <v-app-bar dense dark app>
      <v-btn icon @click.stop="drawer = !drawer">
        <v-app-bar-nav-icon></v-app-bar-nav-icon>
      </v-btn>
      <v-spacer></v-spacer>
      <v-menu offset-y :nudge-width="200">
        <template v-slot:activator="{ on, attrs }">
          <v-btn small rounded v-bind="attrs" v-on="on" color="grey darken-3">
            <v-icon> mdi-menu-down </v-icon>
          </v-btn>
        </template>
        <v-card color="grey lighten-3">
          <v-card-text class="text-center">
            <v-row>
              <v-col
                ><img
                  src="/img/default-profile.png"
                  width="120px"
                  height="100px"
                  alt="User"
                  style="border-radius: 50%"
              /></v-col>
            </v-row>
            <v-row>
              <v-col>
                <h5 class="text--secondary">
                  {{ user.name }}
                </h5>
                <h6 class="text--disabled">
                  {{
                    user.id === 1
                      ? "Administrator"
                      : user.position
                      ? user.position.name
                      : ""
                  }}
                </h6>
              </v-col>
            </v-row>
          </v-card-text>
          <v-divider class="pa-0 mb-0"></v-divider>
          <v-card-actions class="grey darken-2 d-flex justify-content-around">
            <div>
              <v-btn class="white--text" plain small @click="userProfile()">
                <v-icon small class="mr-1">mdi-account</v-icon> Profile
              </v-btn>
            </div>
            <div>
              <v-btn class="white--text" plain small @click="confirmLogout()">
                <v-icon small class="mr-1">mdi-logout</v-icon> Logout
              </v-btn>
            </div>
          </v-card-actions>
        </v-card>
      </v-menu>
    </v-app-bar>

    <!-- Sidebar -->
    <v-navigation-drawer v-model="drawer" dark app>
      <v-list>
        <v-list-item class="px-2">
          <v-list-item-avatar class="rounded-5" height="60" width="60">
            <v-img src="/img/addessa.jpg"></v-img>
          </v-list-item-avatar>
          <v-list-item-content>
            <v-list-item-title>Web Portal</v-list-item-title>
            <v-list-item-subtitle>{{ user.name }}</v-list-item-subtitle>
          </v-list-item-content>
        </v-list-item>
      </v-list>

      <v-divider></v-divider>

      <v-list dense>
        <!-- <v-list-item link to="/dashboard">
          <v-list-item-icon>
            <v-icon>mdi-view-dashboard</v-icon>
          </v-list-item-icon>
          <v-list-item-title>Dashboard</v-list-item-title>
        </v-list-item> -->
        <v-list-group
          no-action
          v-if="userPermissions.user_list || userPermissions.user_create"
        >
          <!-- List Group Icon-->
          <!-- <v-icon slot="prependIcon" class="ma-0">mdi-account-arrow-right-outline</v-icon> -->
          <!-- List Group Title -->
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>
                <v-icon class="mr-4">mdi-account-arrow-right-outline</v-icon>
                User Management</v-list-item-title
              >
            </v-list-item-content>
          </template>
          <!-- List Group Items -->
          <v-list-item link to="/user/index" v-if="userPermissions.user_list">
            <v-list-item-content>
              <v-list-item-title>User Accounts</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/user/create"
            v-if="userPermissions.user_create"
          >
            <v-list-item-content>
              <v-list-item-title>Create New</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-group>
        <v-list-group
          no-action
          v-if="
            userPermissions.product_list ||
            userPermissions.product_create ||
            userPermissions.inventory_recon_list ||
            userPermissions.inventory_recon_create ||
            userPermissions.brand_list ||
            userPermissions.brand_create ||
            userPermissions.product_category_list ||
            userPermissions.product_category_create ||
            userPermissions.product_model_list ||
            userPermissions.product_model_create ||
            userPermissions.serial_number_details
          "
        >
          <!-- List Group Icon-->
          <!-- <v-icon slot="prependIcon">mdi-barcode-scan</v-icon> -->
          <!-- List Group Title -->
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>
                <v-icon class="mr-4">mdi-barcode-scan</v-icon>
                Inventory</v-list-item-title
              >
            </v-list-item-content>
          </template>
          <!-- List Group Items -->
          <v-list-item
            link
            to="/inventory/reconciliation"
            v-if="userPermissions.inventory_recon_list"
          >
            <v-list-item-content>
              <v-list-item-title>Reconciliation</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/product/index"
            v-if="userPermissions.product_list"
          >
            <v-list-item-content>
              <v-list-item-title>Product Lists</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/scan_product"
            v-if="userPermissions.product_create"
          >
            <v-list-item-content>
              <v-list-item-title>Scan Product</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/brand/index"
            v-if="userPermissions.brand_list || userPermissions.brand_create"
          >
            <v-list-item-content>
              <v-list-item-title>Brand</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/product_model/index"
            v-if="
              userPermissions.product_model_list ||
              userPermissions.product_model_create
            "
          >
            <v-list-item-content>
              <v-list-item-title>Product Model</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/product_category/index"
            v-if="
              userPermissions.product_category_list ||
              userPermissions.product_category_create
            "
          >
            <v-list-item-content>
              <v-list-item-title>Product Category</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/serial_number_details"
            v-if="userPermissions.serial_number_details"
          >
            <v-list-item-content>
              <v-list-item-title>Serial Number Details</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-group>
        <v-list-group
          no-action
          v-if="
            userPermissions.employee_list ||
            userPermissions.employee_atlog_list ||
            userPermissions.employee_resigned_list ||
            userPermissions.employee_payroll_list ||
            userPermissions.employee_absences_list ||
            userPermissions.employee_overtime_list ||
            userPermissions.employee_holiday_pay_list ||
            userPermissions.employee_loans_list ||
            userPermissions.employee_premiums_list
          "
        >
          <!-- List Group Icon-->
          <!-- <v-icon slot="prependIcon">mdi-account-multiple</v-icon> -->
          <!-- List Group Title -->
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>
                <v-icon class="mr-4">mdi-account-multiple</v-icon>
                Employee</v-list-item-title
              >
            </v-list-item-content>
          </template>
          <!-- List Group Items -->
          <v-list-item
            link
            to="/employee/list"
            v-if="userPermissions.employee_list"
          >
            <v-list-item-content>
              <v-list-item-title>Employee Lists</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/employee/attlog/list"
            v-if="userPermissions.employee_attlog_list"
          >
            <v-list-item-content>
              <v-list-item-title>Attlog Reports</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/employee/resigned/list"
            v-if="userPermissions.employee_resigned_list"
          >
            <v-list-item-content>
              <v-list-item-title>Resigned</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/employee/payroll/list"
            v-if="userPermissions.employee_payroll_list"
          >
            <v-list-item-content>
              <v-list-item-title>Payroll</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/employee/absences/list"
            v-if="userPermissions.employee_absences_list"
          >
            <v-list-item-content>
              <v-list-item-title>Absences</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/employee/overtime/list"
            v-if="userPermissions.employee_overtime_list"
          >
            <v-list-item-content>
              <v-list-item-title>Overtime</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/employee/holiday_pay/list"
            v-if="userPermissions.employee_holiday_pay_list"
          >
            <v-list-item-content>
              <v-list-item-title>Holiday Pay</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/employee/loans/list"
            v-if="userPermissions.employee_loans_list"
          >
            <v-list-item-content>
              <v-list-item-title>Loans</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/employee/premiums/list"
            v-if="userPermissions.employee_premiums_list"
          >
            <v-list-item-content>
              <v-list-item-title>Premiums</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-group>
        <v-list-group
          no-action
          v-if="
            userPermissions.file_list ||
            userPermissions.file_create ||
            userPermissions.user_files
          "
        >
          <!-- List Group Icon-->
          <!-- <v-icon slot="prependIcon">mdi-folder-multiple-outline</v-icon> -->
          <!-- List Group Title -->
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>
                <v-icon class="mr-4">mdi-folder-multiple-outline</v-icon>
                Training</v-list-item-title
              >
            </v-list-item-content>
          </template>
          <!-- List Group Items -->
          <v-list-item
            link
            to="/training/my_files"
            v-if="userPermissions.user_files && userPermissions.user_files"
          >
            <v-list-item-content>
              <v-list-item-title>My Files</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/training/files_tutorials"
            v-if="userPermissions.file_list"
          >
            <v-list-item-content>
              <v-list-item-title>{{ "Files & Tutorials" }}</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-group>
        <v-list-group
          no-action
          v-if="
            userPermissions.tactical_requisition_list ||
            userPermissions.tactical_requisition_create ||
            userPermissions.marketing_event_list ||
            userPermissions.marketing_event_create
          "
        >
          <!-- List Group Icon-->
          <!-- <v-icon slot="prependIcon">mdi-file-multiple-outline</v-icon> -->
          <!-- List Group Title -->
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>
                <v-icon class="mr-4">mdi-file-multiple-outline</v-icon> Tactical
                Req.</v-list-item-title
              >
            </v-list-item-content>
          </template>
          <!-- List Group Items -->
          <v-list-item
            link
            to="/tactical_requisition/index"
            v-if="userPermissions.tactical_requisition_list"
          >
            <v-list-item-content>
              <v-list-item-title>Tactical List</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/tactical_requisition/create"
            v-if="userPermissions.tactical_requisition_create"
          >
            <v-list-item-content>
              <v-list-item-title>Create Tactical</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/marketing_event/index"
            v-if="userPermissions.marketing_event_list || userPermissions.marketing_event_create"
          >
            <v-list-item-content>
              <v-list-item-title>Marketing Event</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-group>
        <v-list-group
          no-action
          v-if="
            userPermissions.access_chart_list ||
            userPermissions.access_chart_create || 
            userPermissions.access_module_list ||
            userPermissions.access_module_create ||
            userPermissions.access_level_edit
          "
        >
          <!-- List Group Icon-->
          <!-- <v-icon slot="prependIcon">mdi-file-multiple-outline</v-icon> -->
          <!-- List Group Title -->
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>
                <v-icon class="mr-4">mdi-chart-arc</v-icon> Access
                Chart</v-list-item-title
              >
            </v-list-item-content>
          </template>
          <!-- List Group Items -->
          <v-list-item
            link
            to="/access_chart/index"
            v-if="userPermissions.access_chart_list"
          >
            <v-list-item-content>
              <v-list-item-title>Access Chart Lists</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/access_module/index"
            v-if="userPermissions.access_module_list || userPermissions.access_module_create"
          >
            <v-list-item-content>
              <v-list-item-title>Access Module Lists</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/access_level"
            v-if="userPermissions.access_level_edit"
          >
            <v-list-item-content>
              <v-list-item-title>Access Level</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-group>
        <v-list-group
          no-action
          v-if="
            userPermissions.branch_list ||
            userPermissions.branch_create ||
            userPermissions.position_list ||
            userPermissions.position_create ||
            userPermissions.role_list ||
            userPermissions.role_create ||
            userPermissions.permission_list ||
            userPermissions.permission_create || 
            userPermissions.sap_database_list ||
            userPermissions.sap_database_create
          "
        >
          <!-- List Group Icon-->
          <!-- <v-icon slot="prependIcon">mdi-cog</v-icon> -->
          <!-- List Group Title -->
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>
                <v-icon class="mr-4">mdi-cog</v-icon>
                Settings</v-list-item-title
              >
            </v-list-item-content>
          </template>
          <!-- List Group Items -->
          <v-list-item
            link
            to="/branch/index"
            v-if="userPermissions.branch_list || userPermissions.branch_create"
          >
            <v-list-item-content>
              <v-list-item-title>Branch</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/company/index"
            v-if="
              userPermissions.company_list || userPermissions.company_create
            "
          >
            <v-list-item-content>
              <v-list-item-title>Company</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/position/index"
            v-if="
              userPermissions.position_list || userPermissions.position_create
            "
          >
            <v-list-item-content>
              <v-list-item-title>Position</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/department/index"
            v-if="
              userPermissions.department_list ||
              userPermissions.department_create
            "
          >
            <v-list-item-content>
              <v-list-item-title>Department</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/role/index"
            v-if="userPermissions.role_list || userPermissions.role_create"
          >
            <v-list-item-content>
              <v-list-item-title>Role</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/permission/index"
            v-if="userPermissions.permission_list || userPermissions.permission_create"
          >
            <v-list-item-content>
              <v-list-item-title>Permission</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            link
            to="/sap_database/index"
            v-if="userPermissions.sap_database_list || userPermissions.sap_database_create"
          >
            <v-list-item-content>
              <v-list-item-title>SAP Database</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-list-item
            v-if="userPermissions.sap_database_create"
            @click="confirmSyncItemMasterData()"
          >
            <v-list-item-content>
              <v-list-item-title>Sync Item Master data</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-group>
        <!-- <v-list-item
          link
          to="/activity_logs"
          v-if="userPermissions.activity_logs"
        >
          <v-list-item-icon>
            <v-icon>mdi-history</v-icon>
          </v-list-item-icon>
          <v-list-item-title>Activity Logs</v-list-item-title>
        </v-list-item> -->
      </v-list>
    </v-navigation-drawer>
    <v-dialog v-model="dialog_sync" max-width="500px" persistent>
      <v-card>
        <v-card-text>
          <v-container>
            <v-row
              class="fill-height"
              align-content="center"
              justify="center"
              v-if="uploading"
            >
              <v-col class="subtitle-1 font-weight-bold text-center mt-4" cols="12">
                Syncing Item Master Data...
              </v-col>
              <v-col cols="6">
                <v-progress-linear
                  color="primary"
                  indeterminate
                  rounded
                  height="6"
                ></v-progress-linear>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
    
    <v-overlay :absolute="absolute" :value="overlay">
      <v-progress-circular
        :size="70"
        :width="7"
        color="primary"
        indeterminate
      ></v-progress-circular>
    </v-overlay>
    <!-- Content -->
    <router-view />
    <v-footer padless dense dark app>
      <v-col class="text-center" cols="12">
        Copyright © {{ new Date().getFullYear() }} —
        <strong> ADDESSA CORPORATION</strong>
      </v-col>
    </v-footer>
  </v-app>
</template>

<style>
  html { overflow-y: auto !important } /* show scrollbar when overflow */
</style>

<script>
import axios from "axios";
import { mapState, mapActions } from "vuex";

export default {
  data() {
    return {
      absolute: true,
      overlay: false,
      drawer: true,
      mini: false,
      right: null,
      selectedItem: 1,
      uploading: false,
      dialog_sync: false,
    };
  },

  methods: {

    logout() {
      this.overlay = true;
      axios.get("/api/auth/logout").then(
        (response) => {
          if (response.data.success) {
            this.overlay = false;

            // remove all local storage including access_token
            window.localStorage.clear();

            this.$router.push("/login").catch(() => {});
          }
        },
        (error) => {
          this.overlay = false;
          console.log(error);

          // if unauthenticated (401)
          if (error.response.status == "401") {

            // remove all local storage including access_token
            window.localStorage.clear();
            
            this.$router.push({ name: "login" });
          }
        }
      );
    },

    confirmLogout() {
      
      this.$swal({
        title: "Log Out",
        text: "Are you sure you want to log out?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "primary",
        cancelButtonColor: "secondary",
        confirmButtonText: "Log Out",
      }).then((result) => {
        
        if (result.value) {
          this.overlay = true;
          this.logout();
        }
        
      });

    },

    userProfile() {
      this.$router.push({ name: "user.profile" }).catch((e) => {});
    },

    confirmSyncItemMasterData()
    {
      this.$swal({
        title: "Sync Item Master Data",
        text: "You are about to sync Item Master Data!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "primary",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Proceed",
      }).then((result) => {
        // <--

        if (result.value) {
          // <-- if confirmed

          this.syncItemMasterData();
        }
      });
    },

    syncItemMasterData() {
      this.uploading = true;
      this.dialog_sync = true;
      axios.get('/api/product/sync_item_master_data').then(
        (response) => {
         
          this.uploading = false;
          this.dialog_sync = false;

          let data = response.data

          if(data.error)
          { 
            this.showErrorAlert('Error', data.error)
          }

          if(data.success)
          {
            this.showAlert('Item Master Data has been synced', 'success');
          }
          else if(data.empty)
          {
             this.showAlert('Item Master Data is up to date', 'info');
          }
          
        },
        (error) => {
          console.log(error);
          this.uploading = false;
          this.dialog_sync = false;
          
          this.showErrorAlert(error, error.response.data.message);
        }
      )
    },

    showAlert(title, icon) {
      this.$swal({
        position: "center",
        icon: icon,
        title: title,
        showConfirmButton: false,
        // timer: 2500,
      });
    },

    showErrorAlert(title, text) {
      this.$swal({
        position: "center",
        icon: "error",
        title: title,
        text: text,
        showConfirmButton: false,
        // timer: 10000,
      });
    },

    sessionExpiredSwal(){
      this.$swal({
          title: "Session Expired",
          text: "You have been inactive for 30 Minutes(s)",
          showCancelButton: false,
          confirmButtonText: "Ok",
      });

      this.logout();
    },

    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;
        if (
          action == "user-edit" ||
          action == "role-edit" ||
          action == "role-delete" ||
          action == "permission-delete"
        ) {
          this.userRolesPermissions();
        }
      };
    },
    
    ...mapActions("auth", ["getUser"]),
    ...mapActions("userRolesPermissions", ["userRolesPermissions"]),
  },

  computed: {
    isIdle() {

			return this.$store.state.idleVue.isIdle;
		},
    ...mapState("auth", ["user"]),
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
  },
  // watch: {
  //   isIdle(){
  //     if (this.isIdle) {
  //       this.sessionExpiredSwal();
  //     }
  //   }
  // },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");

    this.getUser();
    this.userRolesPermissions();
    // this.websocket();

  },
};
</script>