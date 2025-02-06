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
            <span class="headline mr-3">Serial Number Details {{  editedItem.doctype ? ' - ' + editedItem.doctype : '' }}</span>
            <v-btn 
              color="primary" 
              small 
              @click="dialog_product_history = true"
              v-if="product_histories.length"
            >  
             <v-icon small class="mr-1">mdi-eye</v-icon>  View Product History 
            </v-btn>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="px-6">
            <v-row>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="serial"
                  v-model="serial"
                  label="Serial"
                  append-outer- ='mdi-magnify'
                  :error-messages="serialErrors"
                  @input="$v.serial.$touch() + $v.model.$reset()"
                >
                  <template v-slot:append-outer> 
                    <v-btn small color="primary" class="px-2" @click="searchBySerial()"> 
                      <v-icon small class="mr-1">mdi-magnify</v-icon> 
                      search
                    </v-btn> 
                  </template>
                </v-text-field>
              </v-col>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="model"
                  v-model="editedItem.model"
                  label="Model"
                >
                  <!-- <template v-slot:append-outer> 
                    <v-btn small color="primary" class="px-2"> 
                      <v-icon small class="mr-1">mdi-magnify</v-icon> 
                      search
                    </v-btn> 
                  </template> -->
                </v-text-field>
              </v-col>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="brand"
                  v-model="editedItem.brand"
                  label="Brand"
                  readonly
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="supplier"
                  v-model="editedItem.supplier"
                  label="Supplier"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="item_group"
                  v-model="editedItem.item_group"
                  label="Item Group"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="product_category"
                  v-model="editedItem.product_category"
                  label="Product Category"
                  readonly
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="my-0 py-0">
                <v-text-field
                  v-model="datePurchase"
                  label="Date Purchase"
                  prepend-icon="mdi-calendar"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col class="my-0 py-0">
                <v-text-field
                  v-model="dateIssued"
                  label="Date Issued"
                  prepend-icon="mdi-calendar"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col class="my-0 py-0">
                <v-text-field
                  v-model="Number(editedItem.price).toLocaleString('en', numOpts)"
                  label="price"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col class="my-0 py-0">
                <v-text-field
                  name="grpo_number"
                  v-model="grpo_number"
                  label="GRPO Number"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col class="my-0 py-0">
                <v-text-field
                  name="gi_number"
                  v-model="editedItem.gi_number"
                  label="Goods Issue Number"
                  readonly
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="branch"
                  v-model="editedItem.branch"
                  label="Branch"
                  readonly
                ></v-text-field>
              </v-col>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field
                  name="company"
                  v-model="editedItem.company"
                  label="Company"
                  readonly
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="6" class="my-0 py-0">
                <v-textarea 
                  v-model="grpo_remarks"
                  label="GRPO Remarks"
                  readonly
                  rows="4"
                >
                </v-textarea>
              </v-col>
              <v-col cols="6" class="my-0 py-0">
                <v-textarea 
                  v-model="editedItem.gi_remarks"
                  label="Goods Issue Remarks"
                  readonly
                  rows="4"
                >
                </v-textarea>
              </v-col>
            </v-row>
          </v-card-text>
          <v-divider class="mb-3 mt-0"></v-divider>
          <v-card-actions class="pa-0">
            <v-btn class="ml-6 mb-4" @click="clear() + (serial = '')"> Clear </v-btn>
          </v-card-actions>
        </v-card>
      </v-main>
      <v-dialog v-model="dialog" max-width="1500px" persistent>
        <v-card>
          <v-card-title>
            <span class="headline">Products</span>
              <v-spacer></v-spacer>
              <v-text-field
                v-model="search"
                append-icon="mdi-magnify"
                label="Search"
                single-line
              ></v-text-field>
              <v-spacer></v-spacer>

              <v-icon @click="dialog = false">mdi-close</v-icon>
          </v-card-title>
          <v-card-text>
            <v-data-table
              :headers="headers"
              :items="products"
              :search="search"
              :loading="loading"
              loading-text="Loading... Please wait"
              class="elevation-1 "
            > 
              <template v-slot:item="{ item }">
                <tr @click="selectProduct(item)" style="cursor: pointer;">
                  <td> {{ item.branch }} </td>
                  <td> {{ item.company }} </td>
                  <td> {{ item.serial }} </td>
                  <td> {{ item.model }} </td>
                  <td> {{ item.brand }} </td>
                  <td> {{ item.supplier }} </td>
                  <td> {{ dateFormat(item.date_purchase) }} </td>
                  <td> {{ dateFormat(item.date_issued) }} </td>
                </tr>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-dialog>
      <v-dialog v-model="dialog_product_history" max-width="1500px" persistent>
        <v-card>
          <v-card-title>
            <span class="headline">Products</span>
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
            ></v-text-field>
            <v-spacer></v-spacer>
            <v-icon @click="dialog_product_history = false">mdi-close</v-icon>
          </v-card-title>
          <v-card-text>
            <v-data-table
              :headers="headers"
              :items="product_histories"
              :search="search_history"
              :loading="loading"
              loading-text="Loading... Please wait"
              class="elevation-1 "
            > 
              <template v-slot:item="{ item }">
                <tr @click="selectProduct(item)" style="cursor: pointer;">
                  <td> {{ item.doctype }} </td>
                  <td> {{ dateFormat( item.date_purchase ? item.date_purchase : item.date_issued ) }} </td>
                  <td> {{ item.branch }} </td>
                  <td> {{ item.company }} </td>
                  <td> {{ item.serial }} </td>
                  <td> {{ Number(item.price).toLocaleString('en', numOpts) }} </td>
                  <td> {{ item.model }} </td>
                  <td> {{ item.brand }} </td>
                  <td> {{ item.supplier }} </td>
                </tr>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-dialog>
    </div>
  </div>
