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
            Access Chart Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              v-if="hasPermission('access-chart-list')"
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
                  v-if="hasPermission('access-chart-create')"
                >
                  <v-icon>mdi-plus</v-icon>
                </v-btn>
                <v-dialog v-model="dialog" max-width="600px" persistent>
                  <v-card>
                    <v-card-title class="pa-4">
                      <span class="headline">{{ formTitle }}</span>
                    </v-card-title>
                    <v-divider  class="mt-0"></v-divider>
                    <v-card-text>
                      <v-container>
                        <v-row>
                          <v-col class="mt-0 mb-0 pt-0 pb-0">
                            <v-autocomplete
                              v-model="editedItem.access_for"
                              :items="access_modules"
                              item-text="name"
                              item-value="id"
                              label="Access For"
                              :error-messages="accessForErrors"
                              @input="$v.editedItem.access_for.$touch()"
                              @blur="$v.editedItem.access_for.$touch()"
                            >
                            </v-autocomplete>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="mt-0 mb-0 pt-0 pb-0">
                            <v-text-field
                              name="name"
                              v-model="editedItem.name"
                              label="Name"
                              required
                              :error-messages="nameErrors + nameError.name"
                              @input="
                                $v.editedItem.name.$touch() +
                                  (nameError.name = [])
                              "
                              @blur="$v.editedItem.name.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="mt-0 mb-0 pt-0 pb-0">
                            <v-autocomplete
                              v-model="editedItem.max_approval_level"
                              :items="accessLevels"
                              item-text="level"
                              label="Access Level"
                              :readonly="editedIndex > -1 ? true : false"
                            >
                            </v-autocomplete>
                          </v-col>
                        </v-row>
                        <v-row v-if="hasApprovers">
                          <v-col class="mt-0 mb-0 pt-0 pb-0">
                            <fieldset>
                              <legend class="subtitle-1 font-weight-bold mb-2">
                                No. of Approver per Level
                              </legend>
                              <!-- <v-simple-table>
                                <thead
                                  class="
                                    grey
                                    darken-1
                                    white--text
                                    font-weight-bold
                                  "
                                >
                                  <tr>
                                    <td
                                      v-for="(
                                        item, index
                                      ) in editedItem.approver_per_level"
                                    >
                                      Level {{ index + 1 }}
                                    </td>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr>
                                    <td
                                      v-for="(
                                        item, index
                                      ) in editedItem.approver_per_level"
                                    >
                                      <v-text-field-integer
                                        name="access_level"
                                        v-model="item.num_of_approvers"
                                        v-bind:properties="{
                                          placeholder: '0',
                                          maxLength: 6,
                                          outlined: true,
                                          dense: true,
                                          'hide-details': true,
                                        }"
                                        required
                                      >
                                      </v-text-field-integer>
                                    </td>
                                  </tr>
                                </tbody>
                              </v-simple-table> -->
                              <v-row>
                                <template v-for="(item, index) in editedItem.approver_per_level">
                                  <v-col>
                                    <v-text-field-integer
                                        :label="'Level ' + (index + 1).toString()"
                                        name="access_level[]"
                                        v-model="item.num_of_approvers"
                                        v-bind:properties="{
                                          placeholder: '0',
                                          maxLength: 6,
                                          outlined: true,
                                          dense: true,
                                          'hide-details': true,
                                        }"
                                        required
                                      >
                                      </v-text-field-integer>
                                  </v-col>
                                </template>
                              </v-row>
                            </fieldset>
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
            :items="access_charts"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="hasPermission('access-chart-list')"
          >
            <template v-slot:item.access_chart_user_maps.length="{ item }">
              <v-chip
                :class="
                  item.access_chart_user_maps.length ? 'success' : 'error'
                "
              >
                {{ item.access_chart_user_maps.length }}
              </v-chip>
            </template>
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="info"
                @click="viewApprovingOfficer(item)"
                v-if="hasPermission('approving-officer-list')"
              >
                mdi-eye
              </v-icon>
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editAccessChart(item)"
                v-if="hasPermission('access-chart-edit')"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item, 'Access Chart')"
                v-if="hasPermission('access-chart-delete')"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-data-table>
          <v-dialog
            v-model="dialog_approving_officer"
            max-width="1000px"
            scrollable
          >
            <v-card>
              <v-card-title>
                {{ editedItem.name + " - Access Chart" }}
                <v-spacer></v-spacer>
                <v-btn icon @click="close" class="white">
                  <v-icon>mdi-close-circle</v-icon>
                </v-btn>
              </v-card-title>
              <v-divider class="ma-0"></v-divider>
              <v-card-text>
                <v-row>
                  <v-col class="mt-4">
                    <v-card>
                      <v-card-title>
                        Approving Officers
                        <v-btn
                          class="primary ml-4"
                          small
                          @click="newApprovingOfficer()"
                          v-if="hasPermission('approving-officer-list')"
                        >
                          <v-icon> mdi-plus</v-icon> New
                        </v-btn>
                      </v-card-title>
                      <v-divider class="ma-0"></v-divider>
                      <v-card-text>
                        <v-data-table
                          :headers="headers2"
                          :items="approving_officers"
                          :loading="loading"
                          loading-text="Loading... Please wait"
                          hide-default-footer
                        >
                          <template v-slot:item="{ item, index }">
                            <tr>
                              <td>{{ index + 1 }}</td>
                              <template v-if="!rowFieldIsActive(index, item)">
                                <td>{{ item.user ? "Approving Officer " +  " - " + item.user.name : "No User" }}</td>
                                <td>{{ item.access_level }}</td>
                                <td>
                                  <v-icon
                                    small
                                    class="mr-2"
                                    color="green"
                                    @click="editApprover(item)"
                                    v-if="hasPermission('access-chart-edit')"
                                    :disabled="addEditMode === 'Add' ? true : false"
                                  >
                                    mdi-pencil
                                  </v-icon>

                                  <v-icon
                                    small
                                    color="red"
                                    @click="showConfirmAlert(item, 'Approving Officer')"
                                    v-if="hasPermission('access-chart-delete')"
                                    :disabled="['Add', 'Edit'].includes(addEditMode)"
                                  >
                                    mdi-delete
                                  </v-icon>
                                </td>
                              </template>
                              <template v-if="rowFieldIsActive(index, item)">
                                <td>
                                  <v-autocomplete
                                    class="mb-2"
                                    v-model="approvingOfficer.user_id"
                                    :items="filteredUsers"
                                    item-text="name"
                                    item-value="id"
                                    label="User"
                                    :error-messages="approvingOfficerErrors"
                                    @input="$v.approvingOfficer.user_id.$touch()"
                                    @blur="$v.approvingOfficer.user_id.$touch()"
                                    hide-details=""
                                  >
                                  </v-autocomplete>
                                </td>
                                <td>
                                  <v-autocomplete
                                    class="mb-2"
                                    v-model="approvingOfficer.access_level"
                                    :items="approverAccessLevels"
                                    item-text="level"
                                    label="Access Level"
                                    hide-details=""
                                  >
                                  </v-autocomplete>
                                </td>
                                <td>
                                  <v-btn color="primary" x-small :disabled="disabled" @click="saveApprovingOfficer()" icon>
                                    <v-icon color="primary">mdi-content-save</v-icon>
                                  </v-btn>

                                  <v-btn x-small color="#E0E0E0" @click="cancelEvent(item)" icon>
                                    <v-icon color="red">mdi-cancel</v-icon>
                                  </v-btn>
                                </td>
                              </template>
                            </tr>
                          </template>
                        </v-data-table>
                      </v-card-text>
                    </v-card>
                  </v-col>
                </v-row>
              </v-card-text>
              <v-divider class="mb-3 mt-0"></v-divider>
              <v-card-actions class="pa-0">
                <v-spacer></v-spacer>
                <v-btn
                  color="primary"
                  class="mb-3 mr-4"
                  @click="close()"
                >
                  OK
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
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState, mapGetters } from "vuex";

