<template>
  <div 
    class="d-flex justify-content-end mb-3" 
    v-if="menu.hasPermission"
  >
    <v-menu offset-y :close-on-content-click="false">
      <template v-slot:activator="{ on, attrs }">
        <v-btn small v-bind="attrs" v-on="on" color="primary">
          Actions
          <v-icon small> mdi-menu-down </v-icon>
        </v-btn>
      </template>
      <v-list class="pa-1">
        <template v-for="list in menu.list">
          <v-list-item
            class="ma-0 pa-0"
            style="min-height: 25px"
            v-if="list.hasPermission"
          >
            <v-list-item-title>
              <v-btn
                class="mx-1 white--text"
                :color="list.color"
                width="100px"
                x-small
                @click="callMethod(list.method)"
              >
                <v-icon class="mr-1" x-small> {{ list.icon }} </v-icon>
                {{ list.text }}
              </v-btn>
            </v-list-item-title>
          </v-list-item>
        </template>
      </v-list>
    </v-menu>
  </div>
</template>

<script>

import axios from "axios";

export default {
  name: "MenuActions",
  props: [
    'canReconcile',
    'canDownloadTemplate',
    'canImport',
    'canSync',
    'canExport',
    'canDownloadFile',
    'canPrintPDF',
    'canClearList',
    'export_url',
    'download_url',
    'file_upload_log_id',
  ],

  data () {
    return {
      api_url: "",
    }
  },
  methods: {
    callMethod(method) {
      this.$emit(method)
      // ['export', 'download'].includes(method) ? this.exportData(method) : this.$emit(method);
    },
    exportData(method) {
      if (this.data.length) 
      {
        
        const data = { file_upload_log_id: this.file_upload_log_id };

        let file_type = method === 'export' ? 'xls' : 'dat';

        axios.post(this.export_url, data, { responseType: 'arraybuffer'})
          .then((response) => {
              var fileURL = window.URL.createObjectURL(new Blob([response.data]));
              var fileLink = document.createElement('a');
              fileLink.href = fileURL;
              fileLink.setAttribute('download', 'EmployeeList.' + file_type);
              document.body.appendChild(fileLink);
              fileLink.click();
          }, (error) => {
            console.log(error);
          }
        );
      
      } else {
        this.showAlert("No record found", "warning")
      }
    },
    showAlert(title, icon) {
      this.$swal({
        position: "center",
        icon: icon,
        title: title,
        showConfirmButton: false,
        timer: 2500,
      });
    },
  },
  computed: {
    menu() {
      let menu = { 
        hasPermission: false,
        list: [
          { 
            text: "Reconcile", 
            icon: "mdi-file-multiple", 
            color: "purple lighten-1", 
            method: "reconcile",
            hasPermission: this.canReconcile,
          },
          { 
            text: "Template", 
            icon: "mdi-download", 
            color: "#AB47BC", 
            method: "downloadTemplate",
            hasPermission: this.canDownloadTemplate 
          },
          { 
            text: "File", 
            icon: "mdi-download", 
            color: "info", 
            method: "download",
            hasPermission: this.canDownloadFile 
          },
          { 
            text: "Import", 
            icon: "mdi-import", 
            color: "primary", 
            method: "import",
            hasPermission: this.canImport 
          },
          { 
            text: "Sync", 
            icon: "mdi-sync", 
            color: "info", 
            method: "sync",
            hasPermission: this.canSync 
          },
          { 
            text: "Export", 
            icon: "mdi-microsoft-excel", 
            color: "success", 
            method: "export",
            hasPermission: this.canExport 
          },
          { 
            text: "Print PDF", 
            icon: "mdi-microsoft-excel", 
            color: "secondary", 
            method: "printPDF",
            hasPermission: this.canPrintPDF 
          },
          { 
            text: "Clear List", 
            icon: "mdi-delete", 
            color: "error", 
            method: "clearList",
            hasPermission: this.canClearList 
          },
        ]
      };  

      menu.list.forEach(v => {
        if(v.hasPermission)
        {
          menu.hasPermission = true;
        }
      });

      return menu;
    }
  }
}
</script>
