<template>
  <div class="flex column">
    <div id="_wrapper" class="pa-5">
      <v-overlay :absolute="absolute" :value="overlay">
        <v-progress-circular
          :size="70"
          :width="7"
          color="primary"
          indeterminate
        ></v-progress-circular>
      </v-overlay>
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
            <span class="headline">Product</span>
            <!-- <v-divider vertical class="ml-3"></v-divider> -->
            <v-chip
              class="ml-3 mr-2"
              :color="switch1 ? 'primary' : 'secondary'"
            >
              {{ scanMode }}
            </v-chip>
            <v-divider vertical class="mr-3"></v-divider>
            <v-switch
              class="ma-0 pa-0 mr-4"
              hide-details=""
              inset
              v-model="switch1"
              @click="resetFields()"
            ></v-switch>
            <v-spacer></v-spacer>
            <v-autocomplete
              v-model="inventory_group"
              :items="inventory_groups"
              item-text="name"
              item-value="name"
              label="Inventory Group"
              v-if="user.id === 1"
              hide-details=""
              class="ma-0"
            >
            </v-autocomplete>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-6">
            <v-row>
              <v-col class="my-0 py-0" xs="12" sm="12" md="12" lg="4" xl="4">
                <v-autocomplete
                  v-model="editedItem.brand_id"
                  :items="brands"
                  item-text="name"
                  item-value="id"
                  label="Brand"
                  required
                  :error-messages="brandErrors"
                  @input="(fieldsActive = true)"
                  @blur="(fieldsActive = false)"
                  @focus="(fieldsActive = true)"
                  @change="clearSerialExistStatus()"
                >
                </v-autocomplete>
              </v-col>
            </v-row>
            <!-- <v-row>
              <v-col class="my-0 py-0">
                <v-text-field
                  name="model"
                  label="Model"
                  v-model="editedItem.model"
                  required
                  :error-messages="modelErrors"
                  @input="
                    $v.editedItem.model.$touch() +
                      (fieldsActive = true) +
                      clearSerialExistStatus()
                  "
                  @blur="$v.editedItem.model.$touch() + (fieldsActive = false)"
                  @focus="fieldsActive = true"
                ></v-text-field>
              </v-col>
            </v-row> -->
            <v-row>
              <v-col class="my-0 py-0" xs="12" sm="12" md="12" lg="4" xl="4">
                <v-autocomplete
                  :items="product_models"
                  v-model="editedItem.model"
                  item-text="name"
                  item-value="name"
                  label="Select Model"
                  :search-input.sync="search"
                  required
                  :error-messages="modelErrors"
                  @input="
                      (fieldsActive = true) +
                      clearSerialExistStatus()
                  "
                  @blur="(fieldsActive = false)"
                  @focus="fieldsActive = true"
                >
                  <template v-slot:append-outer>
                    <v-btn icon @click="searchModel()" color="primary"
                      ><v-icon>mdi-magnify</v-icon></v-btn
                    >
                  </template>
                  <template v-slot:append-item v-if="page != last_page">
                    <v-list-item class="ma-0" @click="loadMoreModel()">
                      <v-list-item-content>
                        <v-list-item-title class="blue--text text--darken-2">
                          <v-icon class="" color="primary" small
                            >mdi-chevron-down</v-icon
                          >
                          <span class="subtitle-2">
                            LOAD MORE</span
                          ></v-list-item-title
                        >
                      </v-list-item-content>
                    </v-list-item>
                  </template>
                  <!-- <template v-slot:append-item>
                    <div v-intersect="endIntersect()" />
                  </template> -->
                </v-autocomplete>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="my-0 py-0" xs="12" sm="12" md="12" lg="4" xl="4">
                <v-autocomplete
                  v-model="editedItem.product_category_id"
                  :items="product_categories"
                  item-text="name"
                  item-value="id"
                  label="Product Category"
                  required
                  :error-messages="product_categoryErrors"
                  @input="(fieldsActive = true)"
                  @blur="(fieldsActive = false)"
                  @focus="(fieldsActive = true)"
                  @change="clearSerialExistStatus()"
                >
                </v-autocomplete>
              </v-col>
            </v-row>
            <v-row v-if="!switch1">
              <v-col class="my-0 py-0" xs="12" sm="12" md="12" lg="4" xl="4">
                <v-text-field
                  name="serial"
                  label="Serial"
                  v-model="editedItem.serial"
                  readonly
                  required
                  :error-messages="serialErrors"
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="my-0 py-0" xs="12" sm="12" md="12" lg="4" xl="4">
                <v-autocomplete
                  v-model="editedItem.branch"
                  :items="branches"
                  item-text="name"
                  item-value="id"
                  label="Branch"
                  required
                  return-object
                  :error-messages="branchErrors"
                  @input="(fieldsActive = true)"
                  @blur="(fieldsActive = false)"
                  @focus="(fieldsActive = true)"
                  @change="clearSerialExistStatus()"
                  v-if="user.id === 1 || hasAnyRole('Audit Admin', 'Inventory Admin')"
                >
                </v-autocomplete>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="my-0 py-0" xs="12" sm="12" md="12" lg="4" xl="4">
                <v-autocomplete
                  v-model="editedItem.whse_code"
                  :items="whse_codes"
                  item-text="code"
                  item-value="code"
                  label="Warehouse"
                  required
                  :error-messages="warehouseErrors"
                  @input="(fieldsActive = true)"
                  @blur="(fieldsActive = false)"
                  @focus="(fieldsActive = true)"
                  @change="clearSerialExistStatus()"
                >
                </v-autocomplete>
              </v-col>
            </v-row>
            <v-row v-if="switch1">
              <v-col class="my-0 py-0" xs="12" sm="12" md="12" lg="4" xl="4">
                <v-responsive class="overflow-y-auto" max-height="350px" id="serial-table">
                  <v-simple-table dense class="mt-2">
                    <thead class="grey darken-1 white--text font-weight-bold">
                      <tr>
                        <td width="10px">#</td>
                        <td>Serial</td>
                        <td width="10px"></td>
                      </tr>
                    </thead>

                    <tbody>
                      <tr
                        v-for="(field, index) in serials"
                        :key="index"
                        :class="
                          errorFields[index].serial.duplicate ||
                          errorFields[index].serial.exist
                            ? 'white--text  red darken-1'
                            : ''
                        "
                      >
                        <td>
                          {{ index + 1 }}
                        </td>
                        <td>
                          <span>{{ field.serial }}</span>
                        </td>
                        <td>
                          <v-btn
                            dense
                            x-small
                            :color="
                              errorFields[index].serial.duplicate ||
                              errorFields[index].serial.exist
                                ? 'white red--text'
                                : 'red white--text'
                            "
                            class="ma-1"
                            @click="removeRow(field)"
                          >
                            <v-icon dark> mdi-minus </v-icon>
                          </v-btn>
                        </td>
                      </tr>
                    </tbody>
                  </v-simple-table>
                </v-responsive>
              </v-col>
            </v-row>
            <v-row v-if="serialsEmpty">
              <v-col xs="12" sm="12" md="12" lg="4" xl="4">
                <!-- <span class="v-messages error--text">Please enter serials</span> -->
                <v-alert dense outlined type="error">
                  Please enter serials
                </v-alert>
              </v-col>
            </v-row>

            <v-row v-if="serialExists || serialHasDuplicate">
              <v-col  xs="12" sm="12" md="12" lg="4" xl="4">
                <!-- <span class="v-messages error--text">{{
                  multiSerialErrors
                }}</span> -->
                <v-alert dense outlined type="error">
                  {{ multiSerialErrors }}
                </v-alert>
              </v-col>
            </v-row>
          </v-card-text>
          <v-divider class="mb-3 mt-0"></v-divider>
          <v-card-actions class="pa-0">
            <v-btn
              color="primary"
              @click="save()"
              :disabled="disabled"
              class="ml-6 mb-3 mr-1"
            >
              Save
            </v-btn>
            <v-btn color="#E0E0E0" class="mb-3" @click="clear()"> Clear </v-btn>
          </v-card-actions>
        </v-card>
      </v-main>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import {
  required,
  requiredIf,
  maxLength,
  email,
  minLength,
  sameAs,
} from "vuelidate/lib/validators";
import { mapState, mapGetters } from "vuex";
import goTo from "vuetify/lib/services/goto";
export default {
  mixins: [validationMixin],

  validations: {
    editedItem: {
      branch: { required },
      whse_code: { required },
      brand_id: { required },
      model: { required },
      product_category_id: { required },
      serial: {
        required: requiredIf(function () {
          return this.serialIsRequired;
        }),
      },
    },
  },
  data() {
    return {
      absolute: true,
      overlay: false,
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/product/index",
        },
        {
          text: "Scan Product",
          disabled: true,
        },
      ],
      disabled: false,
      editedIndex: -1,
      editedItem: {
        branch: "",
        whse_code: "",
        brand: "",
        brand_id: "",
        model: "",
        product_category_id: "",
        serial: "",
      },
      defaultItem: {
        branch: "",
        whse_code: "",
        brand: "",
        brand_id: "",
        model: "",
        product_category_id: "",
        serial: "",
      },
      brands: [], 
      branches: [],
      whse_codes: [],
      product_categories: [],
      product_models: [],
      products: [],
      serials: [],
      errorFields: [],
      switch1: false,
      serialsEmpty: false,
      serialExists: false,
      serialHasDuplicate: false,
      fieldsActive: false,
      last_page: 1,
      page: 1,
      pageCount: 0,
      itemsPerPage: 100,
      search: "",
      currSearch: "",
      inventory_groups: [{ name: "Admin-Branch" }, { name: "Audit-Branch" }],
      inventory_group: "Admin-Branch",
      noRecordFound: false,
    };
  },

  methods: {
    getProductOptions() {
      axios.get("/api/product/create").then(
        (response) => {
          
          let data = response.data;
          console.log(data);
          this.brands = data.brands;
          this.branches = data.branches;
          this.product_categories = data.product_categories;
          this.editedItem.branch = data.user.branch;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },
    getProductModel() {
      this.loading = true;

      const data = {
        items_per_page: this.itemsPerPage,
        search: this.currSearch,
      };

      axios.post("/api/product/search_model", data).then(
        (response) => {
          // console.log(response);
          axios
            .post(
              response.data.product_models.path + "?page=" + this.page,
              data
            )
            .then(
              (response) => {
        
                let product_models = response.data.product_models.data;
                this.last_page = response.data.product_models.last_page;

                product_models.forEach((value, index) => {
                  this.product_models.push(value);
                });

                // if product_models has no record and no serial number details found
                // if(!product_models && !this.noRecordFound)
                // {
                //   this.product_models.push(this.currSearch);
                // }

                this.loading = false;
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
      this.currSearch = this.search;

      this.product_models = [];
      this.page = 1;
      this.getProductModel();
    },
    // endIntersect(entries, observer, isIntersecting) {
    //   if (isIntersecting) {
    //     this.page = this.page + 1;
    //     this.getProductModel();
    //   }
    // },
    loadMoreModel() {
      if (this.last_page != this.page) {
        this.page = this.page + 1;
      }

      this.getProductModel();
    },
    searchSerialDetails(barcode) {
      this.noRecordFound = false;
      this.disabled = true;
      this.overlay = true;
      
      const data = { 
        serial: barcode, 
        inventory_group: this.inventory_group, 
        branch_id: this.editedItem.branch.id,  
        whse_code: this.editedItem.whse_code,
      };

      axios.post("/api/product/search_serial", data).then(
        (response) => {
          
          let product = response.data.product;

          if(product)
          {
            this.noRecordFound = false;
         
            let brand = product.brand;
            let model = product.model;
            let product_category = product.product_category;
            let branch_id = product.inventory_recon.branch_id;
            
            this.search = model;
            // call function searchModel to get the Produce Model List
            this.searchModel();
            
            this.brands.forEach(value => {
              if(value.name === brand)
              {
                this.editedItem.brand_id = value.id;
              }
            });

            this.product_categories.forEach(value => {
              if(value.name === product_category)
              {
                this.editedItem.product_category_id = value.id;
              }
            });
          
            this.editedItem.model = model;
            // this.editedItem.branch_id = branch_id;

          }
          else
          {
            
            this.$swal({
              position: "center",
              icon: "warning",
              title: "No record found",
              showConfirmButton: false,
              timer: 2500,
            });
            this.noRecordFound = true;
            this.editedItem.brand_id = null;
            this.editedItem.brand = null;
            this.search = null;
            this.editedItem.model = null;
            this.editedItem.product_category_id = null;
          }
          this.disabled = false;
          this.overlay = false;
        
        },
        (error) => {
          this.isUnauthorized(error);
          this.disabled = false;
          this.overlay = false;
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
          // this.$swal({
          //   position: "center",
          //   icon: "success",
          //   title: "Record fields has been cleared",
          //   showConfirmButton: false,
          //   timer: 2500,
          // });
        }
      });
    },

    async save() {
      this.$v.$touch();

      // if scanMode is Multiple
      if (this.switch1) {
        this.serialsEmpty = (await this.serials.length) ? false : true;
        this.editedItem.serials = await this.serials;

        await this.validateMultiSerial();
      }

      this.editedItem.scan_mode = await this.scanMode;

      if (
        !this.$v.$error &&
        !this.serialsEmpty &&
        !this.serialExists &&
        !this.serialHasDuplicate
      ) {
        this.disabled = true;
        this.overlay = true;
        let branch_id = this.editedItem.branch.id;
        const data = {...this.editedItem,  branch_id:  branch_id, inventory_group: this.inventory_group};
    
        await axios.post("/api/product/store", data).then(
          (response) => {

            this.overlay = false;
            this.disabled = false;

            if (response.data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "product-create" });

              this.showAlert();
              this.resetFields();

            } else if (response.data.existing_products) {
              let products = response.data.existing_products;

              this.serialExists = true;

              products.forEach((value, index) => {
                this.serials.forEach((val, i) => {
                  if (value.serial == val.serial) {
                    this.errorFields[i].serial.exist = true;
                  }
                });
              });
            } else if (response.data.duplicate_serials) {
              let serials = response.data.duplicate_serials;

              serials.forEach((value, index) => {
                this.serials.forEach((val, i) => {
                  if (value == val.serial) {
                    this.errorFields[i].serial.duplicate = true;
                    this.serialHasDuplicate = true;
                  }
                });
              });
            }

            
          },
          (error) => {
            this.isUnauthorized(error);

            this.overlay = false;
            this.disabled = false;
          }
        );
      }
    },

    async validateMultiSerial() {
      this.serialHasDuplicate = await false;
      this.serialExists = await false;

      // set serialExists to false
      await this.errorFields.forEach((value, index) => {
        if (value.serial.exist) {
          this.serialExists = true;
        }
      });

      // refresh error messages for every index
      await this.serials.forEach((value, index) => {
        this.errorFields[index].serial.duplicate = false;
      });

      await this.serials.forEach((value, index) => {
        this.serials.forEach((val, i) => {
          if (value.serial == val.serial && index != i) {
            this.errorFields[i].serial.duplicate = true;
            this.serialExists = true;
          }
        });
      });
    },

    clearSerialExistStatus() {
      let errorFields = this.errorFields;
      this.serialExists = false;
      errorFields.forEach((value, index) => {
        this.errorFields[index].serial.exist = false;
      });
    },

    clear() {
      // if scan mode is multiple
      if (this.switch1) {
        this.$swal({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#d33",
          cancelButtonColor: "#6c757d",
          confirmButtonText: "Clear fields!",
        }).then((result) => {
          // <--

          if (result.value) {
            // <-- if confirmed
            // this.$swal({
            //   position: "center",
            //   icon: "success",
            //   title: "Record fields has been cleared",
            //   showConfirmButton: false,
            //   timer: 2500,
            // });
            this.resetFields();
          }
        });
      } else {
        this.resetFields();
      }
    },

    resetFields() {
      this.$v.$reset();
      // this.editedItem = Object.assign({}, this.defaultItem);

      // retain the value of branch and warehouse code
      this.editedItem = { 
        ...this.editedItem, 
        brand: "",
        brand_id: "",
        model: "",
        product_category_id: "",
        serial: "", 
      };

      // this.editedItem.branch = this.user.branch;
      
      // this.editedItem.whse_code = "";
      this.serialsEmpty = false;
      this.serialExists = false;
      this.serialHasDuplicate = false;
      this.serials = [];
      this.errorFields = [];
      this.product_models = [];
      this.page = 1;
      this.last_page = 1;
      this.search = "";
      this.currSearch = "";
    },

    async removeRow(item) {
      const index = await this.serials.indexOf(item);

      //Delete rows on the object serials
      await this.serials.splice(index, 1);
      await this.errorFields.splice(index, 1);
      await this.validateMultiSerial();
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },

    // Create callback function to receive barcode when the scanner is already done
    async onBarcodeScanned(barcode) {

      // if form field is not active then push barcode data
      if (!this.fieldsActive) {
        this.serialsEmpty = false;

        // if Scan Mode is multiple
        if (this.switch1) {
          this.serials.push({
            serial: barcode,
          });

          this.errorFields.push({
            serial: { duplicate: false, exist: false },
          });

          await this.validateMultiSerial();

          // auto scroll down whole page
          // goTo(9999, {
          //   duration: 1000,
          //   offset: 500,
          //   easing: "easeInOutCubic",
          // });
          
          // auto scroll down serial-table
          await this.updateScroll();

          
        } else {
          this.editedItem.serial = barcode;
          this.serialExists = false;
          this.searchSerialDetails(barcode)
        }
      }

      // do something...
    },
    // auto scroll down when serial is added
    updateScroll() {
      var element = document.getElementById("serial-table");
      element.scrollTop = element.scrollHeight;
    },
    // Reset to the last barcode before hitting enter (whatever anything in the input box)
    resetBarcode() {
      let barcode = this.$barcodeScanner.getPreviousCode();
      // do something...
    },
  },
  watch: {
    'editedItem.branch.id'() {
      
      if(this.editedItem.branch)
      { 
        this.whse_codes = this.editedItem.branch.whse_codes;
        // auto select whse_code
        if(this.whse_codes.length)
        {
          this.editedItem.whse_code = this.whse_codes[0].code;
        }
      }      
  
    }
  },
  computed: {
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

    multiSerialErrors() {
      let serialExists = false;
      let serialHasDuplicate = false;

      this.errorFields.forEach((value, index) => {
        if (value.serial.duplicate) {
          serialHasDuplicate = true;
        }
        if (value.serial.exist) {
          serialExists = true;
        }
      });

      if (serialExists && serialHasDuplicate) {
        return "There are duplicate and existing serials";
      } else if (serialExists) {
        return "There are existing serials";
      } else if (serialHasDuplicate) {
        return "There are duplicate serials";
      }
    },
    branchErrors() {
      const errors = [];
      if (!this.$v.editedItem.branch.$dirty) return errors;
      !this.$v.editedItem.branch.required &&
        errors.push("Branch is required.");
      return errors;
    },
    warehouseErrors() {
      const errors = [];
      if (!this.$v.editedItem.whse_code.$dirty) return errors;
      !this.$v.editedItem.whse_code.required &&
        errors.push("Branch is required.");
      return errors;
    },
    scanMode() {
      if (this.switch1) {
        return " Multiple Scan";
      } else {
        return " Normal Mode";
      }
    },
    serialIsRequired() {
      let isRequired = false;
      if (this.switch1) {
        isRequired = false;
      } else {
        isRequired = true;
      }

      return isRequired;
    },
    ...mapState("auth", ["user"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission"]),
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
    await this.getProductOptions();
    this.$barcodeScanner.init(this.onBarcodeScanned);
    
  },
};
</script>