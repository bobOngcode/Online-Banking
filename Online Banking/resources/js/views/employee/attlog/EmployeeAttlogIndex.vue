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
        <DataTableGroup
          :branches="filteredBranches"
          :loading="loading"
          :canViewList="hasPermission('employee-attlog-list')"
          :canImport="hasPermission('employee-attlog-import')"
          :canExport="hasPermission('employee-attlog-export')"
          :canDownload="hasPermission('employee-attlog-export')"
          :canClearList="hasPermission('employee-attlog-clear-list')"
          @importExcel="importExcel"
          @exportData="exportData"
          @downloadFile="downloadFile"
          @viewList="viewList"
          @clearList="showConfirmAlert"
        />
      </v-main>
      <ImportDialog 
        :branch="branch"
        :api_route="api_route" 
        :dialog_import="dialog_import"
        @getData="getEmployeeAttlog"
        @closeImportDialog="closeImportDialog"
      />
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { mapState, mapGetters } from "vuex";
import ImportDialog from "../../components/ImportDialog.vue";
import DataTableGroup from "../../components/DataTableGroup.vue";

export default {
  name: "EmployeeAttlogIndex",
  components: {
    ImportDialog,
    DataTableGroup
  },
  props: {

  },
  data() {
    return {
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
        },
      ],
      loading: true,
      branch: "",
      branch_id: "",
      dialog_import: false,
      api_route: "",
    };
  },

  methods: {
    getEmployeeAttlog() {
      this.loading = true;
      axios.get("/api/employee_attlog/index").then(
        (response) => {
          this.branches = response.data.branches;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    viewList(item) {
      let branch_id = item.branch_id;
      
      this.$router.push({
        name: 'employee.attlog.list.view',
        params: { branch_id: branch_id, file_upload_log_id: item.id }
      });

    },

    importExcel(item) {
      this.branch_id = item[0].id;
      this.branch = item[0].name;
      this.dialog_import = true;
      this.api_route = 'api/employee_attlog/import_attlog/' + this.branch_id;
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

    },

    showConfirmAlert(item) {
   
      this.$swal({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Delete record!",
      }).then((result) => {
        // <--

        if (result.value) {

          let data = { branch_id: item.branch_id, clear_list: true, file_upload_log_id: item.id };

          axios.post("api/employee_loans/delete", data).then(
            (response) => {
              console.log(response);
              if (response.data.success) {

                this.getEmployee();
                this.showAlert(response.data.success, 'success');

              } else {

                this.showAlert('No record found', 'warning');
              }
            },
            (error) => {
              this.isUnauthorized(error);
            }
          );

        }
      });
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

    closeImportDialog() {
      this.branch_id = "";
      this.dialog_import = false;
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },

    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;

        if (action == "employee-attlog-import") {
          this.getEmployeeAttlog();
        }
      };
    },
  },
  computed: {
    filteredBranches() {
      let branches = [];

      this.branches.forEach((value) => {
        if (this.hasPermission('employee-attlog-list-all')) {
          branches.push(value);
        } else {
           if(value.id === this.user.branch_id)
           {
             branches.push(value);
           }
        }
      });

      return branches;
    },
    
    ...mapState("auth", ["user"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission","hasAnyPermission"]),
  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getEmployeeAttlog();
    // this.websocket();
  },
};
</script>