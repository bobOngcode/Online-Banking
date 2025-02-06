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
            <span class="headline">Access Level</span>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="ml-2">
            <v-row>
              <v-col cols="4" class="my-0 py-0">
                <v-text-field-integer
                  name="access_level"
                  v-model="level"
                  label="Access Level"
                  v-bind:properties="{
                    placeholder: '0',
                    maxLength: 6,
                    messages: accessLevelErrors,
                    error: accessLevelErrors.length ? true : false
                  }"
                  required                
                  
                  @input="$v.level.$touch()"
                  @blur="$v.level.$touch()"
                >
                </v-text-field-integer>
              </v-col>
            </v-row>
          </v-card-text>
          <v-divider class="mb-3 mt-4"></v-divider>
          <v-card-actions class="pa-0">
            <v-btn
              color="primary"
              @click="save"
              :disabled="disabled"
              class="ml-6 mb-4 mr-1"
            >
              Save
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
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapGetters } from "vuex";

export default {
  mixins: [validationMixin],

  validations: {
    level: { required },
  },
  data() {
    return {
      absolute: true,
      overlay: false,
      disabled: false,
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/dashboard",
        },
        {
          text: "Access Level",
          disabled: true,
        },
      ],
      start: "",
      active: false,
      access_level: [],
      level: "",
    };
  },

  methods: {
    getAccessLevel() {
      axios.get("/api/access_chart/get_access_level").then(
        (response) => {
          this.access_level = response.data.access_level;
          this.level = this.access_level.level;
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
      if (!this.$v.$error) {
        this.disabled = true;
        this.overlay = true;

        const data = {
          level: this.level,
        };

        let access_level_id = this.access_level.id;

        axios
          .post(
            "/api/access_chart/update_access_level/" + access_level_id,
            data
          )
          .then(
            (response) => {
              if (response.data.success) {
                // send data to Sockot.IO Server
                // this.$socket.emit("sendData", { action: "access-level-edit" });
                this.showAlert();
              }
              this.overlay = false;
              this.disabled = false;
            },
            (error) => {
              console.log(error);
              this.overlay = false;
              this.disabled = false;
            }
          );
      }
    },
    clear() {
      this.$v.$reset();
      this.access_level = "";
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

        if (action == "project-create") {
          this.getAccessLevel();
        }
      };
    },
  },
  computed: {
    accessLevelErrors() {
      const errors = [];
      if (!this.$v.level.$dirty) return errors;
      !this.$v.level.required && errors.push("Access Level is required.");
      return errors;
    },

    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getAccessLevel();
  },
};
</script>