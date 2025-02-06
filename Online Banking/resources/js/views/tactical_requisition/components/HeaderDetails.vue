<template>
  <div>
    <v-row>
      <v-col cols="8">
        <v-row>
          <v-col class="mb-0 py-0">
            <v-autocomplete
              v-model="editedItem.branch_id = user.branch_id"
              :items="branches"
              item-text="name"
              item-value="id"
              label="Branch"
              required
              :error-messages="branchErrors"
              @input="$v.editedItem.branch_id.$touch()"
              @blur="$v.editedItem.branch_id.$touch()"
              :readonly="user.id !== 1"
            >
            </v-autocomplete>
          </v-col>
          <v-col class="mb-0 py-0">
            <v-menu
              ref="menu"
              v-model="date_menu_date_submit"
              :close-on-content-click="true"
              :return-value.sync="date_menu_date_submit"
              transition="scale-transition"
              offset-y
              min-width="auto"
              disabled
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field
                  v-model="computedDateSubmitFormatted"
                  label="Date Submitted"
                  prepend-icon="mdi-calendar"
                  readonly
                  v-bind="attrs"
                  v-on="on"
                ></v-text-field>
              </template>
              <v-date-picker
                v-model="editedItem.date_submit"
                no-title
                scrollable
                readonly
                :max="date_now"
              >
              </v-date-picker>
            </v-menu>
          </v-col>
        </v-row>
        <v-row>
          <v-col class="my-0 py-0">
            <v-autocomplete
              v-model="editedItem.marketing_event"
              :items="marketing_events"
              item-text="event_name"
              item-value="id"
              label="Event Title"
              return-object
              required
              :error-messages="eventTitleErrors"
              @input="$v.editedItem.marketing_event.$touch()"
              @blur="$v.editedItem.marketing_event.$touch()"
              @change="getMarketingEvent()"
            >
            </v-autocomplete>
          </v-col>
          <v-col class="my-0 py-0">
            <v-text-field
              name="sponsor"
              v-model="editedItem.sponsor"
              :error-messages="sponsorErrors"
              label="Sponsor"
              @input="$v.editedItem.sponsor.$touch()"
              @blur="$v.editedItem.sponsor.$touch()"
            ></v-text-field>
          </v-col>
        </v-row>
        <v-row>
          <v-col class="my-0 py-0">
            <v-text-field
              name="venue"
              v-model="editedItem.venue"
              :error-messages="venueErrors"
              label="Venue"
              @input="$v.editedItem.venue.$touch()"
              @blur="$v.editedItem.venue.$touch()"
            ></v-text-field>
          </v-col>
        </v-row>
        <v-row>
          <v-col class="my-0 py-0">
            <v-row>
              <v-col>
                <v-menu
                  ref="menu"
                  v-model="date_menu_period_fr"
                  :close-on-content-click="true"
                  :return-value.sync="date_menu_period_fr"
                  transition="scale-transition"
                  offset-y
                  min-width="auto"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="computedPeriodFromFormatted"
                      label="Period From"
                      prepend-icon="mdi-calendar"
                      readonly
                      v-bind="attrs"
                      v-on="on"
                    ></v-text-field>
                  </template>
                  <v-date-picker
                    v-model="editedItem.period_from"
                    no-title
                    scrollable
                    :max="editedItem.period_to"
                  >
                  </v-date-picker>
                </v-menu>
              </v-col>
              <v-col>
                <v-menu
                  ref="menu"
                  v-model="date_menu_period_to"
                  :close-on-content-click="true"
                  :return-value.sync="date_menu_period_to"
                  transition="scale-transition"
                  offset-y
                  min-width="auto"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="computedPeriodToFormatted"
                      label="Period To"
                      prepend-icon="mdi-calendar"
                      readonly
                      v-bind="attrs"
                      v-on="on"
                    ></v-text-field>
                  </template>
                  <v-date-picker
                    v-model="editedItem.period_to"
                    no-title
                    scrollable
                    :min="editedItem.period_from"
                  >
                  </v-date-picker>
                </v-menu>
              </v-col>
            </v-row>
          </v-col>
          <v-col class="my-0 py-0">
            <v-row>
              <v-col>
                <v-autocomplete
                  v-model="editedItem.operating_from"
                  :items="timeOptions"
                  item-text="text"
                  item-value="value"
                  label="Operating From"
                  required
                  prepend-icon="mdi-clock"
                  :error-messages="hrFromErrors"
                  @input="$v.editedItem.operating_from.$touch()"
                  @blur="$v.editedItem.operating_from.$touch()"
                >
                </v-autocomplete>
              </v-col>
              <v-col>
                <v-autocomplete
                  v-model="editedItem.operating_to"
                  :items="timeOptions"
                  item-text="text"
                  item-value="value"
                  label="Operating To"
                  required
                  prepend-icon="mdi-clock"
                  :error-messages="hrToErrors"
                  @input="$v.editedItem.operating_to.$touch()"
                  @blur="$v.editedItem.operating_to.$touch()"
                >
                </v-autocomplete>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-col>
      <v-divider vertical></v-divider>
      <v-col>
        <div class="border-1">
          <p class="font-weight-bold subtitle-1">
            PLS ATTACH THE FOLLOWING:
          </p>
          <p class="font-weight-bold">1. AGING</p>
          <p class="mb-0 font-weight-bold">
            2. SUMMARY OF LEDGER PER CATEGORY
          </p>
          <p class="mt-0 font-italic font-weight-medium">
            for mobile, stall exhibit, and appliance tour
          </p>
          <p class="font-weight-bold">
            3. EXPLANATION LETTER
            <span class="font-weight-medium">
              if previous activity is below quota</span
            >
          </p>
          <p class="font-weight-bold">
            4. PICTURES OF PREVIOUS ACTIVITY STATED HEREIN
          </p>
        </div>
        <div class="d-flex justify-start">
          <v-btn color="primary" small @click="openAttachFileDialog()">
            <v-icon small>mdi-attachment</v-icon> Attach Files {{ fileLength ? '(' + fileLength + ')' : '' }}
          </v-btn> 
          <p class="ml-2 font-weight-bold font-italic red--text text--darken-1" v-if="fileError"> {{ fileError }} </p>
        </div>
      </v-col>
    </v-row>
    <v-divider></v-divider>
    <v-row>
      <v-col class="mb-0 py-0 mt-2">
        <p class="font-weight-bold subtitle-1">
          PREVIOUS TACTICAL ACTIVITY
        </p>
      </v-col>
    </v-row>
    <v-row>
      <v-col class="mb-0 py-0">
        <v-menu
          ref="menu"
          v-model="date_menu_prev_period_fr"
          :close-on-content-click="true"
          :return-value.sync="date_menu_prev_period_fr"
          transition="scale-transition"
          offset-y
          min-width="auto"
        >
          <template v-slot:activator="{ on, attrs }">
            <v-text-field
              v-model="computedPrevPeriodFromFormatted"
              label="Period From"
              prepend-icon="mdi-calendar"
              readonly
              v-bind="attrs"
              v-on="on"
            ></v-text-field>
          </template>
          <v-date-picker
            v-model="editedItem.prev_period_from"
            no-title
            scrollable
            :max="editedItem.prev_period_to"
          >
          </v-date-picker>
        </v-menu>
      </v-col>
      <v-col class="mb-0 py-0">
        <v-menu
          ref="menu"
          v-model="date_menu_prev_period_to"
          :close-on-content-click="true"
          :return-value.sync="date_menu_prev_period_to"
          transition="scale-transition"
          offset-y
          min-width="auto"
        >
          <template v-slot:activator="{ on, attrs }">
            <v-text-field
              v-model="computedPrevPeriodToFormatted"
              label="Period To"
              prepend-icon="mdi-calendar"
              readonly
              v-bind="attrs"
              v-on="on"
            ></v-text-field>
          </template>
          <v-date-picker
            v-model="editedItem.prev_period_to"
            no-title
            scrollable
            :min="editedItem.prev_period_from"
            :max="date_now"
          >
          </v-date-picker>
        </v-menu>
      </v-col>
      <v-col class="my-0 py-0">
        <v-text-field
          name="prev_venue"
          v-model="editedItem.prev_venue"
          label="Venue"
        ></v-text-field>
      </v-col>
      <v-col class="my-0 py-0">
        <v-text-field
          name="prev_sponsor"
          v-model="editedItem.prev_sponsor"
          label="Sponsor"
        ></v-text-field>
      </v-col>
    </v-row>
    <v-row>
      <v-col class="my-0 py-0">
        <v-text-field-dotnumber
          v-model="editedItem.prev_quota"
          label= 'Quota'
          v-bind:properties="{
            name: 'prev_quota',
            placeholder: '0',
            error: prevQuotaErrors.length ? true : false,
            messages: prevQuotaErrors,
          }"
          v-bind:options="{
            length: 16,
            precision: 0,
            empty: null,
            
          }"
        >
        </v-text-field-dotnumber>
      </v-col>
      <v-col class="my-0 py-0">
        <v-text-field-dotnumber
          v-model="editedItem.prev_total_sales"
          label= 'Total Sales'
          v-bind:properties="{
            name: 'prev_total_sales',
            placeholder: '0',
            error: prevTotalSalesErrors.length ? true : false,
            messages: prevTotalSalesErrors,
          }"
          v-bind:options="{
            length: 16,
            precision: 0,
            empty: null,
          }"
        >
        </v-text-field-dotnumber>
      </v-col>
      <v-col class="my-0 py-0">
        <v-text-field-dotnumber
          v-model="editedItem.prev_sales_achievement"
          label= 'Sales Achievement'
          v-bind:properties="{
            name: 'prev_sales_achievement',
            placeholder: '0',
            error: prevSalesAchvmntErrors.length ? true : false,
            messages: prevSalesAchvmntErrors,
          }"
          v-bind:options="{
            length: 16,
            precision: 0,
            empty: null,
          }"
        >
        </v-text-field-dotnumber>
      </v-col>
      <v-col class="my-0 py-0">
        <v-text-field-dotnumber
          v-model="editedItem.prev_total_expense"
          label= 'Total Expense'
          v-bind:properties="{
            name: 'prev_total_expense',
            placeholder: '0',
            error: prevTotalExpenseErrors.length ? true : false,
            messages: prevTotalExpenseErrors,
          }"
          v-bind:options="{
            length: 16,
            precision: 0,
            empty: null,
          }"
        >
        </v-text-field-dotnumber>
      </v-col>
    </v-row>
  </div>
