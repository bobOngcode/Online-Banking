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
            Company Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              v-if="hasPermission('company-list')"
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
                  v-if="hasPermission('company-create')"
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
                              name="company"
                              v-model="editedItem.name"
                              label="Company"
                              required
                              :error-messages="
                                companyErrors + companyError.name
                              "
                              @input="
                                $v.editedItem.name.$touch() +
                                  (companyError.name = [])
                              "
                              @blur="$v.editedItem.name.$touch()"
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
            :items="companies"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="hasPermission('company-list')"
          >
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="info"
                @click="viewBranches(item)"
              >
                mdi-eye
              </v-icon>
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editCompany(item)"
                v-if="hasPermission('company-edit')"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="hasPermission('company-delete')"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-data-table>
          <v-dialog v-model="dialogBranches" max-width="700px" persistent>
            <v-card>
              <v-card-title class="pa-4">
                <span class="headline">{{ company.name }}</span>
                <v-spacer></v-spacer>
                <v-btn icon @click="dialogBranches = false"><v-icon>mdi-close</v-icon></v-btn>
              </v-card-title>
              <v-divider class="mt-0"></v-divider>
              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col class="my-0 py-0">
                      <v-expansion-panels :value="opened">
                        <v-expansion-panel>
                          <v-expansion-panel-header>
                            BRANCHES
                          </v-expansion-panel-header>
                          <v-expansion-panel-content >
                            <v-divider class="mt-0"></v-divider>
                            <v-chip
                              color="secondary"
                              v-for="(branch, i) in company.branches"
                              :key="i"
                              class="ma-1"
                            >
                              {{ branch.name }}
                            </v-chip>
                          </v-expansion-panel-content>
                        </v-expansion-panel>
                      </v-expansion-panels>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
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
        { text: "Company", value: "name" },
        { text: "Active", value: "active" },
        { text: "Actions", value: "actions", sortable: false, width: "120px" },
      ],
      switch1: true,
      disabled: false,
      dialog: false,
      companies: [],
      branches: [],
      company: "",
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
          text: "Company Lists",
          disabled: true,
        },
      ],
      loading: true,
      companyError: {
        name: [],
      },
      dialogBranches: false,
      opened: 0,
    };
  },

  methods: {
    getCompany() {
      this.loading = true;
      axios.get("/api/company/index").then(
        (response) => {
          this.companies = response.data.companies;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    editCompany(item) {
      this.editedIndex = this.companies.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
      this.switch1 = true;
      if(this.editedItem.active === 'N')
      {
        this.switch1 = false;
      }
    },

    deleteCompany(company_id) {
      const data = { company_id: company_id };
      this.loading = true;
      axios.post("/api/company/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "company-delete" });
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

          const company_id = item.id;
          const index = this.companies.indexOf(item);

          //Call delete Company function
          this.deleteCompany(company_id);

          //Remove item from array companies
          this.companies.splice(index, 1);

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

    save() {
      this.$v.$touch();
      this.companyError = {
        name: [],
      };

      if (!this.$v.$error) {
        this.disabled = true;

        if (this.editedIndex > -1) {
          const data = this.editedItem;
          const company_id = this.editedItem.id;

          axios.post("/api/company/update/" + company_id, data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "company-edit" });

                Object.assign(
                  this.companies[this.editedIndex],
                  this.editedItem
                );
                this.showAlert();
                this.close();
              } else {
                let errors = response.data;
                let errorNames = Object.keys(response.data);

                errorNames.forEach((value) => {
                  this.companyError[value].push(errors[value]);
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
          const data = this.editedItem;

          axios.post("/api/company/store", data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "company-create" });

                this.showAlert();
                this.close();

                //push recently added data from database
                this.companies.push(response.data.company);
              } else {
                let errors = response.data;
                let errorNames = Object.keys(response.data);

                errorNames.forEach((value) => {
                  this.companyError[value].push(errors[value]);
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

    viewBranches(company) {
      this.dialogBranches = true;
      this.company = company;
    },

    clear() {
      this.$v.$reset();
      this.editedItem.name = "";
      this.companyError = {
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
          action == "company-create" ||
          action == "company-edit" ||
          action == "company-delete"
        ) {
          this.getCompany();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Company" : "Edit Company";
    },
    companyErrors() {
      const errors = [];
      if (!this.$v.editedItem.name.$dirty) return errors;
      !this.$v.editedItem.name.required && errors.push("Company is required.");
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
    this.getCompany();
    // this.websocket();
  },
};
</script>