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
        <v-card>
          <v-card-title class="mb-0 pb-0">
            <span class="headline">Edit Marketing Event</span>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-6">
            <v-row>
              <v-col cols="4" class="mt-0 mb-0 pt-0 pb-0">
                <v-text-field
                  name="event_name"
                  v-model="editedItem.event_name"
                  label="Event Title"
                  :error-messages="eventErrors + eventError.event_name"
                  @input="$v.editedItem.event_name.$touch() + (eventError.event_name = [])"
                  @blur="$v.editedItem.event_name.$touch()"
                >
                  ></v-text-field
                >
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="4" class="my-0 py-0">
                <v-switch v-model="switch1">
                  <template v-slot:label>
                    <span class="mt-2">Attachment Required:</span>  
                    <v-chip small :color="switch1 ? 'primary' : ''" class="ml-2 mt-2"> 
                      {{ switch1 }} 
                    </v-chip>
                  </template>
                </v-switch>
              </v-col>
            </v-row>
            <v-row>
              <v-col>
                <fieldset class="border-1">
                  <legend>Expense Particulars:</legend>
                  <v-treeview
                    v-model="tree"
                    :items="expense_particulars"
                    hoverable
                    :open.sync="open"
                    item-key="index"
                    class="pa-0 mt-4"
                  >
                    <template v-slot:prepend="{ item, open }">
                      <v-btn
                        color="primary"
                        @click="addSubItem(item)"
                        icon
                        v-if="item.children"
                      >
                        <v-icon>mdi-plus-circle</v-icon>
                      </v-btn>
                      <v-btn
                        color="red"
                        @click="showConfirmAlert(item)"
                        icon
                        :class="!item.children ? 'ml-12' : ''"
                      >
                        <v-icon class="ma-0 pa-0">mdi-minus-circle</v-icon>
                      </v-btn>
                    </template>
                    <template v-slot:label="{ item, open }">
                      <v-row class="pa-0 ma-0">
                        <v-col :class="'pa-0 ma-0' + (item.children ? ' mt-2' : '')" :cols="item.children ? 9 : 4">
                          <v-text-field
                            class="pa-0 ma-0"
                            name="description"
                            v-model="item.description"
                            dense
                            hide-details
                            outlined
                            :error-messages="item.hasError ? 'Error' : ''"
                            @input="validateField(item)"
                          ></v-text-field>
                        </v-col>
                        <v-col class="pa-0 ma-0 py-2 ml-4">
                          <v-switch 
                            v-model="item.dynamic" 
                            hide-details="" class="pa-0 ma-0"
                            v-if="item.children"
                          >
                            <template v-slot:label>
                              <span class="mt-2">Dynamic:</span> 
                              <v-chip small :color="item.dynamic ? 'primary' : '' " class="ml-2 mt-2"> 
                                {{ item.dynamic }} 
                              </v-chip>
                            </template>
                          </v-switch>
                        </v-col>
                      </v-row>
                    </template>
                  </v-treeview>
                </fieldset>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="text-right">
                <v-btn color="primary" class="mr-2" @click="addItem()" small>
                  add item
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
          <v-divider class="mb-3 mt-4"></v-divider>
          <v-card-actions class="pa-0">
            <v-btn
              color="primary"
              @click="save"
              :disabled="disabled"
              class="ml-4 mb-4 mr-1"
            >
              Update
            </v-btn>
            <v-btn color="#E0E0E0" to="/" class="mb-4"> Cancel </v-btn>
          </v-card-actions>
        </v-card>
      </v-main>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import { validationMixin } from "vuelidate";
import {
  required,
  maxLength,
  email,
  minLength,
  sameAs,
} from "vuelidate/lib/validators";

