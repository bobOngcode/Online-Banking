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
            Branch Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              v-if="hasPermission('branch-list')"
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
                  v-if="hasPermission('branch-create')"
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
                              name="branch_code"
                              v-model="editedItem.code"
                              label="Branch Code"
                              required
                              :error-messages="
                                branchCodeErrors + branchError.code
                              "
                              @input="
                                $v.editedItem.code.$touch() +
                                  (branchError.code = [])
                              "
                              @blur="$v.editedItem.code.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="my-0 py-0">
                            <v-text-field
                              name="branch"
                              v-model="editedItem.name"
                              label="Branch Name"
                              required
                              :error-messages="branchErrors + branchError.name"
                              @input="
                                $v.editedItem.name.$touch() +
                                  (branchError.name = [])
                              "
                              @blur="$v.editedItem.name.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="my-0 py-0">
                            <v-text-field
                              name="branch"
                              v-model="editedItem.bm_oic"
                              label="BM/OIC"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="my-0 py-0">
                            <v-autocomplete
                              v-model="editedItem.company_id"
                              :items="companies"
                              item-text="name"
                              item-value="id"
                              label="Company"
                              required
                              :error-messages="companyErrors"
                              @input="$v.editedItem.company_id.$touch()"
                              @blur="$v.editedItem.company_id.$touch()"
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
              </v-toolbar>
            </template>
          </v-card-title>

          <v-data-table
            :headers="headers"
            :items="branches"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="hasPermission('branch-list')"
          >
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editBranch(item)"
                v-if="hasPermission('branch-edit')"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="hasPermission('branch-delete')"
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
    editedItem: {
      code: { required },
      name: { required },
      company_id: { required },
    },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "name" },
        { text: "Branch Code", value: "code" },
        { text: "BM/OIC", value: "bm_oic" },
        { text: "Company", value: "company.name" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      disabled: false,
      dialog: false,
      branches: [],
      companies: [],
      editedIndex: -1,
      editedItem: {
        name: "",
        code: "",
        company_id: "",
        bm_oic: "",
      },
      defaultItem: {
        name: "",
        code: "",
        company_id: "",
        bm_oic: "",
      },
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Branch Lists",
          disabled: true,
        },
      ],
      loading: true,
      branchError: {
        name: [],
        code: [],
      },
    };
  },

  methods: {
    getBranch() {
      this.loading = true;
      axios.get("/api/branch/index").then(
        (response) => {
          console.log(response.data);
          this.branches = response.data.branches;
          this.companies = response.data.companies;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    editBranch(item) {
      this.editedIndex = this.branches.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteBranch(branch_id) {
      const data = { branch_id: branch_id };
      this.loading = true;
      axios.post("/api/branch/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "branch-delete" });
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

          const branch_id = item.id;
          const index = this.branches.indexOf(item);

          //Call delete Branch function
          this.deleteBranch(branch_id);

          //Remove item from array branches
          this.branches.splice(index, 1);

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
      this.branchError = {
        code: [],
        name: [],
      };

      if (!this.$v.$error) {
        this.disabled = true;

        if (this.editedIndex > -1) {
          const data = this.editedItem;
          const branch_id = this.editedItem.id;

          axios.post("/api/branch/update/" + branch_id, data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "branch-edit" });

                Object.assign(this.branches[this.editedIndex], this.editedItem);
                this.showAlert();
                this.close();
              } else {
                let errors = response.data;
                let errorNames = Object.keys(response.data);

                errorNames.forEach((value) => {
                  this.branchError[value].push(errors[value]);
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

          axios.post("/api/branch/store", data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "branch-create" });

                this.showAlert();
                this.close();

                //push recently added data from database
                this.branches.push(response.data.branch);
              } else {
                let errors = response.data;
                let errorNames = Object.keys(response.data);

                errorNames.forEach((value) => {
                  this.branchError[value].push(errors[value]);
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
      this.editedItem.name = "";
      this.branchError = {
        code: [],
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
          action == "branch-create" ||
          action == "branch-edit" ||
          action == "branch-delete"
        ) {
          this.getBranch();
        }
      };
    },
  },
  watch: {
    "editedItem.company_id"() {
      this.companies.forEach((value) => {
        if (this.editedItem.company_id === value.id) {
          this.editedItem.company = value;
        }
      });
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Branch" : "Edit Branch";
    },
    branchErrors() {
      const errors = [];
      if (!this.$v.editedItem.name.$dirty) return errors;
      !this.$v.editedItem.name.required && errors.push("Branch is required.");
      return errors;
    },
    branchCodeErrors() {
      const errors = [];
      if (!this.$v.editedItem.code.$dirty) return errors;
      !this.$v.editedItem.code.required &&
        errors.push("Branch Code is required.");
      return errors;
    },
    companyErrors() {
      const errors = [];
      if (!this.$v.editedItem.company_id.$dirty) return errors;
      !this.$v.editedItem.company_id.required &&
        errors.push("Company is required.");
      return errors;
    },
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getBranch();
    // this.websocket();
  },
};
</script>