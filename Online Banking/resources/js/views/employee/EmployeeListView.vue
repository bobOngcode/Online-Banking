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
        <MenuActions
          :canImport="hasPermission('employee-list-import')"
          :canExport="hasPermission('employee-list-export')"
          :canClearList="hasPermission('employee-clear-list')"
          @import="importExcel"
          @export="exportData"
          @clearList="clearList"
        />
        <v-card>
          <v-card-title>
            Employee Lists 
            <!-- <v-chip color="secondary" v-if="branch" class="ml-2"> {{ branch }} </v-chip> -->
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              label="Search"
              single-line
            ></v-text-field>
            <v-spacer></v-spacer>

            <v-btn
              color="primary"
              fab
              dark
              class="mb-2"
              @click="clear() + (dialog = true)"
              v-if="hasPermission('employee-create')"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>
          </v-card-title>
          <DataTable
            :branch="branch"
            :headers="headers"
            :items="employees"
            :search="search"
            :loading="loading"
            :file_upload_log="file_upload_log" 
            :canViewList="hasPermission('employee-list')"
            :canEdit="hasPermission('employee-edit')"
            :canDelete="hasPermission('employee-delete')"
            @edit="editEmployee"
            @confirmDelete="showConfirmAlert"
          />
          <v-dialog v-model="dialog" max-width="1000px" persistent>
            <v-card>
              <v-card-title class="pa-4">
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>
              <v-divider class="mt-0"></v-divider>
              <v-card-text>
                <v-container>
                  <fieldset>
                    <legend class="mb-6">Personal Information</legend>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="employee_code"
                          v-model="editedItem.employee_code"
                          :error-messages="employeeCodeErrors"
                          label="Employee Code"
                          @input="$v.editedItem.employee_code.$touch()"
                          @blur="$v.editedItem.employee_code.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="last_name"
                          v-model="editedItem.last_name"
                          :error-messages="lastNameErrors"
                          label="Last Name"
                          @input="$v.editedItem.last_name.$touch()"
                          @blur="$v.editedItem.last_name.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="first_name"
                          v-model="editedItem.first_name"
                          :error-messages="firstNameErrors"
                          label="First Name"
                          @input="$v.editedItem.first_name.$touch()"
                          @blur="$v.editedItem.first_name.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="middle_name"
                          v-model="editedItem.middle_name"
                          label="Middle Name"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <!-- <v-row>
                    <v-col class="my-0 py-0">
                      <v-divider class="my-0 py-0"></v-divider>
                    </v-col>
                  </v-row> -->
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-menu
                          v-model="input_birth_date"
                          :close-on-content-click="false"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              name="birth_date"
                              v-model="computedBirthDateFormatted"
                              label="Birth Date (MM/DD/YYYY)"
                              persistent-hint
                              readonly
                              v-bind="attrs"
                              v-on="on"
                              :error-messages="birthdateErrors"
                              @input="$v.editedItem.birth_date.$touch()"
                              @blur="$v.editedItem.birth_date.$touch()"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="editedItem.birth_date"
                            no-title
                            @input="input_birth_date = false"
                          ></v-date-picker>
                        </v-menu>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-autocomplete
                          v-model="editedItem.gender"
                          :items="gender_items"
                          item-text="text"
                          item-value="value"
                          label="Gender"
                          :error-messages="genderErrors"
                          @input="$v.editedItem.gender.$touch()"
                          @blur="$v.editedItem.gender.$touch()"
                        >
                        </v-autocomplete>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-autocomplete
                          v-model="editedItem.civil_status"
                          :items="civil_status_items"
                          item-text="text"
                          item-value="value"
                          label="Civil Status"
                          :error-messages="civilStatusErrors"
                          @input="$v.editedItem.civil_status.$touch()"
                          @blur="$v.editedItem.civil_status.$touch()"
                        >
                        </v-autocomplete>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="contact"
                          v-model="editedItem.contact"
                          :error-messages="contactErrors"
                          label="Contact No."
                          @input="$v.editedItem.contact.$touch()"
                          @blur="$v.editedItem.contact.$touch()"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="email"
                          v-model="editedItem.email"
                          :error-messages="emailErrors"
                          label="E-mail"
                          @input="$v.editedItem.email.$touch()"
                          @blur="$v.editedItem.email.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="9" class="my-0 py-0">
                        <v-text-field
                          name="address"
                          v-model="editedItem.address"
                          :error-messages="addressErrors"
                          label="Address"
                          @input="$v.editedItem.address.$touch()"
                          @blur="$v.editedItem.address.$touch()"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col class="my-0 py-0">
                        <v-divider class="my-0 py-0"></v-divider>
                      </v-col>
                    </v-row>
                  </fieldset>
                  <fieldset>
                    <legend class="mb-6 mt-6">Employment Information</legend>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="class"
                          v-model="editedItem.class"
                          :error-messages="classErrors"
                          label="Class"
                          @input="$v.editedItem.class.$touch()"
                          @blur="$v.editedItem.class.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="rank"
                          v-model="editedItem.rank"
                          :error-messages="rankErrors"
                          label="Rank"
                          @input="$v.editedItem.rank.$touch()"
                          @blur="$v.editedItem.rank.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="department"
                          v-model="editedItem.department"
                          :error-messages="departmentErrors"
                          label="Department"
                          @input="$v.editedItem.department.$touch()"
                          @blur="$v.editedItem.department.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="cost_center_code"
                          v-model="editedItem.cost_center_code"
                          :error-messages="costCenterCodeErrors"
                          label="Cost Center Code"
                          @input="$v.editedItem.cost_center_code.$touch()"
                          @blur="$v.editedItem.cost_center_code.$touch()"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="job_description"
                          v-model="editedItem.job_description"
                          :error-messages="jobDescriptionErrors"
                          label="Job Description"
                          @input="$v.editedItem.job_description.$touch()"
                          @blur="$v.editedItem.job_description.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-menu
                          v-model="input_date_employed"
                          :close-on-content-click="false"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              name="date_employed"
                              v-model="computedDateEmployedFormatted"
                              label="Date Employed (MM/DD/YYYY)"
                              persistent-hint
                              readonly
                              v-bind="attrs"
                              v-on="on"
                              :error-messages="dateEmployedErrors"
                              @input="$v.editedItem.date_employed.$touch()"
                              @blur="$v.editedItem.date_employed.$touch()"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="editedItem.date_employed"
                            no-title
                            @input="input_date_employed = false"
                          ></v-date-picker>
                        </v-menu>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="tax_status"
                          v-model="editedItem.tax_status"
                          :error-messages="taxStatusErrors"
                          label="Tax Status"
                          @input="$v.editedItem.tax_status.$touch()"
                          @blur="$v.editedItem.tax_status.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="tax_branch_code"
                          v-model="editedItem.tax_branch_code"
                          :error-messages="taxBranchCodeErrors"
                          label="Tax Branch Code"
                          @input="$v.editedItem.tax_branch_code.$touch()"
                          @blur="$v.editedItem.tax_branch_code.$touch()"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="tin_no"
                          v-model="editedItem.tin_no"
                          :error-messages="tinNoErrors"
                          label="TIN No."
                          @input="$v.editedItem.tin_no.$touch()"
                          @blur="$v.editedItem.tin_no.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="pagibig_no"
                          v-model="editedItem.pagibig_no"
                          :error-messages="pagibigNoErrors"
                          label="PAG-IBIG No."
                          @input="$v.editedItem.pagibig_no.$touch()"
                          @blur="$v.editedItem.pagibig_no.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="philhealth_no"
                          v-model="editedItem.philhealth_no"
                          :error-messages="philhealthNoErrors"
                          label="PHILHEALTH No."
                          @input="$v.editedItem.philhealth_no.$touch()"
                          @blur="$v.editedItem.philhealth_no.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="sss_no"
                          v-model="editedItem.sss_no"
                          :error-messages="sssNoErrors"
                          label="SSS No."
                          @input="$v.editedItem.sss_no.$touch()"
                          @blur="$v.editedItem.sss_no.$touch()"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="time_schedule"
                          v-model="editedItem.time_schedule"
                          :error-messages="timeSchedErrors"
                          label="Time Schedule"
                          @input="$v.editedItem.time_schedule.$touch()"
                          @blur="$v.editedItem.time_schedule.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="restday"
                          v-model="editedItem.restday"
                          :error-messages="restdayErrors"
                          label="Restday"
                          @input="$v.editedItem.restday.$touch()"
                          @blur="$v.editedItem.restday.$touch()"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col class="my-0 py-0">
                        <v-divider class="my-0 py-0"></v-divider>
                      </v-col>
                    </v-row>
                  </fieldset>
                  <fieldset>
                    <legend class="mb-6 mt-6">Educational Background</legend>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="educ_attain"
                          v-model="editedItem.educ_attain"
                          :error-messages="educAttainErrors"
                          label="Educational Attainment"
                          @input="$v.editedItem.educ_attain.$touch()"
                          @blur="$v.editedItem.educ_attain.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="school_attended"
                          v-model="editedItem.school_attended"
                          :error-messages="schoolAttendedErrors"
                          label="School Attended"
                          @input="$v.editedItem.school_attended.$touch()"
                          @blur="$v.editedItem.school_attended.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="course"
                          v-model="editedItem.course"
                          :error-messages="courseErrors"
                          label="Course"
                          @input="$v.editedItem.course.$touch()"
                          @blur="$v.editedItem.course.$touch()"
                        ></v-text-field>
                      </v-col>
                    </v-row>
                  </fieldset>
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
                  :disabled="disabled"
                  class="mb-3 mr-4"
                >
                  Save
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <ImportDialog 
            :branch="branch"
            :api_route="api_route" 
            :dialog_import="dialog_import"
            @getData="refreshData"
            @closeImportDialog="closeImportDialog"
          />
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
import ImportDialog from "../components/ImportDialog.vue";
import MenuActions from "../components/MenuActions.vue";
import DataTable from "../components/DataTable.vue";