export default {
  mixins: [validationMixin],

  validations: {
    editedItem: {
      event_name: { required },
    },
  },
  data() {
    return {
      open_all: true,
      open: [],
      tree: [],
      absolute: true,
      overlay: false,
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Marketing Event Lists",
          disabled: false,
          link: "/marketing_event/index",
        },
        {
          text: "Edit Marketing Event",
          disabled: true,
        },
      ],
      disabled: false,

      editedItem: {
        event_name: "",
        attachment_required: "N"
      },
      defaultItem: {
        event_name: "",
        attachment_required: "N"
      },
      expense_particulars: [],
      errorFields: [],
      tree_view: [],
      deletedRows: [],
      addedRows: [],
      eventError: {
        event_name: [],
        attachment_required: [],
      },
      switch1: false,
    };
  },

  methods: {
    getMarketingEvent() {
      const data = {
        marketing_event_id: this.$route.params.marketing_event_id,
      };

      axios.post("/api/marketing_event/edit", data).then(
        (response) => {
          this.marketing_event = response.data.marketing_event;
          this.editedItem.event_name = this.marketing_event.event_name;
          this.editedItem.attachment_required = this.marketing_event.attachment_required;
          if(this.editedItem.attachment_required === 'Y')
          {
            this.switch1 = true;
          }
          let expense_particulars = this.marketing_event.expense_particulars;

          this.expense_particulars = [];

          expense_particulars.forEach((value, index) => {
            this.open.push(index);
            this.expense_particulars.push({
              index: index,
              id: value.id,
              description: value.description,
              children: [],
              hasError: false,
              dynamic: value.dynamic ? true : false,
            });

            let sub_items = value.expense_sub_particulars;

            if (sub_items.length) {
              sub_items.forEach((val, i) => {
                this.expense_particulars[index].children.push({
                  id: val.id,
                  parent_index: index,
                  description: val.description,
                  hasError: false,
                });
              });
            }
          });

        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
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

    save() {
      this.$v.$touch();
      let hasError = this.validateExpenseParticulars();
      this.eventError = {
        event_name: [],
        attachment_required: [],
      };
      
      if (!this.$v.$error && !hasError) {
        this.disabled = true;
        this.overlay = true;

        this.editedItem["expense_particulars"] = this.expense_particulars;

        const marketing_event_id = this.$route.params.marketing_event_id;

        const data = {
          event_name: this.editedItem.event_name,
          attachment_required: this.editedItem.attachment_required,
          expense_particulars: this.expense_particulars,
          deletedRows: this.deletedRows,
        };

        axios
          .post("/api/marketing_event/update/" + marketing_event_id, data)
          .then(
            (response) => {

              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "marketing-event-create" });
                this.getMarketingEvent();
                this.showAlert();
              } else {
                  let errors = response.data;
                  let errorNames = Object.keys(response.data);
                  errorNames.forEach(value => {
                    this.eventError[value].push(errors[value]);
                  });
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
    clear() {
      this.$v.$reset();
      this.editedItem = Object.assign({}, this.defaultItem);
      this.expense_particulars = [
        { description: "", children: [], hasError: false },
      ];
      this.eventError = {
        event_name: [],
        attachment_required: [],
      }
      this.switch1 = false;
    },

    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },
    addItem() {
      this.expense_particulars.push({
        description: "",
        dynamic: false,
        children: [],
        hasError: false,
      });

      let ctr = this.expense_particulars.length;
      let index = ctr > 0 ? ctr - 1 : 0;
     
      Object.assign(this.expense_particulars[index], { index: index });
    },
    addSubItem(item) {
      let index = this.expense_particulars.indexOf(item);
      let expense_particular = this.expense_particulars[index];

      // if current index has value then push data
      if (item.description) {
        expense_particular.children.push({
          parent_index: index,
          description: "",
          hasError: false,
        });
      }

      this.open.push(item.index); 
    },

    showConfirmAlert(item) {
      this.$swal({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Delete record!",
      }).then((result) => {
        if (result.value) {
          this.removeItem(item);
        }
      });
    },

    removeItem(item) {
      let index = this.expense_particulars.indexOf(item);
      let object_names = Object.keys(item);
      let hasChildren = false;
      let deletedRow = "";
      let ctr = this.expense_particulars.length;

      object_names.forEach((value) => {
        if (value === "children") {
          hasChildren = true;
        }
      });

      // if data has children
      if (hasChildren) {
        if (ctr > 1) {
          this.expense_particulars.splice(index, 1);
          deletedRow = "expense_particulars";
        }
      } else {
        let child_index =
          this.expense_particulars[item.parent_index].children.indexOf(item);

        this.expense_particulars[item.parent_index].children.splice(
          child_index,
          1
        );
        deletedRow = "expense_sub_particulars";
      }

      if (item.id) {
        this.deletedRows.push({ deletedRow: deletedRow, id: item.id });
      }

      // refresh/update parent_index field for child data
      this.expense_particulars.forEach((value, index) => {
        value.children.forEach((val, i) => {
          this.expense_particulars[index].children[i].parent_index = index;
        });
      });
    },
    validateExpenseParticulars() {
      let hasError = false;
      this.expense_particulars.forEach((value, index) => {
        
        // scan duplicated description
        this.expense_particulars.forEach((val, i) => {
          if(value.description === val.description && i > index)
          {
            hasError = true;
            this.expense_particulars[i].hasError = true;          
          }
        });

        if (!value.description) {
          hasError = true;
          this.expense_particulars[index].hasError = true
        }

        value.children.forEach((val, i) => {
          if (!val.description) {
            hasError = true;
            this.expense_particulars[index].children[i].hasError = true;
          }
        });

      });

      return hasError;
    },
    validateField(item)
    {
      if(item.children)
      {
        let index = item.index;

        let field = this.expense_particulars[index];
        
        field.hasError = false;
        // scan duplicated description
        this.expense_particulars.forEach((val, i) => {
          if(item.description === val.description && i != index)
          {
            field.hasError = true;
          }
        });

        if (!item.description) {
          field.hasError = true
        }
      }
      else
      {
        let index = item.parent_index;
        let expense_particular = this.expense_particulars[index];
        let children = expense_particular.children;
        let child_index = expense_particular.children.indexOf(item);
        let field = expense_particular.children[child_index];

        field.hasError = false;
        
        // scan duplicated description
        children.forEach((value, index) => {

          value.hasError = false; //set default value to false

          children.forEach((val, i) => {
            if(value.description === val.description && i != index)
            {
              value.hasError = true;
            }
          }); 

        });

        if (!item.description) {
          field.hasError = true
        }
      }
    }
  },
  computed: {
    eventErrors() {
      const errors = [];
      if (!this.$v.editedItem.event_name.$dirty) return errors;
      !this.$v.editedItem.event_name.required &&
        errors.push("Event Title is required.");
      return errors;
    },
    attachRequiredStatus() {
      if (this.switch1) {
        this.editedItem.attachment_required = "Y";
        return " Attachment Required";
      } else {
        this.editedItem.attachment_required = "N";
        return " Attachment Not Required";
      }

    },
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getMarketingEvent();
  },
};
</script>