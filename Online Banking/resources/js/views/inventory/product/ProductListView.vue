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
          :canImport="hasPermission('product-import')"
          :canExport="hasPermission('product-export')"
          :canClearList="hasPermission('product-clear-list')"
          :canReconcile="hasPermission('product-reconcile')"
          @import="importExcel"
          @export="exportData"
          @clearList="clearList"
          @reconcile="getUnreconciled"
          class="mb-6"
        />
        <v-card>
          <v-row class="pt-2">
            <v-col>
              <v-row>
                <v-col class="text-right mt-2 px-0">
                  <h6 class="font-weight-bold">Branch:</h6>
                </v-col>
                <v-col>
                  <v-chip class="text-subtitle-1">{{ branch }}</v-chip>
                </v-col>
              </v-row>
            </v-col>
            <v-col>
              <v-row>
                <v-col class="text-right mt-2 px-0">
                  <h6 class="font-weight-bold">Warehouse:</h6>
                </v-col>
                <v-col>
                  <v-chip class="text-subtitle-1">{{ whse_code }}</v-chip>
                </v-col>
              </v-row>
            </v-col>
            <v-col>
              <v-row>
                <v-col class="text-right mt-2 px-0">
                  <h6 class="font-weight-bold">Document Type:</h6>   
                </v-col>
                <v-col>
                  <v-chip class="text-subtitle-1">{{ inventory_type }}</v-chip> 
                </v-col>
              </v-row>
            </v-col>
            <v-col>
              <v-row>
                <v-col class="text-right mt-2 px-0">
                  <h6 class="font-weight-bold">Document Date:</h6>  
                </v-col>
                <v-col>
                  <v-chip class="text-subtitle-1">{{ file_upload_log.docdate }}</v-chip>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
          <v-divider></v-divider>
          <!-- <DataTable
            :branch="branch"
            :headers="headers"
            :items="products"
            :search="search"
            :loading="loading"
            :file_upload_log="file_upload_log" 
            :canViewList="hasPermission('product-list')"
            :canEdit="hasPermission('product-edit')"
            :canDelete="hasPermission('product-delete')"
            @edit="editProduct"
            @confirmDelete="showConfirmAlert"
          /> -->
          <v-data-table
            :headers="headers"
            :items="products.data"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            :page.sync="page"
            :footer-props="footerProps"
            @page-count="pageCount = $event"
            :items-per-page="itemsPerPage"
            @update:items-per-page="getItemPerPage"
            v-if="hasPermission('product-list')"
            show-first-last-page
          >
            <template v-slot:top>
              <v-toolbar flat>
                <h4>Product List</h4>
                <v-spacer></v-spacer>
                <v-text-field
                  v-model="search"
                  append-icon="mdi-magnify"
                  label="Search"
                  single-line
                  @click:append="searchProduct()"
                ></v-text-field>
                <v-spacer></v-spacer>
              </v-toolbar>
            </template>
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editProduct(item)"
                v-if="hasPermission('product-edit')"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="hasPermission('product-delete')"
              >
                mdi-delete
              </v-icon>
            </template>
            <template v-slot:footer.page-text>
              <!-- <v-btn color="primary" dark class="ma-2"> Button </v-btn> -->
              {{ products.from ? products.from + " - " + products.to + " of " + products.total : "" }}
              <v-btn
                icon
                class="ml-4"
                @click="firstPage()"
                :disabled="firstBtnDisable"
                ><v-icon> mdi-chevron-double-left </v-icon></v-btn
              >
              <v-btn
                icon
                @click="prevPage()"
                :disabled="prevBtnDisable"
                ><v-icon> mdi-chevron-left </v-icon></v-btn
              >
               <strong> {{ page }} </strong>
              <v-btn icon @click="nextPage()" :disabled="lastBtnDisable"
                ><v-icon> mdi-chevron-right </v-icon></v-btn
              >
              <v-btn icon @click="lastPage()" :disabled="lastBtnDisable"
                ><v-icon> mdi-chevron-double-right </v-icon></v-btn
              >
            </template>
          </v-data-table>
        </v-card>
        <v-dialog
          v-model="dialog_unreconciled"
          max-width="1100px"
          persistent
        >
          <v-card>
            <v-card-title class="pa-4">
              <span class="headline">Unreconciled Inventories</span>
              <v-spacer></v-spacer>
              <v-text-field
                v-model="search_unreconciled"
                append-icon="mdi-magnify"
                label="Search"
                single-line
                hide-details=""
              ></v-text-field>
              <v-spacer></v-spacer>
              <!-- <v-autocomplete
                v-model="inventory_group"
                :items="inventory_groups"
                item-text="name"
                item-value="name"
                label="Inventory Group"
                hide-details=""
                v-if="user.id === 1"
              >
              </v-autocomplete> -->
            </v-card-title>
            <v-divider class="mt-0"></v-divider>
            <v-card-text>
              <v-container>
                <v-row v-if="user.id === 1"> </v-row>
                <v-row>
                  <v-col>
                    <v-data-table
                      :headers="unreconciled_headers"
                      :items="filteredUnreconciled"
                      :search="search_unreconciled"
                      :loading="loading_unreconciled"
                      loading-text="Loading... Please wait"
                    >
                      <template v-slot:item.status="{ item }">
                        <v-chip
                          :color="
                            item.status == 'unreconciled'
                              ? 'red white--text'
                              : 'success'
                          "
                        >
                          {{ item.status.toUpperCase() }}
                        </v-chip>
                      </template>
                      <template v-slot:item.actions="{ item }">
                        <v-btn
                          x-small
                          class="mr-2"
                          color="primary"
                          @click="reconcileProducts(item)"
                        >
                          <v-icon x-small class="mr-1">
                            mdi-file
                          </v-icon>
                          Reconcile
                        </v-btn>
                      </template>
                    </v-data-table>
                  </v-col>
                </v-row>
              </v-container>
            </v-card-text>
            <v-divider class="mb-3 mt-0"></v-divider>
            <v-card-actions class="pa-0">
              <v-spacer></v-spacer>
              <v-btn
                color="#E0E0E0"
                @click="
                  (dialog_unreconciled = false) + (loading = false)
                "
                class="mb-3 mr-4"
              >
                Cancel
              </v-btn>
            </v-card-actions>
            <v-dialog v-model="dialog_recon_loading" max-width="500px" persistent>
              <v-card>
                <v-card-text>
                  <v-container>
                    <v-row
                      class="fill-height"
                      align-content="center"
                      justify="center"
                    >
                      <v-col class="subtitle-1 font-weight-bold text-center mt-4" cols="12">
                        Reconciling Products...
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
          </v-card>
        </v-dialog>
      </v-main>
      <ImportDialog 
        :branch="branch"
        :api_route="api_route" 
        :dialog_import="dialog_import"
        :action="action"
        :branches="branches"
        :user="user"
        :whse_codes="whse_codes"
        :docname="'Product List'"
        @getData="refreshData"
        @closeImportDialog="closeImportDialog"
      />
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import { required } from "vuelidate/lib/validators";
import { mapState, mapGetters, mapActions } from "vuex";
import ImportDialog from '../components/ImportDialog.vue';
import MenuActions from "../../components/MenuActions.vue";
import DataTable from "../../components/DataTable.vue";

