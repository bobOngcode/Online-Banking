<template>
  <div>
    <v-dialog v-model="dialog" max-width="500px" persistent>
      <v-card>
        <v-card-title class="pa-4">
          <span class="headline">{{ formTitle }}</span>
        </v-card-title>
        <v-divider class="mt-0"></v-divider>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col class="my-0 py-0">
                <v-autocomplete
                  v-model="editedItem.brand_id"
                  :items="brands"
                  item-text="name"
                  item-value="id"
                  label="Brand"
                  required
                  :error-messages="brandErrors"
                  @input="
                    $v.editedItem.brand_id.$touch() +
                      (serialExists = false)
                  "
                  @blur="$v.editedItem.brand_id.$touch()"
                >
                </v-autocomplete>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="my-0 py-0">
                <v-text-field
                  name="model"
                  label="Model"
                  v-model="editedItem.model"
                  required
                  :error-messages="modelErrors"
                  @input="
                    $v.editedItem.model.$touch() +
                      (serialExists = false)
                  "
                  @blur="$v.editedItem.model.$touch()"
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="my-0 py-0">
                <v-autocomplete
                  v-model="editedItem.product_category_id"
                  :items="product_categories"
                  item-text="name"
                  item-value="id"
                  label="Product Category"
                  required
                  :error-messages="product_categoryErrors"
                  @input="
                    $v.editedItem.product_category_id.$touch() +
                      (serialExists = false) +
                      selectedProductCategory()
                  "
                  @blur="$v.editedItem.product_category_id.$touch()"
                >
                </v-autocomplete>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="my-0 py-0">
                <v-text-field
                  name="serial"
                  label="Serial"
                  v-model="editedItem.serial"
                  readonly
                  required
                  :error-messages="serialErrors"
                  @input="$v.editedItem.serial.$touch()"
                  @blur="$v.editedItem.serial.$touch()"
                ></v-text-field>
              </v-col>
            </v-row>
            <v-row>
              <v-col class="my-0 py-0">
                <v-autocomplete
                  v-model="editedItem.branch_id"
                  :items="branches"
                  item-text="name"
                  item-value="id"
                  label="Branch"
                  required
                  :error-messages="branchErrors"
                  @input="$v.editedItem.branch_id.$touch()"
                  @blur="$v.editedItem.branch_id.$touch()"
                  v-if="user.id === 1"
                >
                </v-autocomplete>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
        <v-divider class="mb-3 mt-0"></v-divider>
        <v-card-actions class="pa-0">
          <v-spacer></v-spacer>
          <v-btn color="#E0E0E0" @click="close" class="mb-3">
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            @click="save"
            class="mb-3 mr-4"
            :disabled="disabled"
          >
            Save
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>

</template>

<script>

export default {
  name: "DialogProductFields",
  props: [
    'editedItem',
    'dialog',
    'brands',
    
  ],

  data() {
    return {

    }
  }
}
</script>
