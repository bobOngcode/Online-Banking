<template>
  <div>
    <v-dialog v-model="dialog" max-width="500px" persistent>
      <v-card>
        <v-card-title class="pa-4">
          <span class="headline">Attach file</span>
        </v-card-title>
        <v-divider class="mt-0"></v-divider>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col class="my-0 py-0">
                <v-autocomplete
                  v-model="document_type"
                  :items="document_types"
                  label="Document Type"
                  return-object
                  :error-messages="documentTypeErrors"
                  @input="$v.document_type.$touch()"
                  @blur="$v.document_type.$touch()"
                >
                </v-autocomplete>
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
                  @input="$v.file.$touch() + this.validateFile()"
                  @blur="$v.file.$touch()"
                >
                  <template v-slot:selection="{ index, text }">
                    <v-chip small label color="primary">
                      {{ text }}
                    </v-chip>
                  </template>
                </v-file-input>
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
            class="mb-4"
          >
            Close
          </v-btn>
          <v-btn
            color="primary"
            class="mb-3 mr-4"
            @click="uploadFile()"
          >
            Upload
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialog_uploading" max-width="500px" persistent>
      <v-card>
        <v-card-text>
          <v-container>
            <v-row
              class="fill-height"
              align-content="center"
              justify="center"
            >
              <v-col class="subtitle-1 font-weight-bold text-center mt-4" cols="12">
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
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
  import axios from "axios";
  import { required, requiredIf } from "vuelidate/lib/validators";
  import { validationMixin } from "vuelidate";
  export default {
    name: "AttachFileDialog",
    props: ['attachment_required', 'dialog', 'fileIsRequired', 'editedIndex', 'employee_id'],
    validations: {
      file: { required },
      document_type: { required },
      // file: {
      //   required: requiredIf(function () {
      //     return this.fileIsRequired;
      //   }),
      // },
    },
    mixins: [validationMixin],
    data() {
      return {
        file: [],
        document_type: "",
        document_types: [
          'Application Form',
          'Resume',
          'Copy of Grades',
          'Background Investigation',
          'Birth Certificate',
          'Exam',
          'Diploma',
          'Copy of Grades',
          'Police Clearance',
          'Health Declaration',
          'Contract of Employment',
          'Performance for Regularization',
          'Memo of Regularization',

        ],
        fileInvalid: false,
        dialog_uploading: false,
      }
    },
    methods: {
      uploadFile() {
        this.$v.$touch();
        
        if(!this.$v.$error)
        {
          if(this.editedIndex > -1 )// if mode is Edit then from parent component
          {
            this.dialog_uploading = true;
           
            const formData = new FormData();
            
            formData.append('file', this.file);
            formData.append('document_type', this.document_type);

            axios.post('/api/employee_master_data/file_upload/' + this.employee_id, formData, {
              headers: {
                Authorization: "Bearer " + localStorage.getItem("access_token"),
                "Content-Type": "multipart/form-data",
              }
            }).then(
              (response) => {
                this.dialog_uploading = false;
                let data = response.data;
                
                this.showAlert(data.success);
                this.$emit('uploadFile', data.file);
                this.closeDialog();
                this.resetData();
              },
              (error) => {
                this.dialog_uploading = false;
                console.log(error);
              }
            )
            
          
          }
          else
          {
            
            this.$emit('uploadFile', { file: this.file, document_type: this.document_type });
             
            this.closeDialog();
            this.resetData();
          }
        }

      },
      closeDialog() {
        this.$v.$reset();
        this.file = [];
        this.document_type = "";
        this.$emit('closeAttachFileDialog');
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
      removeFile(index, text) {
        this.file.splice(index, 1)
      },
      resetData() {
        this.$v.$reset();
        this.file = [];
        this.document_type = "";
      },

      validateFile() {
      
        let extensions = ['docs', 'docx', 'pdf', 'jpg', 'jpeg', 'png'];
      
        if(this.file)
        {
          if(this.file.name)
          {
            let split_arr = file.name.split('.');
            let split_ctr = split_arr.length;
            let extension = split_arr[split_ctr - 1].toLowerCase();
            
            if(!extensions.includes(extension))
            {
              this.fileInvalid = true;
            }

            if(this.file.size > 5000000) // 5000000 bytes or 5MB
            {
              this.fileInvalid = true;
            }
          }
        }
      },
    },
    computed: {
      fileErrors() {
        const errors = [];
        
        if (!this.$v.file.$dirty) return errors;
        !this.$v.file.required &&
          errors.push("Attachment is required!");

        let extensions = ['docs', 'docx', 'pdf', 'jpg', 'jpeg', 'png'];
        let errorMsg = "";
        let fileInvalid = false;
    
        if(this.file)
        {
          if(this.file.name)
          {
            
            let split_arr = this.file.name.split('.');
            let split_ctr = split_arr.length;
            let extension = split_arr[split_ctr - 1].toLowerCase();
            
            if(!extensions.includes(extension))
            {
              fileInvalid = true;
              errorMsg = `File type must be ${extensions.join(', ')}.`;
            }

            if(this.file.size > 5000000) // 5000000 bytes or 20MB
            {
              errorMsg = "File size maximum is 5MB";
              fileInvalid = true;
            }
          }
        }
        this.fileInvalid = fileInvalid;
        fileInvalid && errors.push(errorMsg);
        
        return errors;
        
      },
      documentTypeErrors() {
        const errors = [];
        
        if (!this.$v.document_type.$dirty) return errors;
        !this.$v.document_type.required &&
          errors.push("Document Type is required!");
        
        return errors;
        
      },
      btnLabel() {
        return this.file.length ? 'Upload' : 'OK';
      }
    }
  }
</script>
