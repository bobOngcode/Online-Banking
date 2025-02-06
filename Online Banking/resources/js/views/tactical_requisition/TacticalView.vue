<template>
  <div class="flex column">
    <div id="_wrapper" class="pa-5">
      <v-overlay :absolute="absolute" :value="overlay">
        <v-progress-circular :size="70" :width="7" color="primary" indeterminate></v-progress-circular>
      </v-overlay>
      <v-main>

        <v-breadcrumbs :items="items">
          <template v-slot:item="{ item }">
            <v-breadcrumbs-item :to="item.link" :disabled="item.disabled">
              {{ item.text.toUpperCase() }}
            </v-breadcrumbs-item>
          </template>
        </v-breadcrumbs>


        <v-skeleton-loader v-if="loading" type="article, article, table"></v-skeleton-loader>


        <v-card v-if="!loading">
          <v-card-title class="mb-0 pb-0">
            <span class="headline mr-2">TACTICAL REQUISITION </span>
            <template v-if="!editMode">
              <v-divider vertical class="mx-2"></v-divider>
              <v-btn icon color="primary" @click="printPreview()">
                <v-icon large>mdi-printer</v-icon>
              </v-btn>
            </template>
            <v-spacer></v-spacer>
            <v-chip :color="editedItem.status === 'Pending'
              ? 'warning'
              : editedItem.status === 'On Process'
                ? '#AB47BC'
                : editedItem.status === 'Approved'
                  ? 'success'
                  : 'error'
              " dark v-if="editedItem.status && !editMode">
              {{ editedItem.status }}
            </v-chip>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="px-6 pt-0">
            <v-row>
              <v-col cols="8">
                <v-row>
                  <v-col class="my-0 py-0">
                    <v-autocomplete v-model="editedItem.branch_id" :items="branches" item-text="name" item-value="id"
                      label="Branch" required readonly>
                    </v-autocomplete>
                  </v-col>
                  <v-col class="mb-0 py-0">
                    <v-menu ref="menu" v-model="date_menu_date_submit" :close-on-content-click="true"
                      :return-value.sync="date_menu_date_submit" transition="scale-transition" offset-y min-width="auto"
                      disabled>
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field v-model="computedDateSubmitFormatted" label="Date Submitted"
                          prepend-icon="mdi-calendar" readonly v-bind="attrs" v-on="on"></v-text-field>
                      </template>
                      <v-date-picker v-model="editedItem.date_submit" no-title scrollable :max="date_now" readonly>
                      </v-date-picker>
                    </v-menu>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col class="my-0 py-0">
                    <v-autocomplete v-model="editedItem.marketing_event_id" :items="marketing_events"
                      item-text="event_name" item-value="id" label="Event Title" required readonly>
                    </v-autocomplete>
                  </v-col>
                  <v-col class="my-0 py-0">
                    <v-text-field name="sponsor" v-model="editedItem.sponsor" :error-messages="sponsorErrors"
                      label="Sponsor" @input="$v.editedItem.sponsor.$touch()" @blur="$v.editedItem.sponsor.$touch()"
                      :readonly="isReadOnly"></v-text-field>
                  </v-col>
                </v-row>
                <v-row>

                  <v-col class="my-0 py-0">
                    <v-text-field name="venue" v-model="editedItem.venue" :error-messages="venueErrors" label="Venue"
                      @input="$v.editedItem.venue.$touch()" @blur="$v.editedItem.venue.$touch()"
                      :readonly="isReadOnly"></v-text-field>
                  </v-col>

                </v-row>
                <v-row>

                  <v-col class="my-0 py-0">
                    <v-row>
                      <v-col>
                        <v-menu ref="menu" v-model="date_menu_period_fr" :close-on-content-click="true"
                          :return-value.sync="date_menu_period_fr" transition="scale-transition" offset-y
                          min-width="auto" :disabled="isReadOnly">
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field v-model="computedPeriodFromFormatted" label="Period From"
                              prepend-icon="mdi-calendar" readonly v-bind="attrs" v-on="on"></v-text-field>
                          </template>
                          <v-date-picker v-model="editedItem.period_from" no-title scrollable
                            :max="editedItem.period_to">
                          </v-date-picker>
                        </v-menu>
                      </v-col>
                      <v-col>
                        <v-menu ref="menu" v-model="date_menu_period_to" :close-on-content-click="true"
                          :return-value.sync="date_menu_period_to" transition="scale-transition" offset-y
                          min-width="auto" :disabled="isReadOnly">
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field v-model="computedPeriodToFormatted" label="Period To"
                              prepend-icon="mdi-calendar" readonly v-bind="attrs" v-on="on"></v-text-field>
                          </template>
                          <v-date-picker v-model="editedItem.period_to" no-title scrollable
                            :min="editedItem.period_from">
                          </v-date-picker>
                        </v-menu>
                      </v-col>
                    </v-row>
                  </v-col>

                  <v-col class="my-0 py-0">
                    <v-row>
                      <v-col>
                        <v-autocomplete v-model="editedItem.operating_from" :items="timeOptions" item-text="text"
                          item-value="value" label="Operating From" required prepend-icon="mdi-clock"
                          :error-messages="hrFromErrors" @input="$v.editedItem.operating_from.$touch()"
                          @blur="$v.editedItem.operating_from.$touch()" :readonly="isReadOnly">
                        </v-autocomplete>
                      </v-col>
                      <v-col>
                        <v-autocomplete v-model="editedItem.operating_to" :items="timeOptions" item-text="text"
                          item-value="value" label="Operating To" required prepend-icon="mdi-clock"
                          :error-messages="hrToErrors" @input="$v.editedItem.operating_to.$touch()"
                          @blur="$v.editedItem.operating_to.$touch()" :readonly="isReadOnly">
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
                      if previous activity is below quota</span>
                  </p>
                  <p class="font-weight-bold">
                    4. PICTURES OF PREVIOUS ACTIVITY STATED HEREIN
                  </p>
                </div>
                <div class="d-flex justify-start">
                  <v-btn color="primary" small @click="openAttachment()">
                    <v-icon small>mdi-attachment</v-icon> Attach Files {{ attachmentLength }}
                  </v-btn>
                  <p class="ml-2 font-weight-bold font-italic red--text text--darken-1" v-if="fileIsRequired">
                    {{ fileErrors[0] }}
                  </p>
                </div>
              </v-col>
            </v-row>
            <v-divider></v-divider>
            <v-row>
              <v-col class="mb-0 py-0">
                <p class="font-weight-bold subtitle-1 mt-2">
                  PREVIOUS TACTICAL ACTIVITY
                </p>
              </v-col>
            </v-row>
            <v-row>

              <v-col class="mb-0 py-0">
                <v-menu ref="menu" v-model="date_menu_prev_period_fr" :close-on-content-click="true"
                  :return-value.sync="date_menu_prev_period_fr" transition="scale-transition" offset-y min-width="auto"
                  :disabled="isReadOnly">
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field v-model="computedPrevPeriodFromFormatted" label="Period From"
                      prepend-icon="mdi-calendar" :readonly="isReadOnly" v-bind="attrs" v-on="on"></v-text-field>
                  </template>
                  <v-date-picker v-model="editedItem.prev_period_from" no-title scrollable
                    :max="editedItem.prev_period_from">
                  </v-date-picker>
                </v-menu>
              </v-col>

              <v-col class="mb-0 py-0">
                <v-menu ref="menu" v-model="date_menu_prev_period_to" :close-on-content-click="true"
                  :return-value.sync="date_menu_prev_period_to" transition="scale-transition" offset-y min-width="auto"
                  :disabled="isReadOnly">
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field v-model="computedPrevPeriodToFormatted" label="Period To" prepend-icon="mdi-calendar"
                      readonly v-bind="attrs" v-on="on"></v-text-field>
                  </template>
                  <v-date-picker v-model="editedItem.prev_period_to" no-title scrollable
                    :max="editedItem.prev_period_to">
                  </v-date-picker>
                </v-menu>
              </v-col>

              <v-col class="my-0 py-0">
                <v-text-field name="prev_venue" v-model="editedItem.prev_venue" label="Venue"
                  :readonly="isReadOnly"></v-text-field>
              </v-col>

              <v-col class="my-0 py-0">
                <v-text-field name="prev_sponsor" v-model="editedItem.prev_sponsor" label="Sponsor"
                  :readonly="isReadOnly"></v-text-field>
              </v-col>

            </v-row>
            <v-row>

              <v-col class="my-0 py-0">
                <v-text-field-dotnumber v-model="editedItem.prev_quota" label='Quota (PHP)' v-bind:properties="{
                  name: 'prev_quota',
                  placeholder: '0',
                  error: prevQuotaErrors.length ? true : false,
                  messages: prevQuotaErrors,
                  readonly: isReadOnly
                }" v-bind:options="{
                  length: 16,
                  precision: 0,
                  empty: null,

                }">
                </v-text-field-dotnumber>
              </v-col>

              <v-col class="my-0 py-0">
                <v-text-field-dotnumber v-model="editedItem.prev_total_sales" label='Total Sales (PHP)'
                  v-bind:properties="{
                    name: 'prev_total_sales',
                    placeholder: '0',
                    error: prevTotalSalesErrors.length ? true : false,
                    messages: prevTotalSalesErrors,
                    readonly: isReadOnly
                  }" v-bind:options="{
                    length: 16,
                    precision: 0,
                    empty: null,
                  }">
                </v-text-field-dotnumber>
              </v-col>

              <v-col class="my-0 py-0">
                <v-text-field-dotnumber v-model="editedItem.prev_sales_achievement" label='Sales Achievement (PHP)'
                  v-bind:properties="{
                    name: 'prev_sales_achievement',
                    placeholder: '0',
                    error: prevSalesAchvmntErrors.length ? true : false,
                    messages: prevSalesAchvmntErrors,
                    readonly: isReadOnly
                  }" v-bind:options="{
                    length: 16,
                    precision: 0,
                    empty: null,
                  }">
                </v-text-field-dotnumber>
              </v-col>

              <v-col class="my-0 py-0">
                <v-text-field-dotnumber v-model="editedItem.prev_total_expense" label='Total Expense (PHP)'
                  v-bind:properties="{
                    name: 'prev_total_expense',
                    placeholder: '0',
                    error: prevTotalExpenseErrors.length ? true : false,
                    messages: prevTotalExpenseErrors,
                    readonly: isReadOnly
                  }" v-bind:options="{
                    length: 16,
                    precision: 0,
                    empty: null,
                  }">
                </v-text-field-dotnumber>
              </v-col>

            </v-row>
            <v-divider></v-divider>
            <v-row>
              <v-col>
                <v-simple-table>
                  <thead class="grey darken-1 white--text font-weight-bold">
                    <tr>
                      <td>PARTICULARS</td>
                      <td>RESOURCE PERSON</td>
                      <td>CONTACT DETAILS</td>
                      <td width="110px">QTY</td>
                      <td width="150px">UNIT COST (PHP)</td>
                      <td>AMOUNT (PHP)</td>
                    </tr>
                  </thead>
                  <tbody v-for="(item, index) in editedItem.expense_particulars">
                    <tr>
                      <td class="font-weight-bold border-0 pr-0">
                        {{ item.description }}
                        <v-btn class="mb-1" color="primary" icon @click="addItem(index)"
                          v-if="item.dynamic && !isReadOnly">
                          <v-icon>mdi-plus-circle</v-icon>
                        </v-btn>

                      </td>
                      <template v-if="item.expense_sub_particulars.length === 0">

                        <td class="border-0 pr-0">
                          <v-text-field name="resource_person" v-model="item.resource_person" dense hide-details
                            outlined @input="getFieldValue(item, '', 'resource_person')"
                            @blur="getFieldValue(item, '', 'resource_person')"
                            :error-messages="errorField(index, 'resource_person')"
                            :readonly="isReadOnly"></v-text-field>
                        </td>

                        <td class="border-0 pr-0">
                          <v-text-field name="contact" v-model="item.contact" dense hide-details outlined
                            @input="getFieldValue(item, '', 'contact')" @blur="getFieldValue(item, '', 'contact')"
                            :error-messages="errorField(index, 'contact')" :readonly="isReadOnly"></v-text-field>
                        </td>

                        <td class="border-0 pr-0">
                          <v-text-field-money class="pa-0" v-model="item.qty" v-bind:properties="{
                            name: 'qty',
                            placeholder: '0',
                            'hide-details': true,
                            outlined: true,
                            dense: true,
                            error: errorField(index, 'qty'),
                            messages: '',
                            readonly: isReadOnly
                          }" v-bind:options="{
                            length: 16,
                            precision: 0,
                            empty: null,
                          }" @input="getFieldValue(item, '', 'qty') + computeAmount()"
                            @blur="getFieldValue(item, '', 'qty') + computeAmount()">
                          </v-text-field-money>
                        </td>

                        <td class="border-0 pr-0">
                          <v-text-field-dotnumber class="pa-0" v-model="item.unit_cost" v-bind:properties="{
                            name: 'unit_cost',
                            placeholder: '0.00',
                            'hide-details': true,
                            outlined: true,
                            dense: true,
                            error: errorField(index, 'unit_cost'),
                            messages: '',
                            readonly: isReadOnly
                          }" v-bind:options="{
                            length: 11,
                            precision: 2,
                            empty: null,
                          }" @input="getFieldValue(item, '', 'unit_cost') + computeAmount()"
                            @blur="getFieldValue(item, '', 'unit_cost') + computeAmount()">
                          </v-text-field-dotnumber>
                        </td>

                        <td class="font-weight-bold border-0 pr-0">
                          {{ !item.amount ? "0.00" : Number(item.amount).toLocaleString('en', numOpts) }}
                        </td>

                      </template>
                    </tr>
                    <tr v-for="(subItem, i) in item.expense_sub_particulars">

                      <td class="border-0 pr-0">
                        <span class="ml-12" v-if="!['New', 'Editable'].includes(subItem.status)">{{ subItem.description
                          }}</span>
                        <div class="d-flex justify-content-end" v-if="['New', 'Editable'].includes(subItem.status)">
                          <v-btn color="error" icon class="mt-1" @click="confirmRemove('RowData', subItem)">
                            <v-icon>mdi-minus-circle</v-icon>
                          </v-btn>
                          <v-text-field name="description" v-model="subItem.description" dense hide-details outlined
                            @input="getFieldValue(item, subItem, 'description')"
                            @blur="getFieldValue(item, subItem, 'description')"
                            :error-messages="errorSubField(index, i, 'description')">
                          </v-text-field>
                        </div>
                      </td>

                      <td class="border-0 pr-0">
                        <v-text-field name="resource_person" v-model="subItem.resource_person" dense hide-details
                          outlined @input="getFieldValue(item, subItem, 'resource_person')"
                          @blur="getFieldValue(item, subItem, 'resource_person')"
                          :error-messages="errorSubField(index, i, 'resource_person')"
                          :readonly="isReadOnly"></v-text-field>
                      </td>

                      <td class="border-0 pr-0">
                        <v-text-field name="contact" v-model="subItem.contact" dense hide-details outlined
                          @input="getFieldValue(item, subItem, 'contact')"
                          @blur="getFieldValue(item, subItem, 'contact')"
                          :error-messages="errorSubField(index, i, 'contact')" :readonly="isReadOnly"></v-text-field>
                      </td>

                      <td class="border-0 pr-0">
                        <v-text-field-money class="pa-0" v-model="subItem.qty" v-bind:properties="{
                          name: 'qty',
                          placeholder: '0',
                          'hide-details': true,
                          outlined: true,
                          dense: true,
                          error: errorSubField(index, i, 'qty'),
                          messages: '',
                          readonly: isReadOnly
                        }" v-bind:options="{
                          length: 16,
                          precision: 0,
                          empty: null,
                        }" @input="getFieldValue(item, subItem, 'qty') + computeAmount()"
                          @blur="getFieldValue(item, subItem, 'qty') + computeAmount()">
                        </v-text-field-money>
                      </td>

                      <td class="border-0 pr-0">
                        <v-text-field-dotnumber class="pa-0" v-model="subItem.unit_cost" v-bind:properties="{
                          name: 'unit_cost',
                          placeholder: '0.00',
                          'hide-details': true,
                          outlined: true,
                          dense: true,
                          error: errorSubField(index, i, 'unit_cost'),
                          messages: '',
                          readonly: isReadOnly
                        }" v-bind:options="{
                          length: 11,
                          precision: 2,
                          empty: null,
                        }" @input="getFieldValue(item, subItem, 'unit_cost') + computeAmount()"
                          @blur="getFieldValue(item, subItem, 'unit_cost') + computeAmount()">
                        </v-text-field-dotnumber>
                      </td>

                      <td class="font-weight-bold border-0">
                        {{ !subItem.amount ? "0.00" : Number(subItem.amount).toLocaleString('en', numOpts) }}
                      </td>

                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="font-weight-black">
                      <td colspan="5" class="text-right">TOTAL AMOUNT</td>
                      <td>{{ Number(grand_total).toLocaleString('en', numOpts) }}</td>
                    </tr>
                  </tfoot>
                </v-simple-table>
              </v-col>
            </v-row>
            <template v-if="editedItem.status.toUpperCase() == 'DISAPPROVED' && !editMode">
              <v-divider></v-divider>
              <v-row>
                <v-col>
                  <span class="h6 font-weight-black">Disapprove Reason</span>
                </v-col>
              </v-row>
              <v-row>
                <v-col>
                  <v-textarea v-model="editedItem.remarks" outlined auto-grow readonly>
                  </v-textarea>
                </v-col>
              </v-row>
            </template>
          </v-card-text>
          <v-divider class="mb-3 mt-0"></v-divider>
          <v-card-actions class="pl-6 pb-4">
            <template v-if="['Pending', 'On Process'].includes(editedItem.status) && !isApproved">
              <v-btn color="primary" @click="updateTactical()" :disabled="disabled" v-if="!isReadOnly">
                <!-- v-if="hasPermission('tactical-requisition-edit') && !approved_logs.length" -->
                Save
              </v-btn>
              <v-btn color="success" @click="showConfirmAlert('approve')"
                v-if="hasPermission('tactical-requisition-approve')">
                Approve
              </v-btn>
              <v-btn color="error" @click="(dialog_remarks = true) + (action = 'disapprove')"
                v-if="hasPermission('tactical-requisition-approve')">
                Disapprove
              </v-btn>
              <v-btn color="error" @click="showConfirmAlert('delete')"
                v-if="hasPermission('tactical-requisition-delete')">
                Delete
              </v-btn>
              <v-btn color="error" @click="showConfirmAlert('cancel')"
                v-if="hasPermission('tactical-requisition-cancel')">
                Cancel
              </v-btn>
            </template>
            <template v-if="editedItem.status == 'Disapproved'">
              <v-btn color="primary" @click="editMode = true" :disabled="disabled" v-if="!editMode">
                Edit & Re-create
              </v-btn>
              <v-btn color="primary" @click="createTactical()" :disabled="disabled" v-if="editMode">
                Save & Re-create
              </v-btn>
            </template>

            <v-btn color="#E0E0E0" to="/tactical_requisition/index"> Cancel </v-btn>
          </v-card-actions>
        </v-card>


        <v-dialog v-model="dialog_attach_file" max-width="500px" persistent>
          <v-card>
            <v-card-title class="pa-4">
              <span class="headline">Attachments</span>
              <v-spacer></v-spacer>
              <v-btn @click="dialog_attach_file = false" icon>
                <v-icon> mdi-close-circle </v-icon>
              </v-btn>
            </v-card-title>
            <v-divider class="mt-0"></v-divider>
            <v-card-text>
              <v-container>
                <v-row v-if="tactical_attachments.length && !editMode">
                  <v-col class="my-0 py-0">
                    <v-simple-table class="elevation-1 file_table" dense>
                      <template v-slot:default>
                        <thead class="grey lighten-3 font-weight-bold">
                          <tr>
                            <th width="10px"> # </th>
                            <th> Uploaded Files </th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(item, i) in tactical_attachments" :key="item.id">
                            <td>{{ i + 1 }}</td>
                            <td>
                              <v-btn class="ma-0" small icon color="error" @click="confirmRemove('File', item)"
                                v-if="hasPermission('tactical-attachment-delete') && !isReadOnly && !approved_logs.length">
                                <v-icon> mdi-close-circle </v-icon>
                              </v-btn>
                              <v-btn x-small text class="blue--text text--darken-2 ma-0" @click="fileDownload(item)">
                                {{ item.file_name.length > 50 ? item.file_name.substr(0, 40) + "..." : item.file_name }}
                              </v-btn>
                            </td>
                          </tr>
                        </tbody>
                      </template>
                    </v-simple-table>
                    <!-- <v-list dense>
                      
                      <v-list-item-group>
                        <v-list-item v-for="item in tactical_attachments" :key="item.id" class="pa-0 ma-0">
                          <v-list-item-content class="pa-0 ma-0">
                            <v-list-item-title class="pa-0 ma-0"> 
                              <v-btn class="ma-0" icon color="error" @click="confirmRemove(item)" v-if="hasPermission('tactical-requisition-delete')">
                                <v-icon> mdi-close-circle </v-icon> 
                              </v-btn>
                              <v-btn x-small text class="blue--text text--darken-2 ma-0" @click="fileDownload(item)">
                                {{ item.file_name.length > 50 ? item.file_name.substr(0, 45) + "..." : item.file_name }}
                              </v-btn>
                            </v-list-item-title>
                          </v-list-item-content>
                        </v-list-item>
                      </v-list-item-group>
                    </v-list> -->
                  </v-col>
                </v-row>
                <template v-if="!isReadOnly">
                  <v-divider class="mt-6" v-if="tactical_attachments.length && !editMode"></v-divider>
                  <v-row v-if="!isApproved">
                    <v-col class="my-0 py-0">
                      <v-file-input v-model="editedItem.file" show-size label="File input" prepend-icon="mdi-paperclip"
                        required multiple>
                        <template v-slot:selection="{ index, text }">
                          <v-chip small label color="primary" close @click:close="removeFile(index, text)">
                            {{ text }}
                          </v-chip>
                        </template>
                      </v-file-input>
                    </v-col>
                  </v-row>
                </template>
              </v-container>
            </v-card-text>
            <v-divider class="mb-3 mt-0"></v-divider>
            <v-card-actions class="pa-0">
              <v-spacer></v-spacer>
              <!-- <v-btn
                color="#E0E0E0"
                @click="(dialog_attach_file = false)"
                class="mb-4"
              >
                Close
              </v-btn> -->
              <v-btn color="primary" class="mb-3 mr-4" @click="uploadFile()">
                {{ attachmentBtnLabel }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>


        <v-dialog v-model="dialog_remarks" max-width="500px" persistent>
          <v-card>
            <v-card-title class="pa-4">
              <span class="headline">Disapprove Reason</span>
              <v-spacer></v-spacer>
              <v-btn @click="dialog_attach_file = false" icon>
                <v-icon> mdi-close-circle </v-icon>
              </v-btn>
            </v-card-title>
            <v-divider class="mt-0"></v-divider>
            <v-card-text>
              <v-row>
                <v-col class="my-0 pt-2">
                  <v-textarea label="Remarks" v-model="editedItem.remarks" outlined :error-messages="remarksErrors"
                    @input="$v.editedItem.remarks.$touch()" @blur="$v.editedItem.remarks.$touch()">
                  </v-textarea>
                </v-col>
              </v-row>
            </v-card-text>
            <v-divider class="mb-3 mt-0"></v-divider>
            <v-card-actions class="pa-0">
              <v-spacer></v-spacer>
              <v-btn color="#E0E0E0" @click="(dialog_remarks = false) + (action = '')" class="mb-4">
                Close
              </v-btn>
              <v-btn color="primary" class="mb-3 mr-4" @click="submitDisapproval()">
                Save
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>


        <v-snackbar v-model="snackbar" color="error">
          {{ fileErrors[0] }}

          <template v-slot:action="{ attrs }">
            <v-btn color="white" text v-bind="attrs" @click="snackbar = false">
              Close
            </v-btn>
          </template>
        </v-snackbar>


        <PrintPreviewDialog :dialog="dialog_print_preview" :data="editedItem" @closePrintPreview="closePrintPreview" />

      </v-main>
    </div>
  </div>
</template>
<style>
.file_table th,
.file_table td {
  border: 1px solid #dddddd;
  border-bottom: 1px solid #dddddd;
}
</style>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import {
  required,
  requiredIf,
} from "vuelidate/lib/validators";
import TimeRangePicker from "vuetify-time-range-picker";
import { mapState, mapGetters } from "vuex";
import PrintPreviewDialog from './components/PrintPreviewDialog.vue';

export default {
  mixins: [validationMixin],
  components: {
    TimeRangePicker,
    PrintPreviewDialog
  },
  validations: {
    editedItem: {
      venue: { required },
      sponsor: { required },
      period_from: { required },
      period_to: { required },
      operating_from: { required },
      operating_to: { required },
      file: {
        required: requiredIf(function () {
          return this.fileIsRequired;
        }),
      },
      remarks: {
        required: requiredIf(function () {
          return this.remarksIsRequired;
        }),
      },
    },
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
          text: "Tactical Requisition Lists",
          disabled: false,
          link: "/tactical_requisition/index",
        },
        {
          text: "View Tactical Requisition",
          disabled: true,
        },
      ],
      disabled: false,
      branches: [],
      marketing_events: [],
      tactical_attachments: [],
      deletedRows: [],
      addedRows: [],
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
        status: "",
        remarks: "",
        file: [],
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
        status: "",
        remarks: "",
        file: [],
      },
      date_now: new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().substr(0, 10),
      grand_total: "0.00",
      errorFields: [],
      date_menu_period_fr: false,
      date_menu_period_to: false,
      date_menu_date_submit: false,
      date_menu_prev_period_fr: false,
      date_menu_prev_period_to: false,
      modal: false,
      expensePaticularHasError: false,
      numOpts: {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      },
      dialog_attach_file: false,
      tactical_requisition_id: null,
      approved_logs: [],
      snackbar: false,
      loading: true,
      action: "",
      editMode: false,
      dialog_remarks: false,
      dialog_print_preview: true,
    };
  },

  methods: {
    getTacticalRequisition() {
      this.loading = true;
      this.tactical_requisition_id = this.$route.params.tactical_requisition_id;

      const data = {
        tactical_requisition_id: this.tactical_requisition_id,
      };

      axios.post("/api/tactical_requisition/edit", data).then(
        (response) => {
          this.branches = response.data.branches;
          this.marketing_events = response.data.marketing_events;

          const data = response.data.tactical_requisitions;

          let headerData = {
            date_submit: "",
            branch_id: "",
            marketing_event: "",
            marketing_event_id: "",
            sponsor: "",
            venue: "",
            period_from: "",
            period_to: "",
            operating_from: "",
            operating_to: "",
            prev_period_from: "",
            prev_period_to: "",
            prev_venue: "",
            prev_sponsor: "",
            prev_quota: "",
            prev_total_sales: "",
            prev_sales_achievement: "",
            prev_total_expense: "",
            status: "",
            expense_particulars: "",
            remarks: "",
          };

          let fields = Object.keys(headerData);

          fields.forEach(field => {
            if (field == 'expense_particulars') {
              this.editedItem[field] = data['tactical_rows'];
            }
            else if (field == 'date_submit') {
              this.editedItem[field] = new Date(data['created_at']).toISOString().substr(0, 10);
            }
            else {
              this.editedItem[field] = field == 'expense_particulars' ? data['tactical_rows'] : data[field];
            }

          });

          data.marketing_event.expense_particulars.forEach(value => {
            this.editedItem.expense_particulars.forEach((val, i) => {
              if (value.description == val.description) {
                let dynamic = value.dynamic ? true : false;
                Object.assign(this.editedItem.expense_particulars[i], { dynamic: dynamic });
              }
            });
          });

          this.tactical_attachments = data.tactical_attachments;

          this.approved_logs = data.approved_logs;

          this.getMarketingEvent();
          this.computeAmount();
          this.loading = false;

        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },
    getMarketingEvent() {
      this.errorFields = [];
      this.expensePaticularHasError = false;
      let expense_particulars = this.editedItem.expense_particulars;
      let mktg_event_particulars = this.editedItem.marketing_event.expense_particulars;

      this.editedItem.expense_particulars = [];

      expense_particulars.forEach((value, index) => {
        this.editedItem.expense_particulars.push({
          tactical_requisition_row_id: value.id,
          description: value.description,
          resource_person: value.resource_person,
          contact: value.contact,
          qty: value.qty,
          unit_cost: value.unit_cost,
          amount: value.amount,
          expense_sub_particulars: [],
          dynamic: value.dynamic,
        });

        this.errorFields.push({
          resource_person: null,
          contact: null,
          qty: null,
          unit_cost: null,
          errorSubFields: [],
        });

        value.tactical_sub_rows.forEach((val, i) => {

          let exists = false;

          mktg_event_particulars.forEach(particular => {

            if (value.description === particular.description) {
              exists = false;

              particular.expense_sub_particulars.forEach(sub_particular => {
                if (val.description === sub_particular.description) {
                  exists = true;
                }
              });

            }
          });

          this.editedItem.expense_particulars[index]
            .expense_sub_particulars.push({
              parent_index: index,
              tactical_requisition_sub_row_id: val.id,
              description: val.description,
              resource_person: val.resource_person,
              contact: val.contact,
              qty: val.qty,
              unit_cost: val.unit_cost,
              amount: val.amount,
              status: !exists ? 'Editable' : '',
            });

          this.errorFields[index].errorSubFields.push({
            resource_person: null,
            contact: null,
            qty: null,
            unit_cost: null,
          });
        });
      });

    },

    updateTactical() {
      this.$v.$touch();

      this.validateExpenseParticulars();


      if (!this.$v.$error && !this.expensePaticularHasError) {
        this.showConfirmAlert('update');
      }

    },

    createTactical() {

      this.$v.$touch();

      if (!this.$v.$error) {
        this.$swal({
          title: "Re-create Tactical Requisition",
          icon: "question",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#6c757d",
          confirmButtonText: "CREATE",
          cancelButtonText: "CANCEL"
        }).then((result) => {

          if (result.value) {
            this.disabled = true;
            this.overlay = true;

            axios.post('/api/tactical_requisition/store', this.formData).then(
              (response) => {
                let data = response.data
                console.log(response);
                if (data.success) {

                  this.showAlert(data.success);
                  setTimeout(() => {
                    this.$router.push({ name: 'tactical.index' })
                  }, 500);

                }
              },
              (error) => {
                this.isUnauthorized(error);
              },
            )
            this.disabled = false;
            this.overlay = false;
          }

        });
      }

      if (this.$v.editedItem.file.$error) {
        this.snackbar = true;
      }

    },

    async submitTactical(action) {

      let data = { tactical_requisition_id: this.tactical_requisition_id, remarks: this.editedItem.remarks };
      let url = "/api/tactical_requisition/" + action;

      Object.assign(this.editedItem, { deletedRows: this.deletedRows });

      // if action is update then add parameter on url
      if (action == 'update') {
        url += '/' + this.tactical_requisition_id;
        data = this.editedItem;
      }

      axios.post(url, data).then(
        (response) => {
          let data = response.data
          console.log(response);
          if (data.success) {

            this.showAlert(data.success);
            this.dialog_remarks = false;
            if (action == 'delete') {
              setTimeout(() => {
                this.$router.push({ name: 'tactical.index' })
              }, 500);
            }
            else {
              this.loading = true;
              this.getTacticalRequisition();
            }
          }
        },
        (error) => {
          this.isUnauthorized(error);
        },
      )
      this.disabled = false;
      this.overlay = false;
    },
    submitDisapproval() {
      this.$v.editedItem.remarks.$touch();
      if (!this.$v.editedItem.remarks.$error) {
        this.showConfirmAlert('disapprove');
      }

    },
    uploadFile() {
      if (this.attachmentBtnLabel === 'Upload') {
        let formData = new FormData();

        this.editedItem.file.forEach(val => {
          formData.append('file[]', val);
        });

        axios.post("/api/tactical_requisition/add_file/" + this.tactical_requisition_id, formData, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem("access_token"),
            "Content-Type": "multipart/form-data",
          }
        }).then(
          (response) => {

            let data = response.data;
            console.log(response.data);

            if (data.success) {
              // send data to Sockot.IO Server
              // this.$socket.emit("sendData", { action: "tactical-requisition-edit" });
              this.tactical_attachments = data.tactical_attachments;
              this.editedItem.file = [];

              this.showAlert(data.success);

            } else {
              let errors = data;
              let errorNames = Object.keys(data);
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
      else {
        this.dialog_attach_file = false;
      }

    },

    deleteFile(item) {

      const data = { file_id: item.id };
      const index = this.tactical_attachments.indexOf(item);

      axios.post("/api/tactical_requisition/delete_file", data).then(
        (response) => {
          console.log(response);
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "tactical-requisition-edit" });

            //Remove item from array tactical_attachements
            this.tactical_attachments.splice(index, 1);

            this.showAlert(response.data.success);

          }
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );

    },

    showConfirmAlert(action) {
      this.action = action;
      let icon = 'question';
      let title = `${action.toUpperCase()} Tactital Requistion`;
      let text = action !== 'Update' ? "You won't be able to revert this!" : '';
      let confirmButtonColor = "#3085d6";
      let actionArr = ['delete', 'disapprove', 'dancel'];
      let btnText = action === 'Cancel' ? 'Proceed' : action;

      if (actionArr.includes(action)) {
        icon = 'warning';
        confirmButtonColor = "#d33";
      }

      this.$swal({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: confirmButtonColor,
        cancelButtonColor: "#6c757d",
        confirmButtonText: btnText.toUpperCase(),
        cancelButtonText: "CANCEL"
      }).then((result) => {

        if (result.value) {
          this.disabled = true;
          this.overlay = true;

          this.submitTactical(action);
        }

      });
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

    confirmRemove(item_type, item) {

      let title = item_type === 'File' ? 'Delete File' : 'Delete Row';

      this.$swal({
        title: title,
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Proceed!",
      }).then((result) => {
        // <--

        if (result.value) {
          // <-- if confirmed
          if (item_type === 'File') {
            this.deleteFile(item);
          }
          else {
            this.removeItem(item)
          }
        }
      });
    },

    fileDownload(item) {
      window.open(location.origin + "/api/tactical_requisition/attachment/download?id=" + item.id, "_blank");
    },

    addItem(index) {
      let item = this.editedItem.expense_particulars[index];
      let subItems = item.expense_sub_particulars;

      subItems.push({
        parent_index: index,
        expense_sub_particular_id: item.expense_sub_particular_id,
        description: "",
        resource_person: "",
        contact: "",
        qty: "",
        unit_cost: "",
        amount: "",
        status: "New",
      })

      this.errorFields[index].errorSubFields.push({
        description: null,
        resource_person: null,
        contact: null,
        qty: null,
        unit_cost: null,
      });

    },

    removeItem(item) {
      console.log(item);
      let id = item.tactical_requisition_sub_row_id;
      let index = item.parent_index;
      let subItem = this.editedItem.expense_particulars[index].expense_sub_particulars;
      let subIndex = subItem.indexOf(item);
      subItem.splice(subIndex, 1)

      // if id fied exists
      if (id) {
        this.deletedRows.push({ id: id });
      }

    },

    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.grand_total = "0.00";
    },

    getFieldValue(item, subItem, fieldName) {
      let expense_particulars = this.editedItem.expense_particulars;
      let index = expense_particulars.indexOf(item);
      let expense_particular = expense_particulars[index];
      let field_value = expense_particular[fieldName];
      let errorFields = this.errorFields[index];
      let error = "";

      // validate parent row if there is no child data
      if (!expense_particular.expense_sub_particulars.length) {
        if (!field_value) {
          error = "error";
        } else {
          // validate unit_cost if numeric
          if (fieldName == "unit_cost") {
            if (field_value % 1 >= 0) {
              error = null;
            } else {
              error = "error";
            }
          } else {
            error = null;
          }
        }
      }

      errorFields[fieldName] = error;

      // input for expense sub particulars
      if (subItem) {
        let expense_sub_particulars = expense_particulars[index]["expense_sub_particulars"];
        let subIndex = expense_sub_particulars.indexOf(subItem);
        let field_value = expense_sub_particulars[subIndex][fieldName];

        error = "";

        if (!field_value) {
          error = "error";
        } else {
          // validate unit_cost if numeric
          if (fieldName == "unit_cost") {
            if (field_value % 1 >= 0) {
              error = null;
            } else {
              error = "error";
            }
          } else {
            error = null;
          }
        }

        errorFields.errorSubFields[subIndex][fieldName] = error;

        expense_sub_particulars.forEach((value, index) => {

          // console.log('value', val);
          if (fieldName === 'description') {

            errorFields.errorSubFields[index][fieldName] = "";

            expense_sub_particulars.forEach((val, i) => {
              if (value.description === val.description && i != index) {
                errorFields.errorSubFields[index][fieldName] = "error";
              }
            });

          }

        });

      }
    },
    errorField(index, fieldName) {
      let errorField = this.errorFields[index];
      return errorField ? errorField[fieldName] : null;
    },
    errorSubField(index, subIndex, fieldName) {
      let errorField = this.errorFields[index].errorSubFields[subIndex];

      return errorField ? errorField[fieldName] : null;
    },

    computeAmount() {
      let expense_particulars = this.editedItem.expense_particulars;
      let grand_total = 0;
      let decimal_length = 2;

      expense_particulars.forEach((value, index) => {
        let qty = value.qty;
        let unit_cost = value.unit_cost;

        if (!qty) {
          qty = "0.00";
        }

        if (!unit_cost) {
          unit_cost = "0.00";
        }

        // if unit_cost has decimal then get the number of decimal places
        if (unit_cost.split(".")[1]) {
          decimal_length = unit_cost.split(".")[1].length;

          if (decimal_length === 1) {
            decimal_length = 2;
          }
        }

        let amount = qty * parseFloat(unit_cost);

        if (!amount) {
          amount = 0.0;
        }

        grand_total += amount;

        value.amount = amount.toFixed(decimal_length);

        // compute amount for expense sub particulars
        value.expense_sub_particulars.forEach((val, i) => {
          qty = val.qty;
          unit_cost = val.unit_cost;

          if (!qty) {
            qty = "0.00";
          }

          if (!unit_cost) {
            unit_cost = "0.00";
          }

          // if unit_cost has decimal then get the number of decimal places
          if (unit_cost.split(".")[1]) {
            decimal_length = unit_cost.split(".")[1].length;

            if (decimal_length === 1) {
              decimal_length = 2;
            }
          }

          let amount = qty * parseFloat(unit_cost);

          if (!amount) {
            amount = 0.0;
          }

          grand_total += amount;

          val.amount = amount.toFixed(decimal_length);
        });
      });

      // get the number of decimals of grand total
      if (String(grand_total).split(".")[1]) {
        decimal_length = String(grand_total).split(".")[1].length;
      } else {
        decimal_length = 2;
      }

      this.grand_total = grand_total.toFixed(decimal_length);
    },


    validateExpenseParticulars() {
      let expense_particulars = this.editedItem.expense_particulars;
      let object_names = "";

      expense_particulars.forEach((value, index) => {
        object_names = Object.keys(expense_particulars[index]);

        // exlude validation for 'dynamic'
        object_names.forEach((fieldName) => {
          if (fieldName != 'dynamic') {
            this.getFieldValue(value, "", fieldName);
          }
        });

        // validate expense sub particulars fields
        value.expense_sub_particulars.forEach((val, i) => {
          object_names = Object.keys(expense_particulars[index]);
          object_names.forEach((fieldName) => {

            // exclude validation for expense_sub_particulars and expense_particular_id object name
            let objArr = ['expense_sub_particulars', 'expense_particular_id', 'tactical_requisition_row_id', 'status', 'dynamic'];
            if (!objArr.includes(fieldName)) {
              this.getFieldValue(value, val, fieldName);
            }

          });
        });
      });

      this.expensePaticularHasError = false;

      // check/scan if field has error on errorFields variable
      this.errorFields.forEach((value, index) => {
        object_names = Object.keys(value);
        object_names.forEach((fieldName) => {
          if (this.errorFields[index][fieldName] == "error") {
            this.expensePaticularHasError = true;
          }
        });

        value.errorSubFields.forEach((val, i) => {
          object_names = Object.keys(value);
          object_names.forEach((fieldName) => {
            if (
              this.errorFields[index].errorSubFields[i][fieldName] == "error"
            ) {
              this.expensePaticularHasError = true;
            }
          });
        });
      });

    },
    openAttachment() {
      // if fields are readonly and no file attachment then show alert message, else open dialog
      if (this.isReadOnly && !this.attachmentLength) {
        this.$swal({
          position: "center",
          icon: "warning",
          title: "No File Attachment",
          showConfirmButton: false,
          timer: 2500,
        });
      }
      else {
        this.dialog_attach_file = true;
      }

    },
    printPreview() {
      this.dialog_print_preview = true;
    },
    closePrintPreview() {
      this.dialog_print_preview = false;
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
    removeFile(index, text) {
      this.editedItem.file.splice(index, 1)
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
    prevQuotaErrors() {
      let errors = [];
      if (!(this.editedItem.prev_quota % 1 >= 0)) {
        errors.push("Invalid Amount");
      }
      return errors;
    },
    prevTotalSalesErrors() {
      let errors = [];
      if (!(this.editedItem.prev_total_sales % 1 >= 0)) {
        errors.push("Invalid Amount");
      }
      return errors;
    },
    prevSalesAchvmntErrors() {
      let errors = [];
      if (!(this.editedItem.prev_sales_achievement % 1 >= 0)) {
        errors.push("Invalid Amount");
      }
      return errors;
    },
    prevTotalExpenseErrors() {
      let errors = [];
      if (!(this.editedItem.prev_total_expense % 1 >= 0)) {
        errors.push("Invalid Amount");
      }
      return errors;
    },
    fileErrors() {
      // if(!this.attachmentLength)
      // {
      //   return "Attachment is required!";
      // }

      const errors = [];

      if (!this.$v.editedItem.file.$dirty) return errors;
      !this.$v.editedItem.file.required &&
        errors.push("Attachment is required!");

      return errors;
    },
    remarksErrors() {
      const errors = [];
      if (!this.$v.editedItem.remarks.$dirty) return errors;
      !this.$v.editedItem.remarks.required &&
        errors.push("Remarks is required.");
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
    formData() {
      let formData = new FormData();

      const data = this.editedItem;
      let fieldName = Object.keys(data);
      let fieldValue;
      fieldName.forEach(field => {
        fieldValue = this.editedItem[`${field}`];
        formData.append(field, JSON.stringify(fieldValue));

        if (field != 'file') {
          formData.append(field, JSON.stringify(fieldValue));
        }
        else {
          // create array formData for file
          fieldValue.forEach(val => {
            formData.append('file[]', val);
          });

        }

      });

      return formData;
    },
    fileIsRequired() {
      return this.editedItem.marketing_event.attachment_required == 'Y' ? true : false;
    },
    remarksIsRequired() {
      return this.action == 'disapprove' ? true : false;
    },
    attachmentLength() {
      let form_file_length = this.editedItem.file.length;
      let tactical_attachment_length = this.tactical_attachments.length;

      // edit mode is true then get the file length of editedItem.file else sum the tactical attachments and editedItem.file
      let ctr = this.editMode ? form_file_length : form_file_length + tactical_attachment_length;
      let length = "";

      if (ctr) {
        length = `(${ctr})`;
      }

      return length;

    },
    attachmentBtnLabel() {
      // if editMode is true then default button label is 'OK' else if file has length then set to 'Upload' else 'OK'
      return this.editMode ? 'OK' : this.editedItem.file.length ? 'Upload' : 'OK';
    },
    isApproved() {
      let isApproved = false;

      // check if current user has already approved the tactical requisition
      this.approved_logs.forEach(value => {
        if (value.approver_id === this.user.id) {
          isApproved = true;
        }
      });

      return isApproved;
    },
    hasApprovedLogs() {

    },
    isReadOnly() {
      let status = this.editedItem.status;
      let hasPermission = this.hasPermission('tactical-requisition-edit');

      // return this.editedItem.status === 'Approved' || this.editedItem.status === 'Disapproved' || !this.hasPermission('tactical-requisition-edit');

      return (status != 'Pending' || !hasPermission) && !this.editMode;

    },
    btnText() {
      return editMode ? 'Save' : 'Edit & Re-create';
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
    ...mapState("auth", ["user"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },
  watch: {
    "editedItem.marketing_event"() {
      this.editedItem.marketing_event_id = this.editedItem.marketing_event.id;
    },
    user() {
      this.editedItem.branch_id = this.user.branch_id;
    },
    editMode() {
      // if editMode is true then reset file[] value
      if (this.editMode) {
        this.editedItem.date_submit = this.defaultItem.date_submit;
        this.editedItem.file = [];
      }
    }
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");

    this.getTacticalRequisition();
  },
};
</script>