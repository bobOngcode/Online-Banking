<template>
  <v-dialog v-model="dialog" max-width="950px" persistent>
    <v-card>
      <v-card-title class="pa-4">
        <span class="headline">Print Preview</span>
        <v-divider vertical class="ml-2"></v-divider>
        <v-tooltip top>
          <template v-slot:activator="{ on, attrs }">
            <v-btn 
              class="ml-2"
              color="primary" 
              icon 
              @click="print()"
              v-bind="attrs" 
              v-on="on"
            >
              <v-icon large>mdi-file-pdf</v-icon>
            </v-btn>
          </template>
          <span>Download PDF</span>
        </v-tooltip>
        <v-spacer></v-spacer>
        <v-btn @click="closeDialog()" icon>
          <v-icon> mdi-close-circle </v-icon>
        </v-btn>
      </v-card-title>
      <v-divider class="mt-0"></v-divider>
      <v-card-text id="card">
        <div class="text-right mb-2">
          <span >Date of Submission: ____________________________</span>
        </div>
        <div class="form-border">
          <div class="text-center mt-2">
            TACTICAL REQUISITION
          </div>
          <div class="mt-5">
            <table border="1">
              <tr v-for="i in 100" :key="i">
                <td>
                  {{ i }}
                </td>
              </tr>
            </table>
          </div>
        </div>
      </v-card-text>
      <v-divider class="mb-3 mt-0"></v-divider>
    </v-card>
  </v-dialog>
</template>
<style scoped>
  .form-border {
    border-style: solid;
    border-width: 0.2em;
  }

</style>
<script>
import html2pdf from "html2pdf.js";
export default {
  name: "PrintPreviewDialog",
  props: ["data", "dialog"],
  data() {
    return {

    }
  },
  methods: {
    print() {
      // html2pdf(document.getElementById("card"), {
			// 	margin: 1,
  		// 	filename: "card.pdf",
			// });

      var element = document.getElementById('card');
      var opt = {
        filename: 'myfile.pdf',
        jsPDF: { format: 'letter', orientation: 'portrait' },
      };

      // New Promise-based usage:
      html2pdf().set(opt).from(element).save();
    },
    closeDialog() {
      this.$emit("closePrintPreview");
    }
  }
}
</script>
