<template>
  <div class="flex column">
    <div id="_wrapper" class="pa-5">
      <v-overlay :absolute="absolute" :value="overlay">
        <v-progress-circular
          :size="70"
          :width="7"
          color="primary"
          indeterminate
        ></v-progress-circular>
      </v-overlay>
      <v-main>
        <v-breadcrumbs :items="items">
          <template v-slot:item="{ item }">
            <v-breadcrumbs-item :to="item.link" :disabled="item.disabled">
              {{ item.text.toUpperCase() }}
            </v-breadcrumbs-item>
          </template>
        </v-breadcrumbs>
        <v-skeleton-loader
          v-if="loading"
          type="article, article, table"
        ></v-skeleton-loader>
        <v-card v-if="!loading">
          <v-card-title class="mb-0 pb-0">
            <span class="headline mr-2">TACTICAL REQUISITION</span>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="px-6 pt-0">
            <HeaderDetails 
              :user="user" 
              :marketing_events="marketing_events" 
              :branches="branches"
              :fileLength="fileLength"
              :fileError="fileError"
              @getMarketingEvent="getMarketingEvent"
              @openAttachFileDialog="openAttachFileDialog"
              ref="HeaderDetails"
            />
            <v-divider></v-divider>
            <ExpenseParticulars
              :expense_particulars="expense_particulars"
              ref="ExpenseParticulars"
            />
          </v-card-text>
          <v-divider class="mb-3 mt-0"></v-divider>
          <v-card-actions class="pl-6 pb-4">
            <v-btn
              color="primary"
              @click="save"
              :disabled="disabled"
            >
              Save
            </v-btn>
            <v-btn color="#E0E0E0" to="/"> Cancel </v-btn>
          </v-card-actions>
        </v-card>
        <AttachFileDialog 
          :dialog="dialog_attach_file" 
          :fileIsRequired="fileIsRequired" 
          :snackbar="snackbar"
          @closeAttachFileDialog="closeAttachFileDialog"
          ref="AttachFileDialog" 
        />
        <v-snackbar
          v-model="snackbar"
          color="error"
        >
          {{ fileError }}

          <template v-slot:action="{ attrs }">
            <v-btn
              color="white"
              text
              v-bind="attrs"
              @click="snackbar = false"
            >
              Close
            </v-btn>
          </template>
        </v-snackbar>
      </v-main>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import {
  required,
  requiredIf,
} from "vuelidate/lib/validators";
import TimeRangePicker from "vuetify-time-range-picker";
import { mapState } from "vuex";
import HeaderDetails from './components/HeaderDetails.vue';
import AttachFileDialog from './components/AttachFileDialog.vue';
import ExpenseParticulars from './components/ExpensePaticulars.vue';