export default {
  mixins: [validationMixin],

  validations: {
    editedItem: {
      access_for: { required },
      name: { required },
    },
    approvingOfficer: {
      user_id: { required },
      access_level: { required },
    },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Access Chart Name", value: "name" },
        { text: "Access For", value: "access_module.name" },
        { text: "Max Approval Level", value: "max_approval_level" },
        { text: "Approving Officers", value: "access_chart_user_maps.length" },
        { text: "Actions", value: "actions", sortable: false, width: "120px" },
      ],
      headers2: [
        { text: "#", value: "row", sortable: false, width: "20px" },
        { text: "Approving Officer", value: "approving_officer" },
        { text: "Approver Level", value: "access_level" },
        { text: "Actions", value: "actions", sortable: false, width: "120px" },
      ],
      disabled: false,
      dialog: false,
      dialog_approving_officer: false,
      access_modules: [],
      access_charts: [],
      approver_access_levels: [],
      access_level: "",
      users: [],
      editedIndex: -1,
      editedIndex2: -1,
      editedItem: {
        access_for: "",
        name: "",
        max_approval_level: "",
        approver_per_level: [],
      },
      defaultItem: {
        access_for: "",
        name: "",
        max_approval_level: "",
        approver_per_level: [],
      },
      approvingOfficer: {
        user_id: "",
        access_level: 1,
      },
      approving_officers: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Access Chart Lists",
          disabled: true,
        },
      ],
      loading: true,
      nameError: {
        name: [],
      },
      addEditMode: "",
      max_access_level: "",
      hasApprovers: false,
      approverPerLevelHasError: false,
    };
  },

  methods: {
    getAccessChart() {
      this.loading = true;
      axios.get("/api/access_chart/index").then(
        (response) => {
          let data = response.data;
          this.access_level = data.access_level.level;
          this.access_charts = data.access_charts;
          this.access_modules = data.access_modules;
          this.users = data.users;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    editAccessChart(item) {
      this.editedIndex = this.access_charts.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteAccessChart(access_chart_id) {
      const data = { access_chart_id: access_chart_id };
      this.loading = true;
      axios.post("/api/access_chart/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "access-chart-delete" });
          }
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    showAlert(msg) {
      this.$swal({
        position: "center",
        icon: "success",
        title: msg,
        showConfirmButton: false,
        timer: 2500,
      });
    },

    showConfirmAlert(item, doctype) {
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

          if (doctype === "Access Chart") {
            const access_chart_id = item.id;

            const index = this.access_charts.indexOf(item);

            //Call delete Access Chart function
            this.deleteAccessChart(access_chart_id);

            //Remove item from array permissions
            this.access_charts.splice(index, 1);
          } else {
            const approver_id = item.id;
            const index = this.approving_officers.indexOf(item);

            //Remove item from array approving_officers
            this.approving_officers.splice(index, 1);

            this.access_charts[this.editedIndex].access_chart_user_maps.splice(
              index,
              1
            );

            this.deleteApprover(approver_id);
          }

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
      this.dialog_approving_officer = false;
      this.approver_access_levels = [];
      this.clear();
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });

      this.approvingOfficer = Object.assign(
        {},
        { user_id: "", access_level: 1 }
      );
      this.approving_officers = [];
      this.editedIndex2 = -1;
      this.addEditMode = "";
    },

    validateApproverPerLevel() {
      this.approverPerLevelHasError = false;
      this.editedItem.approver_per_level.forEach((value) => {
        if (!value.num_of_approvers) {
          this.approverPerLevelHasError = true;
        }
      });
    },

    save() {
      this.$v.editedItem.$touch();
      
      this.validateApproverPerLevel();

      if (!this.$v.editedItem.$error && !this.approverPerLevelHasError) {
        this.disabled = true;

        if (this.editedIndex > -1) {
          const data = this.editedItem;
          const access_chart_id = this.editedItem.id;

          axios.post("/api/access_chart/update/" + access_chart_id, data).then(
            (response) => {
              console.log(response);
              let data = response.data;

              if (data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "access-chart-edit" });

                Object.assign(this.access_charts[this.editedIndex], this.editedItem);
                this.showAlert(data.success);
                this.close();
              } else {
                let errors = data;
                let errorNames = Object.keys(data);
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
          
          axios.post("/api/access_chart/store", data).then(
            (response) => {
              
              let data = response.data;

              if (data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "access-chart-create" });

                this.showAlert(data.success);
                this.close();

                //push recently added data from database
                this.access_charts.push(data.access_chart);
              } else {
                let errors = data;
                let errorNames = Object.keys(data);
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

    viewApprovingOfficer(item) {
      this.editedIndex = this.access_charts.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog_approving_officer = true;

      let approving_officers = item.access_chart_user_maps;

      approving_officers.forEach((value) => {
        this.approving_officers.push(value);
      });

      this.access_level = item.max_approval_level;
    },
    saveApprovingOfficer() {
      this.$v.approvingOfficer.$touch();
      if (!this.$v.approvingOfficer.$error) {
        this.disabled = true;

        let user_maps = this.access_charts[this.editedIndex].access_chart_user_maps;
        let officers = this.approving_officers;

        if (this.editedIndex2 > -1) {
          const data = this.approvingOfficer;
          const approver_id = this.approvingOfficer.id;


          axios
            .post("/api/access_chart_user_map/update/" + approver_id, data)
            .then(
              (response) => {
                console.log(response.data);

                let data = response.data;
                if (data.success) {
                  // send data to Sockot.IO Server
                  // this.$socket.emit("sendData", { action: "approving-officer-edit" })

                  let index = this.editedIndex2;

                  Object.assign(officers[index], data.approver);
                    
                  Object.assign(user_maps, officers);

                  this.clearApprovingOfficer();

                  this.showAlert(data.success);

                } else {
                  let errors = data;
                  let errorNames = Object.keys(data);
                }

                this.disabled = false;
              },
              (error) => {
                this.isUnauthorized(error);
                this.disabled = false;
              }
            );
        } else {
          const data = this.approvingOfficer;

          data["access_chart_id"] = this.editedItem.id;

          axios.post("/api/access_chart_user_map/store", data).then(
            (response) => {
              console.log(response);
              let data = response.data
              if (data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "approving-officer-create" });

                this.showAlert(data.success);

                //push recently added data from database

                let index = officers.length - 1;

                officers.splice(index, 1, data.approver);

                user_maps.push(data.approver);

                this.clearApprovingOfficer();

              } else {
                let errors = response.data;
                let errorNames = Object.keys(response.data);
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

    newApprovingOfficer() {
      this.clearApprovingOfficer();

      this.addEditMode = "Add";

      let hasNew = false;

      this.approving_officers.forEach((value, index) => {
        if (value.status === "New") {
          hasNew = true;
        }
      });

      if (!hasNew) {
        this.approving_officers.push({ status: "New" });
      }
    },

    editApprover(item) {
      this.addEditMode = "Edit";
      this.editedIndex2 = this.approving_officers.indexOf(item);
      this.approvingOfficer = Object.assign(
        {},
        { id: item.id, user_id: item.user.id, access_level: item.access_level }
      );
    },

    cancelEvent(item) {
      this.editedIndex2 = this.approving_officers.indexOf(item);
      if (this.addEditMode === "Edit") {
        this.editedIndex2 = -1;
      } else {
        this.approving_officers.splice(this.editedIndex2, 1);
      }

      this.addEditMode = "";
    },

    clearApprovingOfficer() {
      this.$v.approvingOfficer.$reset();
      this.addEditMode = "";
      this.disabled = false;
      this.editedIndex2 = -1;
      this.approvingOfficer = Object.assign(
        {},
        {
          user_id: "",
          access_level: 1,
        }
      );
    },

    deleteApprover(approver_id) {
      const data = { approver_id: approver_id };
      this.loading = true;
      axios.post("/api/access_chart_user_map/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "approving-officer-delete" });
            Object.assign(
              this.access_charts[this.editedIndex].access_chart_user_maps,
              this.approving_officers
            );
          }
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    clear() {
      this.$v.editedItem.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.editedIndex = -1;
      this.hasApprovers = false;
    },
    rowFieldIsActive(index, item) {
      return  index === this.editedIndex2 || item.status === 'New';
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
          action == "access-chart-create" ||
          action == "access-chart-edit" ||
          action == "access-chart-delete"
        ) {
          this.getAccessChart();
        }
      };
    },
  },
  watch: {
    "editedItem.max_approval_level"() {
      this.hasApprovers = true;
      let approval_level_diff =
        this.editedItem.approver_per_level.length -
        this.editedItem.max_approval_level;
      // add mode
      if (this.editedIndex === -1) {
        this.editedItem.approver_per_level = [];

        for (let i = 1; i <= this.editedItem.max_approval_level; i++) {
          this.editedItem.approver_per_level.push({
            level: i,
            num_of_approvers: "",
          });
        }
      } 
      // else {
      //   // edit mode -- if max_approval_level has changed

      //   // if new max_approval_level is less than the current approver_per_level length then remove/splice the array of approver_per_level
      //   if (
      //     this.editedItem.max_approval_level <
      //     this.editedItem.approver_per_level.length
      //   ) {
      //     for (let i = 0; i < approval_level_diff; i++) {
      //       let index = approval_level_diff;
      //       console.log(this.editedItem.approver_per_level.length);
      //       console.log(this.editedItem.max_approval_level);
      //       this.editedItem.approver_per_level.splice(index, 1);
      //     }
      //   } else if (
      //     this.editedItem.max_approval_level >
      //     this.editedItem.approver_per_level.length
      //   ) {
      //     for (let i = approval_level_diff + 1; i <= this.editedItem.max_approval_level; i++) {
      //       this.editedItem.approver_per_level.push({ level: i, num_of_approvers: "" });
      //     }
      //   }
      // }
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Access Chart" : "Edit Access Chart";
    },
    nameErrors() {
      const errors = [];
      if (!this.$v.editedItem.name.$dirty) return errors;
      !this.$v.editedItem.name.required && errors.push("Name is required.");
      return errors;
    },
    accessForErrors() {
      const errors = [];
      if (!this.$v.editedItem.access_for.$dirty) return errors;
      !this.$v.editedItem.access_for.required &&
        errors.push("Access For is required.");
      return errors;
    },
    approvingOfficerErrors() {
      const errors = [];
      if (!this.$v.approvingOfficer.user_id.$dirty) return errors;
      !this.$v.approvingOfficer.user_id.required &&
        errors.push("User is required.");
      return errors;
    },
    filteredUsers() {
      let users = [];
      let userIdArr = [];

      // push all user id into array variable
      this.approving_officers.forEach((value) => {
        userIdArr.push(value.user_id);
      });

      this.users.forEach((value) => {
        if (!userIdArr.includes(value.id)) {
          users.push(value);
        }

        // if edit mode
        if (
          this.editedIndex2 > -1 &&
          this.approvingOfficer.user_id === value.id
        ) {
          users.push(value);
        }
      });

      return users;
    },
    accessLevels() {
      let access_levels = [];
      for (let ctr = 1; ctr <= this.access_level; ctr++) {
        access_levels.push(ctr);
      }

      return access_levels;
    },
    approverAccessLevels() {
      let accessLevelArr = [];
      let approver_access_levels = [];
      let max_access_level = 1;
      let max_approval_level = this.editedItem.max_approval_level;

      this.approving_officers.forEach((value) => {
        // if has access level
        if (value.access_level) {
          accessLevelArr.push(value.access_level);
        }
      });

      max_access_level = accessLevelArr.length
        ? Math.max(...accessLevelArr)
        : 0;

      // if access_level from DB is less than max_access_level from approving_officers
      if (max_approval_level > max_access_level) {
        max_access_level = max_access_level + 1;
      } else {
        max_access_level = max_approval_level;
      }

      for (let ctr = 1; ctr <= max_access_level; ctr++) {
        approver_access_levels.push(ctr);
      }
      return approver_access_levels;
    },
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getAccessChart();
    // this.websocket();
  },
};
</script>