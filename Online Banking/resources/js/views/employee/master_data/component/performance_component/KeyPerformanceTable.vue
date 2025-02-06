<template>
  <div>
    <v-toolbar flat>
      <v-toolbar-title class="mt-2">Monthly Key Performance</v-toolbar-title>
      <v-divider vertical class="ma-2 ml-4" thickness="20px"></v-divider>
      <v-tooltip top v-if="hasPermission('employee-master-data-key-performance-add')">
        <template v-slot:activator="{ on, attrs }">
          <v-btn 
            small 
            class="mx-2 mt-2" 
            color="primary" 
            rounded 
            fab
            v-bind="attrs" v-on="on"
            @click="openPeriodDialog('Add')"
          >
              <v-icon>mdi-plus</v-icon> 
          </v-btn>
        </template>
        
        <span>Add Period</span>
      </v-tooltip>
      <v-tooltip top v-if=" hasPermission('employee-master-data-key-performance-delete')">
        <template v-slot:activator="{ on, attrs }">
          <v-btn 
            small
            class="mr-2 mt-2" 
            color="error" 
            rounded 
            fab 
            :disabled="!monthly_key_performances.length"
            @click="openPeriodDialog('Delete')"
            v-bind="attrs" 
            v-on="on"
          > 
            <v-icon>mdi-delete</v-icon> 
          </v-btn>
        </template>
        <span>Delete Period</span>
      </v-tooltip> 
    </v-toolbar>
    <v-simple-table fixed-header class="tableFixHead" id="monthly_key_performances">
      <template v-slot:default>
        <thead>
          <tr>
            <th class="pa-4" width="10px">#</th>
            <th class="pa-2">Year</th>
            <th class="pa-2">Month</th>
            <th class="pa-2">Actual Grade(%)</th>
            <th class="pa-2" width="80px"> Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in monthly_key_performances" :class="index === editedPerfomanceIndex ? 'blue lighten-5' : ''">
            <td class="pa-4"> {{ index + 1 }} </td>
            <td class="pa-2"> {{ item.year }} </td>
            <td class="pa-2"> {{ item.month }}</td>

            <!-- START Show Data if row is not for edit (show by default) -->
            <template v-if="index !== editedPerfomanceIndex && item.status !== 'New'">
              <td class="pa-2"> {{ item.grade }} </td>
            </template>
            <!-- END Show Data if row is not for edit (show by default) -->

            <!-- START Show Fields if row is for edit -->
            <template v-if="index === editedPerfomanceIndex || item.status === 'New'">
              <td class="pa-2">
                <v-text-field
                  name="grade"
                  v-model="editedItem.grade"
                  dense
                  hide-details
                  :error-messages="gradeErrors"
                  @input="$v.editedItem.grade.$touch()"
                  @blur="$v.editedItem.grade.$touch()"
                  @keypress="intNumValFilter()"
                ></v-text-field>
              </td>
            </template>
            <!-- END Show Fields if row is for edit -->
            
            <!-- START Show Edit(pencil icon) and Delete (trash icon) button if not Edit Mode (show by default) -->
            <template v-if="index !== editedPerfomanceIndex && item.status !== 'New' ">
              <td class="pa-2">
                <v-icon
                  small
                  class="mr-2"
                  color="green"
                  @click="editItem(item)"
                  :disabled="table_action_mode === 'Add' ? true : false"
                >
                  mdi-pencil
                </v-icon>

                <!-- <v-icon
                  small
                  color="red"
                  @click="showConfirmAlert(item)"
                  :disabled="['Add', 'Edit'].includes(table_action_mode)"
                >
                  mdi-delete
                </v-icon> -->
              </td>
            </template>
            <!-- END  Show Edit(pencil icon) and Delete (trash icon) button if not Edit Mode (show by default) -->

            <!-- START  Show Save and Cancel button if Edit Mode -->
            <template v-if="index === editedPerfomanceIndex ? true : false || item.status === 'New' ">
              <td class="pa-2">
                <v-btn
                  x-small
                  :disabled="disabled"
                  @click="saveItem()"
                  icon
                >
                  <v-icon color="primary">mdi-content-save</v-icon>
                </v-btn>
                <v-btn
                  x-small
                  color="#E0E0E0"
                  @click="cancelEvent(item)"
                  icon
                >
                  <v-icon color="red">mdi-cancel</v-icon>
                </v-btn>
              </td>
            </template>
            <!-- END  Show Save and Cancel button if Edit Mode -->
          </tr>
        </tbody>
      </template>
    </v-simple-table>

    <v-dialog v-model="dialog_period" max-width="500px" persistent>
      <v-card>
        <v-card-title class="pa-4">
          <span class="headline">{{ action_mode }} Period</span>
        </v-card-title>
        <v-divider class="mt-0"></v-divider>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col class="my-0 py-0">
                <v-autocomplete
                  v-model="period"
                  :items="periods"
                  label="Period"
                  :error-messages="periodErrors"
                  @input="$v.period.$touch()"
                  @blur="$v.period.$touch()"
                >
                </v-autocomplete>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
        <v-divider class="mb-3 mt-0"></v-divider>
        <v-card-actions class="pa-0">
          <v-spacer></v-spacer>
          <v-btn
            color="#E0E0E0"
            @click="dialog_period = false"
            class="mb-4"
          >
            Close
          </v-btn>
          <v-btn
            color="primary"
            class="mb-3 mr-4"
            @click="action_mode == 'Add' ? savePeriod() : showConfirmAlert()"
          >
            {{ action_mode }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<style scoped>
  .full-height {
    height: calc(85vh - 130px); /* Adjust 270px to suits your needs */
    overflow-y: auto;
    overflow-x: hidden;
  }

  table {
    width: 100%;
  }

  thead, tbody, tr, td, th { display: block; }

  tr:after {
      content: ' ';
      display: block;
      visibility: hidden;
      clear: both;
  }

  tbody {
      height: calc(65vh - 135px);
      overflow-y: auto;
  }

  tbody td, thead th {
      width: 19.2%;
      float: left;
  }

</style>
<script>

import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, requiredIf, email } from "vuelidate/lib/validators";
import { mapGetters } from "vuex";

