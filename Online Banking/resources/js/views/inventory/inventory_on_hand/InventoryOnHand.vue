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
        <v-card>
          <v-card-title class="mb-0 pb-0">
            Inventory On Hand
            <v-spacer></v-spacer>
            <v-btn class="mx-1 white--text" color="success" width="100px" small @click="exportData()">
              <v-icon class="mr-1" small> mdi-microsoft-excel </v-icon>
              Export
            </v-btn>
          </v-card-title>
          <v-divider class="mb-0 pb-0"></v-divider>
          <v-card-text>
            <v-row class="py-0 my-0">
              <v-col class="my-0 py-0 mt-2" xs="12" sm="12" md="12" lg="4" xl="4">
                <v-row>
                  <v-col class="my-0 py-0" xs="12" sm="12" md="12" lg="12" xl="12">
                    <v-autocomplete :items="productModel.items" v-model="productModel.name" item-text="name"
                      item-value="name" label="Model" :search-input.sync="productModel.search" required
                      :error-messages="modelErrors" :loading="model_loading" clearable>
                      <template v-slot:append-outer>
                        <v-tooltip top>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn icon @click="searchModel()" color="primary" v-bind="attrs" v-on="on"
                              :disabled="model_loading || search_loading">
                              <v-icon>mdi-magnify</v-icon>
                            </v-btn>
                          </template>
                          <span>Seach Model</span>
                        </v-tooltip>
                      </template>
                      <template v-slot:append-item v-if="productModel.page != productModel.last_page">
                        <v-list-item class="ma-0" @click="loadMoreModel()">
                          <v-list-item-content>
                            <v-list-item-title class="blue--text text--darken-2">
                              <v-icon class="" color="primary" small>mdi-chevron-down</v-icon>
                              <span class="subtitle-2">
                                LOAD MORE</span></v-list-item-title>
                          </v-list-item-content>
                        </v-list-item>
                      </template>
                    </v-autocomplete>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col class="my-0 py-0" xs="12" sm="12" md="12" lg="12" xl="12">
                    <v-autocomplete :items="productBrand.items" v-model="productBrand.name" item-text="name"
                      item-value="name" label="Brand" :search-input.sync="productBrand.search" required
                      :error-messages="modelErrors" :loading="brand_loading" clearable>
                      <template v-slot:append-outer>
                        <v-tooltip top>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn icon @click="searchBrand()" color="primary" v-bind="attrs" v-on="on"
                              :disabled="brand_loading || search_loading">
                              <v-icon>mdi-magnify</v-icon>
                            </v-btn>
                          </template>
                          <span>Seach Brand</span>
                        </v-tooltip>
                      </template>
                      <template v-slot:append-item v-if="productBrand.page != productBrand.last_page">
                        <v-list-item class="ma-0" @click="loadMoreBrand()">
                          <v-list-item-content>
                            <v-list-item-title class="blue--text text--darken-2">
                              <v-icon class="" color="primary" small>mdi-chevron-down</v-icon>
                              <span class="subtitle-2">
                                LOAD MORE</span></v-list-item-title>
                          </v-list-item-content>
                        </v-list-item>
                      </template>
                    </v-autocomplete>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col class="my-0 py-0" xs="12" sm="12" md="12" lg="12" xl="12">
                    <v-autocomplete :items="productCategory.items" v-model="productCategory.name" item-text="name"
                      item-value="name" label="Category" :search-input.sync="productCategory.search" required
                      :error-messages="modelErrors" :loading="category_loading" clearable>
                      <template v-slot:append-outer>
                        <v-tooltip top>
                          <template v-slot:activator="{ on, attrs }">
                            <v-btn icon @click="searchCategory()" color="primary" v-bind="attrs" v-on="on"
                              :disabled="category_loading || search_loading">
                              <v-icon>mdi-magnify</v-icon>
                            </v-btn>
                          </template>
                          <span>Seach Category</span>
                        </v-tooltip>
                      </template>
                      <template v-slot:append-item v-if="productCategory.page != productCategory.last_page">
                        <v-list-item class="ma-0" @click="loadMoreCategory()">
                          <v-list-item-content>
                            <v-list-item-title class="blue--text text--darken-2">
                              <v-icon class="" color="primary" small>mdi-chevron-down</v-icon>
                              <span class="subtitle-2">
                                LOAD MORE</span></v-list-item-title>
                          </v-list-item-content>
                        </v-list-item>
                      </template>
                    </v-autocomplete>
                  </v-col>
                </v-row>
                <v-row class="mt-6">
                  <v-col class="my-0 py-0" xs="12" sm="12" md="12" lg="12" xl="12">
                    <v-btn color="primary" @click="getInventory()" class="mb-4" :disabled="search_loading">search
                      product</v-btn>
                    <v-btn color="#E0E0E0" @click="clear()" class="ml-2 mb-4" :disabled="search_loading"> clear </v-btn>
                  </v-col>
                </v-row>
              </v-col>
              <v-divider vertical></v-divider>
              <v-col>
                <v-data-table :headers="headers" :items="products" :search="search" :loading="loading"
                  loading-text="Loading... Please wait" v-if="hasPermission('inventory-on-hand')">
                  <template v-slot:top>
                    <v-toolbar flat>
                      <v-spacer></v-spacer>
                      <v-text-field v-model="search" append-icon="mdi-magnify" label="Search" single-line
                        hide-details=""></v-text-field>
                      <v-spacer></v-spacer>
                    </v-toolbar>
                  </template>
                </v-data-table>
              </v-col>
            </v-row>
          </v-card-text>
          <!-- <v-divider class="mb-3 mt-4"></v-divider>
          <v-card-actions class="pa-0">
            <v-btn color="#E0E0E0" @click="clear()" class="ml-6 mb-4"> clear </v-btn>
          </v-card-actions> -->
        </v-card>
        <v-dialog v-model="dialog" max-width="1000px" persistent>
          <v-card>
            <v-card-title>
              <span class="headline">Select Product</span>
              <v-spacer></v-spacer>
              <v-text-field v-model="search" append-icon="mdi-magnify" label="Search" single-line></v-text-field>
              <v-spacer></v-spacer>
              <v-icon @click="dialog = false">mdi-close</v-icon>
            </v-card-title>
            <v-card-text>
              <v-data-table :headers="searched_headers" :items="searched_product_list" :search="search"
                :loading="loading" loading-text="Loading... Please wait" class="elevation-1 ">
                <template v-slot:item="{ item }">
                  <tr @click="selectProduct(item)" style="cursor: pointer;">
                    <td>
                      {{ item.model }}
                    </td>
                    <td>
                      {{ item.brand }}
                    </td>
                    <td>
                      {{ item.category }}
                    </td>
                  </tr>
                </template>
              </v-data-table>
            </v-card-text>
          </v-card>
        </v-dialog>
        <!-- loader-dialog -->
        <v-dialog v-model="search_loading" persistent width="300">
          <v-card color="primary" dark>
            <v-card-text>
              <p class="text-center pt-2">
                Loading. Please wait...
              </p>
              <v-progress-linear indeterminate color="white" class="mb-0"></v-progress-linear>
            </v-card-text>
          </v-card>
        </v-dialog>
      </v-main>
    </div>
  </div>
