<template>
  <div>
    <v-data-table
      :headers="headers"
      :items="items"
      :search="search"
      :loading="loading"
      loading-text="Loading... Please wait"
      v-if="canViewList"
    > 
      <template v-slot:top v-if="file_upload_log">
        <v-toolbar flat>
          <h6 class="my-0 font-weight-bold">Branch:</h6>  <v-chip color="secondary" class="ml-2">{{ branch }}</v-chip>
          <h6 class="my-0 font-weight-bold ml-4">Document Date:</h6>  <v-chip color="secondary" class="ml-2">{{ file_upload_log.docdate }}</v-chip>
          <h6 class="my-0 font-weight-bold ml-4">Uploaded Date:</h6>  <v-chip color="secondary" class="ml-2">{{ file_upload_log.date_uploaded }}</v-chip>
          <template v-if="file_upload_log.remarks">
            <h6 class="my-0 font-weight-bold ml-4">Warehouse:</h6>  <v-chip color="secondary" class="ml-2"> {{ whseCode(file_upload_log.remarks) }} </v-chip>
            <h6 class="my-0 font-weight-bold ml-4">Document Type:</h6>  <v-chip color="secondary" class="ml-2"> {{ inventoryType (file_upload_log.remarks) }} </v-chip>
          </template>   
        </v-toolbar>
      </template>
      <template v-slot:item.branch="{ item }">
        {{ item.branch.name ? item.branch.name : item.branch }}
      </template>
      <template v-slot:item.actions="{ item }">
        <v-icon
          small
          class="mr-2"
          color="green"
          @click="edit(item)"
          v-if="canEdit"
        >
          mdi-pencil
        </v-icon>
        <v-icon
          small
          color="red"
          @click="confirmDelete(item)"
          v-if="canDelete"
        >
          mdi-delete
        </v-icon>
      </template>
    </v-data-table>
  </div>
</template>
<script>

export default {
  name: "DataTable",
  props :[
    'headers',
    'items',
    'search',
    'loading',
    'branch',
    'file_upload_log', 
    'canViewList',
    'canEdit',
    'canDelete',
  ],
  data() {
    return {

    }
  },
  methods: {
    edit(item) {
      this.$emit("edit", item)
    },
    confirmDelete(item) {
      this.$emit("confirmDelete", item)
    },
    whseCode(item) {

      // split remarks with value 'ADMN-OVERALL'  or 'OVERALL'

      let splitted_remarks = item.split('-');

      // splitted_remarks.length is greater than 1 then get 'ADMN' from 'ADMN-OVERALL' value

      return splitted_remarks.length > 1 ? splitted_remarks[0] : '';
    },
    inventoryType(item) {

      // split remarks with value e.g 'ADMN-OVERALL' or 'OVERALL'

      let splitted_remarks = item.split('-');
 
      // splitted_remarks.length is equal to 1 then get original value 'OVERALL' else get the 'OVERALL' from 'ADMN-OVERALL'

      return splitted_remarks.length == 1 ? item : splitted_remarks[1];
    }
  },
  computed: {

  },
}

</script>
