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
          <v-card-title>
            Division Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              v-if="hasPermission('division-list')"
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
                  v-if="hasPermission('division-create')"
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
                              name="division"
                              v-model="editedItem.division"
                              label="Division"
                              required
                              :error-messages="divisionErrors + divisionError.division"
                              @input="$v.editedItem.division.$touch() + (divisionError.division = [])"
                              @blur="$v.editedItem.division.$touch()"
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
            :items="divisions"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="hasPermission('division-list')"
          >
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editDivision(item)"
                v-if="hasPermission('division-edit')"
              >
                mdi-pencil
              </v-icon>

              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="hasPermission('division-delete')"
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
      division: { required },
    },
  },
  data() {
    return {
      absolute: true,
      overlay: false,
      switch1: true,
      search: "",
      headers: [
        { text: "Division", value: "name" },
        { text: "Active", value: "active" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      disabled: false,
      dialog: false,
      divisions: [],
      editedIndex: -1,
      editedItem: {
        division: "",
        active: "Y",
      },
      defaultItem: {
        division: "",
        active: "Y",
      },
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/dashboard",
        },
        {
          text: "Division Lists",
          disabled: true,
        },
      ],
      loading: true,
      divisionError: {
        division: [],
      }
    };
  },

  methods: {
    getDivision() {
      this.loading = true;
      axios.get("/api/division/index").then(
        (response) => {
          this.divisions = response.data.divisions;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    editDivision(item) {
      this.editedIndex = this.divisions.indexOf(item);
      this.editedItem.id = item.id;
      this.editedItem.division = item.name;
      this.editedItem.active = item.active;
      this.switch1 = true;
      if(this.editedItem.active === 'N')
      {
        this.switch1 = false;
      }
      this.dialog = true;
    },

    deleteDivision(division_id) {
      const data = { division_id: division_id };

      axios.post("/api/division/delete", data).then(
        (response) => {
          // console.log(response.data);
          if (response.data.success) {
            // send data to Socket.IO Server
            // this.$socket.emit("sendData", { action: "division-delete" });
          }
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

          const division_id = item.id;
          const index = this.divisions.indexOf(item);

          //Call delete Division function
          this.deleteDivision(division_id);

          //Remove item from array services
          this.divisions.splice(index, 1);

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
      this.divisionError = {
        division: [],
      };

      if (!this.$v.$error) {
        this.overlay = true;
        this.disabled = true;

        if (this.editedIndex > -1) {
          const data = this.editedItem;
          const division_id = this.editedItem.id;

          axios.post("/api/division/update/" + division_id, data).then(
            (response) => {
              if (response.data.success) {
                // send data to Socket.IO Server
                // this.$socket.emit("sendData", { action: "division-edit" });

                Object.assign(
                  this.divisions[this.editedIndex],
                  response.data.division
                );
                this.showAlert();
                this.close();
              } 
              else
              { 
                let errors = response.data;
                let errorNames = Object.keys(response.data);

                errorNames.forEach(value => {
                  this.divisionError[value].push(errors[value]);
                });
                
              }
              this.overlay = false;
              this.disabled = false;
            },
            (error) => {
              this.isUnauthorized(error);
              this.overlay = false;
            }
          );
        } else {
          const data = this.editedItem;

          axios.post("/api/division/store", data).then(
            (response) => {
              if (response.data.success) {
                // send data to Socket.IO Server
                // this.$socket.emit("sendData", { action: "division-create" });

                this.showAlert();
                this.close();

                //push recently added data from database
                this.divisions.push(response.data.division);
              } 
              else
              { 
                let errors = response.data;
                let errorNames = Object.keys(response.data);

                errorNames.forEach(value => {
                  this.divisionError[value].push(errors[value]);
                });
                
              }
              this.overlay = false;
              this.disabled = false;
            },
            (error) => {
              this.isUnauthorized(error);
              this.overlay = false;
            }
          );
        }
      }
    },
    clear() {
      this.$v.$reset();
      this.editedItem.division = "";
      this.editedItem.active = "Y";
      this.switch1 = true;
      this.divisionError = {
        division: []
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
          action == "division-create" ||
          action == "division-edit" ||
          action == "division-delete"
        ) {
          this.getDivision();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Division" : "Edit Division";
    },
    divisionErrors() {
      const errors = [];
      if (!this.$v.editedItem.division.$dirty) return errors;
      !this.$v.editedItem.division.required &&
        errors.push("Division is required.");
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

    this.getDivision();
    // this.websocket();
  },
};
</script>