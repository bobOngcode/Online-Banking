<template>
  <div>
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
          <tr v-if="editedItem.expense_particulars.length === 0">
            <td colspan="6" class="text-center">
              <p class="font-italic font-weight-bold subtitle-1 my-4">Please select Event Title</p>
            </td>
          </tr>
          <tbody v-for="(item, index) in editedItem.expense_particulars">
            <tr>
              <td class="font-weight-bold border-0 pr-0">
                {{ item.description }}
                <v-btn
                  class="mb-1"
                  color="primary"
                  icon
                  @click="addItem(index)"
                  v-if="item.dynamic && !isReadOnly"
                >
                  <v-icon>mdi-plus-circle</v-icon>
                </v-btn>

              </td>
              <template v-if="item.expense_sub_particulars.length === 0">
                <td class="border-0 pr-0">
                  <v-text-field
                    name="resource_person"
                    v-model="item.resource_person"
                    dense
                    hide-details
                    outlined
                    @input="getFieldValue(item, '', 'resource_person')"
                    @blur="getFieldValue(item, '', 'resource_person')"
                    :error-messages="errorField(index, 'resource_person')"
                    :readonly="isReadOnly"
                  ></v-text-field>
                </td>
                <td class="border-0 pr-0">
                  <v-text-field
                    name="contact"
                    v-model="item.contact"
                    dense
                    hide-details
                    outlined
                    @input="getFieldValue(item, '', 'contact')"
                    @blur="getFieldValue(item, '', 'contact')"
                    :error-messages="errorField(index, 'contact')"
                    :readonly="isReadOnly"
                  ></v-text-field>
                </td>
                <td class="border-0 pr-0">
                  <v-text-field-money
                    class="pa-0"
                    v-model="item.qty"
                    v-bind:properties="{
                      name: 'qty',
                      placeholder: '0',
                      'hide-details': true,
                      outlined: true,
                      dense: true,
                      error: errorField(index, 'qty'),
                      messages: '',
                      readonly: isReadOnly
                    }"
                    v-bind:options="{
                      length: 16,
                      precision: 0,
                      empty: null,
                    }"
                    @input="getFieldValue(item, '', 'qty') + computeAmount()"
                    @blur="getFieldValue(item, '', 'qty') + computeAmount()"
                  >
                  </v-text-field-money>
                </td>
                <td class="border-0 pr-0">
                  <v-text-field-dotnumber
                    class="pa-0"
                    v-model="item.unit_cost"
                    v-bind:properties="{
                      name: 'unit_cost',
                      placeholder: '0.00',
                      'hide-details': true,
                      outlined: true,
                      dense: true,
                      error: errorField(index, 'unit_cost'),
                      messages: '',
                      readonly: isReadOnly
                    }"
                    v-bind:options="{
                      length: 11,
                      precision: 2,
                      empty: null,
                    }"
                    @input="getFieldValue(item, '', 'unit_cost') + computeAmount()"
                    @blur="getFieldValue(item, '', 'unit_cost') + computeAmount()"
                  >
                  </v-text-field-dotnumber>
                </td>
                <td class="font-weight-bold border-0 pr-0">
                  {{ !item.amount ? "0.00" : Number(item.amount).toLocaleString('en', numOpts) }}
                </td>
              </template>
            </tr>
            <tr v-for="(subItem, i) in item.expense_sub_particulars">
              <td class="border-0 pr-0">
                <span class="ml-12" v-if="!['New', 'Editable'].includes(subItem.status)">{{ subItem.description }}</span>
                <div class="d-flex justify-content-end" v-if="['New', 'Editable'].includes(subItem.status)">
                  <v-btn
                    color="error"
                    icon
                    class="mt-1"
                    @click="confirmRemove('RowData', subItem)"
                  >
                    <v-icon>mdi-minus-circle</v-icon>
                  </v-btn>
                  <v-text-field
                    name="description"
                    v-model="subItem.description"
                    dense
                    hide-details
                    outlined
                    @input="getFieldValue(item, subItem, 'description')"
                    @blur="getFieldValue(item, subItem, 'description')"
                    :error-messages="errorSubField(index, i, 'description')"
                  >
                  </v-text-field>
                </div>
              </td>
              <td class="border-0 pr-0">
                <v-text-field
                  name="resource_person"
                  v-model="subItem.resource_person"
                  dense
                  hide-details
                  outlined
                  @input="getFieldValue(item, subItem, 'resource_person')"
                  @blur="getFieldValue(item, subItem, 'resource_person')"
                  :error-messages="errorSubField(index, i, 'resource_person')"
                  :readonly="isReadOnly"
                ></v-text-field>
              </td>
              <td class="border-0 pr-0">
                <v-text-field
                  name="contact"
                  v-model="subItem.contact"
                  dense
                  hide-details
                  outlined
                  @input="getFieldValue(item, subItem, 'contact')"
                  @blur="getFieldValue(item, subItem, 'contact')"
                  :error-messages="errorSubField(index, i, 'contact')"
                  :readonly="isReadOnly"
                ></v-text-field>
              </td>
              <td class="border-0 pr-0">
                <v-text-field-money
                  class="pa-0"
                  v-model="subItem.qty"
                  v-bind:properties="{
                    name: 'qty',
                    placeholder: '0',
                    'hide-details': true,
                    outlined: true,
                    dense: true,
                    error: errorSubField(index, i, 'qty'),
                    messages: '',
                    readonly: isReadOnly
                  }"
                  v-bind:options="{
                    length: 16,
                    precision: 0,
                    empty: null,
                  }"
                  @input="getFieldValue(item, subItem, 'qty') + computeAmount()"
                  @blur="getFieldValue(item, subItem, 'qty') + computeAmount()"
                >
                </v-text-field-money>
              </td>
              <td class="border-0 pr-0">
                <v-text-field-dotnumber
                  class="pa-0"
                  v-model="subItem.unit_cost"
                  v-bind:properties="{
                    name: 'unit_cost',
                    placeholder: '0.00',
                    'hide-details': true,
                    outlined: true,
                    dense: true,
                    error: errorSubField(index, i, 'unit_cost'),
                    messages: '',
                    readonly: isReadOnly
                  }"
                  v-bind:options="{
                    length: 11,
                    precision: 2,
                    empty: null,
                  }"
                  @input="getFieldValue(item, subItem, 'unit_cost') + computeAmount()"
                  @blur="getFieldValue(item, subItem, 'unit_cost') + computeAmount()"
                >
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
  </div>