</template>
<script>                                                                                                                                                                                                                                                                                                                                
import axios from "axios";
import { validationMixin } from "vuelidate";
import { required } from "vuelidate/lib/validators";

export default {
  mixins: [validationMixin],

  validations: {
    serial: { required },
    model: { required }
  },
  data() {
    return {
      absolute: true,
      overlay: false,
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Serial Number Details",
          disabled: true,
        },
      ],
      disabled: false,
      editedIndex: -1,
      serial: "",
      model: "",
      editedItem: {
        doctype: "",
        serial: "",
        branch: "",
        company: "",
        supplier: "",
        brand: "",
        date_issued: "",
        gi_number: "",
        date_purchase: "",
        grpo_number: "",
        item_group: "",
        model: "",
        product_category: "",
        grpo_remarks: "",
        gi_remakrs: "",
        price: "",
      },
      defaultItem: {
        doctype: "",
        serial: "",
        branch: "",
        company: "",
        supplier: "",
        brand: "",
        date_issued: "",
        gi_number: "",
        date_purchase: "",
        grpo_number: "",
        item_group: "",
        model: "",
        product_category: "",
        grpo_remarks: "",
        gi_remakrs: "",
        price: "",
      },
      grpo_number: "",
      grpo_remarks: "",
      date_purchase: "",
      dialog: false,
      dialog_product_history: false,
      products: [],
      product_histories: [],
      loading: false,
      search: "",
      search_history: "",
      headers: [
        { text: "Branch", value: "branch" },
        { text: "Company", value: "company" },
        { text: "Serial", value: "serial" },
        { text: "Model", value: "model" },
        { text: "Brand", value: "brand" },
        { text: "Supplier", value: "supplier" },
        { text: "Date Purchase", value: "date_puchase" },
        { text: "Date Issued", value: "date_issued" },
      ],
      headers: [
        { text: "DocType", value: "" },
        { text: "Document Date", value: "" },
        { text: "Branch", value: "" },
        { text: "Company", value: "" },
        { text: "Serial", value: "" },
        { text: "Price", value: "" },
        { text: "Model", value: "" },
        { text: "Brand", value: "" },
        { text: "Supplier", value: "" },
      ],
      numOpts: { 
        minimumFractionDigits: 2,
        maximumFractionDigits: 2 
      },
    };
  },

  methods: {
    getSerialNumberDetails(data) {

      if (!this.$v.serial.$error)
      {
        this.product_histories = [];
        this.overlay = true;
        
        axios.post("/api/product/serial_number_details", data).then(
          (response) => {
            this.overlay = false;
            
            let data = response.data   
            console.log(data);
            

            if(data.error)
            {
              this.$swal({
                position: "center",
                icon: "error",
                title: "Error",
                text: response.data.error,
                showConfirmButton: false,
                timer: 10000,
              });
            }
            
            // if products is more than 1 rows then show the product list table for selection
            if(data.products.length > 1)
            {
              this.dialog = true;
              this.products = data.products;
            }
            else
            {
              let serial = data.products[0] ? data.products[0].serial : '';
              if(serial)
              {
                this.serial = serial;
                this.editedItem = data.products[0];
                this.date_purchase = this.editedItem.date_purchase;
                this.grpo_number = this.editedItem.grpo_number;
                this.grpo_remarks = this.editedItem.grpo_remarks;
                let product_histories = data.product_histories;
                let databases = Object.keys(product_histories);

                databases.forEach(database => {
                  product_histories[database].forEach(value => {
                    this.product_histories.push(value);
                  });
                });
                

                console.log(this.product_histories);
                

              }
              else if(!data.error)
              {
                this.$swal({
                  position: "center",
                  icon: "warning",
                  title: "No record found",
                  showConfirmButton: false,
                  timer: 2500,
                });
              }
            }
          },
          (error) => {
            this.isUnauthorized(error);
            this.overlay = false;
            this.$swal({
                position: "center",
                icon: "error",
                title: error,
                text: error.response.data.message,
                showConfirmButton: false,
                timer: 10000,
              });
            
          }
        );
      }
      
    },

    searchBySerial(){
      this.$v.model.$reset(); //reset validation for model when serial field is searched
      this.$v.serial.$touch();
      this.editedItem = Object.assign({}, this.defaultItem);
      const data = { serial: this.serial }
      this.getSerialNumberDetails(data);
    },

    searchByModel(){
      this.$v.serial.$reset(); //reset validation for serial when product model field is searched
      this.$v.model.$touch();
      this.dialog = true;
      this.loading = true;
      const data = { model: this.model };
      this.getSerialNumberDetails(data);

    },

    selectProduct(item)
    {
      this.serial = item.serial;
      this.editedItem = item;
      this.dialog_product_history = false;
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

    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.serial = '';
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },
    dateFormat(date){
  
      if (!date) return null;

      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },
  },
  computed: {
    serialErrors() {
      const errors = [];
      if (!this.$v.serial.$dirty) return errors;
      !this.$v.serial.required && errors.push("Serial is required.");
      return errors;
    },
    modelErrors() {
      const errors = [];
      if (!this.$v.model.$dirty) return errors;
      !this.$v.model.required && errors.push("Model is required.");
      return errors;
    },
    datePurchase() {
      let date = this.date_purchase
      if (!date) return null;

      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },
    dateIssued() {
      let date = this.editedItem.date_issued
      if (!date) return null;

      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    }
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
  },
};
</script>