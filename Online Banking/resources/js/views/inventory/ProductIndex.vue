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
        <div 
          class="d-flex justify-content-end mb-3" 
          v-if="hasAnyPermission('product-reconcile', 'product-template-download', 'product-import', 'product-export', 'product-clear-list')"
        >
          <div>
            <v-menu offset-y>
              <template v-slot:activator="{ on, attrs }">
                <v-btn small v-bind="attrs" v-on="on" color="primary">
                  Actions
                  <v-icon small> mdi-menu-down </v-icon>
                </v-btn>
              </template>
              <v-list class="pa-1">
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="hasPermission('product-reconcile')"
                >
                  <v-list-item-title>
                    <v-btn
                      class="mx-1 white--text"
                      color="cyan darken-2"
                      width="100px"
                      x-small
                      @click="getUnreconciled()"
                    >
                      <v-icon class="mr-1" x-small> mdi-file-multiple </v-icon>
                      Reconcile
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="hasPermission('product-template-download')"
                >
                  <v-list-item-title>
                    <v-btn
                      class="mx-1 white--text"
                      color="#AB47BC"
                      width="100px"
                      x-small
                      @click="downloadTemplate()"
                    >
                      <v-icon class="mr-1" x-small> mdi-download </v-icon>
                      Template
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="hasPermission('product-import')"
                >
                  <v-list-item-title>
                    <v-btn
                      color="primary"
                      class="mx-1"
                      width="100px"
                      x-small
                      @click="openImportDialog('import')"
                    >
                      <v-icon class="mr-1" x-small> mdi-import </v-icon>
                      Import
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="hasPermission('product-export')"
                >
                  <v-list-item-title>
                    <v-btn
                      class="mx-1"
                      color="success"
                      width="100px"
                      x-small
                      @click="exportData()"
                    >
                      <v-icon class="mr-1" x-small> mdi-microsoft-excel </v-icon>
                      Export
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="hasPermission('product-clear-list')"
                >
                  <v-list-item-title>
                    <v-btn
                      class="mx-1"
                      color="error"
                      width="100px"
                      x-small
                      @click="clearList()"
                      ><v-icon class="mr-1" x-small> mdi-delete </v-icon>clear
                      list</v-btn
                    >
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </div>
        </div>
        <v-card>
          <v-card-title>
            Product Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
            ></v-text-field>
            <v-spacer></v-spacer>
            <v-autocomplete
              v-model="search_branch"
              :items="branchOptions"
              item-text="name"
              item-value="id"
              label="Branch"
              v-if="user.id === 1 || hasPermission('product-list-all')"
            >
            </v-autocomplete>
            <template>
              <v-toolbar flat>
                <v-spacer></v-spacer>
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
                  max-width="1000px"
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
                      <v-autocomplete
                        v-model="inventory_group"
                        :items="inventory_groups"
                        item-text="name"
                        item-value="name"
                        label="Inventory Group"
                        hide-details=""
                        v-if="user.id === 1"
                      >
                      </v-autocomplete>
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
              </v-toolbar>
            </template>
          </v-card-title>
          <v-data-table
            :headers="tableHeaders"
            :items="filteredProducts"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="hasPermission('product-list')"
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
          </v-data-table>
        </v-card>
      </v-main>
      <ImportDialog 
        :api_route="api_route" 
        :dialog_import="dialog_import"
        :action="action"
        :branches="branches"
        @getData="getProduct"
        @closeImportDialog="closeImportDialog"
      />
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState, mapGetters, mapActions } from "vuex";
import ImportDialog from './components/ImportDialog.vue';

