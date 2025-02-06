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
        <v-skeleton-loader v-if="skeleton_loading"
          type="table-heading, table-thead, table-tbody, table-row, table-tfoot"
          :types="{ 'table-thead': 'heading@8', 'table-tbody': 'table-row-divider@5', 'table-row': 'table-cell@8' }"></v-skeleton-loader>
        <v-card v-if="!skeleton_loading">
          <v-card-title>
            Tactical Requisition Lists
            <v-spacer></v-spacer>
            <v-text-field v-model="search" append-icon="mdi-magnify" label="Search" single-line
              v-if="hasPermission('tactical-requisition-list')"></v-text-field>
            <template>
              <v-toolbar flat>
                <v-spacer></v-spacer>
                <v-btn color="primary" fab dark class="mb-2" @click="createTacticalRequisition()"
                  v-if="hasPermission('tactical-requisition-create')">
                  <v-icon>mdi-plus</v-icon>
                </v-btn>
              </v-toolbar>
            </template>
          </v-card-title>
          <v-data-table :headers="headers" :items="tacticalRequisitions" :search="search" :loading="loading"
            loading-text="Loading... Please wait" v-if="hasPermission('tactical-requisition-list')">
           
            <template v-slot:item.progress="{ item }">
              <template v-for="(item, index) in item.approval_progress">
                <v-tooltip top :color="item.status === 'Approved' ? 'success' : item.status === 'Disapproved' ? 'error' : ''" v-if="item.approver.length">
                  <template v-slot:activator="{ on, attrs }">
                    <v-icon
                      :color="item.status === 'Approved' ? 'success' : item.status === 'Disapproved' ? 'error' : ''"
                      v-bind="attrs" v-on="on">
                      {{ item.status === 'Disapproved' ? 'mdi-close-circle' : 'mdi-checkbox-marked-circle' }}
                    </v-icon>
                  </template>
                  <span>{{ item.approver.join(', ') }}</span>
                </v-tooltip>
                <!-- show check icon without tooltip -->
                <v-icon v-if="!item.approver.length">mdi-checkbox-marked-circle</v-icon>
              </template>
            </template>

            <template v-slot:item.status="{ item }">
              <v-chip :color="item.status === 'Pending'
                  ? 'warning'
                  : item.status === 'On Process'
                    ? '#AB47BC'
                    : item.status === 'Approved'
                      ? 'success'
                      : 'error'
                " dark>
                {{ item.status }}
              </v-chip>
            </template>

            <template v-slot:item.actions="{ item }">

              <v-icon small class="mr-2" color="info" @click="viewTacticalRequisition(item)"
                v-if="hasAnyPermission('tactical-requisition-edit', 'tactical-requisition-approve')">
                mdi-eye
              </v-icon>

              <v-icon small color="red" @click="showConfirmAlert(item)"
                v-if="hasPermission('tactical-requisition-delete')">
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
      description: { required },
    },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Doc Number", value: "id" },
        { text: "Event Title", value: "marketing_event.event_name" },
        { text: "Branch", value: "branch.name" },
        { text: "Created By", value: "user.name" },
        { text: "Progress", value: "progress" },
        { text: "Status", value: "status" },
        { text: "Date Created", value: "date_created" },
        { text: "Date Approved", value: "date_approved" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],

      disabled: false,
      dialog: false,
      tactical_requisitions: [],
      editedIndex: -1,
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Tactical Requisition Lists",
          disabled: true,
        },
      ],
      loading: true,
      expenseError: {
        description: [],
      },
      approval_progress: [],
      skeleton_loading: true,
    };
  },

  methods: {


    getTacticalRequisition() {
      this.loading = true;
      axios.get("/api/tactical_requisition/index").then(
        (response) => {

          console.log(response.data);

          let data = response.data

          this.tactical_requisitions = data.tactical_requisitions;
          this.approval_progress = data.approval_progress

          this.loading = false;
          this.skeleton_loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },


    viewTacticalRequisition(item) {
      this.$router.push({
        name: "tactical.view",
        params: { tactical_requisition_id: item.id },
      });
    },


    deleteTacticalRequisition(tactical_requisition_id) {
      const data = { tactical_requisition_id: tactical_requisition_id };
      this.loading = true;
      axios.post("/api/tactical_requisition/delete", data).then(
        (response) => {

          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "tactical-requisition-delete" });
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

          const tactical_requisition_id = item.id;
          const index = this.tactical_requisitions.indexOf(item);

          //Call delete Brand function
          this.deleteTacticalRequisition(tactical_requisition_id);

          //Remove item from array tactical_requisitions
          this.tactical_requisitions.splice(index, 1);

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


    createTacticalRequisition() {
      this.$router.push({ name: "tactical.create" });
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
          action == "tactical-requisition-create" ||
          action == "tactical-requisition-edit" ||
          action == "tactical-requisition-delete"
        ) {
          this.getTacticalRequisition();
        }
      };
    },

    
  },


  computed: {

    tacticalRequisitions() {
      let tactical_requisitions = [];
      this.tactical_requisitions.forEach((value, i) => {
        tactical_requisitions.push(value);
        this.approval_progress.forEach((val) => {
          if (value.id === val.tactical_requisition_id) {
            tactical_requisitions[i]['approval_progress'] = val.progress;
          }
        });
      });

      return tactical_requisitions;
    },

    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission", "hasAnyPermission"]),

  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getTacticalRequisition();
    // this.websocket();
  },
};
</script>