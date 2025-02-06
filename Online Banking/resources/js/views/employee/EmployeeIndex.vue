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
        <!-- <div class="d-flex justify-content-end mb-3">
          <div>
            <v-btn
              color="success"
              small
              @click="exportData()"
              v-if="hasPermission('employee-list-export', 'employee-list-all')"
            >
              <v-icon class="mr-1" small> mdi-microsoft-excel </v-icon>
              Export All Data
            </v-btn>
          </div>
        </div> -->
        <DataTableGroup
          :branches="filteredBranches"
          :loading="loading"
          :canViewList="hasPermission('employee-list')"
          :canImport="hasPermission('employee-list-import')"
          :canExport="hasPermission('employee-list-export')"
          :canClearList="hasPermission('employee-clear-list')"
          @importExcel="importExcel"
          @exportData="exportData"
          @viewList="viewList"
          @clearList="showConfirmAlert"
        />
      </v-main>
      <ImportDialog 
        :branch="branch"
        :api_route="api_route" 
        :dialog_import="dialog_import"
        @getData="getEmployee"
        @closeImportDialog="closeImportDialog"
      />
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import { mapState, mapGetters } from "vuex";
import ImportDialog from "../components/ImportDialog.vue";
import DataTableGroup from "../components/DataTableGroup.vue";

export default {
  name: "EmployeeIndex",
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
          text: "Employee Lists",
          disabled: false,
        },
      ],
      loading: true,
      branch_id: "",
      branch: "",
      dialog_import: false,
      api_route: "",
    };
  },

  methods: {
    getEmployee() {
      this.loading = true;
      axios.get("/api/employee/index").then(
        (response) => {
          this.branches = response.data.branches;
          this.loading = false;
          console.log(this.branches);
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    viewList(item) {

      let branch_id = item.branch_id;

      this.$router.push({
        name: 'employee.list.view',
        params: { branch_id: branch_id, file_upload_log_id: item.id }
      });

    },

    importExcel(item) {
      this.branch_id = item[0].id;
      this.branch = item[0].name;
      this.dialog_import = true;
      this.api_route = '/api/employee/import_employee/' + this.branch_id;
    },

    exportData(item) {
     
      const data = { file_upload_log_id: item.id }
      axios.post('/api/employee/export_employee', data, { responseType: 'arraybuffer'})
        .then((response) => {
            var fileURL = window.URL.createObjectURL(new Blob([response.data]));
            var fileLink = document.createElement('a');
            fileLink.href = fileURL;
            fileLink.setAttribute('download', 'EmployeeList.xls');
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

          axios.post("api/employee/delete", data).then(
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

    formatDate(date) {
      if (!date) return null;
      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
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

        if (action == "employee-import") {
          this.getEmployee();
        }
      };
    },
  },
  computed: {
    filteredBranches() {
      let branches = [];

      this.branches.forEach((value) => {
        if (this.hasPermission('employee-list-all')) {
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
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission", "hasAnyPermission"]),
  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getEmployee();
    // this.websocket();
  },
};
</script>