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


        <v-skeleton-loader v-if="skeleton_loading"
          type="table-heading, table-thead, table-tbody, table-row, table-tfoot"
          :types="{ 'table-thead': 'heading@8', 'table-tbody': 'table-row-divider@5', 'table-row': 'table-cell@8' }">
        </v-skeleton-loader>


        <v-card v-if="!skeleton_loading">

          <v-card-title>
            Online Banking Requisition Lists
            <v-spacer></v-spacer>

            <v-text-field v-model="search" append-icon="mdi-magnify" label="Search" single-line
              v-if="hasPermission('online-banking-list')"></v-text-field>

            <template>
              <v-toolbar flat>
                <v-spacer></v-spacer>
                <v-btn color="primary" fab dark class="mb-2" @click="dialog = true"
                  v-if="hasPermission('online-banking-create')">
                  <v-icon>mdi-plus</v-icon>
                </v-btn>
              </v-toolbar>
            </template>

          </v-card-title>

          
          <!-- <v-toolbar class="m-3 mt-5" flat>
            <template>
              <v-tabs v-model="tab" bg-color="indigo-darken-2" color="black">
                <v-tabs-slider color="black"></v-tabs-slider>
                <v-tab v-for="(item, index) in navigations" :key="item">
                  <h6><b>{{ item }}</b></h6>
                </v-tab>
              </v-tabs>
            </template>
          </v-toolbar> -->


          <template>
            <v-toolbar class="m-3 mt-5" flat>
              <v-tabs v-model="tab" bg-color="indigo-darken-2" color="black">
                <v-tabs-slider color="black"></v-tabs-slider>
                <v-tab v-for="(item, index) in navigations" :key="item">
                  <h6><b>{{ item }}</b></h6>
                </v-tab>
              </v-tabs>
            </v-toolbar>
          </template>

          <v-tabs-items v-model="tab">

            <v-tab-item>

              <v-data-table class="p-3" :headers="headers" :items="PendingRequest" :loading="loading" :search="search">


                <template v-slot:[`item.count`]="{ item, index }">
                  {{ index + 1 }}
                </template>


                <template v-slot:[`item.submitted_at`]="{ item, index }">
                  {{ formatDate(item.submitted_at) }}
                </template>


                <template v-slot:item.progress="{ item }">
                  <template v-for="(item, index) in item.approval_progress">
                    <v-tooltip top
                      :color="item.status === 'Approved' ? 'success' : item.status === 'Disapproved' ? 'error' : ''"
                      v-if="item.approver.length">
                      <template v-slot:activator="{ on, attrs }">
                        <v-icon
                          :color="item.status === 'Approved' ? 'success' : item.status === 'Disapproved' ? 'error' : ''"
                          v-bind="attrs" v-on="on">
                          {{ item.status === 'Disapproved' ? 'mdi-close-circle' : 'mdi-checkbox-marked-circle' }}
                        </v-icon>
                      </template>
                      <span>{{ item.approver.join(', ') }}</span>
                    </v-tooltip>
                    <v-icon v-if="!item.approver.length">mdi-checkbox-marked-circle</v-icon>
                  </template>
                </template>


                <template v-slot:item.status="{ item }">
                  <v-chip :color="item.status === 'Pending'
                    ? 'warning'
                    : item.status === 'On Process'
                      ? '#AB47BC'
                      : item.status === 'Approved'
                        ? 'success'
                        : 'error'
                    " dark>
                    {{ item.status }}
                  </v-chip>
                </template>


                <template v-slot:item.updated_at="{ item }">
                  {{ formatDate(item.updated_at) }}
                </template>


                <template v-slot:item.actions="{ item }">
                  <v-icon small class="mr-2" color="info" @click="viewbankingrequest(item)"
                    v-if="hasAnyPermission('online-banking-edit', 'online-banking-approve')">
                    mdi-eye
                  </v-icon>
                </template>


              </v-data-table>

            </v-tab-item>



            <v-tab-item>

              <v-data-table class="p-3" :headers="headers" :items="OnprocessRequest" :loading="loading"
                :search="search">

                <template v-slot:[`item.count`]="{ item, index }">
                  {{ index + 1 }}
                </template>

                <template v-slot:[`item.submitted_at`]="{ item, index }">
                  {{ formatDate(item.submitted_at) }}
                </template>


                <template v-slot:item.progress="{ item }">
                  <template v-for="(item, index) in item.approval_progress">
                    <v-tooltip top
                      :color="item.status === 'Approved' ? 'success' : item.status === 'Disapproved' ? 'error' : ''"
                      v-if="item.approver.length">

                      <template v-slot:activator="{ on, attrs }">
                        <v-icon
                          :color="item.status === 'Approved' ? 'success' : item.status === 'Disapproved' ? 'error' : ''"
                          v-bind="attrs" v-on="on">
                          {{ item.status === 'Disapproved' ? 'mdi-close-circle' : 'mdi-checkbox-marked-circle' }}
                        </v-icon>
                      </template>

                      <span>{{ item.approver.join(', ') }}</span>
                    </v-tooltip>
                    <v-icon v-if="!item.approver.length">mdi-checkbox-marked-circle</v-icon>
                  </template>
                </template>


                <template v-slot:item.status="{ item }">
                  <v-chip :color="item.status === 'Pending'
                    ? 'warning'
                    : item.status === 'On Process'
                      ? '#AB47BC'
                      : item.status === 'Approved'
                        ? 'success'
                        : 'error'
                    " dark>
                    {{ item.status }}
                  </v-chip>
                </template>


                <template v-slot:item.updated_at="{ item }">
                  {{ formatDate(item.updated_at) }}
                </template>


                <template v-slot:item.actions="{ item }">
                  <v-icon small class="mr-2" color="info" @click="viewbankingrequest(item)"
                    v-if="hasAnyPermission('online-banking-edit', 'online-banking-approve')">
                    mdi-eye
                  </v-icon>
                </template>

                
              </v-data-table>

            </v-tab-item>



            <v-tab-item>

              <v-data-table class="p-3" :headers="headers" :items="ApprovedRequest" :loading="loading" :search="search">

                <template v-slot:[`item.count`]="{ item, index }">
                  {{ index + 1 }}
                </template>

                <template v-slot:[`item.submitted_at`]="{ item, index }">
                  {{ formatDate(item.submitted_at) }}
                </template>

                <template v-slot:item.progress="{ item }">
                  <template v-for="(item, index) in item.approval_progress">

                    <v-tooltip top
                      :color="item.status === 'Approved' ? 'success' : item.status === 'Disapproved' ? 'error' : ''"
                      v-if="item.approver.length">

                      <template v-slot:activator="{ on, attrs }">
                        <v-icon
                          :color="item.status === 'Approved' ? 'success' : item.status === 'Disapproved' ? 'error' : ''"
                          v-bind="attrs" v-on="on">
                          {{ item.status === 'Disapproved' ? 'mdi-close-circle' : 'mdi-checkbox-marked-circle' }}
                        </v-icon>
                      </template>
                      <span>{{ item.approver.join(', ') }}</span>
                    </v-tooltip>

                    <v-icon v-if="!item.approver.length">mdi-checkbox-marked-circle</v-icon>
                  </template>
                </template>

                <template v-slot:item.status="{ item }">
                  <v-chip :color="item.status === 'Pending'
                    ? 'warning'
                    : item.status === 'On Process'
                      ? '#AB47BC'
                      : item.status === 'Approved'
                        ? 'success'
                        : 'error'
                    " dark>
                    {{ item.status }}
                  </v-chip>
                </template>

                <template v-slot:item.updated_at="{ item }">
                  {{ formatDate(item.updated_at) }}
                </template>

                <template v-slot:item.actions="{ item }">
                  <v-icon small class="mr-2" color="info" @click="viewbankingrequest(item)"
                    v-if="hasAnyPermission('online-banking-edit', 'online-banking-approve')">
                    mdi-eye
                  </v-icon>
                </template>

              </v-data-table>
              
            </v-tab-item>

            <v-tab-item>
              <v-data-table class="p-3" :headers="headers" :items="DisapprovedRequest" :loading="loading"
                :search="search">

                <template v-slot:[`item.count`]="{ item, index }">
                  {{ index + 1 }}
                </template>

                <template v-slot:[`item.submitted_at`]="{ item, index }">
                  {{ formatDate(item.submitted_at) }}
                </template>

                <template v-slot:item.progress="{ item }">
                  <template v-for="(item, index) in item.approval_progress">

                    <v-tooltip top
                      :color="item.status === 'Approved' ? 'success' : item.status === 'Disapproved' ? 'error' : ''"
                      v-if="item.approver.length">

                      <template v-slot:activator="{ on, attrs }">
                        <v-icon
                          :color="item.status === 'Approved' ? 'success' : item.status === 'Disapproved' ? 'error' : ''"
                          v-bind="attrs" v-on="on">
                          {{ item.status === 'Disapproved' ? 'mdi-close-circle' : 'mdi-checkbox-marked-circle' }}
                        </v-icon>
                      </template>
                      <span>{{ item.approver.join(', ') }}</span>
                    </v-tooltip>

                    <v-icon v-if="!item.approver.length">mdi-checkbox-marked-circle</v-icon>
                  </template>
                </template>

                <template v-slot:item.status="{ item }">
                  <v-chip :color="item.status === 'Pending'
                    ? 'warning'
                    : item.status === 'On Process'
                      ? '#AB47BC'
                      : item.status === 'Approved'
                        ? 'success'
                        : 'error'
                    " dark>
                    {{ item.status }}
                  </v-chip>
                </template>

                <template v-slot:item.updated_at="{ item }">
                  {{ formatDate(item.updated_at) }}
                </template>

                <template v-slot:item.actions="{ item }">
                  <v-icon small class="mr-2" color="info" @click="viewbankingrequest(item)"
                    v-if="hasAnyPermission('online-banking-edit', 'online-banking-approve')">
                    mdi-eye
                  </v-icon>
                </template>

              </v-data-table>
            </v-tab-item>



          </v-tabs-items>

        </v-card>


        <v-dialog v-model="dialog" max-width="1000px" max-height="100%" persistent>
          <v-card>

            <v-card-title>

              {{ formtitle }}

              <v-toolbar flat v-if="user_approve_lvl === 1 && status === 'Approved' || status === 'Pending'">
                <v-icon @click="download()" color="black">mdi-file-document</v-icon>
              </v-toolbar>

              <v-spacer></v-spacer>

              <v-btn icon @click="dialog = false + close()">
                <v-icon>mdi-close</v-icon>
              </v-btn>

            </v-card-title>


            <v-divider class="mb-3 mt-3"></v-divider>

            <v-card-text id="card">

              <center>
                <h4 class="mt-5">Online Banking User Maintenance & Instruction Form</h4>
              </center>

              <template>
                <v-container class="p-3" fluid>
                  <v-row>
                    <v-col :cols="12" :md="12">

                      <p :class="{ 'text-danger': banking_formErrors + bankingError.banking_form }">
                        Request Type *
                      </p>

                      <p class="text-danger"><i>{{ banking_formErrors + bankingError.banking_form }}</i></p>

                      <v-row>

                        <v-col :cols="12" :md="3">
                          <v-checkbox v-model="form_request.NEWMAKER"
                            @click="maker() + $v.form_request.banking_form.$touch() + (bankingError.banking_form = [])"
                            :disabled="disabled_maker" :error="!!(banking_formErrors + bankingError.banking_form)"
                            label="New Maker"></v-checkbox>
                        </v-col>


                        <v-col :cols="12" :md="3">
                          <v-checkbox v-model="form_request.NEWVERIFIER"
                            @click="verifier() + $v.form_request.banking_form.$touch() + (bankingError.banking_form = [])"
                            :disabled="disabled_verifier" :error="!!(banking_formErrors + bankingError.banking_form)"
                            label="New Verifier"></v-checkbox>
                        </v-col>


                        <v-col :cols="12" :md="3">
                          <v-checkbox v-model="form_request.DELETION"
                            @click="deletion() + $v.form_request.banking_form.$touch() + (bankingError.banking_form = [])"
                            :disabled="disabled_deletion" :error="!!(banking_formErrors + bankingError.banking_form)"
                            label="For Deletion"></v-checkbox>
                        </v-col>


                        <v-col :cols="12" :md="3">
                          <v-text-field label="Date Submitted *" prepend-icon="mdi-calendar"
                            v-model="form_request.date_now" :error-messages="date_nowErrors + bankingError.date_now"
                            @input="$v.form_request.date_now.$touch() + (bankingError.date_now = [])"
                            @blur="$v.form_request.date_now.$touch()" readonly></v-text-field>
                        </v-col>


                      </v-row>

                    </v-col>
                  </v-row>
                </v-container>

              </template>


              <template>
                <hr>
                <center>
                  <h5>USER INFORMATION</h5>
                </center>

                <v-container fluid class="p-5">
                  <v-row>

                    <v-col :cols="12" :md="4">
                      <v-text-field v-model="form_request.company" :readonly="readonly"
                        :error-messages="companyErrors + bankingError.company"
                        @input="$v.form_request.company.$touch() + (bankingError.company = [])"
                        @blur="$v.form_request.company.$touch()" name="name" label="Company *">
                      </v-text-field>
                    </v-col>

                    <v-col :cols="12" :md="4">
                      <v-text-field v-model="form_request.position" :readonly="readonly"
                        :error-messages="positionErrors + bankingError.position"
                        @input="$v.form_request.position.$touch() + (bankingError.position = [])"
                        @blur="$v.form_request.position.$touch()" name="name" label="Position *">
                      </v-text-field>
                    </v-col>

                    <v-col :cols="12" :md="4">
                      <v-text-field v-model="form_request.name" :error-messages="nameErrors + bankingError.name"
                        :readonly="readonly" @input="$v.form_request.name.$touch() + (bankingError.name = [])"
                        @blur="$v.form_request.name.$touch()" name="name" label="Name *">
                      </v-text-field>
                    </v-col>

                    <v-col :cols="12" :md="4" v-if="!form_request.DELETION">
                      <v-text-field v-model="form_request.birthdate" :readonly="readonly"
                        :error-messages="birthdateErrors + bankingError.birthdate"
                        @input="$v.form_request.birthdate.$touch() + (bankingError.birthdate = [])"
                        @blur="$v.form_request.birthdate.$touch()" type="date" name="name" label="Birhtdate *">
                      </v-text-field>
                    </v-col>

                    <v-col :cols="12" :md="4" v-if="!form_request.DELETION">
                      <v-text-field type="email" v-model="form_request.email" :readonly="readonly"
                        :error-messages="emailErrors + bankingError.email"
                        @input="$v.form_request.email.$touch() + (bankingError.email = [])"
                        @blur="$v.form_request.email.$touch()" name="name" label="Email Address *">
                      </v-text-field>
                    </v-col>

                    <v-col :cols="12" :md="4" v-if="!form_request.DELETION">
                      <v-text-field type="number" v-model="form_request.contact" :readonly="readonly"
                        :error-messages="contactErrors + bankingError.contact"
                        @input="$v.form_request.contact.$touch() + (bankingError.contact = [])"
                        @blur="$v.form_request.contact.$touch()" name="name" label="Cellphone # *">
                      </v-text-field>
                    </v-col>

                    <v-col :cols="12" :md="4" v-if="!form_request.DELETION">
                      <v-text-field type="number" v-model="form_request.tin" :readonly="readonly"
                        :error-messages="tinErrors + bankingError.tin"
                        @input="$v.form_request.tin.$touch() + (bankingError.tin = [])"
                        @blur="$v.form_request.tin.$touch()" name="name" label="TIN # *">
                      </v-text-field>
                    </v-col>

                    <v-col :cols="12" :md="4" v-if="!form_request.DELETION">
                      <v-text-field type="number" v-model="form_request.sss" :readonly="readonly"
                        :error-messages="sssErrors + bankingError.sss"
                        @input="$v.form_request.sss.$touch() + (bankingError.sss = [])"
                        @blur="$v.form_request.sss.$touch()" name="name" label="SSS # *">
                      </v-text-field>
                    </v-col>

                    <v-col :cols="12" :md="4" v-if="!form_request.DELETION">
                      <v-text-field type="number" v-model="form_request.philhealth" :readonly="readonly"
                        :error-messages="philhealthErrors + bankingError.philhealth"
                        @input="$v.form_request.philhealth.$touch() + (bankingError.philhealth = [])"
                        @blur="$v.form_request.philhealth.$touch()" name="name" label="PHILHEALTH # *">
                      </v-text-field>
                    </v-col>

                  </v-row>
                </v-container>

              </template>


              <template v-if="!form_request.DELETION">
                <hr>
                <center>
                  <h5>UPDATE</h5>
                </center>

                <v-container fluid class="p-5">
                  <v-row>

                    <v-col :cols="12" :md="6">
                      <v-autocomplete name="type" v-model="form_request.type" :readonly="readonly" item-text="text"
                        item-value="id" label="Type *" chips :items="distinationtype">
                        <template v-slot:selection="data">
                          <v-chip color="secondary" v-bind="data.attrs" :input-value="data.selected"
                            @click="data.select">
                            {{ data.item.text }}
                          </v-chip>
                        </template>
                      </v-autocomplete>
                    </v-col>

                    <!-- <v-col :cols="12":md="8" v-if="form_request.type !== 'ACCOUNT #'">
                    </v-col> -->

                    <v-col :cols="12" v-if="form_request.type === 'ACCOUNT #'" :md="3">
                      <v-text-field v-model="form_request.account_from" :readonly="readonly"
                        :error-messages="account_fromErrors + bankingError.account_from"
                        @input="$v.form_request.account_from.$touch() + (bankingError.account_from = [])"
                        @blur="$v.form_request.account_from.$touch()" type="number" name="number"
                        label="From Account # *">
                      </v-text-field>
                    </v-col>

                    <v-col :cols="12" v-if="form_request.type === 'ACCOUNT #'" :md="3">
                      <v-text-field v-model="form_request.account_to" :readonly="readonly"
                        :error-messages="account_toErrors + bankingError.account_to"
                        @input="$v.form_request.account_to.$touch() + (bankingError.account_to = [])"
                        @blur="$v.form_request.account_to.$touch()" type="number" name="number" label="To Account # *">
                      </v-text-field>
                    </v-col>

                    <v-col :cols="12" :md="6">
                      <p :class="{ 'text-danger': bankErrors + bankingError.bank }">Account Type *</p>
                      <p class="text-danger"><i>{{ bankErrors + bankingError.bank }}</i></p>
                      <v-row>

                        <v-col :cols="12" :md="3">
                          <v-checkbox v-model="form_request.bdo_type" :readonly="readonly"
                            @click="account_type() + $v.form_request.bank.$touch() + (bankingError.bank = [])"
                            :disabled="bank_bdo" :error="!!(bankErrors + bankingError.bank)" label="BDO"></v-checkbox>
                        </v-col>

                        <v-col :cols="12" :md="4">
                          <v-checkbox v-model="form_request.mbtc_type" :readonly="readonly"
                            @click="account_type() + $v.form_request.bank.$touch() + (bankingError.bank = [])"
                            :disabled="bank_mbtc" :error="!!(bankErrors + bankingError.bank)" label="MBTC"></v-checkbox>
                        </v-col>

                        <v-col v-if="form_request.mbtc_type" :cols="12" :md="5">
                          <v-text-field v-model="form_request.auth_id" :readonly="readonly"
                            :error-messages="auth_idErrors + bankingError.auth_id"
                            @input="$v.form_request.auth_id.$touch() + (bankingError.auth_id = [])"
                            @blur="$v.form_request.auth_id.$touch()" type="number" name="text"
                            label="Authenticator ID *">
                          </v-text-field>
                        </v-col>
                        
                      </v-row>
                    </v-col>

                  </v-row>
                </v-container>
              </template>


              <template>
                <hr>
                <center>
                  <h5>INSTRUCTION FROM (BANK DETAILS)</h5>
                </center>

                <v-container fluid class="p-5">

                  <v-row>

                    <v-col :cols="12" :md="4">
                      <v-text-field v-model="form_request.bank_name" :readonly="readonly"
                        :error-messages="bank_nameErrors + bankingError.bank_name"
                        @input="$v.form_request.bank_name.$touch() + (bankingError.bank_name = [])"
                        @blur="$v.form_request.bank_name.$touch()" name="bankanme" label="Bank Name *">
                      </v-text-field>
                    </v-col>

                    <v-col :cols="12" :md="4">
                      <v-text-field v-model="form_request.account_no" :readonly="readonly"
                        :error-messages="account_noErrors + bankingError.account_no"
                        @input="$v.form_request.account_no.$touch() + (bankingError.account_no = [])"
                        @blur="$v.form_request.account_no.$touch()" type="number" name="account#" label="Account # *">
                      </v-text-field>
                    </v-col>

                    <v-col :cols="12" :md="4" v-if="!form_request.DELETION">
                      <v-text-field v-model="form_request.amount_limit" :readonly="readonly"
                        :error-messages="amount_limitErrors + bankingError.amount_limit"
                        @input="$v.form_request.amount_limit.$touch() + (bankingError.amount_limit = [])"
                        @blur="$v.form_request.amount_limit.$touch()" type="number" name="account#" min="0"
                        label="Tarnsaction Amount Limit" readonly>
                      </v-text-field>
                    </v-col>

                    <v-col :cols="12" :md="6" v-if="!form_request.DELETION">
                      <h6>Allowed Access</h6>
                      <v-row>
                        <v-col :cols="12" :md="6">
                          <v-checkbox :readonly="readonly" v-model="form_request.allow_access1"
                            label="Corporate Check"></v-checkbox>
                        </v-col>
                        <v-col :cols="12" :md="6">
                          <v-checkbox :readonly="readonly" v-model="form_request.allow_access2"
                            label="Statement History"></v-checkbox>
                        </v-col>
                      </v-row>
                    </v-col>

                    <v-col :cols="12" :md="6" v-if="!form_request.DELETION">
                      <v-text-field :readonly="readonly" v-model="form_request.check_series" type="text" name="account#"
                        label="Check Series #">
                      </v-text-field>
                    </v-col>

                    <v-col :cols="12" :md="6" v-if="!form_request.DELETION">
                      <v-text-field :readonly="readonly" v-model="form_request.date_started" type="date"
                        label="Date Start">
                      </v-text-field>
                    </v-col>

                    <v-col :cols="12" :md="6" v-if="!form_request.DELETION">
                      <v-text-field :readonly="readonly" v-model="form_request.date_ended" type="date" label="Date End">
                      </v-text-field>
                    </v-col>

                  </v-row>

                </v-container>
              </template>


              <template>
                <hr>
                <v-container fluid class="p-5 text-center">
                  <p>I confirm that the information above are true and correct. I have read and
                    understood all instructions listed.
                    That I am responsible to upheld the confidentiality of the work email endorsed
                    to me for Online Banking usage.
                    That I will only use / access the BDO/MBTC Online Banking during my tenure only
                    with Addessa.
                  </p>
                  <br>
                  <p>That any problem that may arise in non conformity of all of the above, I am
                    solely reaponaible and accountable.
                  </p>
                  <br>
                  <center>
                    <p v-if="editedIndex === -1 && hassignature"><span><b>Note : </b></span><i class="text-danger">After
                        uploading your e-signature, you won't need to upload it again the next time you create a
                        request.</i></p>
                    <p>
                      <v-img :src="imgpreview" accept="image/*" outlined contain max-height="100"
                        max-width="200"></v-img>

                      <template v-if="editedIndex === -1 && hassignature">

                        <v-btn color="white" class="w-auto m-1 mt-3"
                          :class="{ 'text-danger': e_signatureErrors + bankingError.e_signature }"
                          :error-messages="e_signatureErrors + bankingError.e_signature"
                          @input="$v.form_request.e_signature.$touch() + (bankingError.e_signature = [])"
                          @onchange="e_signatureErrors" @blur="$v.form_request.e_signature.$touch()"
                          @click="triggerFileInput">
                          Upload E-Signature
                        </v-btn>

                        <p class="text-danger"><i>{{ e_signatureErrors + bankingError.e_signature }}</i></p>

                        <br>

                        <input ref="fileInput" name="banner" type="file" accept="image/*" style="display: none"
                          @change="imgSrc" />

                          
                        <template>
                          <u class="mt-3">
                            ___{{ username }}___
                          </u>
                        </template>

                      </template>
                      <template v-else>
                        <b>
                          <u>
                            ___{{ username }}___
                          </u>
                        </b>
                      </template>
                    </p>

                    <p>Signature over printed name</p>
                    <p>Date: <b>{{ form_request.date_now }}</b> </p>
                  </center>
                </v-container>
              </template>


              <template v-if="(form_status === 'Disapproved' && hasPermission('online-banking-create')) || (user_approve_lvl != 0 && form_status === 'Disapproved')">
                <hr>
                
                <v-container fluid class="p-5 text-center">
                  <v-row>
                    <v-col cols="12" sm="12">
                      <v-textarea :error-messages="RemarksError.remarks" @input="(RemarksError.remarks = [])"
                        v-model="remarks" readonly label="Remarks *"></v-textarea>
                    </v-col>
                  </v-row>
                </v-container>

              </template>


              <!-- <template>
                                <hr>
                                <v-container fluid class="p-5">
                                    <p>For official use:</p>
                                    <br>
                                    <v-row>
                                        <v-col :cols="12" :md="6">
                                            <p>Verified by: _____________________________</p>
                                        </v-col>
                                        <v-col :cols="12" :md="6">
                                            <p>201Filed by Hr:<span class="tab"></span> _____________________________
                                            </p>
                                        </v-col>
                                        <v-col :cols="12" :md="2">
                                            <p>Recommended by:</p>
                                        </v-col>
                                        <v-col :cols="12" :md="5">
                                            <center>
                                                <p> _____________________________</p>
                                                <p>FDH</p>
                                            </center>
                                        </v-col>
                                        <v-col :cols="12" :md="5">
                                            <center>
                                                <p>_____________________________</p>
                                                <p>GM</p>
                                            </center>
                                        </v-col>
                                        <v-col :cols="12" :md="2">
                                            <p>Approved by:</p>
                                        </v-col>
                                        <v-col :cols="12" :md="5">
                                            <center>
                                                <p> _____________________________</p>
                                                <p>SDC</p>
                                            </center>
                                        </v-col>
                                        <v-col :cols="12" :md="5">
                                            <center>
                                                <p>_____________________________</p>
                                                <p>SCC</p>
                                            </center>
                                        </v-col>
                                    </v-row>
                                </v-container>
                            </template> -->


            </v-card-text>


            <v-card-actions>
              <v-spacer></v-spacer>


              <v-btn color="#E0E0E0" @click="dialog = false + close()" class="mb-3 font-weight-bold">
                Close
              </v-btn>


              <v-btn v-if="editedIndex === -1 && hasPermission('online-banking-create')" color="primary"
                @click="verifysubmition()" class="mb-3 font-weight-bold">
                Submit
              </v-btn>


              <v-btn v-if="form_status === 'Disapproved' && hasPermission('online-banking-create')" color="primary"
                @click="verifysubmition()" class="mb-3 font-weight-bold">
                Re-Submit
              </v-btn>


              <v-btn v-if="editedIndex != -1 && active_tab === 0 && hasPermission('online-banking-disapprove')"
                @click="reamrks_dialog = true" class="mb-3 bg-danger text-white font-weight-bold">
                Disapprove
              </v-btn>


              <v-btn v-if="editedIndex !== -1 && active_tab === 0 && hasPermission('online-banking-approve')"
                color="success" @click="showConfirmAlert(form_request, editedIndex)" class="mb-3 font-weight-bold">
                Approve
              </v-btn>


            </v-card-actions>

          </v-card>

        </v-dialog>


        <v-dialog v-model="reamrks_dialog" max-width="500px" persistent>
          <v-card>
            <v-card-title>Remarks
              <v-spacer></v-spacer>
              <v-btn icon @click="closeremarks()">
                <v-icon>mdi-close</v-icon>
              </v-btn>
            </v-card-title>
            <v-card-text>
              <template>
                <hr>
                <v-container fluid class=" text-center">
                  <v-row>
                    <v-col cols="12" sm="12">
                      <v-textarea :error-messages="RemarksError.remarks" @input="(RemarksError.remarks = [])"
                        v-model="remarks" label="Remarks *"></v-textarea>
                    </v-col>
                  </v-row>
                </v-container>
              </template>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="#E0E0E0" @click="closeremarks()" class="mb-3 font-weight-bold">
                Cancel
              </v-btn>
              <v-btn @click="showDisapproveAlert()" class="mb-3 bg-primary text-white font-weight-bold">
                Proceed
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>


      </v-main>

    </div>
  </div>
