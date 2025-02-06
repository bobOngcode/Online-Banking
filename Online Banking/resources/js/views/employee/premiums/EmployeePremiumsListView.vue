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
          :canImport="hasPermission('employee-premiums-import')"
          :canExport="hasPermission('employee-premiums-export')"
          :canClearList="hasPermission('employee-premiums-clear-list')"
          @import="importExcel"
          @export="exportData"
          @clearList="clearList"
        />
        <v-card>
          <v-card-title>
            Employee Premiums Lists 
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
              v-if="hasPermission('employee-premiums-create')"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>
          </v-card-title>

          <DataTable
            :branch="branch"
            :headers="headers"
            :items="employee_premiums"
            :search="search"
            :loading="loading"
            :file_upload_log="file_upload_log" 
            :canViewList="hasPermission('employee-premiums-list')"
            :canEdit="hasPermission('employee-premiums-edit')"
            :canDelete="hasPermission('employee-premiums-delete')"
            @edit="editEmployeePremiums"
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
                              name="remarks_date"
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
                        <v-menu
                          v-model="input_date_hired"
                          :close-on-content-click="false"
                          transition="scale-transition"
                          offset-y
                          max-width="290px"
                          min-width="290px"
                        >
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field
                              name="date_hired"
                              v-model="computedDateHiredFormatted"
                              label="Date Hired (MM/DD/YYYY)"
                              persistent-hint
                              readonly
                              v-bind="attrs"
                              v-on="on"
                              :error-messages="dateHiredErrors"
                              @input="$v.editedItem.date_hired.$touch()"
                              @blur="$v.editedItem.date_hired.$touch()"
                            ></v-text-field>
                          </template>
                          <v-date-picker
                            v-model="editedItem.date_hired"
                            no-title
                            @input="input_date_hired = false"
                          ></v-date-picker>
                        </v-menu>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col class="my-0 py-0">
                        <v-divider class="my-0 py-0"></v-divider>
                      </v-col>
                    </v-row>
                  </fieldset>
                  <fieldset>
                    <legend class="mb-6 mt-6">Employee Loans Information</legend>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="or_number"
                          v-model="editedItem.or_number"
                          :error-messages="orNumberErrors"
                          label="OR Number"
                          @input="$v.editedItem.or_number.$touch()"
                          @blur="$v.editedItem.or_number.$touch()"
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
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="editedItem.sss_ee"
                          label= "SSS Premium EE"
                          v-bind:properties="{
                            name: 'sss_ee',
                            placeholder: '0.00',
                            error: sssEEErrors.hasError,
                            messages: sssEEErrors.errors,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                          @input="$v.editedItem.sss_ee.$touch()"
                          @blur="$v.editedItem.sss_ee.$touch()"
                        >
                        </v-text-field-dotnumber>
                      </v-col> 
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="editedItem.sss_er"
                          label= "SSS Premium ER"
                          v-bind:properties="{
                            name: 'sss_er',
                            placeholder: '0.00',
                            error: sssERErrors.hasError,
                            messages: sssERErrors.errors,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                          @input="$v.editedItem.sss_er.$touch()"
                          @blur="$v.editedItem.sss_er.$touch()"
                        >
                        </v-text-field-dotnumber>
                      </v-col> 
                    </v-row>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="editedItem.sss_ec"
                          label= "SSS Premium EC"
                          v-bind:properties="{
                            name: 'sss_ec',
                            placeholder: '0.00',
                            error: sssECErrors.hasError,
                            messages: sssECErrors.errors,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                          @input="$v.editedItem.sss_ec.$touch()"
                          @blur="$v.editedItem.sss_ec.$touch()"
                        >
                        </v-text-field-dotnumber>
                      </v-col> 
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="sssTotal"
                          label= "SSS Premium Total"
                          v-bind:properties="{
                            name: 'sss_total',
                            placeholder: '0.00',
                            //error: sssTotalErrors.hasError,
                            //messages: sssTotalErrors.errors,
                            readonly: true,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                        >
                        </v-text-field-dotnumber>
                      </v-col> 
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="philhealth_no"
                          v-model="editedItem.philhealth_no"
                          :error-messages="philhealthNoErrors"
                          label="Philhealth No."
                          @input="$v.editedItem.philhealth_no.$touch()"
                          @blur="$v.editedItem.philhealth_no.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="editedItem.philhealth_ee"
                          label= "Philhealth Premium EE"
                          v-bind:properties="{
                            name: 'philhealth_ee',
                            placeholder: '0.00',
                            error: philhealthEEErrors.hasError,
                            messages: philhealthEEErrors.errors,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                          @input="$v.editedItem.philhealth_ee.$touch()"
                          @blur="$v.editedItem.philhealth_ee.$touch()"
                        >
                        </v-text-field-dotnumber>
                      </v-col> 
                    </v-row>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="editedItem.philhealth_er"
                          label= "Philhealth Premium ER"
                          v-bind:properties="{
                            name: 'philhealth_er',
                            placeholder: '0.00',
                            error: philhealthERErrors.hasError,
                            messages: philhealthERErrors.errors,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                          @input="$v.editedItem.philhealth_er.$touch()"
                          @blur="$v.editedItem.philhealth_er.$touch()"
                        >
                        </v-text-field-dotnumber>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="philhealthTotal"
                          label= "Philhealth Premium Total"
                          v-bind:properties="{
                            name: 'philhealth_total',
                            placeholder: '0.00',
                            //error: philhealthTotalErrors.hasError,
                            //messages: philhealthTotalErrors.errors,
                            readonly: true,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                        >
                        </v-text-field-dotnumber>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field
                          name="pagibig_no"
                          v-model="editedItem.pagibig_no"
                          :error-messages="pagibigNoErrors"
                          label="Pagibig No."
                          @input="$v.editedItem.pagibig_no.$touch()"
                          @blur="$v.editedItem.pagibig_no.$touch()"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="editedItem.pagibig_ee"
                          label= "Pagibig Premium EE"
                          v-bind:properties="{
                            name: 'pagibig_ee',
                            placeholder: '0.00',
                            error: pagibigEEErrors.hasError,
                            messages: pagibigEEErrors.errors,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                          @input="$v.editedItem.pagibig_ee.$touch()"
                          @blur="$v.editedItem.pagibig_ee.$touch()"
                        >
                        </v-text-field-dotnumber>
                      </v-col> 
                    </v-row>
                    <v-row>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="editedItem.pagibig_er"
                          label= "Pagibig Premium ER"
                          v-bind:properties="{
                            name: 'pagibig_er',
                            placeholder: '0.00',
                            error: pagibigERErrors.hasError,
                            messages: pagibigERErrors.errors,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                          @input="$v.editedItem.pagibig_er.$touch()"
                          @blur="$v.editedItem.pagibig_er.$touch()"
                        >
                        </v-text-field-dotnumber>
                      </v-col>
                      <v-col cols="3" class="my-0 py-0">
                        <v-text-field-dotnumber
                          class="pa-0"
                          v-model="pagibigTotal"
                          label= "Pagibig Premium Total"
                          v-bind:properties="{
                            name: 'pagibig_total',
                            placeholder: '0.00',
                            //error: pagibigTotalErrors.hasError,
                            //messages: pagibigTotalErrors.errors,
                            readonly: true,
                          }"
                          v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }"
                        >
                        </v-text-field-dotnumber>
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
import ImportDialog from "../../components/ImportDialog.vue";
import MenuActions from '../../components/MenuActions.vue';
import DataTable from "../../components/DataTable.vue";


