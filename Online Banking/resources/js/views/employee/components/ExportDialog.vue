<template>
  <div>
    <v-dialog
      transition="dialog-top-transition"
      max-width="500"
      v-model="dialog"
      persistent
    >
      <template v-slot:default="dialog">
        <v-card>
        <v-card-title class="pa-4">
          <span class="headline">Export Data</span>
        </v-card-title>
        <v-divider class="mt-0"></v-divider>
          <v-card-text>
            <v-row>
              <v-col class="my-0 py-0">
                <v-autocomplete
                  v-model="report_type"
                  :items="report_types"
                  label="Report Type"
                  return-object
                  :error-messages="reportTypeErrors"
                  @input="$v.report_type.$touch()"
                  @blur="$v.report_type.$touch()"
                ></v-autocomplete>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="my-0 py-0">
                <v-autocomplete
                  v-model="branch_id"
                  :items="branchList"
                  item-text="name"
                  item-value="id"
                  label="Branch"
                  :error-messages="branchIdErrors"
                  @input="$v.branch_id.$touch()"
                  @blur="$v.branch_id.$touch()"
                ></v-autocomplete>
              </v-col>
            </v-row>
            <v-row v-if="report_type == 'Employee List'">
              <v-col class="my-0 py-0">
                <v-autocomplete
                  v-model="date_field_param"
                  :items="date_field_params"
                  label="Date Field Parameter"
                  :error-messages="dateFieldParameterErrors"
                  :disabled="report_type ? false : true"
                  @input="$v.date_field_param.$touch()"
                  @blur="$v.date_field_param.$touch()"
                ></v-autocomplete>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="my-0 py-0" v-if="report_type == 'Employee List'">
                <v-text-field
                  label="From"
                  hint="MM/DD/YYYY"
                  persistent-hint
                  type="date"
                  prepend-icon="mdi-calendar"
                  v-model="date_from"
                  :error-messages="dateFromErrors"
                  :max="date_to ? date_to : null"
                  @input="$v.date_from.$touch() + validateDate('date_from')"
                  @blur="$v.date_from.$touch()"
                ></v-text-field>
              </v-col>
              <v-col class="my-0 py-0">
                <v-text-field 
                  :label=" report_type != 'Employee List' ? 'As Of' : 'To'"
                  hint="MM/DD/YYYY"
                  persistent-hint
                  type="date"
                  prepend-icon="mdi-calendar"
                  v-model="date_to"
                  :error-messages="dateToErrors"
                  :min="date_from ? date_from : null"
                  @input="$v.date_to.$touch() + validateDate('date_to')"
                  @blur="$v.date_to.$touch()"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="pa-0">
            <v-spacer></v-spacer>
            <v-btn
              color="#E0E0E0"
              @click="closeDialog()"
              class="mb-3"
            >
              Cancel
            </v-btn>
            <v-btn
              color="primary"
              class="mb-3 mr-4"
              @click="exportData()"
              :disabled="loading"
            >
              Export
            </v-btn>
          </v-card-actions>
        </v-card>
      </template>
    </v-dialog>
    <v-dialog
      v-model="loading"
      hide-overlay
      persistent
      width="300"
    >
      <v-card
        color="primary"
        dark
      >
        <v-card-text>
          <p class="text-center pt-4">
            Please stand by...
          </p>
          <v-progress-linear
            indeterminate
            color="white"
            class="mb-0"
          ></v-progress-linear>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
