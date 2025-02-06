<template>
  <div>
    <v-dialog v-model="dialog_import" max-width="500px" persistent>
      <v-card>
        <v-card-title class="pa-4">
          <span class="headline"> {{ actionLabel }} Data </span>
          <v-chip color="secondary" v-if="branch" class="ml-2"> {{ branch }} </v-chip>
        </v-card-title>
        <v-divider class="mt-0"></v-divider>
        <v-card-text>
          <v-container>
            <v-row v-if="user.id === 1">
              <v-col class="my-0 py-0">
                <v-autocomplete v-model="inventory_group" :items="inventory_groups" item-text="name" item-value="name"
                  label="Inventory Group">
                </v-autocomplete>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="my-0 py-0">
                <v-autocomplete v-model="inventory_type" :items="inventory_types" item-text="type" item-value="type"
                  label="Inventory Type" required :readonly="uploadDisabled" :error-messages="inventoryTypeErrors"
                  @input="$v.inventory_type.$touch()" @blur="$v.inventory_type.$touch()">
                </v-autocomplete>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="my-0 py-0">
                <v-autocomplete v-model="whse_code" :items="whse_codes" item-text="code" item-value="value"
                  label="Warehouse Code" required :readonly="uploadDisabled" :error-messages="whseCodeErrors"
                  @input="$v.whse_code.$touch()" @blur="$v.whse_code.$touch()">
                </v-autocomplete>
              </v-col>
            </v-row>
            <v-row>
              <v-col>
                <v-menu v-model="input_docdate" :close-on-content-click="false" transition="scale-transition" offset-y
                  max-width="290px" min-width="290px">
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field name="docdate" v-model="computedDocDate" label="Document Date" hint="MM/DD/YYYY"
                      persistent-hint prepend-icon="mdi-calendar" readonly v-bind="attrs" v-on="on"
                      :error-messages="docdateErrors" @input="$v.docdate.$touch()"
                      @blur="$v.docdate.$touch()"></v-text-field>
                  </template>
                  <v-date-picker v-model="docdate" no-title :readonly="uploadDisabled" @input="input_docdate = false"
                    :max="maxDate"></v-date-picker>
                </v-menu>
              </v-col>
            </v-row>
            <v-row v-if="action === 'sync'">
              <v-col class="my-0 py-0">
                <v-autocomplete v-model="database" :items="filteredDatabases" label="SAP Database"
                  prepend-icon="mdi-database" required :readonly="uploadDisabled" :error-messages="databaseErrors"
                  no-filter :search-input.sync="search" return-object @input="$v.database.$touch()"
                  @blur="$v.database.$touch()">
                  <template slot="selection" slot-scope="data">
                    {{ data.item.server + ' - ' + data.item.database }}
                  </template>
                  <template slot="item" slot-scope="data">
                    {{ data.item.server + ' - ' + data.item.database }}
                  </template>
                </v-autocomplete>
              </v-col>
            </v-row>
            <v-row v-if="action === 'import'">
              <v-col class="my-0 py-0">
                <v-file-input v-model="file" show-size label="File input" prepend-icon="mdi-paperclip" required
                  :disabled="uploadDisabled" :error-messages="fileErrors"
                  @change="$v.file.$touch() + (fileIsEmpty = false) + (fileIsInvalid = false)" @blur="$v.file.$touch()">
                  <template v-slot:selection="{ text }">
                    <v-chip small label color="primary">
                      {{ text }}
                    </v-chip>
                  </template>
                </v-file-input>
              </v-col>
            </v-row>
            <v-row class="fill-height" align-content="center" justify="center" v-if="uploading">
              <v-col class="subtitle-1 text-center" cols="12">
                {{ loadingLabel }}
              </v-col>
              <v-col cols="6">
                <v-progress-linear color="primary" indeterminate rounded height="6"></v-progress-linear>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
        <v-divider class="mb-3 mt-0"></v-divider>
        <v-card-actions class="pa-0">
          <v-spacer></v-spacer>
          <v-btn color="#E0E0E0" @click="closeDialog()" class="mb-3">
            Cancel
          </v-btn>
          <v-btn color="primary" class="mb-3 mr-4" @click="submit()" :disabled="uploadDisabled">
            {{ actionLabel }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialog_error_list" max-width="1000px" persistent>
      <v-card>
        <v-card-title class="pa-4">
          <span class="headline">Error List</span>
          <v-spacer></v-spacer>
          <v-btn @click="dialog_error_list = false" icon>
            <v-icon> mdi-close </v-icon>
          </v-btn>
        </v-card-title>
        <v-divider class="mt-0"></v-divider>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col>
                <v-simple-table dense>
                  <thead>
                    <tr>
                      <th width="10px">#</th>
                      <th>Error Message</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in imported_file_errors">
                      <td>{{ index + 1 }}</td>
                      <td v-html="item"></td>
                    </tr>
                  </tbody>
                </v-simple-table>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, requiredIf } from "vuelidate/lib/validators";
