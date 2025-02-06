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
          :canExport="hasPermission('product-export')"
          :canClearList="hasPermission('product-clear-list')"
          :canReconcile="hasPermission('product-reconcile')"
          @export="openDialogExport"
          @clearList="clearList"
          @reconcile="getUnreconciled"
        />
        <v-card>
          <v-card-title class="mb-0 pb-0">
            Scanned Product Lists
            <v-divider vertical class="mx-4"></v-divider>
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              label="Search"
              single-line
              hide-details=""
              v-if="hasPermission('product-list')"
            >
              <template slot="append">
                <v-btn small color="primary" icon>
                  <v-icon @click="searchProduct()">mdi-magnify</v-icon>
                </v-btn>
              </template>
            </v-text-field>
            <v-spacer></v-spacer>
            <!-- <v-autocomplete
              v-model="search_branch"
              :items="branchOptions"
              item-text="name"
              item-value="id"
              label="Branch"
              v-if="user.id === 1 || hasPermission('product-list-all')"
            >
            </v-autocomplete> -->
            <v-autocomplete
              v-model="search_whse"
              :items="warehouseOptions"
              item-text="code"
              item-value="code"
              label="Warehouse"
              hide-details=""
              style="width: 250px"
              class="mx-1"
            >
              <template slot="selection" slot-scope="data"  >
                {{ data.item.code + ' - ' + data.item.branch }}
              </template>
              <template slot="item" slot-scope="data">
                {{ data.item.code + ' - ' + data.item.branch }}
              </template> 
            </v-autocomplete>
            <v-spacer></v-spacer>
            <template v-if="user.id === 1">
              <v-autocomplete
                v-model="inventory_group"
                :items="inventory_groups"
                item-text="name"
                item-value="name"
                label="Inventory Group"
                hide-details=""
                v-if="user.id === 1"
                class="mx-1"
              >
              </v-autocomplete>
              <v-spacer></v-spacer>
            </template>
            <v-autocomplete
              v-model="scanned_by"
              :items="scanned_grp_items"
              item-text="name"
              item-value="name"
              label="Scanned By"
              hide-details=""
              v-if="hasAnyRole('Administrator', 'Inventory Admin', 'Audit Admin')"
              class="mx-1"
            >
            </v-autocomplete>
            <v-spacer></v-spacer>
          </v-card-title>
          <!-- <DataTable
            :headers="headers"
            :items="filteredProducts"
            :search="search"
            :loading="loading"
            :canViewList="hasPermission('product-list')"
            :canEdit="hasPermission('product-edit')"
            :canDelete="hasPermission('product-delete')"
            @edit="editProduct"
            @confirmDelete="showConfirmAlert"
          /> -->

          <v-data-table
            :headers="headers"
            :items="filteredProducts"
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
          <v-dialog v-model="dialog" max-width="500px" persistent>
            <v-card>
              <v-card-title class="pa-4">
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>
              <v-divider class="mt-0"></v-divider>
              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col class="my-0 py-0">
                      <v-autocomplete
                        v-model="editedItem.brand_id"
                        :items="brands"
                        item-text="name"
                        item-value="id"
                        label="Brand"
                        required
                        :error-messages="brandErrors"
                        @input="
                          $v.editedItem.brand_id.$touch() +
                            (serialExists = false)
                        "
                        @blur="$v.editedItem.brand_id.$touch()"
                      >
                      </v-autocomplete>
                    </v-col>
                  </v-row>
                  <v-row>
                    <v-col class="my-0 py-0">
                      <v-text-field
                        name="model"
                        label="Model"
                        v-model="editedItem.model"
                        required
                        :error-messages="modelErrors"
                        @input="
                          $v.editedItem.model.$touch() +
                            (serialExists = false)
                        "
                        @blur="$v.editedItem.model.$touch()"
                      ></v-text-field>
                    </v-col>
                  </v-row>
                  <v-row>
                    <v-col class="my-0 py-0">
                      <v-autocomplete
                        v-model="editedItem.product_category_id"
                        :items="product_categories"
                        item-text="name"
                        item-value="id"
                        label="Product Category"
                        required
                        :error-messages="product_categoryErrors"
                        @input="
                          $v.editedItem.product_category_id.$touch() +
                            (serialExists = false) +
                            selectedProductCategory()
                        "
                        @blur="$v.editedItem.product_category_id.$touch()"
                      >
                      </v-autocomplete>
                    </v-col>
                  </v-row>
                  <v-row>
                    <v-col class="my-0 py-0">
                      <v-text-field
                        name="serial"
                        label="Serial"
                        v-model="editedItem.serial"
                        readonly
                        required
                        :error-messages="serialErrors"
                        @input="$v.editedItem.serial.$touch()"
                        @blur="$v.editedItem.serial.$touch()"
                      ></v-text-field>
                    </v-col>
                  </v-row>
                  <v-row>
                    <v-col class="my-0 py-0">
                      <v-autocomplete
                        v-model="editedItem.branch_id"
                        :items="branches"
                        item-text="name"
                        item-value="id"
                        label="Branch"
                        required
                        :error-messages="branchErrors"
                        @input="$v.editedItem.branch_id.$touch()"
                        @blur="$v.editedItem.branch_id.$touch()"
                        v-if="user.id === 1"
                      >
                      </v-autocomplete>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>
              <v-divider class="mb-3 mt-0"></v-divider>
              <v-card-actions class="pa-0">
                <v-spacer></v-spacer>
                <v-btn color="#E0E0E0" @click="close" class="mb-3">
                  Cancel
                </v-btn>
                <v-btn
                  color="primary"
                  @click="save"
                  class="mb-3 mr-4"
                  :disabled="disabled"
                >
                  Save
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>

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
          <v-dialog v-model="dialog_export" max-width="500px" persistent>
            <v-card>
              <v-card-title class="pa-4">
                <span class="headline">Export Data</span>
                <!-- <v-chip color="secondary" v-if="branch" class="ml-2"> {{ branch }} </v-chip> -->
              </v-card-title>
              <v-divider class="mt-0"></v-divider>
              <v-card-text>
                <v-container>
                  <v-row> 
                    <v-col class="my-0 py-0">
                      <v-autocomplete
                        v-model="export_type"
                        :items="export_types"
                        item-text="type"
                        item-value="type"
                        label="Export Type"
                        required
                        :readonly="export_loading"
                      >
                      </v-autocomplete>
                    </v-col>
                  </v-row>
                  <v-row> 
                    <v-col class="my-0 py-0">
                      <v-autocomplete
                        v-model="inventory_type"
                        :items="inventory_types"
                        item-text="type"
                        item-value="type"
                        label="Inventory Type"
                        required
                        :readonly="export_loading"
                        v-if="export_type == 'MERGE PRODUCT TEMPLATE'"
                      >
                      </v-autocomplete>
                    </v-col>
                  </v-row>
                  <v-row
                    class="fill-height"
                    align-content="center"
                    justify="center"
                    v-if="export_loading"
                  >
                    <v-col class="subtitle-1 font-weight-bold text-center mt-4" cols="12">
                      Downloading Excel File...
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
                  @click="dialog_export = false"
                  class="mb-3"
                >
                  Cancel
                </v-btn>
                <v-btn
                  color="success"
                  class="mb-3 mr-4"
                  @click="exportData()"
                  :disabled="export_loading"
                >
                  Export
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-card>
      </v-main>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import { required } from "vuelidate/lib/validators";