</template>
<script>

  import { required } from "vuelidate/lib/validators";

  export default{
    name: "HeadetDetails",
    props: [
      'user',
      'branches',
      'marketing_events',
      'fileLength',
      'fileError',
    ],
    validations: {
      editedItem: {
        marketing_event: { required },
        marketing_event_id: { required },
        branch_id: { required },
        venue: { required },
        sponsor: { required },
        period_from: { required },
        period_to: { required },
        operating_from: { required },
        operating_to: { required },
        // file: {
        //   required: requiredIf(function () {
        //     return this.fileIsRequired;
        //   }),
        // },
      },
    },
    data() {
      return {
        date_now: new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().substr(0, 10),
        date_menu_period_fr: false,
        date_menu_period_to: false,
        date_menu_date_submit: false,
        date_menu_prev_period_fr: false,
        date_menu_prev_period_to: false,
        dialog_attach_file: false,
        snackbar: false,
        editedItem: {
          marketing_event: "",
          marketing_event_id: "",
          branch_id: "",
          venue: "",
          sponsor: "",
          expense_particulars: [],
          date_submit: new Date(
            Date.now() - new Date().getTimezoneOffset() * 60000
          )
            .toISOString()
            .substr(0, 10),
          period_from: new Date(
            Date.now() - new Date().getTimezoneOffset() * 60000
          )
            .toISOString()
            .substr(0, 10),
          period_to: new Date(Date.now() - new Date().getTimezoneOffset() * 60000)
            .toISOString()
            .substr(0, 10),
          operating_from: "08:00",
          operating_to: "08:00",
          prev_period_from: null,
          prev_period_to: null,
          prev_venue: "",
          prev_sponsor: "",
          prev_quota: "",
          prev_total_sales: "",
          prev_sales_achievement: "",
          prev_total_expense: "",
        },
        defaultItem: {
          marketing_event: "",
          marketing_event_id: "",
          branch_id: "",
          venue: "",
          sponsor: "",
          expense_particulars: [],
          date_submit: new Date(
            Date.now() - new Date().getTimezoneOffset() * 60000
          )
            .toISOString()
            .substr(0, 10),
          period_from: new Date(
            Date.now() - new Date().getTimezoneOffset() * 60000
          )
            .toISOString()
            .substr(0, 10),
          period_to: new Date(Date.now() - new Date().getTimezoneOffset() * 60000)
            .toISOString()
            .substr(0, 10),
          operating_from: "",
          operating_to: "",
          prev_period_from: null,
          prev_period_to: null,
          prev_venue: "",
          prev_sponsor: "",
          prev_quota: "",
          prev_total_sales: "",
          prev_sales_achievement: "",
          prev_total_expense: "",
        },
      }
    },
    methods: {
      getMarketingEvent() {
        // execute function getMarketingEvent from parent component
        this.$emit('getMarketingEvent');
      },
      openAttachFileDialog() {
        this.$emit('openAttachFileDialog');
      },
      formatDate(date) {
        if (!date) return null;

        const [year, month, day] = date.split("-");
        return `${month}/${day}/${year}`;
      },
      resetData() {
         this.$v.$reset();
         this.editedItem = Object.assign({}, this.defaultItem);
         this.editedItem.branch_id = this.user.branch_id
      },
    },
    computed: {
      eventTitleErrors() {
        const errors = [];
        if (!this.$v.editedItem.marketing_event.$dirty) return errors;
        !this.$v.editedItem.marketing_event.required &&
          errors.push("Event Title is required.");
        return errors;
      },
      branchErrors() {
        const errors = [];
        if (!this.$v.editedItem.branch_id.$dirty) return errors;
        !this.$v.editedItem.branch_id.required &&
          errors.push("Branch is required.");
        return errors;
      },
      venueErrors() {
        const errors = [];
        if (!this.$v.editedItem.venue.$dirty) return errors;
        !this.$v.editedItem.venue.required && errors.push("Venue is required.");
        return errors;
      },
      sponsorErrors() {
        const errors = [];
        if (!this.$v.editedItem.sponsor.$dirty) return errors;
        !this.$v.editedItem.sponsor.required &&
          errors.push("Sponsor is required.");
        return errors;
      },
      hrFromErrors() {
        const errors = [];
        if (!this.$v.editedItem.operating_from.$dirty) return errors;
        !this.$v.editedItem.operating_from.required &&
          errors.push("Select time.");
        return errors;
      },
      hrToErrors() {
        const errors = [];
        if (!this.$v.editedItem.operating_to.$dirty) return errors;
        !this.$v.editedItem.operating_to.required && errors.push("Select time.");
        return errors;
      },
      prevQuotaErrors(){
        let errors = [];
        if(!(this.editedItem.prev_quota % 1 >= 0))
        {
          errors.push("Invalid Amount");
        }
        return errors;
      },
      prevTotalSalesErrors(){
        let errors = [];
        if(!(this.editedItem.prev_total_sales % 1 >= 0))
        {
          errors.push("Invalid Amount");
        }
        return errors;
      },
      prevSalesAchvmntErrors(){
        let errors = [];
        if(!(this.editedItem.prev_sales_achievement % 1 >= 0))
        {
          errors.push("Invalid Amount");
        }
        return errors;
      },
      prevTotalExpenseErrors(){
        let errors = [];
        if(!(this.editedItem.prev_total_expense % 1 >= 0))
        {
          errors.push("Invalid Amount");
        }
        return errors;
      },
      computedPeriodFromFormatted() {
        return this.formatDate(this.editedItem.period_from);
      },
      computedPeriodToFormatted() {
        return this.formatDate(this.editedItem.period_to);
      },
      computedDateSubmitFormatted() {
        return this.formatDate(this.editedItem.date_submit);
      },
      computedPrevPeriodFromFormatted() {
        return this.formatDate(this.editedItem.prev_period_from);
      },
      computedPrevPeriodToFormatted() {
        return this.formatDate(this.editedItem.prev_period_to);
      },
      formData(){
        let formData = new FormData();
        
        // if user is not administrator then set the user_id with the id of current user
        if(this.user.id != 1)
        {
          this.editedItem.branch_id = this.user.branch_id;
        }

        const data = this.editedItem;
        let fieldName = Object.keys(data);
        let fieldValue;
        fieldName.forEach(field => {
          fieldValue = this.editedItem[`${field}`];
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
      fileIsRequired(){
        return this.editedItem.marketing_event.attachment_required == 'Y' ? true : false;
      },
      timeOptions() {
        let options = [];
        for (let hr = 1; hr <= 24; hr++) {
          let meridiem = hr < 12 || hr > 23 ? 'AM' : 'PM'; 
          let hour = hr > 12 ? hr - 12 : hr;
          let padStart = String(hr).padStart(2, "0") + ":00";
          
          options.push({ value: padStart, text: String(hour) + ":00 " + meridiem });

        }

        return options;
      },
    },
    watch: {
      "editedItem.marketing_event"() {
        this.editedItem.marketing_event_id = this.editedItem.marketing_event.id;
      },
      user() {
        this.editedItem.branch_id = this.user.branch_id;
      },
    },
  }

</script>
