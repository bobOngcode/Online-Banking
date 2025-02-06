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
          :canViewList="hasPermission('product-list')"
          :canImport="hasPermission('product-import')"
          :canExport="hasPermission('product-export')"
          :canDownloadTemplate="hasPermission('product-template-download')"
          :canClearList="hasPermission('product-clear-list')"
          @importExcel="importExcel"
          @exportData="exportData"
          @downloadTemplate="openTemplateDialog"
          @viewList="viewList"
          @clearList="showConfirmAlert"
        />
        
        <ImportDialog 
          :api_route="api_route" 
          :dialog_import="dialog_import"
          :action="action"
          :branch="branch"
          :branch_id="branch_id"
          :whse_codes="whse_codes"
          :docname="'Product List'"
          @getData="getProduct"
          @closeImportDialog="closeImportDialog"
        />

        <v-dialog v-model="dialog_template" max-width="500px" persistent>
          <v-card>
            <v-card-title class="pa-4">
              <span class="headline">Generate Product Template</span>
              <v-chip color="secondary" v-if="branch" class="ml-2"> {{ branch }} </v-chip>
            </v-card-title>
            <v-divider class="mt-0"></v-divider>
            <v-card-text>
              <v-container>
                <v-row> 
                  <v-col class="my-0 py-0">
                    <v-autocomplete
                      v-model="inventory_type"
                      :items="inventory_types"
                      item-text="type"
                      item-value="type"
                      label="Inventory Type"
                      required
                      :readonly="template_loading"
                    >
                    </v-autocomplete>
                  </v-col>
                </v-row>
                <v-row> 
                  <v-col class="my-0 py-0">
                    <v-autocomplete
                      v-model="whse_code"
                      :items="whse_codes"
                      item-text="code"
                      item-value="value"
                      label="Warehouse Code"
                      required
                      :readonly="template_loading"
                    >
                    </v-autocomplete>
                  </v-col>
                </v-row>
                <v-row
                  class="fill-height"
                  align-content="center"
                  justify="center"
                  v-if="template_loading"
                >
                  <v-col class="subtitle-1 font-weight-bold text-center mt-4" cols="12">
                    Generating Product Template...
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
            <v-divider class="mb-3 mt-0"></v-divider>
            <v-card-actions class="pa-0">
              <v-spacer></v-spacer>
              <v-btn
                color="#E0E0E0"
                @click="dialog_template = false"
                class="mb-3"
              >
                Cancel
              </v-btn>
              <v-btn
                color="primary"
                class="mb-3 mr-4"
                @click="downloadTemplate()"
                :disabled="disabled"
              >
                Generate
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-main>
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
  name: "ProductIndex",
  components: {
    ImportDialog,
    DataTableGroup
  },
  props: {

  },
  mixins: [validationMixin],
  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "branch" },
        { text: "Date Uploaded", value: "date_uploaded" },
        { text: "Document Date", value: "docdate" },
        { text: "Inventory Type", value: "inventory_type" },
        { text: "Actions", value: "actions", sortable: false, width: "120px" },
      ],
      disabled: false,
      expanded: [],
      dialog: false,
      employees: [],
      branches: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Product Lists",
          disabled: false,
        },
      ],
      loading: true,
      branch: "",
      branch_id: "",
      dialog_import: false,
      api_route: "",
      inventory_types: [ { type: "OVERALL" }, { type: "REPO" } ],
      inventory_type: "OVERALL",
      whse_codes: [],
      whse_code: "",
      dialog_template: false,
      template_loading: false,
      action: "",
    };
  },

  methods: {
    getProduct() {
      this.loading = true;
      axios.get("/api/product/index").then(
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
        name: 'product.list.view',
        params: { branch_id: branch_id, file_upload_log_id: item.id }
      });

    },

    importExcel(item) {
      this.action = 'import';
      this.branch = item[0].name;
      this.branch_id = item[0].id;
      this.whse_codes = item[0].whse_codes;

      if(this.whse_codes.length)
      {
        this.whse_code = this.whse_codes[0].code;
      }

      this.dialog_import = true;
      this.api_route = 'api/product/import/' + this.branch_id;
    },

    exportData(item) {
      const data = { file_upload_log_id: item.id };
      this.fetchDownload('/api/product/export', data, 'ProductList.xls');

    },
    
    openTemplateDialog(item)
    { 
      this.branch = item[0].name;
      this.whse_codes = item[0].whse_codes;

      if(this.whse_codes.length)
      {
        this.whse_code = this.whse_codes[0].code;
      }

      this.branch_id = item[0].id;
      this.dialog_template = true;
    },

    downloadTemplate() {
      this.template_loading = true;
      this.disabled = true;

      const data = { 
        branch_id: this.branch_id, 
        inventory_type: this.inventory_type, 
        whse_code: this.whse_code, 
      };

      this.fetchDownload('/api/product/template/download', data, 'ProductTemplate - '+this.inventory_type+'.xls');

    },

    fetchDownload(url, data, file_name) {

      axios.post(url, data, { responseType: 'arraybuffer'})
        .then((response) => {
          if(!response.data.error)
          {
            this.template_loading = false;
            var fileURL = window.URL.createObjectURL(new Blob([response.data]));
            var fileLink = document.createElement('a');
            fileLink.href = fileURL;
            fileLink.setAttribute('download', file_name);
            document.body.appendChild(fileLink);
            fileLink.click();

            this.$swal({
              position: "center",
              icon: "success",
              title: "File has been downloaded",
              showConfirmButton: false,
            });

            this.dialog_template = false;
            this.disabled = false;
          }
          else
          {
            this.$swal({
              position: "center",
              icon: "error",
              title: "Error Occurred. Contact your Admnistrator",
              showConfirmButton: false,
            });
          }
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

          axios.post("api/product/delete", data).then(
            (response) => {
              console.log(response);
              if (response.data.success) {

                this.getProduct();
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

        if (action == "product-import") {
          this.getProduct();
        }
      };
    },
  },
  computed: {
    filteredBranches() {
      let branches = [];

      this.branches.forEach((value) => {
        if (this.hasPermission('product-list-all')) {
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
    this.getProduct();
    // this.websocket();
  },
};
</script>