</template>
<script>

import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState, mapGetters } from "vuex";

export default {

  mixins: [validationMixin],

  validations: {
    model: { required },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Warehouse Code", value: "whse_code" },
        { text: "Warehouse Name", value: "whse_name" },
        { text: "In-Stock", value: "onhand" },
        { text: "Committed", value: "committed" },
        { text: "Ordered", value: "ordered" },
        { text: "Available", value: "available" },
      ],
      searched_headers: [
        { text: "Model", value: "model" },
        { text: "Brand", value: "brand" },
        { text: "Category", value: "category" },
      ],
      disabled: false,
      dialog: false,
      products: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Inventory Onhand",
          disabled: true,
        },
      ],
      products: [],
      searched_product_list: [],
      brands: [],
      product_models: [],
      product_categories: [],
      loading: false,
      brand_loading: false,
      model_loading: false,
      category_loading: false,
      search: "",
      productBrand: {
        items: [],
        search: "",
        last_page: 1,
        page: 1,
        pageCount: 0,
        itemsPerPage: 100,
        currSearch: "",
      },
      productModel: {
        items: [],
        search: "",
        last_page: 1,
        page: 1,
        pageCount: 0,
        itemsPerPage: 100,
        currSearch: "",
      },
      productCategory: {
        items: [],
        search: "",
        last_page: 1,
        page: 1,
        pageCount: 0,
        itemsPerPage: 100,
        currSearch: "",
      },

      defaultItems: {
        items: [],
        search_model: "",
        last_page: 1,
        page: 1,
        pageCount: 0,
        itemsPerPage: 100,
        currSearch: "",
      },

      model: "",
      brand: "",
      category: "",
      search_loading: false,
    };
  },

  methods: {
    getInventory() {

      this.search_loading = true;

      const data = {
        model: this.productModel.name,
        brand: this.productBrand.name,
        category: this.productCategory.name,
      };

      axios.post("/api/product/inventory_on_hand", data).then(
        (response) => {
          let data = response.data;
          this.search_loading = false;
          console.log(data);
          if (data.multiple_brand_model_category) {
            this.searched_product_list = data.multiple_brand_model_category;
            this.dialog = true;
          }
          else {
            this.products = response.data.products;

            if (!this.products.length) {
              this.showAlert('No Record Found', 'warning')
            }

            this.productBrand.search = data.brand;
            this.productBrand.name = data.brand;
            this.productBrand.items = [data.brand];
            this.productModel.search = data.model;
            this.productModel.name = data.model;
            this.productModel.items = [data.model];
            this.productCategory.search = data.category;
            this.productCategory.name = data.category;
            this.productCategory.items = [data.category];

            // this.searchBrand();
            // this.searchModel();
            // this.searchCategory();
          }

        },
        (error) => {
          this.search_loading = false;
          this.isUnauthorized(error);
          this.overlay = false;

          this.showAlert(error.response.data.message, 'error');

        }
      );
    },

    getProductBrand() {
      this.brand_loading = true;

      const data = {
        items_per_page: this.itemsPerPage,
        search: this.productBrand.currSearch,
      };

      axios.post("/api/product/search_brand", data).then(
        (response) => {
          // console.log(response);
          axios
            .post(
              response.data.brands.path + "?page=" + this.productBrand.page,
              data
            )
            .then(
              (response) => {

                let brands = response.data.brands.data;
                this.productBrand.last_page = response.data.brands.last_page;

                brands.forEach((value, index) => {
                  this.productBrand.items.push(value);
                });

                // if brands has no record and no serial number details found
                // if(!brands && !this.noRecordFound)
                // {
                //   this.brands.push(this.currSearch);
                // }

                this.brand_loading = false;
              },
              (error) => {
                this.isUnauthorized(error);
              }
            );
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },
    searchBrand() {
      // assign value to upon clicking of search button
      this.productBrand.currSearch = this.productBrand.search;
      this.productBrand.items = [];
      this.productBrand.page = 1;
      this.getProductBrand();
    },
    loadMoreBrand() {
      if (this.productBrand.last_page != this.productBrand.page) {
        this.productBrand.page = this.productBrand.page + 1;
      }

      this.getProductBrand();
    },

    getProductModel() {
      this.model_loading = true;

      const data = {
        items_per_page: this.itemsPerPage,
        search: this.productModel.currSearch,
      };

      axios.post("/api/product/search_model", data).then(
        (response) => {
          // console.log(response);
          axios
            .post(
              response.data.product_models.path + "?page=" + this.productModel.page,
              data
            )
            .then(
              (response) => {

                let product_models = response.data.product_models.data;
                this.productModel.last_page = response.data.product_models.last_page;

                product_models.forEach((value, index) => {
                  this.productModel.items.push(value);
                });

                // if product_models has no record and no serial number details found
                // if(!product_models && !this.noRecordFound)
                // {
                //   this.product_models.push(this.currSearch);
                // }

                this.model_loading = false;
              },
              (error) => {
                this.isUnauthorized(error);
              }
            );
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },
    searchModel() {
      // assign value to upon clicking of search button
      this.productModel.currSearch = this.productModel.search;

      this.productModel.items = [];
      this.productModel.page = 1;
      this.getProductModel();
    },
    loadMoreModel() {
      if (this.productModel.last_page != this.productModel.page) {
        this.productModel.page = this.productModel.page + 1;
      }

      this.getProductModel();
    },

    getProductCategory() {
      this.category_loading = true;

      const data = {
        items_per_page: this.itemsPerPage,
        search: this.productCategory.currSearch,
      };

      axios.post("/api/product/search_category", data).then(
        (response) => {
          // console.log(response);
          axios
            .post(
              response.data.product_categories.path + "?page=" + this.productCategory.page,
              data
            )
            .then(
              (response) => {

                let product_categories = response.data.product_categories.data;
                this.productCategory.last_page = response.data.product_categories.last_page;

                product_categories.forEach((value, index) => {
                  this.productCategory.items.push(value);
                });

                this.category_loading = false;
              },
              (error) => {
                this.isUnauthorized(error);
              }
            );
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },
    searchCategory() {
      // assign value to upon clicking of search button
      this.productCategory.currSearch = this.productCategory.search;

      this.productCategory.items = [];
      this.productCategory.page = 1;
      this.getProductCategory();
    },
    loadMoreCategory() {
      if (this.productCategory.last_page != this.productCategory.page) {
        this.productCategory.page = this.productCategory.page + 1;
      }

      this.getProductCategory();
    },

    async selectProduct(item) {
      this.dialog = false;
      this.productBrand.search = item.brand;
      this.productBrand.items = [item.brand];
      this.productBrand.name = item.brand;

      this.productModel.search = item.model;
      this.productModel.items = [item.model];
      this.productModel.name = item.model;

      this.productCategory.search = item.category;
      this.productCategory.items = [item.category];
      this.productCategory.name = item.category;

      // await this.searchBrand();
      // await this.searchModel();
      // await this.searchCategory();
      await this.getInventory();
    },

    exportData() {
      if (this.products.length) {
        console.log(this.products);
        const data = { products: this.products };

        axios.post('/api/product/export_inventory_on_hand', data, { responseType: 'arraybuffer' })
          .then((response) => {
            var fileURL = window.URL.createObjectURL(new Blob([response.data]));
            var fileLink = document.createElement('a');
            fileLink.href = fileURL;
            fileLink.setAttribute('download', 'InventoryOnHand.xls');
            document.body.appendChild(fileLink);
            fileLink.click();
          }, (error) => {
            console.log(error);
          }
          );

      } else {
        this.showAlert("No record found", "warning")
      }
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

    clear() {
      this.$v.$reset();
      this.brand = "";
      this.model = "";
      this.category = "";
      this.products = [];
      this.brands = [];
      this.product_models = [];
      this.product_categories = [];
      this.searched_product_list = [];
      this.search = "";

      this.productBrand = Object.assign({}, this.defaultItems);
      this.productModel = Object.assign({}, this.defaultItems);
      this.productCategory = Object.assign({}, this.defaultItems);

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
          action == "position-create" ||
          action == "position-edit" ||
          action == "position-delete"
        ) {
          this.getInventory();
        }
      };
    },
  },


  computed: {

  modelErrors() {
      const errors = [];
      if (!this.$v.model.$dirty) return errors;
      !this.$v.model.required && errors.push("Model is required.");
      return errors;
    },
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission", "hasAnyPermission"]),
  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
  },
  
};
</script>