export default {
  name: "EmployeePremiumsListView",
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
      last_name: { required },
      first_name: { required },
      birth_date: { required },
      date_hired: { required },
      or_number: { required },
      sss_no: { required },
      sss_ee: { required },
      sss_er: { required },
      sss_ec: { required },
      sss_total: { required },
      philhealth_no: { required },
      philhealth_ee: { required },
      philhealth_er: { required },
      philhealth_total: { required },
      pagibig_no: { required },
      pagibig_ee: { required },
      pagibig_er: { required },
      pagibig_total: { required }
    },
    file: { required },
  },
  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "branch.name" },
        { text: "Lastname", value: "last_name" },
        { text: "Firstname", value: "first_name" },
        { text: "Middlename", value: "middle_name" },
        { text: "Birth Date", value: "birth_date" },
        { text: "Date Hired", value: "date_hired" },
        { text: "Issued OR No.", value: "or_number" },
        { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      disabled: false,
      dialog: false,
      employee_premiums: [],
      branches: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Employee Premiums Lists",
          disabled: false,
          link: "/employee/premiums/list",
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
      search_branch: "",
      file: [],
      fileIsEmpty: false,
      fileIsInvalid: false,
      uploadDisabled: false,
      uploading: false,
      dialog_import: false,
      dialog_error_list: false,
      errors_array: [],
      fileError: "",
      branch: "",
      branch_id: "",
      editedIndex: -1,
      editedItem: {
        last_name: "",
        first_name: "",
        middle_name: "",
        birth_date: "",
        date_hired: "",
        or_number: "",
        sss_no: "",
        sss_ee: 0.00,
        sss_er: 0.00,
        sss_ec: 0.00,
        sss_total: 0.00,
        philhealth_no: "",
        philhealth_ee: 0.00,
        philhealth_er: 0.00,
        philhealth_total: 0.00,
        pagibig_no: "",
        pagibig_ee: 0.00,
        pagibig_er: 0.00,
        pagibig_total: 0.00
      },
      defaultItem: {
        last_name: "",
        first_name: "",
        middle_name: "",
        birth_date: "",
        date_hired: "",
        or_number: "",
        sss_no: "",
        sss_ee: 0.00,
        sss_er: 0.00,
        sss_ec: 0.00,
        sss_total: 0.00,
        philhealth_no: "",
        philhealth_ee: 0.00,
        philhealth_er: 0.00,
        philhealth_total: 0.00,
        pagibig_no: "",
        pagibig_ee: 0.00,
        pagibig_er: 0.00,
        pagibig_total: 0.00
      },
      input_birth_date: false,
      input_date_hired: false,
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
          this.getEmployeePremiums(this.$route.params.file_upload_log_id);
        }
      },
    },
    userRolesPermissionsIsLoaded: {
      handler() {
        if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
          this.getEmployeePremiums(this.$route.params.file_upload_log_id);
        }
      },
    },
  },

  methods: {
    getEmployeePremiums(file_upload_log_id) {
      this.loading = true;
      let data = { file_upload_log_id: file_upload_log_id }
      axios.post("/api/employee_premiums/list/view", data).then(
        (response) => {
          // if user has no permission to view overall list
          let data = response.data;

          if (!this.hasPermission('employee-premiums-list-all') &&
            this.user.branch_id != this.branch_id
          ) {
            this.$router.push({ name: "unauthorize" });
          }

          this.branch = data.file_upload_log.branch.name;
          this.file_upload_log = data.file_upload_log;
          this.employee_premiums = data.employee_premiums;
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
        name: 'employee.premiums.list.view',
        params: { branch_id: this.file_upload_log.branch.id, file_upload_log_id: file_upload_log_id }
      });

    },

    save() {
      this.$v.editedItem.$touch();
 
      if (!this.$v.$error) {
        this.disabled = true;
        this.overlay = true;
        this.editedItem.branch_id = this.branch_id;
        const data = this.editedItem;
        const employee_premiums_id = this.editedItem.id;

        let url = "/api/employee_premiums" + (this.editedIndex > -1 ? "/update/" + employee_premiums_id : "/store");

          axios.post(url, data).then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "employee-premiums-edit" });

                let employee_premiums = response.data.employee_premiums;
                
                // format birth_date
                let [year, month, day] = employee_premiums.dob.split("-");
                employee_premiums.birth_date = `${month}/${day}/${year}`;

                // format date_hired
                [year, month, day] = employee_premiums.date_hired.split("-");
                employee_premiums.date_hired = `${month}/${day}/${year}`;

                if (this.editedIndex > -1) 
                {
                  Object.assign(this.employee_premiums[this.editedIndex], employee_premiums);
                }
                else
                {
                  this.employee_premiums.push(employee_premiums);
                }

                this.showAlert(response.data.success, 'success');
                this.close();
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
    
    editEmployeePremiums(item) {

      this.editedIndex = this.employee_premiums.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;

      let [month, day, year] = this.editedItem.birth_date.split("/");
      this.editedItem.birth_date = `${year}-${month}-${day}`;

      [month, day, year] = this.editedItem.date_hired.split("/");
      this.editedItem.date_hired = `${year}-${month}-${day}`;
    },

    deleteEmployeePremiums(employee_premiums_id) {
      const data = { employee_premiums_id: employee_premiums_id };

      axios.post("/api/employee_premiums/delete", data).then(
        (response) => {
  
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "employee-delete" });
            this.showAlert(response.data.success, 'success');
          }
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    showConfirmAlert(item) {
      Object.assign(this.swalAttr, { title: "Delete Record", confirmButtonText: "Delete Record!" });

      this.$swal(this.swalAttr).then((result) => {
        // <--

        if (result.value) {
          // <-- if confirmed

          const employee_premiums_id = item.id;
          const index = this.employee_premiums.indexOf(item);

          //Call delete User function
          this.deleteEmployeePremiums(employee_premiums_id);

          //Remove item from array services
          this.employee_premiums.splice(index, 1);
          
        }
      });
    },

    close() {
      this.dialog = false;
      this.loading = false;
      this.clear();
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    closeImportDialog() {
      this.dialog_import = false;
    },

    importExcel() {
      this.dialog_import = true;
    },

    exportData() {
      if (this.employee_premiums.length) {
        const data = { file_upload_log_id: this.file_upload_log_id }
        axios.post('/api/employee_premiums/export_premiums', data, { responseType: 'arraybuffer'})
          .then((response) => {
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement('a');
              fileLink.href = fileURL;
              fileLink.setAttribute('download', 'EmployeePremiums.xls');
              document.body.appendChild(fileLink);
              fileLink.click();
          }, (error) => {
            console.log(error);
          }
        );
      } else {
        this.showAlert('No record found', 'warning');
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

    clearList() {
      Object.assign(this.swalAttr, { title: "Clear List", confirmButtonText: "Clear List!" });

      if (this.employee_premiums.length) {
        this.$swal(this.swalAttr).then((result) => {

          if (result.value) {
            // <-- if confirmed

            let data = { 
              branch_id: this.branch_id, 
              file_upload_log_id: this.file_upload_log_id, 
              clear_list: true 
            };

            axios.post("api/employee_premiums/delete", data).then(
              (response) => {

                if (response.data.success) {
                  // send data to Sockot.IO Server
                  // this.$socket.emit("sendData", { action: "product-create" });

                  this.employee_premiums = [];
                  this.file_upload_log = null;

                  this.showAlert(response.data.success, 'success');
                  this.$router.push({ name: 'employee.premiums.list'});
                } else {
                  this.showAlert('No record found', 'warning');
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
        this.showAlert('No record found', 'warning');
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
          action == "employee-premiums-create" ||
          action == "employee-premiums-edit" ||
          action == "employee-premiums-delete" ||
          action == "employee-premiums-import" ||
          action == "employee-premiums-clear-list"
        ) {
          this.getEmployeePremiums();
        }
      };
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "New Employee" : "Edit Employee";
    },
    fileErrors() {
      const errors = [];
      if (!this.$v.file.$dirty) return errors;
      !this.$v.file.required && errors.push("File is required.");
      this.fileIsEmpty && errors.push("File is empty.");
      this.fileIsInvalid &&
        errors.push("File type must be 'xlsx', 'xls' or 'ods'.");
      return errors;
    },
    imported_file_errors() {
      return this.errors_array.sort();
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
    dateHiredErrors() {
      const errors = [];
      if (!this.$v.editedItem.date_hired.$dirty) return errors;
      !this.$v.editedItem.date_hired.required &&
        errors.push("Date Hired is required.");
      return errors;
    },
    computedDateHiredFormatted() {
      return this.formatDate(this.editedItem.date_hired);
    },
    orNumberErrors() {
      const errors = [];
      if (!this.$v.editedItem.or_number.$dirty) return errors;
      !this.$v.editedItem.or_number.required &&
        errors.push("OR Number is required.");
      return errors;
    },
    sssNoErrors() {
      const errors = [];
      if (!this.$v.editedItem.sss_no.$dirty) return errors;
      !this.$v.editedItem.sss_no.required &&
        errors.push("SSS No. is required.");
      return errors;
    },
    sssEEErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.sss_ee.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.sss_ee)
      {
        errors.push("SSS Premiums EE is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    sssERErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.sss_er.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.sss_er)
      {
        errors.push("SSS Premiums ER is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    sssECErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.sss_ec.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.sss_ec)
      {
        errors.push("SSS Premiums EC is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    sssTotalErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.sss_total.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.sss_total)
      {
        errors.push("SSS Premiums Total is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    philhealthNoErrors() {
      const errors = [];
      if (!this.$v.editedItem.philhealth_no.$dirty) return errors;
      !this.$v.editedItem.philhealth_no.required &&
        errors.push("Philhealth No. is required.");
      return errors;
    },
    philhealthEEErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.philhealth_ee.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.philhealth_ee)
      {
        errors.push("Philhealth Premiums EE is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    philhealthERErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.philhealth_er.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.philhealth_er)
      {
        errors.push("Philhealth Premiums ER is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    philhealthTotalErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.philhealth_total.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.philhealth_total)
      {
        errors.push("Philhealth Premiums Total is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    
    pagibigNoErrors() {
      const errors = [];
      if (!this.$v.editedItem.pagibig_no.$dirty) return errors;
      !this.$v.editedItem.pagibig_no.required &&
        errors.push("PagIbig No. is required.");
      return errors;
    },
    pagibigEEErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.pagibig_ee.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.pagibig_ee)
      {
        errors.push("PagIbig Premiums EE is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    pagibigERErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.pagibig_er.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.pagibig_er)
      {
        errors.push("PagIbig Premiums ER is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    pagibigTotalErrors() {
      const errors = [];
      let hasError = false;
      
      if (!this.$v.editedItem.pagibig_total.$dirty) return {errors: errors, hasError: hasError};

      if(!this.editedItem.pagibig_total)
      {
        errors.push("PagIbig Premiums Total is required.");
        hasError = true;
      }
        
      return {errors: errors, hasError: hasError};
    },
    sssTotal()
    {

      let sss_ee = this.editedItem.sss_ee ? parseFloat(this.editedItem.sss_ee) : 0;
      let sss_er = this.editedItem.sss_er ? parseFloat(this.editedItem.sss_er) : 0;
      let sss_ec = this.editedItem.sss_ec ? parseFloat(this.editedItem.sss_ec) : 0;

      return (sss_ee + sss_er + sss_ec).toFixed(2);
    },
    philhealthTotal()
    {

      let philhealth_ee = this.editedItem.philhealth_ee ? parseFloat(this.editedItem.philhealth_ee) : 0;
      let philhealth_er = this.editedItem.philhealth_er ? parseFloat(this.editedItem.philhealth_er) : 0;

      return (philhealth_ee + philhealth_er).toFixed(2);
    },
    pagibigTotal()
    {

      let pagibig_ee = this.editedItem.pagibig_ee ? parseFloat(this.editedItem.pagibig_ee) : 0;
      let pagibig_er = this.editedItem.pagibig_er ? parseFloat(this.editedItem.pagibig_er) : 0;

      return (pagibig_ee + pagibig_er).toFixed(2);
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
    this.api_route = 'api/employee_premiums/import_premiums/' + this.branch_id; //set api route for uploading excel
    
    if (this.userIsLoaded && this.userRolesPermissionsIsLoaded) {
      this.getEmployeePremiums(this.file_upload_log_id);
    }

    // this.websocket();
  },
};
</script>