import { mapState, mapGetters } from "vuex";
export default {

  name: "ImportDialog",
  props: [
    'branch',
    'branch_id',
    'api_route',
    'dialog_import',
    'branches',
    'databases',
    'action',
    'whse_codes',
  ],

  mixins: [validationMixin],
  validations: {
    inventory_type: { required },
    file: {
      required: requiredIf(function () {
        return this.action === 'import';
      })
    },
    docdate: { required },
    database: {
      required: requiredIf(function () {
        return this.action === 'sync';
      })
    },
    whse_code: { required },
  },
  data() {
    return {
      docdate: new Date().toISOString().substr(0, 10),
      file: [],
      inventory_types: [{ type: "OVERALL" }, { type: "REPO" }],
      inventory_type: "OVERALL",
      database: "",
      loading: true,
      uploadDisabled: false,
      uploading: false,
      dialog_error_list: false,
      errors_array: [],
      input_docdate: false,
      whse_code: [],
      inventory_groups: [{ name: "Admin-Branch" }, { name: "Audit-Branch" }],
      inventory_group: "Admin-Branch",
      search: "",
    }
  },
  methods: {
    async submit() {

      await this.$v.$touch();

      if (!this.$v.$error) {
        this.uploadDisabled = true;
        this.uploading = true;
        if (this.action === 'import') {
          await this.uploadFile(); //upload file
        }
        else {
          await this.syncInventoryRecon(); //sync data from SAP
        }
      }

    },
    syncInventoryRecon() {
      this.syncIsClicked = true;

      const data = {
        branch_id: this.branch_id,
        database: this.database,
        docdate: this.docdate,
        inventory_group: this.inventory_group,
        inventory_type: this.inventory_type,
        whse_code: this.whse_code,
      };

      axios.post(this.api_route, data).then(
        (response) => {
          let data = response.data;

          this.uploadDisabled = false;
          this.uploading = false;

          if (data.error) {
            this.showErrorAlert('Error', data.error);
          }

          if (data.success) {
            // send data to Socket.IO Server
            // this.$socket.emit("sendData", { action: "import-project" });

            this.$emit('getData');
            this.$emit('closeImportDialog');

            this.showAlert(data.success, 'success');

            this.$v.$reset();

          } else if (data.empty) {

            this.showAlert(data.empty, 'warning');
          }

        },
        (error) => {
          console.log(error);
          this.uploadDisabled = false;
          this.uploading = false;
          this.showErrorAlert(error, error.response.data.message);
          this.isUnauthorized(error);

        }
      );
    },

    uploadFile() {

      // this.fileIsEmpty = false;
      // this.fileIsInvalid = false;

      let formData = new FormData();

      formData.append("file", this.file);
      formData.append("docdate", this.docdate);
      formData.append("inventory_group", this.inventory_group);
      formData.append("branch_id", this.branch_id);
      formData.append("inventory_type", this.inventory_type);
      formData.append("whse_code", this.whse_code);

      axios.post(this.api_route, formData, {
        headers: {
          Authorization: "Bearer " + localStorage.getItem("access_token"),
          "Content-Type": "multipart/form-data",
        },
      }).then(
        (response) => {
          console.log(response.data);
          this.errors_array = [];
          let data = response.data

          if (data.success) {
            // send data to Socket.IO Server
            // this.$socket.emit("sendData", { action: "import-project" });
            this.$emit('getData', data.file_upload_log_id);
            this.$emit('closeImportDialog');

            this.showAlert(data.success, 'success');

            this.$v.$reset();
            this.file = [];
            this.fileIsEmpty = false;
            this.fileIsInvalid = false;
          } else if (data.error_column) {
            this.errors_array = data.error_column;
            this.dialog_error_list = true;
          } else if (data.error_row_data) {
            let error_keys = Object.keys(data.error_row_data);
            let errors = data.error_row_data;
            let field_values = data.field_values;
            let row = "";
            let col = "";

            error_keys.forEach((value, index) => {
              row = value.split(".")[0];
              col = value.split(".")[1];
              errors[value].forEach((val, i) => {
                this.errors_array.push(
                  "Error on row: <label class='text-info'>" +
                  (parseInt(row) + 1) +
                  "</label>; Column: <label class='text-primary'>" +
                  col +
                  "</label>; Msg: <label class='text-danger'>" +
                  val +
                  "</label>; Value: <label class='text-success'>" +
                  field_values[row][col] +
                  "</label>"
                );
              });
            });

            this.dialog_error_list = true;
          } else if (data.error_empty) {
            this.fileIsEmpty = true;
          }
          else if (data.duplicate_serials) {
            let error_keys = Object.keys(data.duplicate_serials);
            let errors = data.duplicate_serials;

            error_keys.forEach(val => {
              this.errors_array.push(
                "Duplicate Serial # on row: <span class='text-info'>" +
                parseInt(val) +
                "</span>; Serial: <span class='text-danger'>" +
                errors[val] +
                "</span>"
              );
            });

            this.dialog_error_list = true;

          }
          else if (data.error) {
            this.showErrorAlert("ERROR!", data.error);
          }
          else {
            this.fileIsInvalid = true;
          }

          this.uploadDisabled = false;
          this.uploading = false;
        },
        (error) => {
          console.log(error);
          this.showErrorAlert("ERROR!", error);
          this.uploadDisabled = false;
        }
      );

    },
    formatDate(date) {
      if (!date) return null;
      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },
    closeDialog() {
      this.$emit('closeImportDialog');
      this.file = [];
      this.database = "";
      this.fileIsEmpty = false;
      this.uploadDisabled = false;
      this.uploading = false;
      this.fileIsInvalid = false;
      this.whse_code = "";
      this.$v.$reset();
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
    showErrorAlert(title, text) {
      this.$swal({
        position: "center",
        icon: "error",
        title: title,
        text: text,
        showConfirmButton: false,
        timer: 10000,
      });
    },
    reset() {

    },
    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },
  },
  computed: {
    fileErrors() {
      const errors = [];

      if (!this.$v.file.$dirty) return errors;
      !this.$v.file.required && errors.push("File is required.");
      this.fileIsEmpty && errors.push("File is empty.");

      if (this.file != null) {
        if (this.file.name) {
          let split_arr = this.file.name.split('.');
          let split_ctr = split_arr.length;
          let extension = split_arr[split_ctr - 1];
          let extensions = ['xls', 'xlxs', 'ods', 'csv'];

          if (!extensions.includes(extension)) {
            this.fileIsInvalid = true;
          }
        }

      }

      this.fileIsInvalid && errors.push("File type must be 'xlsx', 'xls' or 'ods'.");
      return errors;
    },
    inventoryTypeErrors() {
      const errors = [];
      if (!this.$v.inventory_type.$dirty) return errors;
      !this.$v.inventory_type.required && errors.push("Inventory Type is required.");
      return errors;
    },
    branchErrors() {
      const errors = [];
      if (!this.$v.branch_id.$dirty) return errors;
      !this.$v.branch_id.required && errors.push("Branch is required.");
      return errors;
    },
    databaseErrors() {
      const errors = [];
      if (!this.$v.database.$dirty) return errors;
      !this.$v.database.required && errors.push("SAP Database is required.");
      return errors;
    },
    whseCodeErrors() {
      const errors = [];
      if (!this.$v.whse_code.$dirty) return errors;
      !this.$v.whse_code.required && errors.push("Warehouse is required.");
      return errors;
    },
    imported_file_errors() {
      return this.errors_array.sort();
    },
    actionLabel() {
      return this.action === 'import' ? 'Upload' : 'Sync';
    },
    loadingLabel() {
      return this.action === 'import' ? 'Uploading...' : 'Syncing...';
    },
    computedDocDate() {
      return this.formatDate(this.docdate);
    },
    docdateErrors() {
      const errors = [];
      if (!this.$v.docdate.$dirty) return errors;
      !this.$v.docdate.required &&
        errors.push("Document Date is required.");
      return errors;
    },
    maxDate() {
      let date = new Date();
      return date.toISOString().slice(0, 10);
    },
    // getItemText(item) {
    //   return `${item.server} - ${item.database}`;
    // },
    filteredDatabases() {
      let search = this.search ? this.search.toLowerCase() : '';
      let databases = this.databases.filter((value) => {
        return value.database.toLowerCase().includes(search) || value.server.toLowerCase().includes(search);
      });

      // return default list this.databases if search is null;
      return search ? databases : this.databases;

    },
    ...mapState("auth", ["user"]),
    ...mapGetters("userRolesPermissions", ["hasAnyRole", "hasPermission"]),
  },
  watch: {
    dialog_import() { // everytime the dialog pops  
      // if branch has only 1 whse_code then auto select/assign value
      if (this.whse_codes.length) {
        this.whse_code = this.whse_codes[0].code;
      }
    },

  },

}

</script>