import axios from 'axios';
import { mapState, mapGetters } from 'vuex';
import { validationMixin } from "vuelidate";
import { required, requiredIf, minLength, email } from "vuelidate/lib/validators";
export default {
  mixins: [validationMixin],
  props: [
    'branches',
    'dialog',
  ],
  validations: {
    branch_id: { required },
    date_from: {   
      required: requiredIf(function () {
        return this.report_type == 'Employee List';
      }),
    },
    date_to: { required },
    report_type: { required },
    date_field_param: {   
      required: requiredIf(function () {
        return this.report_type == 'Employee List';
      }),
    },
  },
  data() {
    return {
      date_from: "",
      date_to: "", 
      dateErrors: {
        date_from: false,
        date_to: false,
      },
      branch_id: "",
      loading: false,
      report_types: ['Employee List', 'Branch Manpower Report'],
      report_type: "",
      date_field_param: "",
      date_field_params: [
        { text: 'Date Employed', value: 'date_employed' },
        { text: 'Date Resigned', value: 'date_resigned' },
      ]
    }
  },
  methods: {
    exportData(){
      this.$v.$touch();

      if(!this.$v.$error && !this.dateErrors.date_from && !this.dateErrors.date_to){
   
        this.loading = true;

        const data = {
          'date_from': this.date_from,
          'date_to': this.date_to,
          'asOfDate': this.date_to,
          'branch_id': this.branch_id,
          'report_type': this.report_type,
          'date_field_param': this.date_field_param,
        };
        
        axios.post('/api/employee_master_data/export', data, { responseType: 'arraybuffer'})
          .then((response) => {
              this.loading = false;
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement('a');
              fileLink.href = fileURL;
              fileLink.setAttribute('download', this.report_type + '.xls');
              document.body.appendChild(fileLink);
              fileLink.click();
              this.closeDialog();
          }, (error) => {
            this.loading = false;
            console.log(error);
          }
        );
      }
    },

    // exportData() {
    //   this.$v.$touch();

    //   if(!this.$v.$error && !this.dateErrors.date_from && !this.dateErrors.date_to){
    //     const data = { branc_id: this.branch_id, report_type: this.report_type }
    //     axios.post('/api/employee_master_data/export', data, { responseType: 'arraybuffer'})
    //       .then((response) => {
    //           var fileURL = window.URL.createObjectURL(new Blob([response.data]));
    //           var fileLink = document.createElement('a');
    //           fileLink.href = fileURL;
    //           fileLink.setAttribute('download', 'EmployeeList.xls');
    //           document.body.appendChild(fileLink);
    //           fileLink.click();
    //           this.closeDialog();
    //       }, (error) => {
    //         console.log(error);
    //       }
    //     );
    //   }
    // },

    validateDate(model) {
      let min_date = new Date('1900-01-01').getTime();
      let max_date = new Date().getTime();
      let date = this[model];
      let date_value = new Date(date).getTime();
      
      let [year, month, day] = date.split('-');

      this.dateErrors[model] = false;

      if (date_value < min_date || year.length > 4) {
        this.dateErrors[model] = true;
      }  
    },

    validateDateRange(min, max) {
      let hasError = false;
      min = min ? new Date(min) : new Date('1900-01-01').getTime();
      max = max ? new Date(max) : new Date().getTime();

      if (max < min) {
        hasError = true;
      }  
      return { hasError: hasError };
    },
    clearData() {
      this.$v.$reset();
      this.date_from = "";
      this.date_to = ""; 
      this.dateErrors = {
        date_from: false,
        date_to: false,
      };
      this.branch_id = this.user.branch_id;
      this.date_field_param = "";
      this.loading = false;
      this.report_type = "";

    },
    closeDialog() {
      this.clearData();
      this.$emit('closeDialog');
    }
  },

  computed: {
    branchIdErrors() {
      const errors = [];
      if (!this.$v.branch_id.$dirty) return errors;
      !this.$v.branch_id.required &&
        errors.push("Please select a branch.");
      return errors;
    },
    reportTypeErrors() {
      const errors = [];
      if (!this.$v.report_type.$dirty) return errors;
      !this.$v.report_type.required &&
        errors.push("Please select report type.");
      return errors;
    },
    dateFieldParameterErrors() {
      const errors = [];
      if (!this.$v.date_field_param.$dirty) return errors;
      !this.$v.date_field_param.required &&
        errors.push("Please select date field parameter.");
      return errors;
    },
    dateFromErrors() {
      const errors = [];
      if (!this.$v.date_from.$dirty) return errors;
      !this.$v.date_from.required &&
        errors.push("Please enter date.");
      if(this.dateErrors.date_from || this.validateDateRange(this.date_from, this.date_to).hasError)
      {
        errors.push('Invalid Date Range');
      } 
      return errors;
    },
    dateToErrors() {
      const errors = [];
      if (!this.$v.date_to.$dirty) return errors;
      !this.$v.date_to.required &&
        errors.push("Please enter date.");
      if(this.dateErrors.date_to || this.validateDateRange(this.date_from, this.date_to).hasError)
      {
        errors.push('Enter a valid date');
      } 
      return errors;
    },
    branchList() {
      let branches = this.branches;
      branches.unshift({ id: 0, name: 'ALL' });

      return branches;
    },
    ...mapState("userRolesPermissions", ["userRoles", "userPermissions"]),
    ...mapState("auth", ["user", "userIsLoaded"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },

  watch: {

  },

  mounted() {
   
  }
}
</script>