export default {

  name: "EmployeeListView",
  components: {
    ImportDialog,
    MenuActions,
    DataTable,
  },
  props: {

  },
  mixins: [validationMixin],

  validations: {
    editedItem: {
      employee_code: { required },
      last_name: { required },
      first_name: { required },
      gender: { required },
      civil_status: { required },
      birth_date: { required },
      address: { required },
      contact: { required },
      email: { required, email },
      class: { required },
      rank: { required },
      department: { required },
      cost_center_code: { required },
      job_description: { required },
      date_employed: { required },
      tax_status: { required },
      tax_branch_code: { required },
      tin_no: { required },
      pagibig_no: { required },
      philhealth_no: { required },
      sss_no: { required },
      time_schedule: { required },
      restday: { required },
      educ_attain: { required },
      school_attended: { required },
      course: { required },
    },
    
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "branch.name" },
        { text: "Emp. Code", value: "employee_code" },
        { text: "Lastname", value: "last_name" },
        { text: "Firstname", value: "first_name" },
        { text: "Middlename", value: "middle_name" },
        // { text: "Birthday", value: "birth_date" },
        // { text: "Address", value: "address" },
        // { text: "Contact #", value: "contact" },
        // { text: "Email", value: "email" },
        { text: "Class", value: "class" },
        { text: "Rank", value: "rank" },
        { text: "Department", value: "department" },
        // { text: "Cost Center Code", value: "cost_center_code" },
        // { text: "Job Description", value: "job_description" },
        // { text: "Date Employed", value: "date_employed" },
        // { text: "Gender", value: "gender" },
        // { text: "Civil Status", value: "civil_status" },
        // { text: "Tax Status", value: "tax_status" },
        // { text: "TIN #", value: "tin_no" },
        // { text: "Tax Branch Code", value: "tax_branch_code" },
        // { text: "Pag-IBIG #", value: "pagibig_no" },
        // { text: "PhilHealth #", value: "philhealth_no" },
        // { text: "SSS #", value: "sss_no" },
        // { text: "Time Schedule", value: "time_schedule" },
        // { text: "Restday", value: "restday" },
        // { text: "Educ. Attainment", value: "educ_attain" },
        // { text: "School Attended", value: "school_attended" },
        // { text: "Course", value: "course" },
        // { text: "Length of Service", value: "length_of_service" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      disabled: false,
      dialog: false,
      employees: [],
      branches: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Employee Lists",
          disabled: false,
          link: "/employee/list",
        },
        {
          text: "View",
          disabled: true,
        },
      ],
      gender_items: [
        { text: "MALE", value: "MALE" },
        { text: "FEMALE", value: "FEMALE" },
      ],
      civil_status_items: [
        { text: "SINGLE", value: "SINGLE" },
        { text: "MARRIED", value: "MARRIED" },
        { text: "DIVORCED", value: "DIVORCED" },
        { text: "WIDOWED", value: "WIDOWED" },
      ],
      loading: true,
      branch: "",
      branch_id: "",
      file_upload_log_id: "",
      editedIndex: -1,
      editedItem: {
        branch_id: "",
        employee_code: "",
        last_name: "",
        first_name: "",
        birth_date: "",
        address: "",
        contact: "",
        email: "",
        class: "",
        rank: "",
        department: "",
        cost_center_code: "",
        job_description: "",
        date_employed: "",
        gender: "",
        civil_status: "",
        tax_status: "",
        tin_no: "",
        tax_branch_code: "",
        pagibig_no: "",
        philhealth_no: "",
        sss_no: "",
        time_schedule: "",
        restday: "",
        educ_attain: "",
        school_attended: "",
        course: "",
      },
      defaultItem: {
        branch_id: "",
        employee_code: "",
        last_name: "",
        first_name: "",
        birth_date: "",
        address: "",
        contact: "",
        email: "",
        class: "",
        rank: "",
        department: "",
        cost_center_code: "",
        job_description: "",
        date_employed: "",
        gender: "",
        civil_status: "",
        tax_status: "",
        tin_no: "",
        tax_branch_code: "",
        pagibig_no: "",
        philhealth_no: "",
        sss_no: "",
        time_schedule: "",
        restday: "",
        educ_attain: "",
        school_attended: "",
        course: "",
      },
      input_birth_date: false,
      input_date_employed: false,
      input_docdate: false,
      file_upload_log: "",
      dialog_import: false,
      api_route: "",
      swalAttr: {
        title: "",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "",
      }
    };
  },

  watch: {
    userIsLoaded: {
      handler() {
        if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
          this.getEmployee(this.$route.params.file_upload_log_id);
        }
      },
    },
    userRolesPermissionsIsLoaded: {
      handler() {
        if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
          this.getEmployee(this.$route.params.file_upload_log_id);
        }
      },
    },
  },

  methods: {
    getEmployee(file_upload_log_id) {

      this.loading = true;
      let data = { file_upload_log_id: file_upload_log_id }
      axios.post("/api/employee/list/view", data).then(
        (response) => {
          // if user has no permission to view overall list
          let data = response.data;
          if (!this.hasPermission('employee-list-all') &&
            this.user.branch_id != this.branch_id
          ) {
            this.$router.push({ name: "unauthorize" });
          }
          this.branch = data.file_upload_log.branch.name;
          this.file_upload_log = data.file_upload_log;
          this.employees = data.employees;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
          console.log(error);
        }
      );
    },
    refreshData(file_upload_log_id) {
      this.getEmployee(file_upload_log_id);
      this.$router.push({
        name: 'employee.list.view',
        params: { branch_id: this.file_upload_log.branch.id, file_upload_log_id: file_upload_log_id }
      });

    },
    editEmployee(item) {
      this.editedItem.gender = item.gender.toUpperCase();
      this.editedItem.civil_status = item.civil_status.toUpperCase();

      this.editedIndex = this.employees.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;

      let [month, day, year] = this.editedItem.birth_date.split("/");
      this.editedItem.birth_date = `${year}-${month}-${day}`;

      [month, day, year] = this.editedItem.date_employed.split("/");
      this.editedItem.date_employed = `${year}-${month}-${day}`;
    },

    deleteEmployee(employee_id) {
      const data = { employee_id: employee_id };

      axios.post("/api/employee/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "employee-delete" });
             this.showAlert(response.data.success, "success")
          }
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    save() {
      this.$v.editedItem.$touch();
      if (!this.$v.$error) {
        this.disabled = true;
        this.overlay = true;
        
        this.editedItem.branch_id = this.branch_id;
        this.editedItem.file_upload_log_id = this.file_upload_log_id;
        
        const data = this.editedItem;
        const employee_id = this.editedItem.id;
        let url = "/api/employee" + (this.editedIndex > -1 ? "/update/" + employee_id : "/store");

        axios.post(url, data).then(
          (response) => {
            let data = response.data;
            if (data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "employee-edit" });

              let employee = data.employee;
              employee.branch = data.branch;
              
              // format date_granted
              let [year, month, day] = employee.dob.split("-");
              employee.birth_date = `${month}/${day}/${year}`;

              [year, month, day] = employee.date_employed.split("-");
              employee.date_employed = `${month}/${day}/${year}`;

              if(this.editedIndex > -1)
              {
                Object.assign(this.employees[this.editedIndex], employee );
              }
              else
              {
                this.employees.push(employee);
              }
              

              this.showAlert(data.success, "success");
              this.close();
            } else {
            }
            this.overlay = false;
            this.disabled = false;
          },
          (error) => {
            this.isUnauthorized(error);

            this.overlay = false;
            this.disabled = false;
          }
        );
      }
    },

    showAlert(title, icon) {
      this.$swal({
        position: "center",
        icon: icon,
        title: title,
        showConfirmButton: false,
        timer: 2500,
      });
    },

    showConfirmAlert(item) {
      Object.assign(this.swalAttr, { title: "Delete Record", confirmButtonText: "Delete Record!" });

      this.$swal(this.swalAttr).then(async (result) => {
        // <--

        if (result.value) {
          // <-- if confirmed

          const employee_id = item.id;
          const index = this.employees.indexOf(item);

          //Call delete User function
          this.deleteEmployee(employee_id);

          //Remove item from array services
          this.employees.splice(index, 1);
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

    closeImportDialog() {
      this.dialog_import = false;
    },

    clearList() {
      Object.assign(this.swalAttr, { title: "Clear List", confirmButtonColor: "#d33", confirmButtonText: "Clear List!" });
      
      if (this.employees.length) {

        this.$swal(this.swalAttr).then((result) => {

          if (result.value) {
            // <-- if confirmed

            let data = { 
              branch_id: this.branch_id, 
              file_upload_log_id: this.file_upload_log_id, 
              clear_list: true 
            };

            axios.post("api/employee/delete", data).then(
              (response) => {
                if (response.data.success) {
                  // send data to Sockot.IO Server
                  // this.$socket.emit("sendData", { action: "product-create" });

                  this.employees = [];
                  this.file_upload_log = null;
                  this.showAlert(response.data.success, "success");
                  this.$router.push({ name: 'employee.list'});

                } else {
                  this.showAlert("No record found", "warning");
                }
              },
              (error) => {
                this.isUnauthorized(error);
                console.log(error);
              }
            );
          }
        });
      } else {
        this.showAlert("No record found", "warning")
      }
    },

    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },

    importExcel() {
      this.dialog_import = true;
    },

    exportData() {
      if (this.employees.length) {
        const data = { file_upload_log_id: this.file_upload_log_id }
        axios.post('/api/employee/export_employee', data, { responseType: 'arraybuffer'})
          .then((response) => {
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement('a');
              fileLink.href = fileURL;
              fileLink.setAttribute('download', 'EmployeeList.xls');
              document.body.appendChild(fileLink);
              fileLink.click();
          }, (error) => {
            console.log(error);
          }
        );
        
        // window.open(
        //   location.origin + "/api/employee/export_employee/" + this.file_upload_log_id,
        //   "_blank"
        // );
      } else {
        this.showAlert("No record found", "warning")
      }
    },
    formatDate(date) {
      if (!date) return null;
      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },
    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;

        if (
          action == "employee-create" ||
          action == "employee-edit" ||
          action == "employee-delete" ||
          action == "employee-import"
        ) {
          this.getEmployee();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Employee" : "Edit Employee";
    },
    employeeCodeErrors() {
      const errors = [];
      if (!this.$v.editedItem.employee_code.$dirty) return errors;
      !this.$v.editedItem.employee_code.required &&
        errors.push("Employee Code is required.");
      return errors;
    },
    lastNameErrors() {
      const errors = [];
      if (!this.$v.editedItem.last_name.$dirty) return errors;
      !this.$v.editedItem.last_name.required &&
        errors.push("Last Name is required.");
      return errors;
    },
    firstNameErrors() {
      const errors = [];
      if (!this.$v.editedItem.first_name.$dirty) return errors;
      !this.$v.editedItem.first_name.required &&
        errors.push("First Name is required.");
      return errors;
    },
    birthdateErrors() {
      const errors = [];
      if (!this.$v.editedItem.birth_date.$dirty) return errors;
      !this.$v.editedItem.birth_date.required && errors.push("Birthdate is required.");
      return errors;
    },
    computedBirthDateFormatted() {
      return this.formatDate(this.editedItem.birth_date);
    },
    addressErrors() {
      const errors = [];
      if (!this.$v.editedItem.address.$dirty) return errors;
      !this.$v.editedItem.address.required &&
        errors.push("Address is required.");
      return errors;
    },
    contactErrors() {
      const errors = [];
      if (!this.$v.editedItem.contact.$dirty) return errors;
      !this.$v.editedItem.contact.required &&
        errors.push("Contact is required.");
      return errors;
    },
    emailErrors() {
      const errors = [];
      if (!this.$v.editedItem.email.$dirty) return errors;
      !this.$v.editedItem.email.required && errors.push("Email is required.");
      !this.$v.editedItem.email.email && errors.push("Must be valid e-mail");
      return errors;
    },
    classErrors() {
      const errors = [];
      if (!this.$v.editedItem.class.$dirty) return errors;
      !this.$v.editedItem.class.required && errors.push("Class is required.");
      return errors;
    },
    rankErrors() {
      const errors = [];
      if (!this.$v.editedItem.rank.$dirty) return errors;
      !this.$v.editedItem.rank.required && errors.push("Rank is required.");
      return errors;
    },
    departmentErrors() {
      const errors = [];
      if (!this.$v.editedItem.department.$dirty) return errors;
      !this.$v.editedItem.department.required &&
        errors.push("Department is required.");
      return errors;
    },
    costCenterCodeErrors() {
      const errors = [];
      if (!this.$v.editedItem.cost_center_code.$dirty) return errors;
      !this.$v.editedItem.cost_center_code.required &&
        errors.push("Cost Center Code is required.");
      return errors;
    },
    jobDescriptionErrors() {
      const errors = [];
      if (!this.$v.editedItem.job_description.$dirty) return errors;
      !this.$v.editedItem.job_description.required &&
        errors.push("Job Description is required.");
      return errors;
    },
    contactErrors() {
      const errors = [];
      if (!this.$v.editedItem.contact.$dirty) return errors;
      !this.$v.editedItem.contact.required &&
        errors.push("Contact is required.");
      return errors;
    },
    dateEmployedErrors() {
      const errors = [];
      if (!this.$v.editedItem.date_employed.$dirty) return errors;
      !this.$v.editedItem.date_employed.required &&
        errors.push("Date Employed is required.");
      return errors;
    },
    computedDateEmployedFormatted() {
      return this.formatDate(this.editedItem.date_employed);
    },
    contactErrors() {
      const errors = [];
      if (!this.$v.editedItem.contact.$dirty) return errors;
      !this.$v.editedItem.contact.required &&
        errors.push("Contact is required.");
      return errors;
    },
    genderErrors() {
      const errors = [];
      if (!this.$v.editedItem.gender.$dirty) return errors;
      !this.$v.editedItem.gender.required && errors.push("Gender is required.");
      return errors;
    },
    civilStatusErrors() {
      const errors = [];
      if (!this.$v.editedItem.civil_status.$dirty) return errors;
      !this.$v.editedItem.civil_status.required &&
        errors.push("Civil Status is required.");
      return errors;
    },
    taxStatusErrors() {
      const errors = [];
      if (!this.$v.editedItem.tax_status.$dirty) return errors;
      !this.$v.editedItem.tax_status.required &&
        errors.push("Tax Status is required.");
      return errors;
    },
    tinNoErrors() {
      const errors = [];
      if (!this.$v.editedItem.tin_no.$dirty) return errors;
      !this.$v.editedItem.tin_no.required &&
        errors.push("TIN No. is required.");
      return errors;
    },
    taxBranchCodeErrors() {
      const errors = [];
      if (!this.$v.editedItem.tax_branch_code.$dirty) return errors;
      !this.$v.editedItem.tax_branch_code.required &&
        errors.push("Tax Branch Code is required.");
      return errors;
    },
    pagibigNoErrors() {
      const errors = [];
      if (!this.$v.editedItem.pagibig_no.$dirty) return errors;
      !this.$v.editedItem.pagibig_no.required &&
        errors.push("PagIbig No. is required.");
      return errors;
    },
    philhealthNoErrors() {
      const errors = [];
      if (!this.$v.editedItem.philhealth_no.$dirty) return errors;
      !this.$v.editedItem.philhealth_no.required &&
        errors.push("Philhealth No. is required.");
      return errors;
    },
    sssNoErrors() {
      const errors = [];
      if (!this.$v.editedItem.sss_no.$dirty) return errors;
      !this.$v.editedItem.sss_no.required &&
        errors.push("SSS No. is required.");
      return errors;
    },
    timeSchedErrors() {
      const errors = [];
      if (!this.$v.editedItem.pagibig_no.$dirty) return errors;
      !this.$v.editedItem.pagibig_no.required &&
        errors.push("Time Schedule is required.");
      return errors;
    },
    restdayErrors() {
      const errors = [];
      if (!this.$v.editedItem.restday.$dirty) return errors;
      !this.$v.editedItem.restday.required &&
        errors.push("Restday is required.");
      return errors;
    },
    educAttainErrors() {
      const errors = [];
      if (!this.$v.editedItem.educ_attain.$dirty) return errors;
      !this.$v.editedItem.educ_attain.required &&
        errors.push("Educ. Attainment is required.");
      return errors;
    },
    schoolAttendedErrors() {
      const errors = [];
      if (!this.$v.editedItem.school_attended.$dirty) return errors;
      !this.$v.editedItem.school_attended.required &&
        errors.push("School Attended is required.");
      return errors;
    },
    courseErrors() {
      const errors = [];
      if (!this.$v.editedItem.course.$dirty) return errors;
      !this.$v.editedItem.course.required && errors.push("Course is required.");
      return errors;
    },
    ...mapState("auth", ["user", "userIsLoaded"]),
    ...mapState("userRolesPermissions", ["userRolesPermissionsIsLoaded"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission", "hasAnyPermission"]),
  },

  mounted() {
    
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.branch_id = this.$route.params.branch_id;
    this.file_upload_log_id = this.$route.params.file_upload_log_id;
    this.api_route = 'api/employee/import_employee/' + this.branch_id; //set api route for uploading excel

    if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {

      this.getEmployee(this.file_upload_log_id);
      
    }

  },
};
</script>