<template>
  <div>
    <v-dialog v-model="dialog_import" max-width="500px" persistent>
      <v-card>
        <v-card-title class="pa-4">
          <span class="headline">Import Data</span>
        </v-card-title>
        <v-divider class="mt-0"></v-divider>
        <v-card-text>
          <v-container>
            <v-row v-if="docname != 'Employee Master Data'">
              <v-col>
                <v-menu
                  v-model="input_docdate"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  max-width="290px"
                  min-width="290px"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      name="docdate"
                      v-model="computedDocDate"
                      label="Document Date"
                      hint="MM/DD/YYYY"
                      persistent-hint
                      prepend-icon="mdi-calendar"
                      readonly
                      v-bind="attrs"
                      v-on="on"
                      :error-messages="docdateErrors"
                      @input="$v.docdate.$touch()"
                      @blur="$v.docdate.$touch()"
                    ></v-text-field>
                  </template>
                  <v-date-picker
                    v-model="docdate"
                    no-title
                    @input="input_docdate = false"
                    :max="maxDate"
                  ></v-date-picker>
                </v-menu>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="my-0 py-0">
                <v-file-input
                  v-model="file"
                  show-size
                  label="File input"
                  prepend-icon="mdi-paperclip"
                  required
                  :error-messages="fileErrors"
                  @change="$v.file.$touch() + (fileIsEmpty = false)"
                  @blur="$v.file.$touch()"
                  
                >
                  <template v-slot:selection="{ text }">
                    <v-chip small label color="primary">
                      {{ text }}
                    </v-chip>
                  </template>
                </v-file-input>
              </v-col>
            </v-row>
            <v-row
              class="fill-height"
              align-content="center"
              justify="center"
              v-if="uploading"
            >
              <v-col class="subtitle-1 text-center" cols="12">
                Uploading...
              </v-col>
              <v-col cols="6">
                <v-progress-linear
                  color="primary"
                  indeterminate
                  rounded
                  height="6"
                ></v-progress-linear>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
        <v-divider class="mb-3 mt-0"></v-divider>
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
            @click="uploadFile()"
            :disabled="uploadDisabled"
          >
            Upload
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
                      <th>#</th>
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
import { required, requiredIf, email } from "vuelidate/lib/validators";
import { mapState } from "vuex";
export default {
  
  name: "ImportDialog",
  props: [ 
    'importedData',
    'api_route',
    'dialog_import',
    'docname'
  ],
  mixins: [validationMixin],
  validations: {
    file: { required },
    docdate: { 
      requiredIf: function() {
        return this.docname != 'Employee Master Data';
      }
    },
  },
  data () {
    return {
      branch_id: "",
      docdate: new Date().toISOString().substr(0, 10),
      file: [],
      loading: true,
      uploadDisabled: false,
      uploading: false,
      dialog_error_list: false,
      errors_array: [],
      input_docdate: false,
    }
  },
  methods: {

    uploadFile() {
      this.$v.$touch();
      this.fileIsEmpty = false;
      this.fileIsInvalid = false;

      
      if (!this.$v.file.$error) {
        this.uploadDisabled = true;
        this.uploading = true;

        let formData = new FormData();

        formData.append("file", this.file);
        formData.append("docdate", this.docdate);

        axios
          .post(this.api_route, formData, {
            headers: {
              Authorization: "Bearer " + localStorage.getItem("access_token"),
              "Content-Type": "multipart/form-data",
            },
          })
          .then(
            (response) => {
              this.errors_array = [];
              let data = response.data;
              console.log(data);
              if (data.success) {
                // send data to Socket.IO Server
                // this.$socket.emit("sendData", { action: "import-project" });
                this.file_upload_log_id = data.file_upload_log_id;
                
                // Note: file_upload_log_id is for Employee List upload with document date, used for MSS compliance, uploading for monthly report.
                // disregard file_upload_log_id and leave empty value for uploading Employee Master Data

                this.$emit('getData', this.file_upload_log_id);
                this.$swal({
                  position: "center",
                  icon: "success",
                  title: "Record has been imported",
                  showConfirmButton: false,
                  timer: 2500,
                });
                this.$v.$reset();
                this.closeDialog();

              } else if (data.error_column) {
                this.errors_array = data.error_column;
                this.dialog_error_list = true;
              } else if (data.error_row_data) {
                console.log(data);
                let error_keys = Object.keys(data.error_row_data);
                let errors = data.error_row_data;
                let field_values = data.field_values;
                let row = "";
                let col = "";

                console.log(errors);
                console.log(error_keys);

                error_keys.forEach((value, index) => {
                  row = value.split(".")[0];
                  col = value.split(".")[1];
                  errors[value].forEach((val, i) => {
                    this.errors_array[index] =
                      "Error on row: <span class='text-info'>" +
                      (parseInt(row) + 1) +
                      "</span>; Column: <span class='text-primary'>" +
                      col +
                      "</span>; Msg: <span class='text-danger'>" +
                      val +
                      "</span>; Value: <span class='text-success'>" +
                      field_values[row][col] +
                      "</span>";
                  });
                });

                this.dialog_error_list = true;
              } else if (data.error_empty) {
                this.fileIsEmpty = true;
              } else {
                this.fileIsInvalid = true;
              }
              this.uploadDisabled = false;
              this.uploading = false;
            },
            (error) => {
              this.isUnauthorized(error);
              this.uploadDisabled = false;
              console.log(error);
              this.uploading = false;
            }
          );

        
      }
    },
    formatDate(date) {
      if (!date) return null;
      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },
    closeDialog() {
      this.$emit('closeImportDialog');
      this.docdate = new Date().toISOString().substr(0, 10);
      this.file = [];
      this.fileIsEmpty = false;
      this.uploadDisabled = false;
      this.uploading = false;
      this.$v.$reset();
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
      this.fileIsInvalid &&
        errors.push("File type must be 'xlsx', 'xls' or 'ods'.");
      return errors;
    },
    imported_file_errors() {
      return this.errors_array.sort();
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
      return date.toISOString().slice(0,10);
    },
  }
  
}

</script>