export default {

  props: ['editedIndex', 'key_performances', 'employee_id'],

  mixins: [validationMixin],

  validations: {
    editedItem: { 
      grade: { required: requiredIf(function () {
            return this.table_action_mode;
          }),  
      },
    },
    period: { required },
  },
  data() {
    return {
      editedPerfomanceIndex: -1,
      editedItem: {
        year: "",
        month: "",
        grade: "",
      },
      defaultField: {
        year: "",
        month: "",
        grade: "",
      },
      monthly_key_performances: [],
      added_monthly_key_performances: [],
      deleted_monthly_key_performances: [],
      action_mode: "",
      table_action_mode: "",
      disabled: false,
      months: [
        'January', 
        'February', 
        'March', 
        'April', 
        'May', 
        'June', 
        'July', 
        'August', 
        'September', 
        'October', 
        'November', 
        'December'
      ],
      dialog_period: false,
      period: "",
      addedItems: [],
      deletedItems: [],
    };
  },

  methods: {
    openPeriodDialog(action) {
      this.action_mode = action;
      this.dialog_period = true;
    },

    storePeriod() {
      let data = { employee_id: this.employee_id, monthly_key_performances: this.added_monthly_key_performances };
      
      axios.post("/api/employee_master_data/key_performance/store", data).then(
        (response) => {
          this.dialog_period = false;
          this.loading = false;
          let data = response.data;
          
          if(data.success)
          {
            let performances = data.performances;

            this.monthly_key_performances = performances;

            this.showAlert(response.data.success);
          }
          
          // reset array
          this.added_monthly_key_performances = [];
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    updatePeriod() {

      let data = { grade: this.editedItem.grade };
      axios.post("/api/employee_master_data/key_performance/update/"+this.editedItem.id, data).then(
        (response) => {
          this.loading = false;
          this.showAlert(response.data.success);
          
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    deletePeriod() {

      let data = { employee_id: this.employee_id, period: this.period };
      axios.post("/api/employee_master_data/key_performance/delete", data).then(
        (response) => {
          this.loading = false;
          
          this.showAlert(response.data.success);
          this.deleted_monthly_key_performance = [];
        },
        (error) => {
          this.showErrorAlert(error);
          this.isUnauthorized(error);
        }
      );
    },

    savePeriod() {
      this.$v.$touch()

      if(!this.$v.period.$error)
      {
       
        // if edit mode then store to database
        if(this.editedIndex > -1)
        {

          this.months.forEach(month => {
            this.added_monthly_key_performances.push({ year: this.period, month: month, grade: "" });  
          });

          this.storePeriod();
        }
        else
        {
          this.months.forEach(month => {
            this.monthly_key_performances.push({ year: this.period, month: month, grade: "" });  

            // newly added monthly_key_performances if editedIndex > -1 (edit mode)
            if(this.editedIndex > -1)
            {
              this.added_monthly_key_performances.push({ year: this.period, month: month, grade: "" });  
            }

          });
        }

        this.dialog_period = false;
        this.reserPeriod();
      }
      
    },

    removePeriod() {
      let items = this.monthly_key_performances.filter(value => value.year == this.period);
      this.deletedItems = items;
      items.forEach( value => {
        let index = this.monthly_key_performances.indexOf(value);
        this.monthly_key_performances.splice(index, 1);
      
        // deleted period from monthly_key_performances if editedIndex > -1 (edit mode)
        if(this.editedIndex > -1)
        {
          this.deleted_monthly_key_performances.push(value)
        }
      });

      this.dialog_period = false;
      this.reserPeriod();

    },

    saveItem(){
 
      this.$v.editedItem.$touch();

      if(!this.$v.editedItem.$error)
      {
        if(this.table_action_mode === 'Add')
        {
          let index = this.monthly_key_performances.indexOf({ status: 'New' }); 
          this.monthly_key_performances.splice(index, 1);
          this.monthly_key_performances.push(this.editedItem);
        }
        else
        {
          this.monthly_key_performances[this.editedPerfomanceIndex] = this.editedItem;
          if(this.editedIndex > -1)
          {
            this.updatePeriod();
          }
        }

        this.resetData();
      }
      
    },

    cancelEvent(item) {
      this.editedPerfomanceIndex = this.monthly_key_performances.indexOf(item);
      if (this.table_action_mode === "Add") {
        this.monthly_key_performances.splice(this.editedPerfomanceIndex, 1);
      } 

      this.resetData();
    },

    editItem(item) {
      this.table_action_mode = "Edit";
      this.editedItem = Object.assign({}, item);
      this.editedPerfomanceIndex = this.monthly_key_performances.indexOf(item);
    },

    deleteItem() {
      
      this.loading = true;
      axios.post("/api/employee_master_data/key_performance/delete", data).then(
        (response) => {
          this.loading = false;
          this.showAlert(response.data.success);
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    resetData(){
      this.$v.editedItem.$reset();
      this.editedItem = Object.assign({}, this.defaultField);
      this.editedPerfomanceIndex = -1;
      this.table_action_mode = "";
    },

    reserPeriod() {
      this.period = "";
      this.$v.period.$reset();
    },
    
    clear() {
      this.resetData();
      this.monthly_key_performances = [];
      this.added_monthly_key_performances = [];
      this.deleted_monthly_key_performances = [];
      this.addedItems = [];
      this.deletedItems = [];

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

    showErrorAlert(msg) {     
      this.$swal({
        position: "center",
        icon: "error",
        title: msg,
        showConfirmButton: false,
        timer: 2500,
      });
    },

    showConfirmAlert() {
   
      if(this.editedIndex > -1)
      {
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
            let data = { employee_id: this.employee_id, period: this.period };
            this.loading = true;
            axios.post("/api/employee_master_data/key_performance/delete", data).then(
              (response) => {
                this.loading = false;
                let data = response.data;
                if(data.success)
                {
                  this.showAlert(data.success);
                  this.removePeriod();
                  this.dialog_period = false;
                }
                else
                {
                  this.showErrorAlert(data.error);
                }
              },
              (error) => {
                this.showErrorAlert(error.response.statusText)
                this.isUnauthorized(error);
              }
            );
            
          }
        });
      }
      else
      {
        this.removePeriod();
      }

      
    },

    intNumValFilter(evt) {
      evt = (evt) ? evt : window.event;
      let value = evt.target.value.toString() + evt.key.toString();

      if (!/^[-+]?[0-9]*\.?[0-9]*$/.test(value)) {
        evt.preventDefault();
      }
      else if(value.indexOf(".") > -1)
      {
        let split_val = value.split('.');
        let whole_num = split_val[0];
        let decimal_places = split_val[1];
        let whole_num_length = whole_num.length  
        let decimal_length = decimal_places.length;
  
        if(decimal_length > 2) //decimal places limit 2
        {
          evt.preventDefault();
        }

      } else {

        return true;
      }
    },

  },
  computed: {
    
    gradeErrors(){
      const errors = [];
      if (!this.$v.editedItem.grade.$dirty) return errors;
      !this.$v.editedItem.grade.required && errors.push("Grade is required.");
      return errors;
    },

    periodErrors(){
      const errors = [];
      if (!this.$v.period.$dirty) return errors;
      !this.$v.period.required && errors.push("Period is required.");
      return errors;
    },

    periods() {

      // Return today's date and time
      var currentTime = new Date()

      // returns the year (four digits)
      var year = currentTime.getFullYear();

      let yearArr = [];

      for (let i = 2020; i <= year; i++) {
        let index = this.monthly_key_performances.findIndex((value) => value.year == i);
        
        if(this.action_mode == 'Add')
        {
          // push years that are not on the list
          if(index < 0)
          {
            yearArr.push(i);  
          }
        }
        else
        {
          // push years that are not on the list
          if(index > -1)
          {
            yearArr.push(i);  
          }
        }
        
        
      }

      return yearArr;
    },

    ...mapGetters("auth", ["isUnauthorized"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission", "hasAnyPermission"]),

  },

  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    
    if(this.editedIndex > -1)
    {
      this.monthly_key_performances = this.key_performances;
    }
      // this.months.forEach(month => {
      //   this.monthly_key_performances.push({ year: 2000, month: month, grade: "" });
      // });
  },
};
</script>
