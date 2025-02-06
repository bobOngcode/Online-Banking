<template>
  <div>
    <v-card>
      <v-card-title>
        Branches 
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search"
          single-line
        ></v-text-field>
        <v-spacer></v-spacer>
      </v-card-title>
      <v-data-table
        :headers="headers"
        :items="branches"
        :search="search"
        :loading="loading"
        group-by="name"
        class="elevation-1"
        :expanded.sync="expanded"
        loading-text="Loading... Please wait"
        v-if="canViewList"
      >
        <template v-slot:item.date_uploaded="{ item }">
          <v-chip color="secondary" v-if="item.date_uploaded">
            {{ item.date_uploaded }}
          </v-chip>
        </template>
        <template v-slot:group.header="{ items, headers, toggle, isOpen, }">
          <td colspan="4">
            <v-row>
              <v-col>
                <v-btn @click="toggle" small icon :ref="items" :data-open="isOpen">
                  <v-icon v-if="isOpen">mdi-chevron-up</v-icon>
                  <v-icon v-else>mdi-chevron-down</v-icon>
                </v-btn>
                {{ items[0].name }}
              </v-col>
            </v-row>
          </td>
          <td> 
            <v-menu offset-y v-if="canDownloadTemplate && canImport">
              <template v-slot:activator="{ on, attrs }">
                <v-btn x-small v-bind="attrs" v-on="on" class="primary">
                  Actions
                  <v-icon small> mdi-menu-down </v-icon>
                </v-btn>
              </template>
              <v-list class="pa-1">
                <template v-for="list in actionListTblHdr">
                  <v-list-item class="ma-0 pa-0" style="min-height: 25px">
                    <v-list-item-title>
                      <v-btn x-small @click="callMethod(list.method, items)" class="mx-1 white--text" width="100px" :color="list.color">
                        <v-icon class="mr-1" x-small> {{ list.icon }} </v-icon>
                        {{ list.title }}
                      </v-btn>
                    </v-list-item-title>
                  </v-list-item>
                </template>
              </v-list>
            </v-menu>
            <v-btn x-small color="primary" @click="callMethod('importExcel', items)" v-if="canImport && !canDownloadTemplate"> 
              <v-icon x-small class="mr-1">mdi-upload</v-icon> import
            </v-btn> 
          </td>
        </template>
        <template v-slot:item="{ item }">
          <tr v-for="(value, index) in item.file_upload_logs">
            <td> </td>
            <td> {{ value.user ? value.user.name : '' }} </td>
            <td> <v-chip color="secondary"> {{ value.date_uploaded }} </v-chip> </td>
            <td> <v-chip color="secondary"> {{ value.docdate }} </v-chip> </td>
            <td>
              <v-menu offset-y v-if="canImport || canExport || canDownload">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn x-small v-bind="attrs" v-on="on" class="primary--text">
                    Actions
                    <v-icon small> mdi-menu-down </v-icon>
                  </v-btn>
                </template>
                <v-list class="pa-1">
                  <template v-for="list in actionListTblRow">
                    <v-list-item class="ma-0 pa-0" style="min-height: 25px" v-if="list.hasPermission">
                      <v-list-item-title>
                        <v-btn x-small @click="callMethod(list.method, value)" class="mx-1 white--text" width="100px" :color="list.color">
                          <v-icon class="mr-1" x-small> {{ list.icon }} </v-icon>
                          {{ list.title }}
                        </v-btn>
                      </v-list-item-title>
                    </v-list-item>
                  </template>
                </v-list>
              </v-menu>
            </td>
          </tr>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>

<script>

export default {
  name: "DataTableGroup",
  props: [
    'branches',
    'loading',
    'canViewList',
    'canImport',
    'canExport',
    'canDownload',
    'canDownloadTemplate',
    'canClearList',
  ],
  data() {
    return {
      search: "",
      headers: [
        { text: "Branch", value: "grp_branch" },
        { text: "", value: "name" },
        { text: "Uploaded By", value: "uploaded_by" },
        { text: "Date Uploaded", value: "date_uploaded" },
        { text: "Document Date", value: "docdate" },
        { text: "Actions", value: "actions", sortable: false, width: "120px" },
      ],
      expanded: [],
    }
  },
  methods: {
    callMethod(method, item) {
      this.$emit(method, item);
    },

  },
  computed: {
    actionListTblHdr(){
       let menu = [
        {
          title: 'Import',
          icon: 'mdi-upload',
          method: 'importExcel',
          hasPermission: true,
          color: 'primary',
        },
        {
          title: 'Template',
          icon: 'mdi-download',
          method: 'downloadTemplate',
          hasPermission: this.canDownloadTemplate,
          color: '#AB47BC',
        },
       ];

       return menu;
    },
    actionListTblRow(){
       let menu = [
        {
          title: 'View',
          icon: 'mdi-eye',
          method: 'viewList',
          hasPermission: true,
          color: 'info',
        },
        {
          title: 'Export',
          icon: 'mdi-microsoft-excel',
          method: 'exportData',
          hasPermission: this.canExport,
          color: 'success',
        },
        {
          title: 'File',
          icon: 'mdi-download',
          method: 'downloadFile',
          hasPermission: this.canDownload,
          color: 'primary',
        },
        {
          title: 'Delete',
          icon: 'mdi-delete',
          method: 'clearList',
          hasPermission: this.canClearList,
          color: 'error',
        },
       ];

       return menu;
    },
  }
}

</script>
