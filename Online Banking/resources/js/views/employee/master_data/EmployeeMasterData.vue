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
        <div class="d-flex justify-content-end mb-3">
          <v-menu offset-y :close-on-content-click="false">
            <template v-slot:activator="{ on, attrs }">
              <v-btn small v-bind="attrs" v-on="on" color="primary">
                Actions
                <v-icon small> mdi-menu-down </v-icon>
              </v-btn>
            </template>
            <v-list class="pa-1">
              <v-list-item
                class="ma-0 pa-0"
                style="min-height: 25px"
                v-if="hasPermission('employee-master-data-import')"
              >
                <v-list-item-title>
                  <v-btn
                    class="mx-1 white--text"
                    color="primary"
                    width="100px"
                    x-small
                    @click="importExcel()"
                  >
                    <v-icon class="mr-1" x-small> mdi-import </v-icon>
                    Import
                  </v-btn>
                </v-list-item-title>
              </v-list-item>
              <v-list-item
                class="ma-0 pa-0"
                style="min-height: 25px"
                v-if="hasPermission('employee-master-data-export')"
              >
                <v-list-item-title>
                  <v-btn
                    class="mx-1 white--text"
                    color="success"
                    width="100px"
                    x-small
                    @click="exportData()"
                  >
                    <v-icon class="mr-1" x-small> mdi-microsoft-excel </v-icon>
                    Export
                  </v-btn>
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>
        <v-card>
          <v-data-table
            :headers="tableHeaders"
            :items="employees"
            :search="search"
            :loading="loading"
            loading-text="Loading... Please wait"
            v-model="selectedItems"
            item-key="id"
            show-select
          > 
            <template v-slot:top>
              <v-toolbar flat class="my-2 pt-2">
                <v-toolbar-title class="mt-4">Employee Master Data </v-toolbar-title>
                <v-divider vertical class="ma-2 ml-4" thickness="20px"></v-divider>
                
                <v-tooltip top v-if="hasPermission('employee-master-data-create')">
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn 
                      small 
                      class="mx-2 mt-4" 
                      color="primary" 
                      rounded 
                      fab
                      v-bind="attrs" v-on="on"
                      @click="clear() + (dialog = true)"
                    >
                        <v-icon>mdi-plus</v-icon> 
                    </v-btn>
                  </template>
                  
                  <span>Add Data</span>
                </v-tooltip>
                <v-tooltip top v-if="hasPermission('employee-master-data-list')">
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn 
                      small 
                      class="mr-2 mt-4 white--text" 
                      color="#AB47BC" 
                      rounded 
                      fab 
                      @click="getEmployee()"
                      :disabled="loading"
                      v-bind="attrs" v-on="on"
                    > 
                      <v-icon>mdi-refresh</v-icon> 
                    </v-btn>
                  </template>
                  <span>Refresh Data</span>
                </v-tooltip> 
                <v-tooltip top v-if=" hasPermission('employee-master-data-delete')">
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn 
                      small
                      class="mr-2 mt-4" 
                      color="error" 
                      rounded 
                      fab 
                      :disabled="selectedItems.length ? false : deleteIsDisabled"
                      @click="showConfirmAlert(selectedItems, 'Multiple Delete')"
                      v-bind="attrs" 
                      v-on="on"
                    > 
                      <v-icon>mdi-delete</v-icon> 
                    </v-btn>
                  </template>
                  <span>Delete Selected Row</span>
                </v-tooltip> 
                <v-spacer></v-spacer>
                <v-text-field
                  class="mr-2"
                  v-model="search"
                  append-icon="mdi-magnify"
                  label="Search"
                  single-line
                  hide-details=""
                ></v-text-field>
                
              </v-toolbar>
              <v-toolbar flat class="ma-0 pa-0" extended extension-height="30px">
                <v-autocomplete
                  class="mt-8"
                  v-model="selectedTableHeaders"
                  :items="headerItems"
                  item-text="text"
                  item-value="name"
                  label="Table Columns"
                  multiple
                  hide-details=""
                  item-disabled="disable"
                  return-object
                  chips
                >
                  <template v-slot:selection="data">
                    <v-chip
                      v-bind="data.attrs"
                      :input-value="data.selected"
                      :close="selectedTableHeaders.length > 1 ? true : false"
                      @click="data.select"
                      @click:close="removeSelectedHeader(data.item)"
                    >
                      {{ data.item.text }}
                    </v-chip>
                  </template>
                </v-autocomplete>
              </v-toolbar>
            </template>
            <template v-slot:header.data-table-select="{ props, on }">
              <v-simple-checkbox
                v-model="selectAll"
                v-on="on"
                :indeterminate="indeterminate"
                color="primary"
                @click="selectUnselect(props)"
              />
            </template>
          
            <template v-slot:item.data-table-select="{ isSelected, select }">
              <v-simple-checkbox color="primary" v-ripple :value="isSelected" @input="select($event)"></v-simple-checkbox>
            </template>
            <template v-slot:item.date_employed="{ item }">
              {{ formatDate(item.date_employed) }}
            </template>
            <template v-slot:item.date_resigned="{ item }">
              {{ formatDate(item.date_resigned) }}
            </template>
            <template v-slot:item.active="{ item }">
              <v-chip small :color="item.active ? 'primary' : 'secondary'">
                {{ item.active ? 'Active' : 'Inactive' }}
              </v-chip>
            </template>
            <template v-slot:item.actions="{ item }">
              <v-icon
                small
                class="mr-2"
                color="green"
                @click="editEmployee(item)"
                v-if="hasPermission('employee-master-data-edit')"
              >
                mdi-pencil
              </v-icon>
              <v-icon
                small
                color="red"
                @click="showConfirmAlert(item)"
                v-if="hasAnyPermission('employee-master-data-delete')"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-data-table>
          <v-dialog v-model="dialog" persistent fullscreen>
            <v-card>
              <v-card-title class="pa-4">
                <span class="headline">{{ formTitle }}</span>
                <v-spacer></v-spacer>
                <v-btn @click="close()" icon>
                  <v-icon> mdi-close </v-icon>
                </v-btn>
              </v-card-title>
              <v-divider class="mt-0"></v-divider>
              <v-card-text>
                <EmployeeInformationTabs
                  :data="editedItem"
                  :files="employee_files"
                  :key_performances="monthly_key_performances"
                  :editedIndex="editedIndex"
                  :positions="positions"
                  :branches="branches"
                  :departments="departments"
                  @openAttachFileDialog="openAttachFileDialog"
                  ref="EmployeeInformationTabs"
                  :key="employeeInformationComponentKey"
                />
              </v-card-text>
              <v-divider class="mb-3 mt-0"></v-divider>
              <v-card-actions class="pa-0">
                <v-spacer></v-spacer>
                <v-btn color="#E0E0E0" @click="close()" class="mb-3">
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
          <AttachFileDialog 
            :dialog="attach_file_dialog" 
            :employee_id="editedItem.id"
            :editedIndex="editedIndex"
            @closeAttachFileDialog="closeAttachFileDialog"
            @uploadFile="uploadFile"
          />
          <ImportDialog 
            :api_route="api_route" 
            :dialog_import="dialog_import"
            docname="Employee Master Data"
            @getData="getEmployee"
            @closeImportDialog="closeImportDialog"
          />
          <ExportDialog 
            :dialog="dialog_export"
            :branches="branches"
            @closeDialog="closeExportDialog"
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
import ExportDialog from "../components/ExportDialog.vue";
import EmployeeInformationTabs from './component/EmployeeInformationTabs.vue';
import AttachFileDialog from './component/AttachFileDialog.vue';

