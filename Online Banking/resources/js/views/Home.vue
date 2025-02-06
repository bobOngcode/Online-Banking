
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
      <v-list class="pb-0">
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
      <v-list dense class="pt-0" expand>
        <template v-for=" (menu, index) in menuList " v-if="menu.hasPermission">
          <v-divider></v-divider>
          <span class="subtitle-2 font-weight-bold ml-4 blue--text text--lighten-4"> 
            {{ menu.group_header_title }} 
          </span>
  
          <template v-for=" (list, i) in menu.list_items ">
            <v-list-group
              :class="i === 0 ? 'mt-2' : ''"
              no-action
              v-if="list.children.length && list.hasPermission"
            >
              <template v-slot:activator>
                <v-list-item-content>
                  <v-list-item-title>
                    <v-icon class="mr-2">{{ list.icon }}</v-icon>
                    {{ list.title }}
                  </v-list-item-title>
                </v-list-item-content>
              </template>
              <template v-for=" child in list.children ">
                <v-list-item
                  link
                  :to="child.link"
                  v-if="child.hasPermission"
                >
                  <v-list-item-content>
                    <v-list-item-title>{{ child.title }}</v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
              </template>
            </v-list-group>
            <v-list-item
              :class="i === 0 ? 'mt-2' : ''"
              @click="callMethod(list.method)"
              link
              :to="list.link"
              v-if="!list.children.length && list.hasPermission"
            >
              <v-list-item-content >
                <v-list-item-title> 
                  <v-icon class="mr-2">{{ list.icon }}</v-icon>
                  {{ list.title }}
                </v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </template>
        </template>
        
        <!-- <v-list-item
          link
          to="/activity_logs"
          v-if="hasPermission('activity-logs')"
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
import { mapState, mapActions, mapGetters } from "vuex";