export default {
  name: "ProductIndex",
  components: {
    ImportDialog,
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
        { text: "Branch", value: "branch.name" },
        { text: "Date Created", value: "date_created" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      unreconciled_headers: [
        { text: "Branch", value: "branch.name" },
        { text: "Date Created", value: "date_created" },
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
        },
      ],
      loading: true,
      loading_unreconciled: true,
      user: "",
      search_branch: "",
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
      dialog_import: false,
    };
  },

  methods: {
    getProduct() {

      this.loading = true;
      axios.get("/api/product/index").then(
        (response) => {
          let data = response.data;
          console.log(data);
          this.user = data.user;
          this.products = data.products;
          this.brands = data.brands;
          this.branches = data.branches;
          this.product_categories = data.product_categories;
          this.editedItem.branch_id = this.user.branch_id;
          this.search_branch = this.user.branch_id;
          this.loading = false;
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
                Object.assign(this.products[this.editedIndex], this.editedItem);
              }
              else
              {
                this.products.push(data.product);
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
      this.editedIndex = this.products.indexOf(item);
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
      const data = { branch_id: this.search_branch };
      if (this.filteredProducts.length) {

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
      if (this.filteredProducts.length) {
        // if Dropdown Branch value is 'ALL'
        if (this.search_branch == 0) {

          this.showAlert('Please select specific branch!', 'warning');

        } else {
          this.loading_unreconciled = true;
          this.dialog_unreconciled = true;

          let data = {
            branch_id: this.search_branch,
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
        }
      } else {

        this.showAlert('Nothing to reconcile', 'warning');
        
      }
    },

    reconcileProducts(item) {
      this.$swal({
        title: "Reconcile Products",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "primary",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Proceed",
      }).then((result) => {
        // <--

        if (result.value) {
          // <-- if confirmed
          this.dialog_recon_loading = true;
          let data = {
            inventory_recon_id: item.id,
            products: this.filteredProducts,
            inventory_group: this.inventory_group,
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
      this.$swal({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Delete record!",
      }).then(async (result) => {
        // <--

        if (result.value) {
          // <-- if confirmed

          const product_id = item.id;
          const index = this.products.indexOf(item);

          //Call delete Product function
          await this.deleteProduct(product_id);

          //Remove item from array products
          await this.products.splice(index, 1);

        }
      });
    },


    clearList() {
      if (this.filteredProducts.length) {
        this.$swal({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#d33",
          cancelButtonColor: "#6c757d",
          confirmButtonText: "Clear List!",
        }).then((result) => {
          // <--

          if (result.value) {
            // <-- if confirmed

            let data = { branch_id: this.search_branch, clear_list: true };

            axios.post("api/product/delete", data).then(
              (response) => {
                if (response.data.success) {
                  // send data to Sockot.IO Server
                  // this.$socket.emit("sendData", { action: "product-create" });

                  let products = this.products;

                  // clear products array
                  this.products = [];

                  products.forEach((value, index) => {
                    // push products to array where except deleted data
                    if (value.branch_id != this.search_branch) {
                      this.products.push(value);
                    } else if (this.search_branch === 0) {
                      this.products = [];
                    }
                  });

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
    openImportDialog(action) {
      this.action = action;
      this.api_route = "/api/product/import";
      this.dialog_import = true;
    },

    closeImportDialog() { 
      this.dialog_import = false;
    },
    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.editedItem.branch_id = this.user.branch_id;
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
    // Create callback function to receive barcode when the scanner is already done
    onBarcodeScanned(barcode) {
      // console.log(barcode);
      this.serialExists = false;
      this.editedItem.serial = barcode;
      // do something...
    },
    // Reset to the last barcode before hitting enter (whatever anything in the input box)
    resetBarcode() {
      let barcode = this.$barcodeScanner.getPreviousCode();
      // do something...
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

      this.products.forEach((value) => {
        if (this.search_branch === 0) {
          products.push(value);
        } else if (value.branch_id === this.search_branch) {
          products.push(value);
        }
      });

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
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission", "hasAnyPermission"]),
  },
  created() {
    // Add barcode scan listener and pass the callback function
    this.$barcodeScanner.init(this.onBarcodeScanned);
  },
  destroyed() {
    // Remove listener when component is destroyed
    this.$barcodeScanner.destroy();
  },
  async mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    await this.getProduct();
    this.$barcodeScanner.init(this.onBarcodeScanned);
    // this.websocket();
  },
};
</script>