</template>
<script>

  export default {
    name: "ExpenseParticulars",
    props: ['expense_particulars', 'isReadOnly'],
    data() {
      return {
        grand_total: "0.00",
        errorFields: [],
        expenseParticularHasError: false,
        editedItem: {
          expense_particulars: [],
        },
        defaultItem: {
          expense_particulars: [],
        },
        numOpts: { 
          minimumFractionDigits: 2,
          maximumFractionDigits: 2 
        },
      }
    },
    methods: {
      getMarketingEvent() {

        this.errorFields = [];
        this.expenseParticularHasError = false;

        this.editedItem.expense_particulars = [];

        this.expense_particulars.forEach((value, index) => {
          this.editedItem.expense_particulars.push({
            expense_particular_id: value.id,
            description: value.description,
            resource_person: "",
            contact: "",
            qty: "",
            unit_cost: "",
            amount: "",
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

          value.expense_sub_particulars.forEach((val, i) => {
            this.editedItem.expense_particulars[
              index
            ].expense_sub_particulars.push({
              expense_sub_particular_id: val.id,
              description: val.description,
              resource_person: "",
              contact: "",
              qty: "",
              unit_cost: "",
              amount: "",
            });

            this.errorFields[index].errorSubFields.push({
              description: null,
              resource_person: null,
              contact: null,
              qty: null,
              unit_cost: null,
            });
          });
        });
      },
      addItem(index)
      {
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
        let index = item.parent_index; 
        let subItem = this.editedItem.expense_particulars[index].expense_sub_particulars;
        let subIndex = subItem.indexOf(item);
        subItem.splice(subIndex, 1)
      },

      resetData() {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.grand_total = "0.00";
        this.errorFields = [];
        this.expenseParticularHasError = false;
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
          let expense_sub_particulars =  expense_particulars[index]["expense_sub_particulars"];
          let subIndex = expense_sub_particulars.indexOf(subItem);
          let expense_sub_particular = expense_sub_particulars[subIndex];

          field_value = expense_sub_particular[fieldName]
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
            if(fieldName === 'description')
            {

              errorFields.errorSubFields[index][fieldName] = "";
            
              expense_sub_particulars.forEach((val, i) => {
                if(value.description === val.description && i != index)
                {
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
            if(fieldName != 'dynamic')
            {
              this.getFieldValue(value, "", fieldName);
            }
          });

          // validate expense sub particulars fields
          value.expense_sub_particulars.forEach((val, i) => {
            object_names = Object.keys(expense_particulars[index]);
            object_names.forEach((fieldName) => {

              // exclude validation for expense_sub_particulars and expense_particular_id object name
              let objArr = ['expense_sub_particulars', 'expense_particular_id', 'dynamic'];
              if(!objArr.includes(fieldName))
              {
                this.getFieldValue(value, val, fieldName);
              }
              
            });
          });
        });

        this.expenseParticularHasError = false;

        // check/scan if field has error on errorFields variable
        this.errorFields.forEach((value, index) => {
          object_names = Object.keys(value);
          object_names.forEach((fieldName) => {
            if (this.errorFields[index][fieldName] == "error") {
              this.expenseParticularHasError = true;
            }
          });

          value.errorSubFields.forEach((val, i) => {
            object_names = Object.keys(value);
            object_names.forEach((fieldName) => {
              if (
                this.errorFields[index]["errorSubFields"][i][fieldName] == "error"
              ) {
                this.expenseParticularHasError = true;
              }
            });
          });
        });

        // console.log('expenseParticularHasError', this.expenseParticularHasError);
        // console.log('errorFields', this.errorFields);
        
      },
    },
    watch: {
      expense_particulars() {
        this.getMarketingEvent();
      }
    }
  }
</script>