</template>

<script>
import axios from "axios";
import { isEmpty } from "lodash";
import { readonly } from "vue";
import { validationMixin } from "vuelidate";
import { required, maxLength, email, requiredIf } from "vuelidate/lib/validators";
import { mapState, mapGetters } from "vuex";
import html2pdf from "html2pdf.js";
import { jsPDF } from "jspdf";
import { size } from "lodash";


export default {


  mixins: [validationMixin],


  validations: {

    form_request: {


      //FIRST ROW
      banking_form: { required },
      date_now: { required },


      // SECOND ROW
      company: { required },
      position: { required },
      name: { required },
      birthdate: {
        required: requiredIf(function () {
          console.log(this.formtypePrereq);

          return this.formtypePrereq;
        }),
      },

      contact: {
        required: requiredIf(function () {
          return this.formtypePrereq;
        }),
      },

      email: {
        required: requiredIf(function () {
          return this.formtypePrereq;
        }),
        email
      },

      tin: {
        required: requiredIf(function () {
          return this.formtypePrereq;
        }),
      },

      sss: {
        required: requiredIf(function () {
          return this.formtypePrereq;
        }),
      },
      philhealth: {
        required: requiredIf(function () {
          return this.formtypePrereq;
        }),
      },


      // THIRD ROW
      // type: {
      //   required
      //   // : function () {
      //   //   return this.form_request.update_status === true;
      //   // }
      // },


      account_from: {
        required: requiredIf(function () {
          return this.acctTypePrereq;
        }),

      },

      account_to: {

        required: requiredIf(function () {
          return this.acctTypePrereq;
        }),

      },

      bank: {
        required: requiredIf(function () {
          return this.formtypePrereq;
        }),
      },


      auth_id: {
        required: requiredIf(function () {
          return this.bankTypePrereq;
        }),
      },


      //FOURTH ROW
      bank_name: { required },
      account_no: { required },
      amount_limit: {
        required: requiredIf(function () {
          return this.formtypePrereq;
        }),
      },


      e_signature: {
        required: requiredIf(function () {
          return !this.imgpreview ? true : false;
        }),
      }


    },

    remarks: { required },


  },


  data() {

    return {

      active_tab: 0,
      status: '',
      user_approve_lvl: 0,
      pendings: [],
      onprocess: [],
      approves: [],
      disapproves: [],
      tab: null,
      navigations: [],
      banner: '',
      form_status: '',
      reamrks_dialog: false,
      formtitle: 'New Form Request',
      remarks: '',
      hassignature: true,
      imgpreview: null,
      username: null,
      onlinebankings: {},
      approval_progress: [],

      pending_approval_progress: [],
      onprocess_approval_progress: [],
      approved_approval_progress: [],
      disapproved_approval_progress: [],


      RemarksError:
      {

        Remarks: [],

      },


      bankingError: {
        bank: [],
        banking_form: [],
        DELETION: [],
        NEWVERIFIER: [],
        NEWMAKER: [],
        date_now: [],
        company: [],
        position: [],
        name: [],
        birthdate: [],
        email: [],
        contact: [],
        tin: [],
        sss: [],
        philhealth: [],
        update_status: [],
        type: [],
        bdo_type: [],
        mbtc_type: [],
        auth_id: [],
        account_from: [],
        account_to: [],
        bank_name: [],
        account_no: [],
        check_series: [],
        amount_limit: [],
        allow_access1: [],
        allow_access2: [],
        date_started: [],
        date_ended: [],
        e_signature: [],
      },


      bank_bdo: false,
      bank_mbtc: false,


      form_request: {
        bank: '',
        banking_form: '',
        DELETION: false,
        NEWVERIFIER: false,
        NEWMAKER: false,
        date_now: new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().substr(0, 10),
        company: '',
        position: '',
        name: '',
        birthdate: '',
        email: '',
        contact: '',
        tin: '',
        sss: '',
        philhealth: '',
        update_status: false,
        type: '',
        bdo_type: false,
        mbtc_type: false,
        auth_id: '',
        account_from: '',
        account_to: '',
        bank_name: '',
        account_no: '',
        check_series: '',
        amount_limit: 3000000,
        allow_access1: false,
        allow_access2: false,
        date_started: '',
        date_ended: '',
        e_signature: [],
      },


      default_form: {
        bank: '',
        banking_form: '',
        DELETION: false,
        NEWVERIFIER: false,
        NEWMAKER: false,
        date_now: new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().substr(0, 10),
        company: '',
        position: '',
        name: '',
        birthdate: '',
        email: '',
        contact: '',
        tin: '',
        sss: '',
        philhealth: '',
        update_status: false,
        type: '',
        bdo_type: false,
        mbtc_type: false,
        auth_id: '',
        account_from: '',
        account_to: '',
        bank_name: '',
        account_no: '',
        check_series: '',
        amount_limit: 3000000,
        allow_access1: false,
        allow_access2: false,
        date_started: '',
        date_ended: '',
        e_signature: [],
      },


      distinationtype: [
        {
          text: "FROM MAKER TO VERIFIER", value: 'FROM MAKER TO VERIFIER'
        },
        {
          text: "FROM VERIFIER TO MAKER", value: 'FROM VERIFIER TO MAKER'
        },
        {
          text: "ACCOUNT #", value: 'ACCOUNT #'
        },
      ],


      allowedaccess: [
        {
          text: "Corporate Check", value: 'Corporate Check'
        },
        {
          text: "Statement History", value: 'Statement History'
        },
      ],


      date_now: new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().substr(0, 10),


      dialog: false,


      search: "",



      headers: [
        { text: "#", value: 'count', width: "50px" },
        { text: "Request Form Type", value: "formtype" },
        { text: "Date Submitted", value: "submitted_at" },
        { text: "Company", value: "company" },
        { text: "Name", value: "name" },
        { text: "Position", value: "position" },
        { text: "Progress", value: "progress" },
        { text: "Status", value: "status" },
        { text: "Last Action Date", value: "updated_at" },
        { text: "Actions", value: "actions", sortable: false },
      ],



      disabled_maker: false,
      disabled_verifier: false,
      disabled_deletion: false,



      dialog: false,
      readonly: false,
      editedIndex: -1,



      items: [

        {
          text: "Home",
          disabled: false,
          link: "/",
        },

        {
          text: "Online Banking Requisition List",
          disabled: true,
        },

      ],



      loading: true,



      // expenseError: {
      //     description: [],
      // },



      skeleton_loading: true,



    };

  },


  methods: {


    download() {

      const formData = new FormData();

      Object.keys(this.form_request).forEach(key => {
        if (key !== 'id') formData.append(key, this.form_request[key]);
      });

      var element = document.getElementById('card');

      var opt = {

        filename: this.form_request.name,

        jsPDF: {
          unit: 'mm',
          format: 'letter',
          orientation: 'portrait'
        },

        html2pdf: {
          maxHeight: 3500
        }

      };

      html2pdf().set(opt).from(element).save();

    },



    closeremarks() {
      this.remarks = '';
      this.$v.remarks.$reset();
      this.reamrks_dialog = false;
    },


    showDisapproveAlert() {
      this.$swal({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "red",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "<span style='color: white;'>Disapprove</span>",
        cancelButtonText: "<span style='color: white;'>Cancel</span>",
      }).then((result) => {
        if (result.value) {
          this.disapprove_request();
        }
      });
    },


    disapprove_request() {
      const data = { id: this.editedIndex, remarks: this.remarks };
      this.loading = true;
      axios.post("/api/onlinebanking/disapprove", data)
        .then((response) => {
          this.loading = false;
          
          if (response.data.remarks) {
            this.RemarksError.remarks = response.data.remarks[0];
          } 
          else {
            this.reamrks_dialog = false;
            this.dialog = false;
            this.getBankingRequest();
            this.$swal({
              icon: "success",
              title: "Record has been DISAPPROVED", 
              text: "Request Disapproved successfully",
              toast: true,
              position: "top-end",
              showConfirmButton: false,
              timer: 2000,
              timerProgressBar: true,
            });
          }
        })
        .catch((error) => {
          this.isUnauthorized(error); // Handle unauthorized or other errors
        });
    },


    triggerFileInput() {
      this.$refs.fileInput.click();
    },


    imgSrc(event) {
      const file = event.target.files[0];
      if (file) {
        this.form_request.e_signature = file;
        const reader = new FileReader();
        reader.onload = (e) => {
          this.imgpreview = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },


    // date format of data
    formatDate(date) {
      const d = new Date(date);
      const day = String(d.getDate()).padStart(2, '0');
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const year = d.getFullYear();
      return `${day}/${month}/${year}`;
    },


    // for switch icon in baking request
    update_status() {
      if (!this.form_request.update_status) {
        this.form_request.bank = '';
        this.form_request.type = '';
        this.form_request.bdo_type = false;
        this.form_request.mbtc_type = false;
        this.$v.form_request.bank.$reset();
        this.$v.form_request.type.$reset();
        this.$v.form_request.bdo_type.$reset();
        this.$v.form_request.mbtc_type.$reset();

      }

    },


    showAlert(msg) {
      this.$swal({
        position: "center",
        icon: msg.icon,
        title: msg.title,
        text: msg.text,
        showConfirmButton: false,
        timer: 2500,
      });
    },


    // clearing properties of elements
    clear() {
      this.form_status = '';
      this.remarks = '';
      this.formtitle = 'New Form Request';
      this.readonly = false;
      this.editedIndex = -1;
      this.$v.$reset();
      this.form_request = { ...this.default_form };
      this.form_request.NEWMAKER = false;
      this.form_request.NEWVERIFIER = false;
      this.form_request.DELETION = false;
      this.form_request.bdo_type = false;
      this.form_request.mbtc_type = false;
      this.bank_bdo = false;
      this.bank_mbtc = false;
      this.bankingError = {
        bank: [],
        banking_form: [],
        DELETION: [],
        NEWVERIFIER: [],
        NEWMAKER: [],
        date_now: [],
        company: [],
        position: [],
        name: [],
        birthdate: [],
        email: [],
        contact: [],
        tin: [],
        sss: [],
        philhealth: [],
        update_status: [],
        type: [],
        bdo_type: [],
        mbtc_type: [],
        auth_id: [],
        account_from: [],
        account_to: [],
        bank_name: [],
        account_no: [],
        check_series: [],
        amount_limit: [],
        allow_access1: [],
        allow_access2: [],
        date_started: [],
        date_ended: [],
        e_signature: [],
      };
    },


    // closing the dialog window 
    close() {
      this.disabled_maker = false,
        this.disabled_verifier = false,
        this.disabled_deletion = false,
        this.clear();
    },



    // Saving banking request form
    save() {

      this.$v.$reset();
      this.bankingError = {
        bank: [],
        banking_form: [],
        DELETION: [],
        NEWVERIFIER: [],
        NEWMAKER: [],
        date_now: [],
        company: [],
        position: [],
        name: [],
        birthdate: [],
        email: [],
        contact: [],
        tin: [],
        sss: [],
        philhealth: [],
        update_status: [],
        type: [],
        bdo_type: [],
        mbtc_type: [],
        auth_id: [],
        account_from: [],
        account_to: [],
        bank_name: [],
        account_no: [],
        check_series: [],
        amount_limit: [],
        allow_access1: [],
        allow_access2: [],
        date_started: [],
        date_ended: [],
        e_signature: [],
      };

      // const data = { banking_form: { ...this.form_request } };
      const formData = new FormData();

      Object.keys(this.form_request).forEach(key => {
        if (key !== 'id') formData.append(key, this.form_request[key]);
      });

      axios.post("/api/onlinebanking/store", formData).then(
        response => {
          if (response.data.success === true) {
            this.close();
            this.dialog = false;
            this.$swal.fire({
              icon: "success",
              title: "Request Submitted",
              text: "Request submitted successfully",
              toast: true,
              position: "top-end",
              showConfirmButton: false,
              timer: 2000,
              timerProgressBar: true,
            });
            this.getBankingRequest();
          }
          else if (response.data.success === false) {
            this.showAlert(response.data);
          }
          else {
            this.handleErrors(response.data.errors);
          }
        },
        error => {
          this.isUnauthorized(error);
        }
      ).finally(() => {
        // this.disabled = false;
      });
    },



    // VALIDATION ITEMS
    handleErrors(errors) {

      Object.keys(errors).forEach(field => {
        this.bankingError[field].push(errors[field]);
      });
    },



    // CONDITION FOR CHECKBOX FOR
    account_type() {

      if (this.form_request.bdo_type) {
        this.bank_bdo = false;
        this.bank_mbtc = true;
        this.form_request.bank = "BDO";
      }

      else if (this.form_request.mbtc_type) {

        this.bank_bdo = true;
        this.bank_mbtc = false;
        this.form_request.bank = "MBTC";


      }

      else {

        this.bank_bdo = false;
        this.bank_mbtc = false;
        this.form_request.bank = "";
        this.form_request.auth_id = '';
        this.$v.form_request.auth_id.$reset();

      }
    },



    // CONDITION FOR CHECKBOX FOR
    maker() {
      if (!this.form_request.NEWMAKER) {
        this.form_request.update_status = false;
        this.disabled_verifier = false;
        this.disabled_deletion = false;
        this.form_request.banking_form = '';
        this.$v.form_request.banking_form.$reset();
      }

      else {
        this.form_request.update_status = true;
        this.disabled_verifier = true;
        this.disabled_deletion = true;
        this.form_request.banking_form = 'NEW MAKER';
        ;

      }

    },



    // CONDITION FOR CHECKBOX FOR
    verifier() {
      if (!this.form_request.NEWVERIFIER) {
        this.form_request.update_status = false;
        this.disabled_maker = false;
        this.disabled_deletion = false;
        this.disabled_verifier = false;
        this.form_request.banking_form = '';


      }
      else {
        this.form_request.update_status = true;
        this.disabled_maker = true;
        this.disabled_deletion = true;
        this.form_request.banking_form = 'NEW VERIFIER';
        this.form_request.formtype = 'NEW VERIFIER';
      }
    },



    deletion() {
      if (!this.form_request.DELETION) {
        this.form_request.amount_limit = 300000,
          this.disabled_verifier = false;
        this.disabled_maker = false;
        this.form_request.banking_form = '';
      }
      else {
        this.bank_bdo = false
        this.bank_mbtc = false
        Object.assign(this.form_request, {
          bank: null,
          birthdate: null,
          email: null,
          contact: null,
          tin: null,
          sss: null,
          philhealth: null,
          type: null,
          bdo_type: false,
          mbtc_type: false,
          auth_id: null,
          account_from: null,
          account_to: null,
          check_series: null,
          amount_limit: null,
          allow_access1: false,
          allow_access2: false,
          date_started: null,
          date_ended: null
        });


        this.disabled_verifier = true;
        this.disabled_maker = true;
        this.form_request.banking_form = 'FOR DELETION';
        this.form_request.formtype = 'FOR DELETION';
      }
    },



    // get and filtering records from the backend
    getBankingRequest() {
      this.loading = true;
      axios.get("/api/onlinebanking/index").then(
        (response) => {
          let data = response.data;

          this.user_approve_lvl = data.user_current_lvl;
          this.onlinebankings = data.bankingrequest;

          this.pendings = data.pendings;
          
          this.onprocess = data.onprocess;
          this.approves = data.approves;
          this.disapproves = data.disapproves;

          this.approval_progress = data.approval_progress;

          this.pending_approval_progress = data.pending_approval_progress;
          
          this.onprocess_approval_progress = data.onprocess_approval_progress;
          this.approved_approval_progress = data.approved_approval_progress;
          this.disapproved_approval_progress = data.disapproved_approval_progress;

          let onlinebankings = [];

          if (data.username) {
            this.imgpreview = data.username[0].e_signature ? 'img/e_signature/' + data.username[0].e_signature.e_signature : null;
            this.username = data.username[0].name;
          }

          this.hassignature = this.imgpreview ? false : true;
          this.onlinebankings.forEach((value, i) => {

            onlinebankings.push(value);

            this.approval_progress.forEach((val) => {
              if (value.id === val.banking_request_id) {
                onlinebankings[i]['approval_progress'] = val.progress;
              }
            });
          });

          this.loading = false;
          this.skeleton_loading = false;
          this.navigations = this.user_approve_lvl === null ? ['Request', 'On Process', 'Approved', 'Disapproved'] : ['Pending Request', 'On Process', 'Approved', 'Disapproved'];
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );

    },



    // VIEW BANKING REQUEST ITEM
    viewbankingrequest(item) {

      this.formtitle = item.user.name + ' Request Form';
      this.readonly = true;
      this.disabled_maker = this.disabled_verifier = this.disabled_deletion = false;
      this.editedIndex = item.id;
      this.dialog = true;
      this.status = item.status;

      //UPDATE TYPE 
      this.form_request.banking_form = item.formtype;
      this.form_request.DELETION = item.formtype === 'FOR DELETION' ? true : false;
      this.form_request.NEWMAKER = item.formtype === 'NEW MAKER' ? true : false;
      this.form_request.NEWVERIFIER = item.formtype === 'NEW VERIFIER' ? true : false;
      this.form_request.date_now = new Date(item.submitted_at).toLocaleDateString();

      //USER INFORMATION
      this.form_request.company = item.company;
      this.form_request.position = item.position;
      this.form_request.name = item.name;
      this.form_request.birthdate = item.bdate;
      this.form_request.email = item.email === null ? '' : item.email;
      this.form_request.contact = item.contact === null ? '' : item.contact;
      this.form_request.sss = item.sss === null ? '' : item.sss;
      this.form_request.philhealth = item.philhealth === null ? '' : item.philhealth;
      this.form_request.tin = item.tin === null ? '' : item.tin;

      // UPDATE
      this.form_request.type = item.update_type === null ? '' : item.update_type;
      this.form_request.account_from = item.from_acct === null ? '' : item.from_acct;
      this.form_request.account_to = item.to_acct === null ? '' : item.to_acct;
      this.form_request.bank = item.bank;
      this.bank_bdo = item.bank === 'MBTC' ? true : false;
      this.bank_mbtc = item.bank === 'BDO' ? true : false;
      this.form_request.bank === 'MBTC' ? this.form_request.mbtc_type = true : (this.form_request.bank === 'BDO' ? this.form_request.bdo_type = true : false);
      this.form_request.auth_id = item.auth_id === null ? '' : item.auth_id;

      // INSTRUCTION FROM
      this.form_request.bank_name = item.bank_name;
      this.form_request.account_no = item.acct_no;
      this.form_request.allow_access2 = item.allowed_access2 ? true : false;
      this.form_request.allow_access1 = item.allowed_access1 ? true : false;
      this.form_request.check_series = item.check_series === null ? '' : item.check_series;
      this.form_request.date_started = item.date_started;
      this.form_request.date_ended = item.date_ended;

      this.imgpreview = item.user.e_signature ? item.user.e_signature.file_path + '/' + item.user.e_signature.e_signature : null;
      this.username = item.user.name;
      this.remarks = item.remarks;
      this.form_status = item.status;



      if (this.form_status == "Disapproved") {
        // this.readonly =  false;
        // this.readonly = this.user_approve_lvl != 0 ? true : false;
        this.readonly = this.user_approve_lvl === null? false : true;
        this.disabled_deletion = this.user_approve_lvl != 0 ? true : item.formtype === 'FOR DELETION' ? false : true;
        this.disabled_maker = this.user_approve_lvl != 0 ? true : item.formtype === 'NEW MAKER' ? false : true;
        this.disabled_verifier = this.user_approve_lvl != 0 ? true : item.formtype === 'NEW VERIFIER' ? false : true;
      }

      else {
        this.disabled_deletion = true;
        this.disabled_maker = true;
        this.disabled_verifier = true;
      }



    },



    verifysubmition() {
      this.$v.form_request.$touch();
      if (this.$v.form_request.$error) {
        return;
      }
      this.$swal({
        title: "Confirm Submission",
        text: "Double-check your inputs.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "primary",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "<span style='color: white;'>Proceed to Submit</span>",
        cancelButtonText: "<span style='color: white;'>Cancel</span>",
      }).then((result) => {
        if (result.value) {
          this.save();

        }
      });

    },



    showConfirmAlert(item, index) {
      this.$swal({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "primary",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "<span style='color: white;'>Approve</span>",
        cancelButtonText: "<span style='color: white;'>Cancel</span>",
      }).then((result) => {
        if (result.value) {
          this.approveentry();
          this.$swal.fire({
            icon: "success",
            title: "Record has been APPROVED",
            text: "Request Approved successfully",
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
          });
        }
      });
    },



    approveentry() {
      const data = { id: this.editedIndex };
      this.loading = true;
      axios.post("/api/onlinebanking/approve", data).then(
        (response) => {
          this.loading = false;
          this.dialog = false;
          this.getBankingRequest();
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },



    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },



    websocket() {
      // Socket.IO fetch data
      this.$options.sockets.sendData = (data) => {
        let action = data.action;
        if (
          action == "tactical-requisition-create" ||
          action == "tactical-requisition-edit" ||
          action == "tactical-requisition-delete"
        ) {
          this.getBankingRequest();
        }
      };
    },



  },


  computed: {

    reamrks() {

      this.reamrks_dialog = true;
    },


    typeupdate() {
      if (this.form_request.type !== "Account #") {
        this.form_request.account_from = '';
        this.form_request.account_to = '';
      }
    },


    e_signatureErrors() {
      if (this.$v.form_request.e_signature.$error) {
        this.bankingError.e_signature = [];
        return ['E-signature is required'];
      }

      return [];

    },


    bankErrors() {
      if (!this.readonly) {
        if (this.$v.form_request.bank.$error) {
          return ['Please select at least one option (BDO or MBTC)'];
        }
      }
      return [];
    },


    banking_formErrors() {
      if (this.$v.form_request.banking_form.$error) {
        return ['Please select at least one option (New Maker, New Verifier, or For Deletion)'];
      }

      return [];
    },


    companyErrors() {
      if (this.$v.form_request.company.$error) {
        return ['Company is required'];
      }
      return [];
    },


    positionErrors() {
      if (this.$v.form_request.position.$error) {
        return ['Position is required'];
      }
      return [];
    },


    nameErrors() {
      if (this.$v.form_request.name.$error) {
        return ['Name is required'];
      }
      return [];
    },


    birthdateErrors() {
      if (this.$v.form_request.birthdate.$error) {
        return ['Birthdate is required'];
      }
      return [];
    },


    contactErrors() {
      if (this.$v.form_request.contact.$error) {
        return ['Contact is required'];
      }
      return [];
    },


    emailErrors() {
      if (this.$v.form_request.email.$error) {
        return ['Email is required'];
      }
      return [];
    },


    tinErrors() {
      if (this.$v.form_request.tin.$error) {
        return ['TIN # is required'];
      }
      return [];
    },


    sssErrors() {
      if (this.$v.form_request.sss.$error) {
        return ['SSS # is required'];
      }
      return [];
    },


    philhealthErrors() {
      if (this.$v.form_request.philhealth.$error) {
        return ['PHILHELATH # is required'];
      }
      return [];
    },


    account_fromErrors() {
      if (this.$v.form_request.account_from.$error) {
        return ['Account # from is required'];
      }
      return [];
    },


    account_toErrors() {
      if (this.$v.form_request.account_to.$error) {
        return ['Account # to is required'];
      }
      return [];
    },


    auth_idErrors() {
      if (this.$v.form_request.auth_id.$error) {
        return ['Authenticator ID is required'];

      }
      return [];
    },


    bank_nameErrors() {
      if (this.$v.form_request.bank_name.$error) {
        return ['Bank name is required'];
      }
      return [];
    },


    account_noErrors() {
      if (this.$v.form_request.account_no.$error) {
        return ['Account #  is required'];
      }
      return [];
    },


    amount_limitErrors() {
      if (this.$v.form_request.amount_limit.$error) {
        return ['Amount limit is required'];
      }
      return [];
    },


    date_nowErrors() {
      if (this.$v.form_request.date_now.$error) {
        this.form_request.e_signature = [];
        return ['Date submitted is required'];
      }
      return [];
    },


    PendingRequest() {

      let pending_request = [];
      this.pendings.forEach((value, i) => {
        pending_request.push(value);
        this.approval_progress.forEach((val) => {
          if (value.id === val.banking_request_id) {
            pending_request[i]['approval_progress'] = val.progress;
          }
        });
      });
      return pending_request;
    },


    OnprocessRequest() {

      let onprocess_request = [];
      this.onprocess.forEach((value, i) => {
        onprocess_request.push(value);
        this.approval_progress.forEach((val) => {
          if (value.id === val.banking_request_id) {
            onprocess_request[i]['approval_progress'] = val.progress;
          }
        });
      });
      return onprocess_request;
    },


    ApprovedRequest() {
      let approves_request = [];
      this.approves.forEach((value, i) => {
        approves_request.push(value);
        this.approval_progress.forEach((val) => {
          if (value.id === val.banking_request_id) {
            approves_request[i]['approval_progress'] = val.progress;
          }
        });
      });
      return approves_request;
    },


    DisapprovedRequest() {
      let disapproves_request = [];
      this.disapproves.forEach((value, i) => {
        disapproves_request.push(value);
        this.approval_progress.forEach((val) => {
          if (value.id === val.banking_request_id) {
            disapproves_request[i]['approval_progress'] = val.progress;
          }
        });
      });
      return disapproves_request;
    },


    acctTypePrereq() {
      let isRequired = false;
      if (this.form_request.type === 'ACCOUNT #' && this.formtypePrereq) {
        isRequired = true;
      }
      return isRequired;
    },


    bankTypePrereq() {
      let isRequired = false;
      if (this.form_request.mbtc_type && this.formtypePrereq) {
        isRequired = true;
      }
      return isRequired;
    },


    formtypePrereq() {
      let isRequired = true;
      if (this.form_request.banking_form === 'FOR DELETION') {
        isRequired = false;

      }

      return isRequired;
    },


    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission", "hasAnyPermission"]),

  },


  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getBankingRequest();
   
    // this.websocket();

  },


  watch: {
    tab(newTab) {
      this.active_tab = newTab;
      // this.navigations = this.user_approve_lvl === null || this.user_approve_lvl === 0 ? ['Request', 'On Process', 'Approved', 'Disapproved'] : ['Pending Request', 'On Process', 'Approved', 'Disapproved'];
         },

    
  }


};
</script>