export default {

  name: "EmployeeListView",
  components: {
    ImportDialog,
    ExportDialog,
    EmployeeInformationTabs,
    AttachFileDialog
  },
  props: {

  },
  mixins: [validationMixin],

  // validations: {
  //   editedItem: {
  //     branch: { required },
  //     employee_code: { required },
  //     last_name: { required },
  //     first_name: { required },
  //     gender: { required },
  //     civil_status: { required },
  //     birth_date: { required },
  //     address: { required },
  //     contact: { required },
  //     email: { email },
  //     position: { required },
  //     department: { required },
  //     date_employed: { required },
  //     tin_no: { required },
  //     pagibig_no: { required },
  //     philhealth_no: { required },
  //     sss_no: { required },
  //     educ_attain: { required },
  //     school_attended: { required },
  //     course: { required },
  //   },
    
  // },
  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "branch.name" },
        { text: "Company", value: "branch.company.name" },
        { text: "Emp. Code", value: "employee_code" },
        { text: "Lastname", value: "last_name" },
        { text: "Firstname", value: "first_name" },
        { text: "Middlename", value: "middle_name" },
        { text: "Birthday", value: "birth_date" },
        { text: "Address", value: "address" },
        { text: "Contact #", value: "contact" },
        { text: "Email", value: "email" },
        { text: "Job Description", value: "position.name" },
        { text: "Rank", value: "position.rank.name" },
        { text: "Department", value: "department.name" },
        { text: "Division", value: "department.division.name" },
        { text: "Date Employed", value: "date_employed" },
        { text: "Gender", value: "gender" },
        { text: "Civil Status", value: "civil_status" },
        { text: "TIN #", value: "tin_no" },
        { text: "Pag-IBIG #", value: "pagibig_no" },
        { text: "PhilHealth #", value: "philhealth_no" },
        { text: "SSS #", value: "sss_no" },
        { text: "Educ. Attainment", value: "educ_attain" },
        { text: "School Attended", value: "school_attended" },
        { text: "Course", value: "course" },
        { text: "Length of Service", value: "length_of_service" },
        { text: "Status", value: "active" },
        // { text: "Actions", value: "actions", sortable: false, width: "80px" },
      ],
      disabled: false,
      dialog: false,
      employees: [],
      employee_files: [],
      monthly_key_performances: [],
      branches: [],
      departments: [],
      positions: [],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Employee Master Data",
          disabled: true,
        },
      ],
      loading: false,
      dialog_import: false,
      dialog_export: false,
      api_route: "/api/employee_master_data/import", // api rout for uploading excel file
      swalAttr: {
        title: "",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "",
      },
      selectedTableHeaders: [],
      defaultTableHeaders: [
        { text: "Emp. Code", value: "employee_code" },
        { text: "Lastname", value: "last_name" },
        { text: "Firstname", value: "first_name" },
        { text: "Middlename", value: "middle_name" },
        { text: "Job Description", value: "position.name" },
        { text: "Branch", value: "branch.name" },
        { text: "Department", value: "department" },
        { text: "Date Employed", value: "date_employed" },
        { text: "Status", value: "active" },
      ],
      selectedItems: [],
      deleteIsDisabled: true,
      swalDeleteAttr: {
        title: "Delete Record",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonColor: "#d33", 
        confirmButtonText: "Delete Record"
      },
      selectAll: false,
      indeterminate: false,
      editedItem: {},
      editedIndex: -1,
      attach_file_dialog: false,
      employeeInformationComponentKey: 0,
      employee_files_error: [],
    };
  },

  methods: {
    getEmployee() {

      this.loading = true;

      axios.get("/api/employee_master_data/index").then(
        (response) => {
          // if user has no permission to view overall list
          let data = response.data;          

          this.employees = data.employees;
          this.branches = data.branches;
          this.departments = data.departments;
          this.positions = data.positions;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
          console.log(error);
        }
      );
    },
    editEmployee(item) {
      
      this.editedItem.gender = item.gender.toUpperCase();
      this.editedItem.civil_status = item.civil_status.toUpperCase();

      Object.assign(item, { birth_date: item.dob });

      this.editedIndex = this.employees.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;

      this.employee_files = item.files;
      this.employeeInformationComponentKey += 1;

      this.monthly_key_performances = item.key_performances;

      // let [month, day, year] = this.editedItem.dob.split("/");
      // this.editedItem.birth_date = `${year}-${month}-${day}`;

      // [month, day, year] = this.editedItem.date_employed.split("/");
      // this.editedItem.date_employed = `${year}-${month}-${day}`;

      // [month, day, year] = this.editedItem.date_resigned.split("/");
      // this.editedItem.date_resigned = `${year}-${month}-${day}`;
    },

    deleteEmployee(data) {
      
      axios.post("/api/employee_master_data/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "employee-master-data-delete" });
             this.showAlert(response.data.success, "success");
             this.getEmployee();
          }
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },

    save() {

      let EmployeeInformationTabs = this.$refs.EmployeeInformationTabs;

      let dateModelHasErrors = Object.values(EmployeeInformationTabs.dateErrors).map((obj) => obj.status).includes(true);

      EmployeeInformationTabs.$v.editedItem.$touch();
      
      if (!EmployeeInformationTabs.$v.editedItem.$error && !dateModelHasErrors) {

        this.disabled = true;
        this.overlay = true;

        const employee_id = this.editedItem.id;
        let url = "/api/employee_master_data" + (this.editedIndex > -1 ? "/update/" + employee_id : "/store");
        
        axios.post(url, this.formData()).then(
          (response) => {
            
            this.overlay = false;
            this.disabled = false;
            let data = response.data;
    
            if(data.employee_files_error)
            {
              this.employee_files_error = data.employee_files_error;
            }
            
            if (data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "employee-master-data-edit" });

              let employee = data.employee;
              // console.log(employee);
              
              // format date_granted
              // let [year, month, day] = employee.dob.split("-");
              // employee.birth_date = `${month}/${day}/${year}`;

              // [year, month, day] = employee.date_employed.split("-");
              // employee.date_employed = `${month}/${day}/${year}`;

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
          },
          (error) => {
            this.isUnauthorized(error);

            this.overlay = false;
            this.disabled = false;
          }
        );
      }
    },

    formData(){
      let formData = new FormData();

        // from EmployeeInformationTabs component
      let EmployeeInformationTabs = this.$refs.EmployeeInformationTabs;
      let AttachFileDialog = this.$refs.AttachFileDialog;
      
      let data = EmployeeInformationTabs.editedItem
      data.branch_id = data.branch.id;
      data.department_id = data.department.id;
      data.position_id = data.position.id;       

      let fields = Object.keys(data);

      fields.forEach(field => {
        let fieldValue = data[`${field}`];
        formData.append(field, fieldValue);
      });

      let activeStatus = ['True', 'true', true, 1, '1'].includes(data.active) ? 1 : ['False', 'false', false, '0', 0].includes(data.active) ? 0 : 0;
      
      formData.append('active', activeStatus);

      if(this.editedIndex == -1) //ADD MODE
      {
        let employee_files = EmployeeInformationTabs.employee_files;   
        employee_files.forEach((item, i) => {
          formData.append('employee_files[]', item.file);
          formData.append('document_types[]', item.document_type);
        });
      }
      
      let regularization_file_input = EmployeeInformationTabs.regularization_file_input;
      let regularization_memo_file_input = EmployeeInformationTabs.regularization_memo_file_input;
      
      if(regularization_file_input.name)
      {
        formData.append('employee_files[]', regularization_file_input);
        formData.append('document_types[]', 'Performance for Regularization');
      }

      if(regularization_memo_file_input.name)
      {
        formData.append('employee_files[]', regularization_memo_file_input);
        formData.append('document_types[]', 'Memo of Regularization');
      }

      if(EmployeeInformationTabs.keyPerformances)
      {
        let monthly_key_performances = EmployeeInformationTabs.keyPerformances;
        formData.append('monthly_key_performances', JSON.stringify(monthly_key_performances));
      }

      return formData;
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

    showConfirmAlert(item, action) {

      Object.assign(this.swalAttr, { title: "Delete Record", confirmButtonText: "Delete Record!" });
      let data = { employee_id: item.id } 

      if(action == 'Multiple Delete')
      {
        Object.assign(this.swalAttr, { 
          title: "Delete Multiple Record", 
          confirmButtonText: "Delete Record",
          confirmButtonColor: "#d33", 
        });

        data = { employee_id: item.map(value => value.id) };
        
      }

      this.$swal(this.swalAttr).then(async (result) => {
        // <--

        if (result.value) {
          // <-- if confirmed

          // const employee_id = item.id;
          // const index = this.employees.indexOf(item);

          //Call delete User function
          this.deleteEmployee(data);

          // //Remove item from array services
          // this.employees.splice(index, 1);
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
      this.employee_files = [];

      let EmployeeInformationTabs = this.$refs.EmployeeInformationTabs
      EmployeeInformationTabs.clear();
      
    },

    closeImportDialog() {
      this.dialog_import = false;
    },

    clear() {
      this.editedItem = Object.assign({}, this.defaultItem);
    },
    
    openAttachFileDialog() {
      this.attach_file_dialog = true;
    },

    closeAttachFileDialog() {
      this.attach_file_dialog = false;
    },

    uploadFile(data) {
      let EmployeeInformationTabs = this.$refs.EmployeeInformationTabs;
      if(this.editedIndex > -1) //update mode
      {
        
        
        this.employees[this.editedIndex].files.push(data);
      }
      else
      {
        this.employee_files.push(data);
      }

      EmployeeInformationTabs.employee_files = this.employee_files;
      
      // this.employeeInformationComponentKey += 1;
      
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
       this.dialog_export = true;
      } else {
        this.showAlert("No record found", "warning")
      }
    },
    closeExportDialog() {
      this.dialog_export = false;
    },
    formatDate(date) {
      if (!date) return null;
      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },
    removeSelectedHeader(item) {
      let index = this.selectedTableHeaders.findIndex(header => header.value === item.value);
      this.selectedTableHeaders.splice(index, 1);
    },
    selectUnselect(status) {
      
    },
    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;

        if (
          action == "employee-master-data-create" ||
          action == "employee-master-data-edit" ||
          action == "employee-master-data-delete" ||
          action == "employee-master-data-import"
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
    tableHeaders() {
      
      let headers = [];
      
      this.selectedTableHeaders.forEach(value => {
        this.headers.forEach(header => {
          if(header.value == value.value)
          {
            headers.push(header);
          }
        }); 
      });

      headers.push({ text: "Actions", value: "actions", sortable: false, width: "80px" })
 
      return headers;
    },
    headerItems() {
      
      let headers = [];
      let selected_items = this.selectedTableHeaders;
      
      this.headers.forEach(header => {
    
        let disable = false;
        
        // if selectedTableHeaders has 1 item then disable item -- must be atleast 1 column for data datable
        if(selected_items.length == 1 && selected_items[0].value == header.value)
        { 
          disable = true;
          headers.push(Object.assign(header, { disable: disable }));
        }
        //disable all unselected items when selected items is equal to 9
        else if(selected_items.length == this.defaultTableHeaders.length)
        { 
          let index = selected_items.findIndex(item => item.value === header.value);  
          disable = index > -1 ? false : true;
        } 
        
        headers.push(Object.assign(header, { disable: disable }));
      });
      
      return headers;
    },
    activeStatus() {
      return this.editedItem.active ? 'Active' : 'Inactive';
    },
    // personalDataTabItem() {
    //   if (this.$refs.EmployeeInformationTabs) {
    //     let editedItem = this.$refs.EmployeeInformationTabs.editedItem;
    //     let fields = [  
    //       'employee_code',
    //       'last_name',
    //       'first_name',
    //       'birth_date',
    //       'address',
    //       'contact',
    //       'email',
    //       'gender',
    //       'civil_status',
    //       'tin_no',
    //       'pagibig_no',
    //       'philhealth_no',
    //       'sss_no',
    //       'educ_attain',
    //       'school_year',
    //       'school_attended',
    //       'course',
    //     ];

    //     let data = {};

    //     fields.forEach(field => {
    //       Object.assign(...data, editedItem[field])
    //     });

    //     return JSON.stringify(data);
    //   }
    //   else
    //   {
    //     return '';
    //   }
      
      
    // },
    // employeeDetailsTabItem() {
      
    //   if (this.$refs.EmployeeInformationTabs) {
    //     let editedItem = this.$refs.EmployeeInformationTabs.editedItem;
    //     let fields = [
    //       'branch_id',
    //       'branch',
    //       'company',
    //       'position_id',
    //       'position',
    //       'department',
    //       'department_id',
    //       'division',
    //       'rank',
    //       'date_employed',
    //       'date_resigned',
    //       'date_assigned',
    //       'length_of_service',
    //       'cost_center',
    //       'employment_type',
    //       'regularization_date',
    //       'active',
    //     ];

    //     let data = {};

    //     fields.forEach(field => {
    //       Object.assign(...data, this.editedItem[field]);
    //     });

    //     return JSON.stringify(data);
    //   }
    //   else
    //   {
    //     return '';
    //   }
      
    // },
    ...mapState("auth", ["user", "userIsLoaded"]),
    ...mapState("userRolesPermissions", ["userRolesPermissionsIsLoaded"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission", "hasAnyPermission"]),
  },

  watch: {
    selectAll() {
      if(this.selectAll)
      {
        this.selectedItems = this.employees;
      }
      else if(!this.indeterminate && !this.selectAll)
      {
        this.selectedItems = [];
      }
    },  
    selectedItems() {
      let selectedItemsID = this.selectedItems.map(value => value.id);
      let employeesID = this.employees.map(value => value.id);
      this.selectAll = false;
      this.indeterminate = false;
      if(selectedItemsID.length == employeesID.length && employeesID.length != 0)
      {
        this.selectAll = true;
      }
      else if(selectedItemsID.length > 0 && selectedItemsID.length != employeesID.length)
      {
        this.indeterminate = true;
      }

    },
    employees() {
      if(this.employees.length == 0)
      {
        this.selectAll = false;
        this.indeterminate = false;
      }
    },

    // employeeDetailsTabItem() {
    //   console.log('asdad');
      
    //   let editedItem = this.$refs.EmployeeInformationTabs.editedItem;
    //   let originalItem = this.$refs.EmployeeInformationTabs.originalItem;
    //   let fields = [  
    //     'employee_code',
    //     'last_name',
    //     'first_name',
    //     'birth_date',
    //     'address',
    //     'contact',
    //     'email',
    //     'gender',
    //     'civil_status',
    //     'tin_no',
    //     'pagibig_no',
    //     'philhealth_no',
    //     'sss_no',
    //     'educ_attain',
    //     'school_year',
    //     'school_attended',
    //     'course',
    //   ];

    //   let data = {};

    //   fields.forEach(field => {
    //     Object.assign(...data, editedItem[field])
    //   });

    //   let edited = this.editedItem;
    //   let original = this.originalItem;

    //   if(this.editedIndex > -1)
    //   {
    //     this.btnText = "OK";
    //     if(JSON.stringify(editedItem) != JSON.stringify(originalItem))
    //     {
    //       this.btnText = "Update"; 
    //     }
    //   }
      
    // },

  }, 

  mounted() {
    
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");

    this.defaultTableHeaders.forEach(value => {
      this.selectedTableHeaders.push(value)
    });

    this.getEmployee();
  },
};
</script>