export default {
  data() {
    return {
      absolute: true,
      overlay: false,
      drawer: true,
      mini: false,
      right: null,
      selectedItem: 1,
      dialog_sync: false,
      menu_items: []
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

      this.dialog_sync = true;
      axios.get('/api/product/sync_item_master_data').then(
        (response) => {

          this.dialog_sync = false;

          let data = response.data
          console.log(data);
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

    callMethod(name){
      name ? this[name]() : '';
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

    menuList() {
      
      let menu = [

        { // START Dashboard
          group_header_title: 'Dashboard',
          hasPermission: false,
          list_items: [
            {
              title: 'Dashboard',
              icon: 'mdi-view-dashboard',
              link: '/dashboard',
              method: '',
              hasPermission: this.hasRole('Administrator'),
              children: [],
            },
          ]
        }, // END Dashboard

        { // START Inventory / Purchasing group menu
          group_header_title: 'Inventory / Purchasing',
          hasPermission: false,
          list_items: [
            {
              title: 'Inventory',
              icon: 'mdi-barcode-scan',
              link: '',
              method: '',
              hasPermission: false,
              children: [
                { 
                  title: 'Product List',
                  link: '/product/index',
                  hasPermission: this.hasPermission('product-list'),
                },
                { 
                  title: 'Reconciliation',
                  link: '/inventory/reconciliation',
                  hasPermission: this.hasPermission('inventory-recon-list'),
                },
                { 
                  title: 'Scan Product',
                  link: '/scan_product',
                  hasPermission: this.hasPermission('product-scan'),
                },
                { 
                  title: 'Scanned Product List',
                  link: '/scanned_product_list',
                  hasPermission: this.hasPermission('product-list-scanned'),
                },
                { 
                  title: 'Serial Number Details',
                  link: '/serial_number_details',
                  hasPermission: this.hasPermission('serial-number-details'),
                },
                { 
                  title: 'Inventory On Hand',
                  link: '/inventory_on_hand',
                  hasPermission: this.hasPermission('inventory-on-hand'),
                },
              ] 
            },
            {
              title: 'Item Master Data',
              icon: 'mdi-folder-open',
              link: '',
              method: '',
              hasPermission: false,
              children: [
                { 
                  title: 'Brand',
                  link: '/brand/index',
                  hasPermission: this.hasAnyPermission('brand-list', 'brand-create'),
                },
                { 
                  title: 'Product Model',
                  link: '/product_model/index',
                  hasPermission: this.hasAnyPermission('product-model-list', 'product-model-create'),
                },
                { 
                  title: 'Product Category',
                  link: '/product_category/index',
                  hasPermission: this.hasAnyPermission('product-model-list', 'product-model-create'),
                },
              ] 
            },
          ]
        }, // END Inventory / Purchasing group menu
        { // START HR/ Payroll group menu
          group_header_title: 'HR / Payroll',
          hasPermission: false,
          list_items: [
            {
              title: 'Employee',
              icon: 'mdi-account-multiple',
              link: '',
              method: '',
              hasPermission: false,
              children: [
                { 
                  title: 'Master Data',
                  link: '/employee/master_data',
                  hasPermission: this.hasPermission('employee-master-data-list'),
                },
                { 
                  title: 'Employee Reports',
                  link: '/employee/list',
                  hasPermission: this.hasPermission('employee-list'),
                },
                { 
                  title: 'Attlog Reports',
                  link: '/employee/attlog/list',
                  hasPermission: this.hasPermission('employee-attlog-list'),
                },
                { 
                  title: 'Resigned',
                  link: '/employee/resigned/list',
                  hasPermission: this.hasPermission('employee-resigned-list'),
                },
                { 
                  title: 'Payroll',
                  link: '/employee/payroll/list',
                  hasPermission: this.hasPermission('employee-payroll-list'),
                },
                { 
                  title: 'Absences',
                  link: '/employee/absences/list',
                  hasPermission: this.hasPermission('employee-absences-list'),
                },
                { 
                  title: 'Overtime',
                  link: '/employee/overtime/list',
                  hasPermission: this.hasPermission('employee-overtime-list'),
                },
                { 
                  title: 'Holiday Pay',
                  link: '/employee/holiday_pay/list',
                  hasPermission: this.hasPermission('employee-holiday-pay-list'),
                },
                { 
                  title: 'Loans',
                  link: '/employee/loans/list',
                  hasPermission: this.hasPermission('employee-loans-list'),
                },
                { 
                  title: 'Premiums',
                  link: '/employee/premiums/list',
                  hasPermission: this.hasPermission('employee-premiums-list'),
                },
              ],
            },


            {
              title: 'Training',
              icon: 'mdi-folder-multiple-outline',
              link: '',
              method: '',
              hasPermission: false,
              children: [
                { 
                  title: 'My Files',
                  link: '/training/my_files',
                  hasPermission: this.hasPermission('user-files', 'file-create'),
                },
                { 
                  title: 'Files & Tutorials',
                  link: '/training/files_tutorials',
                  hasPermission: this.hasPermission('file-list'),
                },
              ],
            }
          ]
        }, // END HR/ Payroll group menu

        { // START Sales & Marketing group menu
          group_header_title: 'Sales & Marketing',
          hasPermission: false,
          list_items: [
            {
              title: 'Tactical Req.',
              icon: 'mdi-file-multiple-outline',
              link: '',
              method: '',
              hasPermission: false,
              children: [
                { 
                  title: 'Calendar',
                  link: '/tactical_requisition/calendar',
                  hasPermission: this.hasPermission('tactical-requisition-calendar'),
                },
                { 
                  title: 'Tactical List',
                  link: '/tactical_requisition/index',
                  hasPermission: this.hasPermission('tactical-requisition-list'),
                },
                { 
                  title: 'Create Tactical',
                  link: '/tactical_requisition/create',
                  hasPermission: this.hasPermission('tactical-requisition-create'),
                },
                { 
                  title: 'Marketing Event',
                  link: '/marketing_event/index',
                  hasPermission: this.hasAnyPermission('marketing-event-list', 'marketing-event-create'),
                },
              ]
            }
          ]
        }, // END Sales & Marketing group menu

        { // START Motorpool
          group_header_title: 'Motorpool',
          hasPermission: false,
          list_items: [
            {
              title: 'Gasoline',
              icon: 'mdi-gas-station',
              link: '',
              method: '',
              hasPermission: false,
              children: [
                { 
                  title: 'P.O Requests',
                  link: '/tactical_requisition/index',
                  hasPermission: this.hasAnyPermission('gasoline-list', 'gasoline-create'),
                },
              ],
            },
          ]
        }, // END Motorpool


        { // START EXPENSES & ACCOUNT  PAYABLES
          group_header_title: 'Expenses & Account Payables ',
          hasPermission: false,
          list_items: [
            {
              title: 'Online Banking',
              icon: 'mdi-credit-card-check',
              link: '',
              method: '',
              hasPermission: false,
              children: [
                { 
                  title: 'Form Request',
                  link: '/expense_&_accounts_payable/online_banking/request_form',
                  hasPermission: this.hasAnyPermission('online-banking-list', 'online-banking-create'),
                },
              ],
            },
          ]
        }, // END PAYABLES


        { // START Set Up & Athorization group menu
          group_header_title: 'Set Up & Authorizations',
          hasPermission: false,
          list_items: [
            {
              title: 'User Management',
              icon: 'mdi-account-arrow-right-outline',
              link: '',
              method: '',
              hasPermission: false,
              children: [
                { 
                  title: 'User Accounts',
                  link: '/user/index',
                  hasPermission: this.hasPermission('user-list'),
                },
                { 
                  title: 'Create New',
                  link: '/user/create',
                  hasPermission: this.hasPermission('user-create'),
                },
              ],
            },
            {
              title: 'Access Chart',
              icon: 'mdi-chart-arc',
              link: '',
              method: '',
              hasPermission: false,
              children: [
                { 
                  title: 'Access Chart Lists',
                  link: '/access_chart/index',
                  hasPermission: this.hasPermission('access-chart-list'),
                },
                { 
                  title: 'Access Module Lists',
                  link: '/access_module/index',
                  hasPermission: this.hasAnyPermission('access-module-list', 'access-module-create'),
                },
                { 
                  title: 'Access Level',
                  link: '/access_level',
                  hasPermission: this.hasPermission('access-level-edit'),
                },
              ],
            },
            {
              title: 'Settings',
              icon: 'mdi-cog',
              link: '',
              method: '',
              hasPermission: false,
              children: [
                { 
                  title: 'Branch',
                  link: '/branch/index',
                  hasPermission: this.hasAnyPermission('branch-list', 'branch-create'),
                },
                { 
                  title: 'Company',
                  link: '/company/index',
                  hasPermission: this.hasAnyPermission('company-list', 'company-create'),
                },
                { 
                  title: 'Rank',
                  link: '/rank/index',
                  hasPermission: this.hasAnyPermission('rank-list', 'rank-create'),
                },
                { 
                  title: 'Position',
                  link: '/position/index',
                  hasPermission: this.hasAnyPermission('position-list', 'position-create'),
                },
                { 
                  title: 'Department',
                  link: '/department/index',
                  hasPermission: this.hasAnyPermission('department-list', 'department-create'),
                },
                { 
                  title: 'Division',
                  link: '/division/index',
                  hasPermission: this.hasAnyPermission('division-list', 'division-create'),
                },
                { 
                  title: 'Role',
                  link: '/role/index',
                  hasPermission: this.hasAnyPermission('role-list', 'role-create'),
                },
                { 
                  title: 'Permission',
                  link: '/permission/index',
                  hasPermission: this.hasAnyPermission('permission-list', 'permission-create'),
                },
              ],
            },
          ],
        }, // END Set Up & Athorization group menu


        { // START SAP Business One group menu
          group_header_title: 'SAP Business One',
          hasPermission: false,
          list_items: [
            {
              title: 'SAP Database',
              icon: 'mdi-database',
              link: '/sap_database/index',
              method: '',
              hasPermission: this.hasAnyPermission('sap-database-list', 'sap-database-create'),
              children: [],
            },
            {
              title: 'Sync Item Master data',
              icon: 'mdi-sync',
              link: '',
              method: 'confirmSyncItemMasterData',
              hasPermission: this.hasAnyPermission('sap-database-list', 'sap-database-create'),
              children: [],
            },
          ]
        } // END SAP Business One group menu
        
      ];

      menu.forEach(item => {
        item.list_items.forEach(list => {

          list.children.forEach(child => {
            if(child.hasPermission)
            {
              list.hasPermission = true;
            }
          }); 

          if(list.hasPermission)
          {
            item.hasPermission = true;
          }

        });
      });
      return menu;
    },
    isIdle() {
			return this.$store.state.idleVue.isIdle;
		},
    ...mapState("auth", ["user"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission", "hasAnyPermission"]),
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