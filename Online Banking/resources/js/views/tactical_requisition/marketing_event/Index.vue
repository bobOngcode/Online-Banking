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
            Marketing Event Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              v-if="hasPermission('marketing-event-list')"
            ></v-text-field>
            <template>
              <v-toolbar flat>
                <v-spacer></v-spacer>
                <v-btn
                  color="primary"
                  fab
                  dark
                  class="mb-2"
                  @click="createMarketingEvent()"
                  v-if="hasPermission('marketing-event-create')"
                >
                  <v-icon>mdi-plus</v-icon>
                </v-btn>
              </v-toolbar>
            </template>
          </v-card-title>

          <v-data-table
            :headers="headers"
            :items="marketing_events"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="hasPermission('marketing-event-list')"
          >
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
                @click="editMarketingEvent(item)"
                v-if="hasPermission('marketing-event-edit')"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item, 'Marketing Event')"
                v-if="hasPermission('marketing-event-delete')"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-data-table>
          
          <!-- Approving Officers per Marketing Event -->
          <v-dialog
            v-model="dialog_approving_officer"
            max-width="1000px"
            scrollable
            persistent
          >
            <v-card>
              <v-card-title>
                {{ editedItem.event_name + " - Marketing Event" }}
                <v-spacer></v-spacer>
                <v-btn icon @click="close" class="white">
                  <v-icon>mdi-close-circle</v-icon>
                </v-btn>
              </v-card-title>
              <v-divider class="ma-0"></v-divider>
              <v-card-text>
                <v-row class="mb-4">
                  <v-col>
                    <v-row>
                      <v-col class="mt-4 mb-0 pt-0 pb-0">
                        <v-autocomplete
                          v-model="editedItem.max_approval_level"
                          :items="accessLevels"
                          item-text="level"
                          label="Access Level"
                        >
                        </v-autocomplete>
                      </v-col>
                    </v-row>
                    <v-row v-if="editedItem.max_approval_level">
                      <v-col class="mt-0 mb-0 pt-0 pb-0">
                        <fieldset>
                          <legend class="subtitle-1 font-weight-bold mb-2">
                            No. of Approver per Level
                          </legend>
                          <v-row>
                            <template
                              v-for="(
                                item, index
                              ) in editedItem.approver_per_level"
                            >
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
                                    error: errorFields[index]
                                    ? errorFields[index]
                                    : null,
                                    messages: '',
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
                  </v-col>
                </v-row>
                <v-divider v-if="editedItem.max_approval_level"></v-divider>
                <v-row v-if="editedItem.max_approval_level">
                  <v-col>
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
                                    v-if="hasPermission('marketing-event-edit')"
                                    :disabled="addEditMode === 'Add' ? true : false"
                                  >
                                    mdi-pencil
                                  </v-icon>

                                  <v-icon
                                    small
                                    color="red"
                                    @click="showConfirmAlert(item, 'Approving Officer')"
                                    v-if="hasPermission('marketing-event-delete')"
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
                                  <v-btn x-small :disabled="disabled" @click="saveApprovingOfficer()" icon>
                                    <v-icon color="primary">mdi-content-save</v-icon>
                                  </v-btn>

                                  <v-btn x-small @click="cancelEvent(item)" icon>
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
                <v-btn color="#E0E0E0" @click="close" class="mb-3">
                  Cancel
                </v-btn>
                <v-btn color="primary" class="mb-3 mr-4" :disabled="disabled" @click="updateApproverPerLevel()">
                  <!-- {{ btnText }} -->
                  Update
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
      description: { required },
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
        // { text: "Event Title", value: "event_name" },
        { text: "Event Title", value: "event_name" },
        { text: "Active", value: "active" },
        { text: "Attachment Required", value: "attachment_required" },
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
      marketing_events: [],
      editedIndex: -1,
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Marketing Event Lists",
          disabled: true,
        },
      ],
      loading: true,
      expenseError: {
        description: [],
      },
      approving_officers: [],
      addEditMode: "",
      max_access_level: "",
      hasApprovers: false,
      approverPerLevelHasError: false,
      dialog_approving_officer: false,
      approver_access_levels: [],
      access_level: "",
      users: [],
      editedItem: {
        event_name: "",
        max_approval_level: "",
        approver_per_level: [],
      },
      defaultItem: {
        event_name: "",
        max_approval_level: "",
        approver_per_level: [],
      },
      approver_per_level: [],
      errorFields: [],
      approvingOfficer: {
        marketing_event_id: "",
        user_id: "",
        access_level: 1,
      },
    };
  },

  methods: {
    getMarketingEvent() {
      this.loading = true;
      axios.get("/api/marketing_event/index").then(
        (response) => {
          let data = response.data;
          this.marketing_events = data.marketing_events;
          this.access_level = data.access_level.level;
          this.users = data.users
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    editMarketingEvent(item) {
      this.$router.push({
        name: "marketing.event.edit",
        params: { marketing_event_id: item.id },
      });
    },

    deleteMarketingEvent(marketing_event_id) {
      const data = { marketing_event_id: marketing_event_id };
      this.loading = true;
      axios.post("/api/marketing_event/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "marketing-event-delete" });
          }
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    viewApprovingOfficer(item) {

      this.editedIndex = this.marketing_events.indexOf(item);

      this.editedItem = {
        id: item.id,
        event_name: item.event_name,
        max_approval_level: item.max_approval_level,
        approver_per_level: [],
      };

      this.approvingOfficer.marketing_event_id = item.id;
      
      let approver_per_level = item.approver_per_level
      approver_per_level.forEach(value => {
        
        this.editedItem.approver_per_level.push({
          level: value.level, 
          marketing_event_id: value.marketing_event_id, 
          num_of_approvers: value.num_of_approvers
        });
 
      });

      this.dialog_approving_officer = true;

      let approving_officers = item.marketing_event_user_maps;

      approving_officers.forEach((value) => {
        this.approving_officers.push(value);
      });

    },

    updateApproverPerLevel() {
   
      this.loading = true;
      this.disabled = true;
      axios.post("/api/marketing_event_user_map/update_approver_per_level", this.editedItem).then(
        (response) => {
         console.log(response.data);
          let data = response.data;

          this.loading = false;
          this.disabled = false;

          if(data.success)
          {  
            let value  = {
              max_approval_level:  this.editedItem.max_approval_level,
              approver_per_level: data.approver_per_level,
            };
            let marketing_event = this.marketing_events[this.editedIndex];
            marketing_event = Object.assign(marketing_event, value)
            
            this.showAlert(data.success)
          }
        },
        (error) => {
          console.log(error);
          this.loading = false;
          this.disabled = false;
        }
      );

    },

    saveApprovingOfficer() {
      this.$v.approvingOfficer.$touch();

      if (!this.$v.approvingOfficer.$error) {
        this.disabled = true;

        let user_maps = this.marketing_events[this.editedIndex].marketing_event_user_maps;
        let officers = this.approving_officers;

        let url = "";
        let mode = "";

        if (this.editedIndex2 > -1)
        { 
          mode = "Edit";
          url = "/api/marketing_event_user_map/update/" + this.approvingOfficer.id;
        }
        else
        {
          mode = "Add";
          url = "/api/marketing_event_user_map/store";
        }
        
        this.approvingOfficer.marketing_event_id = this.editedItem.id;
        axios.post(url, this.approvingOfficer).then(
          (response) => {
            console.log(response.data);
  
            let data = response.data;

            if (data.success) {
              
              if(mode === 'Edit')
              {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "approving-officer-edit" });
                let index = this.editedIndex2;

                Object.assign(officers[index], data.approver);
                Object.assign(user_maps, officers);
              }
              else
              {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "approving-officer-create" });

                //push recently added data from database
                let index = officers.length - 1;
                officers.splice(index, 1, data.approver);
                user_maps.push(data.approver);
              }

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
      this.approvingOfficer = Object.assign({}, { 
        id: item.id, 
        user_id: item.user.id, 
        access_level: item.access_level 
      });
    },

    cancelEvent(item) {
      let officers = this.approving_officers;
      this.editedIndex2 = officers.indexOf(item);

      if (this.addEditMode === "Edit") {
        this.editedIndex2 = -1;
      } else {
        officers.splice(this.editedIndex2, 1);
      }

      this.addEditMode = "";
    },

    clearApprovingOfficer() {
      this.$v.approvingOfficer.$reset();
      this.addEditMode = "";
      this.disabled = false;
      this.editedIndex2 = -1;
      this.approvingOfficer = Object.assign({}, {
          user_id: "",
          access_level: 1,
          marketing_event_id: "",
      });
    },

    deleteApprover(approver_id) {
      const data = { approver_id: approver_id };
      this.loading = true;

      axios.post("/api/marketing_event_user_map/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "approving-officer-delete" });

            let user_maps = this.marketing_events[this.editedIndex].marketing_event_user_maps;
            let officers = this.approving_officers;

            Object.assign(user_maps, officers)

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

          const marketing_event_id = item.id;
          const index = this.marketing_events.indexOf(item);

          //Call delete Brand function
          this.deleteMarketingEvent(marketing_event_id);

          //Remove item from array marketing_events
          this.marketing_events.splice(index, 1);

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

          if (doctype === "Marketing Event") {
            const marketing_event_id = item.id;
            const index = this.marketing_events.indexOf(item);

            //Call delete Brand function
            this.deleteMarketingEvent(marketing_event_id);

            //Remove item from array marketing_events
            this.marketing_events.splice(index, 1);
          } else {
            let officers = this.approving_officers
            const approver_id = item.id;
            const index = officers.indexOf(item);

            //Remove item from array approving_officers
            officers.splice(index, 1);

            this.marketing_events[this.editedIndex].marketing_event_user_maps.splice(index, 1);

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

    createMarketingEvent() {
      this.$router.push({ name: "marketing.event.create" });
    },

    clear() {
      this.$v.editedItem.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.approver_per_level = [];
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
          action == "marketing-event-create" ||
          action == "marketing-event-edit" ||
          action == "marketing-event-delete"
        ) {
          this.getMarketingEvent();
        }
      };
    },

  },
  computed: {
    
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
    approvingOfficerErrors() {
      const errors = [];
      if (!this.$v.approvingOfficer.user_id.$dirty) return errors;
      !this.$v.approvingOfficer.user_id.required &&
        errors.push("User is required.");
      return errors;
    },

    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },
  watch: {
    "editedItem.max_approval_level"() {
      let approver_per_level_length = this.editedItem.approver_per_level.length;
      let max_approval_level = this.editedItem.max_approval_level;
      let approver_per_level = this.editedItem.approver_per_level;

      if (max_approval_level < approver_per_level_length) {
        for (let i = approver_per_level_length; i > max_approval_level; i--) {
          approver_per_level.splice(i - 1, 1);
        }
      } 
      else 
      {
        for (let i = approver_per_level_length; i < max_approval_level; i++) {
          approver_per_level.push({ 
            level: i + 1,
            marketing_event_id: this.editedItem.id, 
            num_of_approvers: "",
          });
        }

      }
   
      this.editedItem.approver_per_level = approver_per_level;
    },

  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getMarketingEvent();
    // this.websocket();
  },
};
</script>