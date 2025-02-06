<template>
  <div>
    <v-dialog v-model="dialog" max-width="500px" persistent>
      <v-card>
        <v-card-title class="pa-4">
          <span class="headline">Attach file</span>
        </v-card-title>
        <v-divider class="mt-0"></v-divider>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col class="my-0 py-0">
                <v-file-input v-model="file" show-size label="File input" prepend-icon="mdi-paperclip" required multiple
                  :error-messages="fileErrors" @input="$v.file.$touch()" @blur="$v.file.$touch()">
                  <template v-slot:selection="{ index, text }">
                    <v-chip small label color="primary" close @click:close="removeFile(index, text)">
                      {{ text }}
                    </v-chip>
                  </template>
                </v-file-input>
              </v-col>
            </v-row>
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
          <v-btn color="primary" class="mb-3 mr-4" @click="closeDialog()">
            OK
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
import { required, requiredIf } from "vuelidate/lib/validators";
export default {
  name: "AttachFileDialog",
  props: ['attachment_required', 'dialog', 'fileIsRequired'],
  validations: {
    // file: { required },
    file: {
      required: requiredIf(function () {
        return this.fileIsRequired;
      }),
    },
  },
  data() {
    return {
      file: [],
    }
  },
  methods: {
    closeDialog() {
      this.$emit('closeAttachFileDialog');
    },
    removeFile(index, text) {
      this.file.splice(index, 1)
    },
    resetData() {
      this.$v.$reset();
      this.file = [];
    }
  },
  computed: {
    fileErrors() {
      // if(this.fileIsRequired)
      // {
      //   if(this.$v.editedItem.file.$dirty)
      //   { 
      //     return "Attachment is required!";
      //   }
      // }
      const errors = [];

      if (!this.$v.file.$dirty) return errors;
      !this.$v.file.required &&
        errors.push("Attachment is required!");

      return errors;

    },
  }
}
</script>
