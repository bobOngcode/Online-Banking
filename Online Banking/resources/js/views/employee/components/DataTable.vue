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
        <v-toolbar
          flat
        >
          <h6 class="my-0 font-weight-bold">Document Date:</h6>  <v-chip color="secondary" class="ml-2">{{ file_upload_log.docdate }}</v-chip>
          <h6 class="my-0 font-weight-bold ml-8">Uploaded Date:</h6>  <v-chip color="secondary" class="ml-2">{{ file_upload_log.date_uploaded }}</v-chip>
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
    }
  },
  computed: {

  },
}

</script>