import { mapState, mapGetters, mapActions } from "vuex";
import MenuActions from "../../components/MenuActions.vue";
import DataTable from "../../components/DataTable.vue";

export default {
  name: "ProductIndex",
  components: {
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
        { text: "Branch", value: "branch.name" },
        { text: "Warehouse", value: "whse_code" },
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
      search_branch: "",
      search_whse: "",
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
          text: "Scanned Product Lists",
          disabled: false,
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
      swalAttr: {
        title: "",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "",
      },

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
      lastBtnDisable: true,
      inventory_groups: [{ name: "Admin-Branch" }, { name: "Audit-Branch" }],
      inventory_group: "Admin-Branch",
      scanned_grp_items: [{ name: "Admin" }, { name: "Branch" }],
      scanned_by: "Admin",
      dialog_export: false,
      inventory_types: [ { type: "OVERALL" }, { type: "REPO" } ],
      inventory_type: "OVERALL",
      export_types: [  { type: "SCANNED PRODUCTS" }, { type: "MERGE PRODUCT TEMPLATE" } ],
      export_type: "SCANNED PRODUCTS",
      export_loading: false,
    };
  },

  methods: {
    getProduct() {
      this.loading = true;

      const data = { 
        items_per_page: this.itemsPerPage, 
        whse_code: this.search_whse, 
        search: this.search, 
        inventory_group: this.inventory_group,
        scanned_by: this.scanned_by, 
      };

      axios.post("/api/product/scanned_products?page=" + this.page, data).then(
        (response) => {
          let data = response.data;

          this.file_upload_log = data.file_upload_log;
          this.products = data.products;
          this.brands = data.brands;
          this.branches = data.branches;
          this.whse_codes = data.whse_codes;
          this.product_categories = data.product_categories;
          this.loading = false;

          this.last_page = this.products.last_page;

          this.footerProps.pagination = {
            page: this.page,
            itemsPerPage: this.itemsPerPage,
            pageStart: this.products.from - 1,
            pageStop: this.products.to,
            pageCount: this.products.last_page,
            itemsLength: this.products.total,
          };

          this.resetPaginationButtons();

        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
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
                Object.assign(this.products.data[this.editedIndex], this.editedItem);
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

    exportData() {

      if (this.filteredProducts.length) 
      {
        // const data = { branch_id: this.search_branch };
        
        let data = {  
          inventory_group: this.inventory_group,
          whse_code: this.search_whse,
          scanned_by: this.scanned_by,
          product_type: 'scanned', // these products were scanned using barcode scanner
        };

        this.export_loading = true;

        let api = this.export_type == 'SCANNED PRODUCTS' ? 'export' : 'export_merged_template'

        axios.post('/api/product/' + api, data, { responseType: 'arraybuffer'})
            .then((response) => {
                var fileURL = window.URL.createObjectURL(new Blob([response.data]));
                var fileLink = document.createElement('a');
                fileLink.href = fileURL;
                fileLink.setAttribute('download', 'Scanned Products ' + '(' + this.search_whse + ') - Scanned by ' + this.scanned_by + '.xls');
                document.body.appendChild(fileLink);
                fileLink.click();
                this.export_loading = false;
                this.dialog_export = false;
                this.export_type = 'SCANNED PRODUCTS';
            }, (error) => {
              console.log(error);
            });
      }
      else
      {
        this.showAlert('No record found', 'warning');
      }

    },

    openDialogExport() {
      this.dialog_export = true;
      this.export_loading = false;
    },

    getUnreconciled() {
      if (this.filteredProducts.length) {
        // if Dropdown Branch value is 'ALL'

        this.loading_unreconciled = true;
        this.dialog_unreconciled = true;

        let data = {
          branch_id: this.search_branch,
          whse_code: this.search_whse,
          inventory_group: this.inventory_group,
        };

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
          // let branch_id = this.branches.find(value => value.code === this.search_whse).id;
          let data = {
            inventory_recon_id: item.id,
            products: this.filteredProducts, // due to pagination, products data is based from api response pagination
            inventory_group: this.inventory_group,
            whse_code: this.search_whse,
            scanned_by: this.scanned_by,
            product_type: 'scanned', // these products were scanned using barcode scanner
          };

          axios.post("api/inventory_reconciliation/reconcile", data).then(
            (response) => {

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

      if (this.filteredProducts.length) {
        this.$swal(this.swalAttr).then((result) => {

          if (result.value) {
            // <-- if confirmed
            // console.log(this.search_whse);
            // console.log(this.branches.find(value => value.code == this.search_whse));
            // let branch_id = this.branches.find(value => value.code == this.search_whse).id;
            
            let data = {

              inventory_group: this.inventory_group,
              // branch_id: branch_id,
              whse_code: this.search_whse,
              scanned_by: this.scanned_by,
              clear_list: true 
            };

            axios.post("api/product/delete", data).then(
              (response) => {
                if (response.data.success) {
                  // send data to Sockot.IO Server
                  // this.$socket.emit("sendData", { action: "product-create" });

                  // clear products array
                  // this.products = [];
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
    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.editedItem.branch_id = this.user.branch_id;
    },
    whseCode(item) {

      // split remarks with value 'ADMN-OVERALL'  or 'OVERALL'

      let splitted_remarks = item.split('-');

      // splitted_remarks.length is greater than 1 then get 'ADMN' from 'ADMN-OVERALL' value

      return splitted_remarks.length > 1 ? splitted_remarks[0] : '';
    },
    inventoryType(item) {

      // split remarks with value e.g 'ADMN-OVERALL' or 'OVERALL'

      let splitted_remarks = item.split('-');
 
      // splitted_remarks.length is equal to 1 then get original value 'OVERALL' else get the 'OVERALL' from 'ADMN-OVERALL'

      return splitted_remarks.length == 1 ? item : splitted_remarks[1];
    },

    nextPage() {
      if (this.page < this.last_page) {
        this.page = this.page + 1;
      }
      this.getProduct();
    },
    prevPage() {
      if (this.page > 1) {
        this.page = this.page - 1;
      }
      this.getProduct();
    },
    firstPage() {
      this.page = 1;
      this.prevBtnDisable = true;
      this.firstBtnDisable = true;

      this.getProduct();
    },

    lastPage() {
      this.page = this.last_page;
      this.nexBtnDisable = true;
      this.lastBtnDisable = true;

      this.getProduct();
    },

    getItemPerPage(val) {
      this.itemsPerPage = val;
      this.page = 1;
      this.getProduct();
    },
    
    searchProduct() {
      this.page = 1;
      this.firstBtnDisable = true;
      this.prevBtnDisable = true;
      this.getProduct();
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

    setFilterDefaultValue() {
      this.search_branch = this.user.branch_id;

        if(this.user)
        {
          let whse_codes = this.user.branch.whse_codes;
          if(whse_codes.length)
          {
            this.search_whse = whse_codes[0].code;
          }
        }

        if(this.hasAnyRole('Administrator', 'Inventory Admin', 'Audit Admin'))
        {
          this.scanned_by = 'Admin';
        }
        else
        {
          this.scanned_by = 'Branch';
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
  watch: {
    userIsLoaded: {
      handler() {
        if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
          this.setFilterDefaultValue();
        }
      },
    },
    userRolesPermissionsIsLoaded: {
      handler() {
        if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
          this.setFilterDefaultValue();
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

    search_whse() {
      this.page = 1;
      this.firstBtnDisable = true;
      this.prevBtnDisable = true;
      this.getProduct();
    },

    search() {
      if(!this.search)
      {
        this.page = 1;
        this.firstBtnDisable = true;
        this.prevBtnDisable = true;
        this.getProduct();
      }
    },

    inventory_group() {
      this.getProduct();
    },

    scanned_by() {
      this.getProduct();
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
    filteredProducts() {
      let products = [];

      if(this.products.data)
      {
        this.products.data.forEach((value) => {
          if (this.search_whse === 'ALL') {
            products.push(value);

          } else if (value.whse_code === this.search_whse) {
            products.push(value);
          }
        });
      }
      
      return products;
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
    branchOptions() {
      let branches = [{ id: 0, name: "ALL" }];
      this.branches.forEach(v => branches.push(v));

      return branches;
    },
    warehouseOptions() {
      let whse_codes = [];
      if(this.hasAnyRole('Administrator', 'Audit Admin', 'Inventory Admin'))
      {
        whse_codes.push({ code: 'ALL', branch: "ALL" });
        this.whse_codes.forEach(v => whse_codes.push(v)); 
      }
      else
      {
        if(this.user)
        {
          whse_codes = this.user.branch.whse_codes;
        }
      }
      
      return whse_codes;
    },
    ...mapState("auth", ["user", "userIsLoaded"]),
    ...mapState("userRolesPermissions", ["userRolesPermissionsIsLoaded"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission", "hasAnyPermission"]),
  },

  destroyed() {
    // Remove listener when component is destroyed
    this.$barcodeScanner.destroy();
  },
  async mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    
    this.search_branch = this.user.branch_id;
    
    if(!this.hasRole('Administrator') && this.hasRole('Inventory Admin'))
    {
      this.inventory_group = 'Admin-Branch';
    }
    else if(!this.hasRole('Administrator') && this.hasRole('Audit Admin'))
    {
      this.inventory_group = 'Audit-Branch';
    }

    if(this.user)
    {
      let whse_codes = this.user.branch.whse_codes;
      if(whse_codes.length)
      {
        this.search_whse = whse_codes[0].code;
      }
    }

    await this.getProduct();
    

    this.$barcodeScanner.init(this.onBarcodeScanned);

    // this.websocket();
  },
};
</script>