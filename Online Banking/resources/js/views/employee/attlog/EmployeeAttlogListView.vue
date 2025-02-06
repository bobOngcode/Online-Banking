<template>
  <div class="flex column">
    <div id="_wrapper" class="pa-5">
      <v-main>
        <v-breadcrumbs :items="items">
          <template v-slot:item="{ item }">
            <v-breadcrumbs-item :to="item.link" :disabled="item.disabled">
              {{ item.text.toUpperCase() }}
            </v-breadcrumbs-item>
          </template>
        </v-breadcrumbs>

        <MenuActions
          :canImport="hasPermission('employee-attlog-import')"
          :canDownloadFile="hasPermission('employee-attlog-export')"
          :canExport="hasPermission('employee-attlog-export')"
          :canClearList="hasPermission('employee-attlog-clear-list')"
          @import="importExcel"
          @download="downloadFile"
          @export="exportData"
          @clearList="clearList"
        />
        
        <v-card>
          <v-card-title>
            Employee Attlog Lists 
            <!-- <v-chip color="secondary" v-if="branch" class="ml-2"> {{ branch }} </v-chip> -->
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
            ></v-text-field>
            <v-spacer></v-spacer>

            <v-btn
              color="primary"
              fab
              dark
              class="mb-2"
              @click="clear() + (dialog = true)"
              v-if="hasPermission('employee-attlog-create')"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>
          </v-card-title>
          <DataTable
            :branch="branch"
            :headers="headers"
            :items="employee_attlogs"
            :search="search"
            :loading="loading"
            :file_upload_log="file_upload_log" 
            :canViewList="hasPermission('employee-attlog-list')"
            :canEdit="false"
            :canDelete="false"
            @edit="editEmployeeAttlog"
            @confirmDelete="showConfirmAlert"
          />
          <ImportDialog 
            :branch="branch"
            :api_route="api_route" 
            :dialog_import="dialog_import"
            @getData="refreshData"
            @closeImportDialog="closeImportDialog"
          />
        </v-card>
      </v-main>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState, mapGetters } from "vuex";
import ImportDialog from "../../components/ImportDialog.vue";
import MenuActions from '../../components/MenuActions.vue';
import DataTable from "../../components/DataTable.vue";