export default {
  mixins: [validationMixin],
  components: {
    TimeRangePicker,
    HeaderDetails,
    AttachFileDialog,
    ExpenseParticulars,
  },
  data() {
    return {
      absolute: true,
      overlay: false,
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Create Tactical Requisition",
          disabled: true,
        },
      ],
      disabled: false,
      branches: [],
      marketing_events: [],
      expense_particulars: [],
      dialog_attach_file: false,
      fileLength: 0,
      fileError: "",
      fileIsRequired: false,
      snackbar: false,
      loading: false,
    };
  },

  methods: {
    getTacticalOptions() {
      this.loading = true;
      axios.get("/api/tactical_requisition/create").then(
        (response) => {
          this.branches = response.data.branches;
          this.marketing_events = response.data.marketing_events;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },
    async getMarketingEvent() {

      await this.resetAttachFileDialogComponent(); //reset onchange marketing event
      let HeaderDetails = await this.$refs.HeaderDetails;
      let ExpenseParticulars = await this.$refs.ExpenseParticulars;
      this.snackbar = false;
      this.expense_particulars = await HeaderDetails.editedItem.marketing_event.expense_particulars;
      ExpenseParticulars.getMarketingEvent();
    },
    
    save() {
      let HeaderDetails = this.$refs.HeaderDetails;
      let AttachFileDialog = this.$refs.AttachFileDialog;
      let ExpenseParticulars = this.$refs.ExpenseParticulars;

      HeaderDetails.$v.$touch();
      AttachFileDialog.$v.$touch();
      ExpenseParticulars.validateExpenseParticulars();
      this.snackbar = false;

      let expenseParticularHasError = ExpenseParticulars.expenseParticularHasError;
  
      // console.log('HeaderDetails.$v.$error', HeaderDetails.$v.$error);
      // console.log('AttachFileDialog.$v.$error', AttachFileDialog.$v.$error);
      // console.log('expenseParticularHasError', expenseParticularHasError);

      if (!HeaderDetails.$v.$error && !AttachFileDialog.$v.$error && !expenseParticularHasError) {
        this.disabled = true;
        this.overlay = true;

        axios.post("/api/tactical_requisition/store", this.formData, {
          headers: {
              Authorization: "Bearer " + localStorage.getItem("access_token"),
              "Content-Type": "multipart/form-data",
            }
        }).then(
          (response) => {
            console.log(response.data);
            if (response.data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "tactical-requisition-create" });

              this.showAlert();
              this.resetData();
            } else {
              let errors = response.data;
              let errorNames = Object.keys(response.data);
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

      // if AttachFileDialog Component has error
      if(AttachFileDialog.$v.file.$error)
      {
        this.snackbar = true;
        this.fileError = AttachFileDialog.fileErrors[0];
      }

    },
    resetData() {
      let HeaderDetails = this.$refs.HeaderDetails; // child component data
      let AttachFileDialog = this.$refs.AttachFileDialog; // child component data
      let ExpensePaticulars = this.$refs.ExpenseParticulars; // child component data

      HeaderDetails.resetData();
      AttachFileDialog.resetData();
      ExpensePaticulars.resetData();

      this.expense_particulars = [],
      this.fileLength = 0;
      this.fileIsRequired = false;

    },
    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },
    openAttachFileDialog() {
      this.dialog_attach_file = true;
    },
    closeAttachFileDialog() {
      let AttachFileDialog = this.$refs.AttachFileDialog;
      this.fileLength = AttachFileDialog.file.length; // get the file length from child component
      this.fileError = AttachFileDialog.fileErrors[0];
      this.dialog_attach_file = false;
    },
    resetAttachFileDialogComponent() {
      let AttachFileDialog = this.$refs.AttachFileDialog;
      let HeaderDetails = this.$refs.HeaderDetails;
      AttachFileDialog.$v.$reset();

      this.fileIsRequired = HeaderDetails.editedItem.marketing_event.attachment_required == 'Y' ? true : false;
      this.fileError = "";
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
  },
  computed: {
    formData(){
      let formData = new FormData();
      let HeaderDetails = this.$refs.HeaderDetails; // child component data
      let AttachFileDialog = this.$refs.AttachFileDialog; // child component data
      let ExpensePaticulars = this.$refs.ExpenseParticulars; // child component data

      const data =  {...HeaderDetails.editedItem,  ...ExpensePaticulars.editedItem, file: AttachFileDialog.file}; // merge all into 1 data

      let fieldName = Object.keys(data);
      let fieldValue;
      fieldName.forEach(field => {
        fieldValue = data[`${field}`];
        formData.append(field, JSON.stringify(fieldValue));

        if (field != 'file') {
          formData.append(field, JSON.stringify(fieldValue));
        }
        else
        {
          // create array formData for file
          fieldValue.forEach(val => {
            formData.append('file[]', val);
          });
          
        }
        
      });

      return formData;
    },
    // fileIsRequired(){
    //   let HeaderDetails = this.$refs.HeaderDetails;
      
    //   if(HeaderDetails) // if HeaderDetails component is rendered
    //   {
    //     return HeaderDetails.editedItem.marketing_event.attachment_required == 'Y' ? true : false;
    //   }
    //   else
    //   {
    //     return false;
    //   }
      
    // },
    ...mapState("auth", ["user"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");

    this.getTacticalOptions();
  },
};
</script>