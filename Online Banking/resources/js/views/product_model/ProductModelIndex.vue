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
          <v-card-title>
            Product Model Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              label="Search"
              single-line
              v-if="hasPermission('product-model-list')"
            >
              <template slot="append">
                <v-btn small color="primary" icon>
                  <v-icon @click="searchModel()">mdi-magnify</v-icon>
                </v-btn>
              </template>
            </v-text-field>
            <template>
              <v-toolbar flat>
                <v-spacer></v-spacer>
                <v-btn
                  color="primary"
                  fab
                  dark
                  class="mb-2"
                  @click="clear() + (dialog = true)"
                  v-if="hasPermission('product-model-create')"
                >
                  <v-icon>mdi-plus</v-icon>
                </v-btn>
                <v-dialog v-model="dialog" max-width="500px" persistent>
                  <v-card>
                    <v-card-title class="pa-4">
                      <span class="headline">{{ formTitle }}</span>
                    </v-card-title>
                    <v-divider class="mt-0"></v-divider>
                    <v-card-text>
                      <v-container>
                        <v-row>
                          <v-col class="mt-0 mb-0 pt-0 pb-0">
                            <v-text-field
                              name="product_model"
                              v-model="editedItem.name"
                              label="Product Model"
                              required
                              :error-messages="
                                product_modelErrors + product_modelError.name
                              "
                              @input="
                                $v.editedItem.name.$touch() +
                                  (product_modelError.name = [])
                              "
                              @blur="$v.editedItem.name.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col cols="2" class="mt-0 mb-0 pt-0 pb-0">
                            <v-switch
                              v-model="switch1"
                              :label="activeStatus"
                            ></v-switch>
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
              </v-toolbar>
            </template>
          </v-card-title>
          <v-data-table
            :headers="headers"
            :items="product_models.data"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            :page.sync="page"
            :footer-props="footerProps"
            @page-count="pageCount = $event"
            :items-per-page="itemsPerPage"
            @update:items-per-page="getItemPerPage"
            v-if="hasPermission('product-model-list')"
            show-first-last-page
          >
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editProductModel(item)"
                v-if="hasPermission('product-model-edit')"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="hasPermission('product-model-delete')"
              >
                mdi-delete
              </v-icon>
            </template>
            <template v-slot:footer.page-text>
              <!-- <v-btn color="primary" dark class="ma-2"> Button </v-btn> -->
              {{
                product_models.from
                  ? product_models.from +
                    " - " +
                    product_models.to +
                    " of " +
                    product_models.total
                  : ""
              }}
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
    editedItem: {
      name: { required },
    },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Product Model", value: "name" },
        { text: "Active", value: "active" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
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
      switch1: true,
      disabled: false,
      dialog: false,
      product_models: {},
      editedIndex: -1,
      editedItem: {
        name: "",
        active: "Y",
      },
      defaultItem: {
        name: "",
        active: "Y",
      },
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Product Model Lists",
          disabled: true,
        },
      ],
      loading: true,
      product_modelError: {
        name: [],
      },
      last_page: 0,
      prevBtnDisable: true,
      nextBtnDisable: true,
      firstBtnDisable: true,
      lastBtnDisable: false,
      modelTotalCount: 0,
    };
  },

  methods: {
    getProductModel() {
      this.product_models = {};
      this.loading = true;

      const data = { items_per_page: this.itemsPerPage, search: this.search };
      
      axios.post("/api/product_model/index?page=" + this.page, data).then(
        (response) => {
          
          let product_models = response.data.product_models;
          this.product_models = product_models;
          this.last_page = product_models.last_page;

          this.footerProps.pagination = {
            page: this.page,
            itemsPerPage: this.itemsPerPage,
            pageStart: product_models.from - 1,
            pageStop: product_models.to,
            pageCount: product_models.last_page,
            itemsLength: product_models.total,
          };

          this.resetPaginationButtons();

          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
        
      
    },
    nextPage() {
      if (this.page < this.last_page) {
        this.page = this.page + 1;
      }
      this.getProductModel();
    },
    prevPage() {
      if (this.page > 1) {
        this.page = this.page - 1;
      }
      this.getProductModel();
    },
    firstPage() {
      this.page = 1;
      this.prevBtnDisable = true;
      this.firstBtnDisable = true;

      this.getProductModel();
    },

    lastPage() {
      this.page = this.last_page;
      this.nexBtnDisable = true;
      this.lastBtnDisable = true;

      this.getProductModel();
    },

    save() {
      this.$v.$touch();
      this.product_modelError = {
        name: [],
      };

      if (!this.$v.$error) {
        this.disabled = true;
        const data = this.editedItem;
        const product_model_id = this.editedItem.id;
        let url = "/api/product_model" + (this.editedIndex > -1 ? "/update/" + product_model_id : "/store");

        axios.post(url, data).then(
          (response) => {
            let data = response.data;
            console.log(data);
            if (data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "product-edit" });

              if(this.editedIndex > -1)
              {
                Object.assign(this.product_models.data[this.editedIndex], this.editedItem);
              }
              else
              {
                this.product_models.data.push(data.product_models);
              }
              
              this.showAlert(data.success, 'success');
              this.close();

            } 
            else {
              let errors = response.data;
              let errorNames = Object.keys(response.data);

              errorNames.forEach((value) => {
                this.product_modelError[value].push(errors[value]);
              });
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

    editProductModel(item) {
      
      this.editedIndex = this.product_models.data.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
      this.switch1 = true;
      if(this.editedItem.active === 'N')
      {
        this.switch1 = false;
      }
    },

    deleteProductModel(product_model_id) {
      const data = { product_model_id: product_model_id };
      this.loading = true;
      axios.post("/api/product_model/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "product-model-delete" });
          }
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    showAlert() {
      this.$swal({
        position: "center",
        icon: "success",
        title: "Record has been saved",
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
      }).then((result) => {
        // <--

        if (result.value) {
          // <-- if confirmed

          const product_model_id = item.id;
          const index = this.product_models.data.indexOf(item);

          //Call delete Product Model function
          this.deleteProductModel(product_model_id);

          //Remove item from array product_models
          this.product_models.data.splice(index, 1);

          this.$swal({
            position: "center",
            icon: "success",
            title: "Record has been deleted",
            showConfirmButton: false,
            timer: 2500,
          });
        }
      });
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
      this.editedItem.name = "";
      this.product_modelError = {
        name: [],
      };
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
          action == "product-model-create" ||
          action == "product-model-edit" ||
          action == "product-model-delete"
        ) {
          this.getProductModel();
        }
      };
    },
    getItemPerPage(val) {
      this.itemsPerPage = val;
      this.page = 1;
      this.getProductModel();
    },
    searchModel() {
      this.page = 1;
      this.firstBtnDisable = true;
      this.prevBtnDisable = true;
      this.getProductModel();
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
  },

  watch: {
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
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1
        ? "New Product Model"
        : "Edit Product Model";
    },
    product_modelErrors() {
      const errors = [];
      if (!this.$v.editedItem.name.$dirty) return errors;
      !this.$v.editedItem.name.required &&
        errors.push("Product Model is required.");
      return errors;
    },
    activeStatus() {
      if (this.switch1) {
        this.editedItem.active = "Y";
        return " Active";
      } else {
        this.editedItem.active = "N";
        return " Inactive";
      }
    },
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getProductModel();
    // this.websocket();
  },
};
</script>