export default {
  name: "EmployeeAttlogListView",
  components: {
    ImportDialog,
    MenuActions,
    DataTable,
  },
  props: {

  },
  mixins: [validationMixin],

  validations: {
    file: { required },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "branch" },
        { text: "User ID", value: "user_id" },
        { text: "Date and Time", value: "date_time" },
        { text: "Device ID", value: "device_id" },
        { text: "Status", value: "status" },
        { text: "Verifying Type", value: "verifying_type" },
        { text: "Work Code", value: "work_code" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      disabled: false,
      dialog: false,
      employee_attlogs: [],
      branches: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Employee Attlog Lists",
          disabled: false,
          link: "/employee/attlog/list",
        },
        {
          text: "View",
          disabled: true,
        },
      ],
      loading: true,
      search_branch: "",
      file: [],
      fileIsEmpty: false,
      fileIsInvalid: false,
      uploadDisabled: false,
      uploading: false,
      dialog_import: false,
      dialog_error_list: false,
      errors_array: [],
      fileError: "",
      branch: "",
      branch_id: "",
      editedIndex: -1,
      editedItem: {	
      },
      defaultItem: {
       
      },
      file_upload_log: "",
      dialog_import: false,
      api_route: "",
      swalAttr: {
        title: "",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "",
      }
    };
  },

  watch: {
    userIsLoaded: {
      handler() {
        if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
          this.getEmployeeAttlog(this.$route.params.file_upload_log_id);
        }
      },
    },
    userRolesPermissionsIsLoaded: {
      handler() {
        if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
          this.getEmployeeAttlog(this.$route.params.file_upload_log_id);
        }
      },
    },
  },

  methods: {
    getEmployeeAttlog(file_upload_log_id) {

      this.loading = true;
      let data = { file_upload_log_id: file_upload_log_id }
      axios.post("/api/employee_attlog/list/view", data).then(
        (response) => {
          // if user has no permission to view overall list
          let data = response.data;
          if (
            !this.hasPermission('employee-attlog-list-all') &&
            this.user.branch_id != this.branch_id
          ) {
            this.$router.push({ name: "unauthorize" });
          }
          
          this.branch = data.file_upload_log.branch.name;
          this.file_upload_log = data.file_upload_log;
          this.employee_attlogs = data.employee_attlogs;
          this.loading = false;

        },
        (error) => {
          this.isUnauthorized(error);
          console.log(error);
        }
      );
    },
    refreshData(file_upload_log_id) {
      this.getEmployeeAttlog(file_upload_log_id);
      this.$router.push({
        name: 'employee.attlog.list.view',
        params: { branch_id: this.file_upload_log.branch.id, file_upload_log_id: file_upload_log_id }
      });

    },
    editEmployeeAttlog(item) {

    },
    deleteEmployeeAttlog(employee_attlog_id) {
      const data = { employee_attlog_id: employee_attlog_id };

      axios.post("/api/employee_attlog/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "employee-delete" });
            this.showAlert(response.data.success, 'success');
          }
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    importExcel() {
      this.dialog_import = true;
    },

    exportData() {
      let url = '/api/employee_attlog/export_attlog';
      let file_type = 'xls';

      this.fetchFileResponse(url, file_type);
    },

    downloadFile() {
      let url = '/api/employee_attlog/file/download';
      let file_type = 'dat';

      this.fetchFileResponse(url, file_type);
    },

    fetchFileResponse(url, file_type) {
      const data = { file_upload_log_id: this.file_upload_log_id }

      if (this.employee_attlogs.length) {
        axios.post(url, data, { responseType: 'arraybuffer'})
          .then((response) => {
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement('a');
              fileLink.href = fileURL;
              fileLink.setAttribute('download', 'EmployeeAttlog.' + file_type);
              document.body.appendChild(fileLink);
              fileLink.click();
          }, (error) => {
            console.log(error);
          }
        );
      } else {
        this.showAlert('No Record Found', 'warning');
      }
      
    },

    showAlert(title, icon) {
      this.$swal({
        position: "center",
        icon: icon,
        title: title,
        showConfirmButton: false,
        timer: 2500,
      });
    },

    showConfirmAlert(item) {
      Object.assign(this.swalAttr, { title: "Delete Record", confirmButtonText: "Delete Record!" });

      this.$swal(this.swalAttr).then((result) => {
        // <--

        if (result.value) {
          // <-- if confirmed

          const employee_attlog_id = item.id;
          const index = this.employee_attlogs.indexOf(item);

          //Call delete User function
          this.deleteEmployeeAttlog(employee_attlog_id);

          //Remove item from array employee_attlogs
          this.employee_attlogs.splice(index, 1);

        }
      });
    },

    close() {
      this.dialog = false;
      this.loading = false;
      this.clear();
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    closeImportDialog() {
      this.dialog_import = false;
    },

    clearList() {
      Object.assign(this.swalAttr, { title: "Clear List", confirmButtonText: "Clear List!" });

      if (this.employee_attlogs.length) {
        this.$swal(this.swalAttr).then((result) => {

          if (result.value) {
            // <-- if confirmed

            let data = { 
              branch_id: this.branch_id, 
              file_upload_log_id: this.file_upload_log_id, 
              clear_list: true 
            };

            axios.post("api/employee_attlog/delete", data).then(
              (response) => {
                if (response.data.success) {
                  // send data to Sockot.IO Server
                  // this.$socket.emit("sendData", { action: "product-create" });
              
                  this.employee_attlogs = [];
                  this.file_upload_log = null;

                  this.showAlert(response.data.success, 'success');
                  this.$router.push({ name: 'employee.attlog.index'});
                } else {
                  this.showAlert('No record found', 'warning');
                }
                console.log(response.data);
              },
              (error) => {
                this.isUnauthorized(error);
                console.log(error);
              }
            );
          }
        });
      } else {
        this.showAlert('No record found', 'warning');
      }
    },

    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },

    formatDate(date) {
      if (!date) return null;
      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },
    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;

        if (
          action == "employee-attlog-create" ||
          action == "employee-attlog-edit" ||
          action == "employee-attlog-delete" ||
          action == "employee-attlog-import" ||
          action == "employee-attlog-clear-list"
        ) {
          this.getEmployeeAttlog();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Employee Attlog Information" : "Edit Employee Information";
    },
    fileErrors() {
      const errors = [];
      if (!this.$v.file.$dirty) return errors;
      !this.$v.file.required && errors.push("File is required.");
      this.fileIsEmpty && errors.push("File is empty.");
      this.fileIsInvalid &&
        errors.push("File type must be 'xlsx', 'xls' or 'ods'.");
      return errors;
    },
    imported_file_errors() {
      return this.errors_array.sort();
    },
    ...mapState("auth", ["user", "userIsLoaded"]),
    ...mapState("userRolesPermissions", ["userRolesPermissionsIsLoaded"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission", "hasAnyPermission"]),
  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.branch_id = this.$route.params.branch_id;
    this.file_upload_log_id = this.$route.params.file_upload_log_id;
    this.api_route = 'api/employee_attlog/import_attlog/' + this.branch_id; //set api route for uploading excel
    
    if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
      this.getEmployeeAttlog(this.file_upload_log_id);
    }

    // this.websocket();
  },
};
</script>