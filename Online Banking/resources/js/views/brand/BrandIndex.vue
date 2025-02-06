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
            Brand Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              v-if="hasPermission('brand-list')"
            ></v-text-field>
            <template>
              <v-toolbar flat>
                <v-spacer></v-spacer>
                <v-btn
                  color="primary"
                  fab
                  dark
                  class="mb-2"
                  @click="clear() + (dialog = true)"
                  v-if="hasPermission('brand-create')"
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
                          <v-col class="my-0 py-0">
                            <v-text-field
                              name="brand"
                              v-model="editedBrand.name"
                              label="Brand"
                              required
                              :error-messages="brandErrors + brandError.name"
                              @input="$v.editedBrand.name.$touch() + (brandError.name = [])"
                              @blur="$v.editedBrand.name.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col cols="2" class="my-0 py-0">
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
            :items="brands"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="hasPermission('brand-list')"
          >
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editBrand(item)"
                v-if="hasPermission('brand-edit')"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="hasPermission('brand-delete')"
              >
                mdi-delete
              </v-icon>
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
    editedBrand: {
      name: { required },
    },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Brand", value: "name" },
        { text: "Active", value: "active" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      switch1: true,
      disabled: false,
      dialog: false,
      brands: [],
      editedIndex: -1,
      editedBrand: {
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
          text: "Brand Lists",
          disabled: true,
        },
      ],
      loading: true,
      brandError: {
        name: [],
      }
    };
  },

  methods: {
    getBrand() {
      this.loading = true;
      axios.get("/api/brand/index").then(
        (response) => {
          this.brands = response.data.brands;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    editBrand(item) {
      this.editedIndex = this.brands.indexOf(item);
      this.editedBrand = Object.assign({}, item);
      this.dialog = true;
      this.switch1 = true;
      if(this.editedBrand.active === 'N')
      {
        this.switch1 = false;
      }
    },

    deleteBrand(brand_id) {
      const data = { brand_id: brand_id };
      this.loading = true;
      axios.post("/api/brand/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "brand-delete" });
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

          const brand_id = item.id;
          const index = this.brands.indexOf(item);

          //Call delete Brand function
          this.deleteBrand(brand_id);

          //Remove item from array brands
          this.brands.splice(index, 1);

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
        this.editedBrand = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    save() {
      this.$v.$touch();
      this.brandError = {
        name: [],
      };

      if (!this.$v.$error) {
        this.disabled = true;

        if (this.editedIndex > -1) {
          const data = this.editedBrand;
          const brand_id = this.editedBrand.id;

          axios.post("/api/brand/update/" + brand_id, data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "brand-edit" });

                Object.assign(
                  this.brands[this.editedIndex],
                  this.editedBrand
                );
                this.showAlert();
                this.close();
              }
              else
              { 
                let errors = response.data;
                let errorNames = Object.keys(response.data);

                errorNames.forEach(value => {
                  this.brandError[value].push(errors[value]);
                });
                
              }

              this.disabled = false;
            },
            (error) => {
              this.isUnauthorized(error);
              this.disabled = false;
            }
          );
        } else {
          const data = this.editedBrand;

          axios.post("/api/brand/store", data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "brand-create" });

                this.showAlert();
                this.close();

                //push recently added data from database
                this.brands.push(response.data.brand);
              }
              else
              { 
                let errors = response.data;
                let errorNames = Object.keys(response.data);

                errorNames.forEach(value => {
                  this.brandError[value].push(errors[value]);
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
      }
    },

    clear() {
      this.$v.$reset();
      this.editedBrand.name = "";
      this.brandError = {
        name: []
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
          action == "brand-create" ||
          action == "brand-edit" ||
          action == "brand-delete"
        ) {
          this.getBrand();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Brand" : "Edit Brand";
    },
    brandErrors() {
      const errors = [];
      if (!this.$v.editedBrand.name.$dirty) return errors;
      !this.$v.editedBrand.name.required &&
        errors.push("Brand is required.");
      return errors;
    },
    activeStatus() {
      if (this.switch1) {
        this.editedBrand.active = "Y";
        return " Active";
      } else {
        this.editedBrand.active = "N";
        return " Inactive";
      }
    },
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getBrand();
    // this.websocket();
  },
};
</script>