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
            Position Lists
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
              v-if="hasPermission('position-list')"
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
                  v-if="hasPermission('position-create')"
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
                              name="position"
                              v-model="editedItem.name"
                              label="Position"
                              required
                              :error-messages="positionErrors + positionError.name"
                              @input="$v.editedItem.name.$touch() + (positionError.name = [])"
                              @blur="$v.editedItem.name.$touch()"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="my-0 py-0">
                            <v-autocomplete
                              name="rank"
                              :items="ranks"
                              item-text="name"
                              item-value="id"
                              v-model="editedItem.rank_id"
                              label="Rank"
                              required
                              :error-messages="positionErrors + positionError.rank_id"
                              @input="$v.editedItem.rank_id.$touch() + (positionError.rank_id = [])"
                              @blur="$v.editedItem.rank_id.$touch()"
                            ></v-autocomplete>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="my-0 pt-0">
                            <v-autocomplete
                              name="cost_center"
                              :items="cost_centers"
                              v-model="editedItem.cost_center"
                              label="Cost Center"
                              required
                              :error-messages="costCenterErrors + positionError.cost_center"
                              @input="$v.editedItem.cost_center.$touch() + (positionError.cost_center = [])"
                              @blur="$v.editedItem.cost_center.$touch()"
                            ></v-autocomplete>
                          </v-col>
                        </v-row>
                        <v-row>
                          <v-col class="my-0 py-0">
                            <v-toolbar color="grey darken-1 white--text font-weight-bold" height="40px">
                              <span> Required Employee Per Branch</span>
                              <span class=" ml-2 font-italic font-weight-medium red--text" v-if="fieldsHasError">
                                Fill out all required fields
                              </span>
                            </v-toolbar>
                            <v-responsive class="overflow-y-auto" max-height="300px" id="serial-table">
                              <v-simple-table dense class="mt-2">
                                <!-- <thead class="grey darken-1 white--text font-weight-bold">
                                  <tr>
                                    <td>Branch</td>
                                    <td width="50px"></td>
                                  </tr>
                                </thead> -->
                                <tbody>
                                  <tr v-for="(branch, index) in branchRequirement" :key="index">
                                    <td> <span>{{ branch.branch }}</span></td>
                                    <td width="150px">
                                      <v-text-field
                                        dense
                                        height="20px"
                                        v-model="branch.quantity"
                                        hide-details=""
                                        outlined
                                        :error-messages="branch.error"
                                        @keypress="intNumValFilter() + (branch.error = null)"
                                        @blur="validateField(index)"
                                      ></v-text-field>
                                    </td>
                                  </tr>
                                </tbody>
                              </v-simple-table>
                            </v-responsive>
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
            :items="positions"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-if="hasPermission('position-list')"
          >
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editPosition(item)"
                v-if="hasPermission('position-edit')"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="hasPermission('position-delete')"
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
      name: { required },
      rank_id: { required },
      cost_center: { required },
    },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Position", value: "name" },
        { text: "Rank", value: "rank.name" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      switch1: true,
      disabled: false,
      dialog: false,
      positions: [],
      ranks: [],
      cost_centers: ['HQ-Management', 'BR-Officer', 'BR-Rank & File'],
      branches: [],
      editedIndex: -1,
      editedItem: {
        name: "",
        rank_id: "",
        cost_center: "",
      },
      defaultItem: {
        name: "",
        rank_id: "",
        cost_center: "",
      },
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Position Lists",
          disabled: true,
        },
      ],
      loading: true,
      positionError: {
        name: [],
        rank_id: [],
        cost_center: []
      },
      branchRequirement: [],
    };
  },

  methods: {
    getPosition() {
      this.loading = true;
      axios.get("/api/position/index").then(
        (response) => {
          this.loading = false;
          let data = response.data;

          this.positions = data.positions;
          this.ranks = data.ranks;
          this.branches = data.branches;

          this.branches.forEach(value => {

            this.branchRequirement.push({ 
              branch_id: value.id, 
              branch: value.name, 
              quantity: 15, 
              error: null 
            });
       
          });
            
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    editPosition(item) {
      
      this.editedIndex = this.positions.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
      this.switch1 = true;
      if(this.editedItem.active === 'N')
      {
        this.switch1 = false;
      }

      this.branchRequirement = [];

      let required_employees = item.required_employees;

      this.branches.forEach(branch => {

        let rowData = required_employees.find(v => v.branch_id == branch.id);
        let data = null;
        
        if(rowData)
        {
          data = {
            branch_id: rowData.branch_id,
            branch: rowData.branch.name,
            quantity: rowData.quantity,
            error: null,
          };
        }
        else
        {
          data = { branch_id: branch.id, branch: branch.name, quantity: null, error: null };
        }
        
        this.branchRequirement.push(data);
    
      });

    },

    deletePosition(position_id) {
      const data = { position_id: position_id };
      this.loading = true;
      axios.post("/api/position/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "position-delete" });
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

          const position_id = item.id;
          const index = this.positions.indexOf(item);

          //Call delete Position function
          this.deletePosition(position_id);

          //Remove item from array positions
          this.positions.splice(index, 1);

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
      this.branchRequirement = [];
      this.branches.forEach(value => {
        this.branchRequirement.push({ 
          branch_id: value.id, 
          branch: value.name, 
          quantity: null, 
          error: null 
        });
      });
    },

    save() {
      this.$v.$touch();
      this.positionError = {
        name: [],
        rank_id: [],
        cost_center: [],
      };

      let api = "";
      Object.assign(this.editedItem, { branchRequirement: this.branchRequirement });
      const data = this.editedItem;

      this.branchRequirement.forEach((value, index) => {
        this.validateField(index);
      });
      
      if (!this.$v.$error && !this.fieldsHasError) {
        this.disabled = true;

        if (this.editedIndex > -1) {
          const position_id = this.editedItem.id;
          api = "/api/position/update/" + position_id;
        } else {
          api = "/api/position/store";
        }

        axios.post(api, data).then(
          (response) => {
            this.disabled = false;
            let data = response.data;
            console.log(data);
            
            if (data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "position-create" });

              this.showAlert();
              this.close();

              if (this.editedIndex > -1) 
              {
                Object.assign(
                  this.positions[this.editedIndex],
                  data.position
                );
              }
              else
              {
                //push recently added data from database
                this.positions.push(data.position);
              }
              
            }
            else
            { 
              let errors = data;
              let errorNames = Object.keys(data);

              errorNames.forEach(value => {
                
                let splitted_key = value.split('.');
                if(splitted_key.length)
                {
                  let [key, index, field] = splitted_key;

                  this.branchRequirement[index].error = errors[value];

                }
                else
                {
                  this.positionError[value].push(errors[value]);
                }
                
              });
              
            }
            
          },
          (error) => {
            this.disabled = false;
            this.isUnauthorized(error);
          }
        );
      }
    },

    clear() {
      this.$v.$reset();
      this.editedItem.name = "";
      this.editedItem.rank_id = "";
      this.positionError = {
        name: [],
        rank_id: [],
        cost_center: [],
      }
    },

    intNumValFilter(evt) {
      evt = (evt) ? evt : window.event;
      let value = evt.target.value.toString() + evt.key.toString();

      if (!/^[-+]?[0-9]*?[0-9]*$/.test(value)) {
        evt.preventDefault();
      }
      else {

        return true;
      }
    },

    validateField(index) {
      if(!this.branchRequirement[index].quantity && this.branchRequirement[index].quantity != 0)
      {
        this.branchRequirement[index].error = 'Branch is required';
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
          action == "position-create" ||
          action == "position-edit" ||
          action == "position-delete"
        ) {
          this.getPosition();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Position" : "Edit Position";
    },
    positionErrors() {
      const errors = [];
      if (!this.$v.editedItem.name.$dirty) return errors;
      !this.$v.editedItem.name.required &&
        errors.push("Position is required.");
      return errors;
    },
    rankErrors() {
      const errors = [];
      if (!this.$v.editedItem.rank_id.$dirty) return errors;
      !this.$v.editedItem.rank_id.required &&
        errors.push("Rank is required.");
      return errors;
    },
    costCenterErrors() {
      const errors = [];
      if (!this.$v.editedItem.cost_center.$dirty) return errors;
      !this.$v.editedItem.cost_center.required &&
        errors.push("Cost Center is required.");
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
    fieldsHasError() {
      return this.branchRequirement.find(value => value.error) ? true : false;
    },
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getPosition();
    // this.websocket();
  },
};
</script>