export default {
  name: "ProductIndex",
  components: {
    ImportDialog,
    MenuActions,
    DataTable,
  },
  mixins: [validationMixin],

  validations: {
    editedItem: {
      branch_id: { required },
      brand_id: { required },
      model: { required },
      product_category_id: { required },
      serial: { required },
    },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Brand", value: "brand.name" },
        { text: "Model", value: "model" },
        { text: "Product Category", value: "product_category.name" },
        { text: "Serial", value: "serial" },
        { text: "Quantity", value: "quantity" },
        { text: "Date Created", value: "date_created" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      unreconciled_headers: [
        { text: "Branch", value: "branch.name" },
        { text: "Date Created", value: "date_created" },
        { text: "Document Date", value: "document_date" },
        { text: "Warehouse", value: "whse_code" },
        { text: "Inventory Type", value: "inventory_type" },
        { text: "Status", value: "status" },
        { text: "Actions", value: "actions", sortable: false, width: "100px" },
      ],
      disabled: false,
      dialog: false,
      dialog_unreconciled: false,
      dialog_recon_loading: false,
      products: [],
      brands: [],
      branches: [],
      whse_codes: [],
      product_categories: [],
      editedIndex: -1,
      editedItem: {
        branch_id: "",
        brand: "",
        brand_id: "",
        model: "",
        serial: "",
      },
      defaultItem: {
        branch_id: "",
        brand: "",
        brand_id: "",
        model: "",
        serial: "",
      },
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Product Lists",
          disabled: false,
          link: "/product/index"
        },
        {
          text: "View",
          disabled: true,
        },
      ],
      loading: true,
      loading_unreconciled: true,
      json_fields: {
        BRAND: "brand.name",
        MODEL: "model",
        SERIAL: "serial",
        QUANTITY: " ",
      },
      serialExists: false,
      inventory_groups: [{ name: "Admin-Branch" }, { name: "Audit-Branch" }],
      inventory_group: "Admin-Branch",
      unreconciled_list: [],
      search_unreconciled: "",
      action: "",
      api_route: "",
      file_upload_log: "",
      dialog_import: false,
      file_upload_log_id: "",
      swalAttr: {
        title: "",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "",
      },
      branch: "",
      inventory_type: "",
      whse_code: "",
    
      page: 1,
      pageCount: 0,
      itemsPerPage: 10,
      footerProps: {
        "items-per-page-options": [10, 20, 30, 50, 100, 500],
        pagination: {},
        showFirstLastPage: true,
        firstIcon: null,
        lastIcon: null,
        prevIcon: null,
        nextIcon: null,
      },
      last_page: 0,
      prevBtnDisable: true,
      nextBtnDisable: true,
      firstBtnDisable: true,
      lastBtnDisable: false,
    };
  },

  watch: {
    userIsLoaded: {
      handler() {
        if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
          this.getProduct(this.$route.params.file_upload_log_id);
        }
      },
    },
    userRolesPermissionsIsLoaded: {
      handler() {
        if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
          this.getProduct(this.$route.params.file_upload_log_id);
        }
      },
    },
    
    page() {
      this.resetPaginationButtons();
    },

    last_page() {
      // if last page is equal to 1 then disable prev,next,fist and last button pagination
      if (this.last_page === 1) {
        this.prevBtnDisable = true;
        this.nextBtnDisable = true;
        this.firstBtnDisable = true;
        this.lastBtnDisable = true;
      }
    },

    search() {
      if(!this.search)
      {
        this.getProduct(this.file_upload_log_id);
      }
    },
  },

  methods: {
    getProduct(file_upload_log_id) {
      this.loading = true;
      let data = { items_per_page: this.itemsPerPage, file_upload_log_id: file_upload_log_id, search: this.search };
      
      axios.post("/api/product/list/view?page=" + this.page, data).then(
        (response) => {
          console.log(response.data);
          let data = response.data;
          let log = data.file_upload_log;
          this.branch = log.branch.name;
          this.whse_codes = log.branch.whse_codes;
          this.file_upload_log = log;
          
          let products = data.products;

          this.products = products;

          this.brands = data.brands;
          this.branches = data.branches;
          this.product_categories = data.product_categories;
          this.editedItem.branch_id = this.user.branch_id;

          let splitted_remarks = log.remarks.split('-');

          this.whse_code = splitted_remarks.length == 2 ? splitted_remarks[0] : ''; // split the remarks (e.g ADMN-OVERALL) then get the 'ADMN'
          this.inventory_type = splitted_remarks.length == 2 ? splitted_remarks[1] : log.remarks; // split the remarks (e.g ADMN-OVERALL) then get the 'OVERALL'
          
          this.loading = false; 

          this.last_page = products.last_page;

          this.footerProps.pagination = {
            page: this.page,
            itemsPerPage: this.itemsPerPage,
            pageStart: products.from - 1,
            pageStop: products.to,
            pageCount: products.last_page,
            itemsLength: products.total,
          };

          this.resetPaginationButtons();
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    refreshData(file_upload_log_id) {
      this.getProduct(file_upload_log_id);
      this.$router.push({
        name: 'product.list.view',
        params: { branch_id: this.file_upload_log.branch.id, file_upload_log_id: file_upload_log_id }
      });

    },

    save() {
      this.$v.$touch();

      if (!this.$v.$error) {
        this.disabled = true;
        const data = this.editedItem;
        const product_id = this.editedItem.id;
        let url = "/api/product" + (this.editedIndex > -1 ? "/update/" + product_id : "/store");

        axios.post(url, data).then(
          (response) => {
            let data = response.data;

            if (data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "product-edit" });

              if(this.editedIndex > -1)
              {
                Object.assign(this.products[this.editedIndex], this.editedItem);
              }
              else
              {
                this.products.data.push(data.product);
              }
              
              this.showAlert(data.success, 'success');
              this.close();

            } else if (data.existing_products) {
              this.serialExists = true;
            }

            this.disabled = false;
          },
          (error) => {
            this.isUnauthorized(error);
            this.disabled = false;
          }
        );
      }
    },

    editProduct(item) {
      this.editedIndex = this.products.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteProduct(product_id) {
      const data = { product_id: product_id };
      this.loading = true;
      axios.post("/api/product/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "product-delete" });
            this.showAlert(response.data.success, 'success');
          }
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    importExcel() {
      this.action = 'import';
      this.dialog_import = true;
    },

    exportData() {
      // const data = { branch_id: this.branch_id };
      const data = { file_upload_log_id: this.file_upload_log.id };
      if (this.products.data.length) {

        axios.post('/api/product/export', data, { responseType: 'arraybuffer'})
          .then((response) => {
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement('a');
              fileLink.href = fileURL;
              fileLink.setAttribute('download', 'Products.xls');
              document.body.appendChild(fileLink);
              fileLink.click();
          }, (error) => {
            console.log(error);
          }
        );
      }
      else {
        this.showAlert('No record found', 'warning');
      }

    },

    downloadTemplate() {
      axios.post('/api/product/template/download', data, { responseType: 'arraybuffer'})
          .then((response) => {
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement('a');
              fileLink.href = fileURL;
              fileLink.setAttribute('download', 'ProductTemplate.xls');
              document.body.appendChild(fileLink);
              fileLink.click();
          }, (error) => {
            console.log(error);
          }
        );
    },

    getUnreconciled() {
      if (this.products.data.length) {
        // if Dropdown Branch value is 'ALL'

        this.loading_unreconciled = true;
        this.dialog_unreconciled = true;

        let data = {
          branch_id: this.branch_id,
          inventory_group: this.inventory_group,
          inventory_type: this.inventory_type,
          whse_code: this.whse_code,
        };

        console.log(data);

        axios
          .post("/api/inventory_reconciliation/unreconcile/list", data)
          .then(
            (response) => {
              this.unreconciled_list = response.data.unreconciled_list;
              this.loading_unreconciled = false;
            },
            (error) => {
              console.log(error);
            }
          );
        
      } else {

        this.showAlert('Nothing to reconcile', 'warning');
        
      }
    },

    reconcileProducts(item) {

      Object.assign(this.swalAttr, { title: "Reconcile Products", confirmButtonColor: "primary", confirmButtonText: "Reconcile" });

      this.$swal(this.swalAttr).then((result) => {

        if (result.value) {
     
          this.dialog_recon_loading = true;
          let data = {
            inventory_recon_id: item.id,
            products: this.products.data, // due to pagination, products data is based from api response pagination (number of rows is based from pagination)
            inventory_group: this.inventory_group,
            whse_code: this.whse_code,
            file_upload_log_id: this.file_upload_log.id,
            product_type: 'uploaded', // these products were uploaded
          };

          axios.post("api/inventory_reconciliation/reconcile", data).then(
            (response) => {

              console.log(response.data);
              this.dialog_unreconciled = false;
              this.dialog_recon_loading = false;
            
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "product-reconcile" });

                let index = this.unreconciled_list.indexOf(item);
                this.unreconciled_list.splice(index, 1);

                this.showAlert(response.data.success, "success");
                
              } else if (response.data.duplicate) {
                this.showAlert(response.data.duplicate, "warning");
              }
            },
            (error) => {
              this.dialog_recon_loading = false;
              this.isUnauthorized(error);
            }
          );
        }
      });
    },

    showAlert(msg, icon) {
      this.$swal({
        position: "center",
        icon: icon,
        title: msg,
        showConfirmButton: false,
        timer: 2500,
      });
    },

    showConfirmAlert(item) {
      Object.assign(this.swalAttr, { title: "Delete Record", confirmButtonColor: "#d33", confirmButtonText: "Delete Record!" });

      this.$swal(this.swalAttr).then(async (result) => {

        if (result.value) {
          // <-- if confirmed

          const product_id = item.id;
          const index = this.products.data.indexOf(item);

          //Call delete Product function
          await this.deleteProduct(product_id);

          //Remove item from array products
          await this.products.data.splice(index, 1);

        }
      });
    },

    clearList() {
      Object.assign(this.swalAttr, { title: "Clear List", confirmButtonColor: "#d33", confirmButtonText: "Clear List!" });

      if (this.products.data.length) {
        this.$swal(this.swalAttr).then((result) => {

          if (result.value) {
            // <-- if confirmed

            let data = { branch_id: this.branch_id, clear_list: true, file_upload_log_id: this.file_upload_log_id };

            axios.post("api/product/delete", data).then(
              (response) => {
                console.log(response);
                if (response.data.success) {
                  // send data to Sockot.IO Server
                  // this.$socket.emit("sendData", { action: "product-create" });

                  // clear products array
                  this.products = [];
                  this.file_upload_log = null;
                  this.$router.push({ name: 'product.index'});

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
      } else {
        this.showAlert('No record found', 'warning');
      }
    },
    selectedProductCategory() {
      let product_category = {};

      this.product_categories.forEach((value) => {
        if (this.editedItem.product_category_id == value.id) {
          product_category = value;
        }
      });

      this.editedItem.product_category = product_category;
    },
    close() {
      this.dialog = false;
      this.clear();
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    closeImportDialog() { 
      this.dialog_import = false;
    },
    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.editedItem.branch_id = this.user.branch_id;
    },

    nextPage() {
      if (this.page < this.last_page) {
        this.page = this.page + 1;
      }
      this.getProduct(this.file_upload_log_id);
    },
    prevPage() {
      if (this.page > 1) {
        this.page = this.page - 1;
      }
      this.getProduct(this.file_upload_log_id);
    },
    firstPage() {
      this.page = 1;
      this.prevBtnDisable = true;
      this.firstBtnDisable = true;

      this.getProduct(this.file_upload_log_id);
    },

    lastPage() {
      this.page = this.last_page;
      this.nexBtnDisable = true;
      this.lastBtnDisable = true;

      this.getProduct(this.file_upload_log_id);
    },

    getItemPerPage(val) {
      this.itemsPerPage = val;
      this.page = 1;
      this.getProduct(this.file_upload_log_id);
    },
    
    searchProduct() {
      this.page = 1;
      this.firstBtnDisable = true;
      this.prevBtnDisable = true;
      
      this.getProduct(this.file_upload_log_id);
    },

    resetPaginationButtons() {
      this.nextBtnDisable = false;
      this.prevBtnDisable = false;
      this.firstBtnDisable = false;
      this.lastBtnDisable = false;

      if (this.page === 1) {
        this.prevBtnDisable = true;
        this.firstBtnDisable = true;
      }

      if (this.page === this.last_page || this.last_page === 1) {
        this.nextBtnDisable = true;
        this.lastBtnDisable = true;
      }
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

        if (
          action == "product-create" ||
          action == "product-edit" ||
          action == "product-delete"
        ) {
          this.getProduct();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Product" : "Edit Product";
    },
    brandErrors() {
      const errors = [];
      if (!this.$v.editedItem.brand_id.$dirty) return errors;
      !this.$v.editedItem.brand_id.required &&
        errors.push("Brand is required.");
      return errors;
    },
    modelErrors() {
      const errors = [];
      if (!this.$v.editedItem.model.$dirty) return errors;
      !this.$v.editedItem.model.required && errors.push("Model is required.");
      return errors;
    },
    product_categoryErrors() {
      const errors = [];
      if (!this.$v.editedItem.product_category_id.$dirty) return errors;
      !this.$v.editedItem.product_category_id.required &&
        errors.push("Product Category is required.");
      return errors;
    },
    serialErrors() {
      const errors = [];
      if (!this.$v.editedItem.serial.$dirty) return errors;
      !this.$v.editedItem.serial.required && errors.push("Serial is required.");
      if (this.serialExists) {
        errors.push("Serial exists");
      }
      return errors;
    },
    branchErrors() {
      const errors = [];
      if (!this.$v.editedItem.branch_id.$dirty) return errors;
      !this.$v.editedItem.branch_id.required &&
        errors.push("Branch is required.");
      return errors;
    },

    filteredUnreconciled() {
      let unreconciled = [];

      if (this.user.id !== 1) {
        // if user has role Audit Admin
        if (this.hasRole('Audit Admin')) {
          this.inventory_group = "Audit-Branch";
        }
        // if user has role Inventory Admin
        else if (this.hasRole('Inventory Admin')) {
          this.inventory_group = "Admin-Branch";
        }
      }

      this.unreconciled_list.forEach((value, index) => {
        if (value.inventory_group === this.inventory_group) {
          unreconciled.push(value);
        }
      });

      return unreconciled;
    },
    tableHeaders() {
      let headers = [];

      this.headers.forEach((value) => {
        headers.push(value);
      });

      // remove Actions column if user is not permitted
      if (!this.hasPermission('product-edit', 'product-delete')) {
        headers.splice(5, 1);
      }

      return headers;
    },
    ...mapState("auth", ["user", "userIsLoaded"]),
    ...mapState("userRolesPermissions", ["userRolesPermissionsIsLoaded"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission", "hasAnyPermission"]),
  },

  destroyed() {
    // Remove listener when component is destroyed
    this.$barcodeScanner.destroy();
  },
  async mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    
    this.branch_id = this.$route.params.branch_id;
    this.file_upload_log_id = this.$route.params.file_upload_log_id;
    this.api_route = 'api/product/import/' + this.branch_id; //set api route for uploading excel

    if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {

      this.getProduct(this.file_upload_log_id);
      
    }
    // this.websocket();
  },
};
</script>