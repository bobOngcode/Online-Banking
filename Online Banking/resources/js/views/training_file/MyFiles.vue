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

        <div class="d-flex justify-content-end mb-3">
          <div>
            <v-menu offset-y v-if="hasPermission('file-create')">
              <template v-slot:activator="{ on, attrs }">
                <v-btn small v-bind="attrs" v-on="on" color="primary">
                  Actions
                  <v-icon small> mdi-menu-down </v-icon>
                </v-btn>
              </template>
              <v-list class="pa-1">
                <v-list-item
                  class="ma-0 pa-0"
                  style="min-height: 25px"
                  v-if="hasPermission('file-create')"
                >
                  <v-list-item-title>
                    <v-btn
                      color="primary"
                      class="mx-1"
                      width="100px"
                      x-small
                      @click="uploadFile()"
                    >
                      <v-icon class="mr-1" x-small> mdi-import </v-icon>
                      Upload
                    </v-btn>
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </div>
        </div>

        <v-card>
          <v-card-text>
            <v-tabs color="primary" left>
              <v-tab>Videos</v-tab>
              <v-tab>Files</v-tab>
              <v-spacer></v-spacer>
              <v-text-field
                class="ma-0"
                v-model="search"
                append-icon="mdi-magnify"
                label="Search"
                single-line
              ></v-text-field>
              <v-spacer></v-spacer>
              <v-tab-item>
                <v-divider></v-divider>
                <v-container fluid>
                  <v-row v-if="!filteredVideos.length && loaded">
                    <v-col class="text-center">
                      <h4 class="pa-4">No Record Found</h4>
                    </v-col>
                  </v-row>
                  <v-row>
                    <template v-for="(item, index) in filteredVideos">
                      <v-col cols="12" sm="6" md="4" lg="3" xl="3">
                        <v-card>
                          <v-img
                            src="/img/film-colored.png"
                            class="ml-4 mr-4"
                          ></v-img>
                          <v-tooltip top>
                            <template v-slot:activator="{ on, attrs }">
                              <v-card-title
                                class="justify-center"
                                v-bind="attrs"
                                v-on="on"
                              >
                                {{
                                  item.title.length > 20
                                    ? item.title.substr(0, 17) + "..."
                                    : item.title
                                }}
                              </v-card-title>
                            </template>
                            <span> {{ item.title }} </span>
                          </v-tooltip>
                          <v-card-actions class="justify-center grey lighten-3">
                            <template v-if="">

                            </template>
                            <v-btn
                              @click="playVideo(item)"
                              icon
                              v-if="user.id !== 1 ? user.id === item.user_id && hasAnyPermission('file-edit', 'file-delete') : true"
                            >
                              <v-icon color="primary">mdi-play-circle</v-icon>
                            </v-btn>
                            <v-btn
                              icon
                              v-if="hasPermission('file-edit') && (user.id !== 1 ? user.id === item.user_id : true)"
                              @click="editFile(item)"
                            >
                              <v-icon color="success">mdi-pencil</v-icon>
                            </v-btn>

                            <v-btn
                              icon
                              v-if="hasPermission('file-delete') && (user.id !== 1 ? user.id === item.user_id : true) "
                              @click="showConfirmAlert(item)"
                            >
                              <v-icon color="red">mdi-delete</v-icon>
                            </v-btn>
                          </v-card-actions>
                        </v-card>
                      </v-col>
                    </template>
                  </v-row>
                </v-container>
              </v-tab-item>
              <v-tab-item>
                <v-container fluid>
                  <v-data-table
                    :headers="headers"
                    :items="filteredOtherFiles"
                    :search="search"
                    :loading="loading"
                    loading-text="Loading... Please wait"
                    v-if="hasPermission('file-list')"
                  >
                    <template v-slot:item.actions="{ item }">
                      <v-icon
                        small
                        class="mr-2"
                        color="primary"
                        @click="fileDownload(item)"
                      >
                        mdi-download
                      </v-icon>

                      <v-icon
                        small
                        class="mr-2"
                        color="green"
                        @click="editFile(item)"
                        v-if="hasPermission('file-edit')"
                      >
                        mdi-pencil
                      </v-icon>
                      <v-icon
                        small
                        color="red"
                        @click="showConfirmAlert(item)"
                        v-if="hasPermission('file-delete')"
                      >
                        mdi-delete
                      </v-icon>
                    </template>
                  </v-data-table>
                </v-container>
              </v-tab-item>
            </v-tabs>
          </v-card-text>
        </v-card>
        <v-dialog v-model="dialog_upload" max-width="500px" persistent>
          <v-card>
            <v-card-title class="mb-0 pb-0">
              <span class="headline"> {{ formTitle }} </span>
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text>
              <v-container>
                <v-row v-if="editedIndex === -1">
                  <v-col class="my-0 py-0">
                    <v-file-input
                      v-model="file"
                      show-size
                      label="File input"
                      prepend-icon="mdi-paperclip"
                      required
                      :error-messages="fileErrors"
                      @change="$v.file.$touch() + (fileIsEmpty = false)"
                      @blur="$v.file.$touch()"
                    >
                      <template v-slot:selection="{ text }">
                        <v-chip small label color="primary">
                          {{ text }}
                        </v-chip>
                      </template>
                    </v-file-input>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col class="my-0 py-0">
                    <v-text-field
                      name="title"
                      v-model="editedItem.title"
                      :error-messages="titleErrors"
                      label="Title"
                      @input="$v.editedItem.title.$touch()"
                      @blur="$v.editedItem.title.$touch()"
                    ></v-text-field>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col class="my-0 py-0">
                    <v-textarea
                      rows="3"
                      label="Description"
                      v-model="editedItem.description"
                    ></v-textarea>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col>
                    <v-autocomplete
                      v-model="editedItem.permitted_positions"
                      :items="positions"
                      item-text="name"
                      item-value="id"
                      label="Permitted Positions"
                      multiple
                      chips
                      :error-messages="permittedPositionsErrors"
                      @input="$v.editedItem.permitted_positions.$touch()"
                      @blur="$v.editedItem.permitted_positions.$touch()"
                    >
                      <template v-slot:selection="data">
                        <v-chip
                          color="secondary"
                          v-bind="data.attrs"
                          :input-value="data.selected"
                          @click="data.select"
                        >
                          {{ data.item.name }}
                        </v-chip>
                      </template>
                    </v-autocomplete>
                  </v-col>
                </v-row>
                <v-row
                  class="fill-height"
                  align-content="center"
                  justify="center"
                  v-if="uploading"
                >
                  <v-col class="subtitle-1 text-center" cols="12">
                    Uploading...
                  </v-col>
                  <v-col cols="6">
                    <v-progress-linear
                      color="primary"
                      indeterminate
                      rounded
                      height="6"
                    ></v-progress-linear>
                  </v-col>
                </v-row>
              </v-container>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn
                color="#E0E0E0"
                @click="(dialog_upload = false) + clear()"
                class="mb-4"
              >
                Cancel
              </v-btn>
              <v-btn
                color="primary"
                class="mb-4 mr-4"
                @click="save()"
                :disabled="uploadDisabled"
              >
                {{ editedIndex === -1 ? "Upload" : "Update" }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <v-dialog v-model="dialog_video" max-width="1050px" persistent>
          <v-card>
            <v-card-title class="secondary white--text">
              <span class="headline">{{ video_title }}</span>
              <v-spacer></v-spacer>
              <v-icon @click="closeVideo()" class="white--text">
                mdi-close-circle
              </v-icon>
            </v-card-title>
            <v-card-text class="mt-4">
              <v-row>
                <v-col class="text-center">
                  <Media
                    :kind="'video'"
                    :isMuted="false"
                    :src="[video_src]"
                    poster="/img/video-play.jpg"
                    :autoplay="autoplay"
                    :controls="true"
                    :ref="'fish'"
                    :style="{ width: '1000px', height: '500px' }"
                  >
                  </Media>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-dialog>
      </v-main>
    </div>
  </div>
</template>
<script>
import Media from "@dongido/vue-viaudio";
import axios from "axios";
import { validationMixin } from "vuelidate";
import { required, maxLength, email } from "vuelidate/lib/validators";
import { mapState, mapGetters } from "vuex";
export default {
  mixins: [validationMixin],
  components: {
    Media,
  },
  validations: {
    editedItem: {
      title: { required },
      permitted_positions: { required },
    },
    file: { required },
  },
  data() {
    return {
      absolute: true,
      overlay: false,
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "My Files",
          disabled: true,
        },
      ],
      headers: [
        { text: "Title", value: "title" },
        { text: "File Type", value: "file_type" },
        { text: "Actions", value: "actions", sortable: false, width: "120px" },
      ],
      video: {
        sources: [
          {
            src: "/videos/dontspeak.mp4",
            type: "video/mp4",
          },
        ],
        errorMsg: "ERROR!",
      },
      file: [],
      fileIsEmpty: false,
      fileIsInvalid: false,
      uploadDisabled: false,
      uploading: false,
      dialog_upload: false,
      dialog_video: false,
      search: "",
      autoplay: false,
      title: "",
      description: "",
      files: [],
      positions: [],
      permitted_positions: "",
      video_src: "",
      video_title: "",
      loaded: false,
      editedIndex: -1,
      editedItem: {
        title: "",
        description: "",
        permitted_positions: "",
      },
      defaultItem: {
        title: "",
        description: "",
        permitted_positions: "",
      },
      loading: true,
    };
  },
  methods: {
    getFiles() {
      axios.get("api/training/user_files").then(
        (response) => {
          this.files = response.data.files;
          this.positions = response.data.positions;
          this.loaded = true;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
          console.log(error);
        }
      );
    },

    uploadFile() {
      this.dialog_upload = true;
      this.file = [];
      this.$v.$reset();
    },
    save() {
      if (this.editedIndex === -1) {
        this.$v.$touch();
      } else {
        this.$v.editedItem.$touch();
      }

      this.fileIsEmpty = false;

      if (this.editedIndex === -1) {
        this.$v.$touch();
        if (!this.$v.$error && !this.fileIsInvalid) {
          this.uploadDisabled = true;
          this.uploading = true;
          let formData = new FormData();

          formData.append("file", this.file);
          formData.append("title", this.editedItem.title);
          formData.append("description", this.editedItem.description);
          formData.append(
            "permitted_positions",
            this.editedItem.permitted_positions
          );

          axios
            .post("api/training/file/upload", formData, {
              headers: {
                Authorization: "Bearer " + localStorage.getItem("access_token"),
                "Content-Type": "multipart/form-data",
              },
            })
            .then(
              (response) => {
                if (response.data.success) {
                  // send data to Socket.IO Server
                  // this.$socket.emit("sendData", { action: "import-project" });

                  this.$swal({
                    position: "center",
                    icon: "success",
                    title: "Record has been uploaded",
                    showConfirmButton: false,
                    timer: 2500,
                  });

                  this.clear();
                  this.dialog_upload = false;
                  this.getFiles();
                } else {
                  this.fileIsInvalid = true;
                }

                console.log(response.data);

                this.uploadDisabled = false;
                this.uploading = false;
              },
              (error) => {
                this.isUnauthorized(error);
                this.uploadDisabled = false;
                console.log(error);
              }
            );
        }
      } else {
        if (!this.$v.$error) {
          let formData = new FormData();

          formData.append("title", this.editedItem.title);
          formData.append("description", this.editedItem.description);
          formData.append(
            "permitted_positions",
            this.editedItem.permitted_positions
          );

          const training_file_id = this.editedItem.id;

          axios
            .post("api/training/file/update/" + training_file_id, formData, {
              headers: {
                Authorization: "Bearer " + localStorage.getItem("access_token"),
                "Content-Type": "multipart/form-data",
              },
            })
            .then(
              (response) => {
                if (response.data.success) {
                  // send data to Socket.IO Server
                  // this.$socket.emit("sendData", { action: "import-project" });

                  this.$swal({
                    position: "center",
                    icon: "success",
                    title: "Record has been updated",
                    showConfirmButton: false,
                    timer: 2500,
                  });

                  Object.assign(
                    this.files[this.editedIndex],
                    response.data.video_details
                  );

                  this.clear();
                  this.dialog_upload = false;
                  // this.getFiles();
                }
              },
              (error) => {
                this.isUnauthorized(error);
                console.log(error);
              }
            );
        }
      }
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
        // <--

        if (result.value) {
          // <-- if confirmed

          const training_file_id = item.id;
          const index = this.files.indexOf(item);

          //Call delete Training file function
          this.deleteFile(training_file_id);

          //Remove item from array companies
          this.files.splice(index, 1);

          this.$swal({
            position: "center",
            icon: "success",
            title: "Record has been deleted",
            showConfirmButton: false,
            timer: 2500,
          });
        }
      });
    },

    editFile(item) {
      this.editedIndex = this.files.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog_upload = true;

      let permitted_positions = [];

      item.file_has_permissions.forEach((value) => {
        permitted_positions.push(value.position_id);
      });

      this.editedItem.permitted_positions = permitted_positions;
    },

    deleteFile(training_file_id) {
      const data = { training_file_id: training_file_id };

      axios.post("/api/training/file/delete", data).then(
        (response) => {
          if (response.data.success) {
            // send data to Sockot.IO Server
            // this.$socket.emit("sendData", { action: "employee-delete" });
          }
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },
    clear() {
      this.$v.$reset();
      this.dialog_upload = false;
      this.fileIsEmpty = false;
      this.editedItem = Object.assign({}, this.defaultItem);
      this.editedIndex = -1;
      this.file = [];
    },
    playVideo(item) {
      let video_src = item.file_path + "/" + item.file_name;
      this.video_src = video_src;
      this.video_title = item.title;
      this.autoplay = true;
      this.dialog_video = true;
    },
    closeVideo() {
      this.autoplay = false;
      this.dialog_video = false;
      this.video_src = "";
      this.video_title = "";
    },

    fileDownload(item) {
      window.open(location.origin + "/api/training/file/download?id=" + item.id, "_blank");
    },
    isUnauthorized(error) {
      // if unauthenticated (401)
      if (error.response.status == "401") {
        this.$router.push({ name: "unauthorize" });
      }
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Upload Video" : "Edit Video Details";
    },
    filteredVideos() {
      let videos = [];
      let video_type = [
        "mp4",
        "m4v",
        "mkv",
        "mov",
        "wmv",
        "avi",
        "avchd",
        "flv",
        "f4v",
        "swf",
      ];

      this.files.forEach((value) => {
        if (video_type.includes(value.file_type)) {
          videos.push(value);
        }
      });

      if (this.search) {
        return videos.filter((item) => {
          return this.search
            .toLowerCase()
            .split(" ")
            .every((v) => item.title.toLowerCase().includes(v));
        });
      } else {
        return videos;
      }
    },
    filteredOtherFiles() {
      let other_files = [];
      let video_type = [
        "mp4",
        "m4v",
        "mkv",
        "mov",
        "wmv",
        "avi",
        "avchd",
        "flv",
        "f4v",
        "swf",
      ];

      this.files.forEach((value) => {
        if (!video_type.includes(value.file_type)) {
          other_files.push(value);
        }
      });

      if (this.search) {
        return other_files.filter((item) => {
          return this.search
            .toLowerCase()
            .split(" ")
            .every((v) => item.title.toLowerCase().includes(v));
        });
      } else {
        return other_files;
      }
    },
    fileErrors() {
      const errors = [];
      if (!this.$v.file.$dirty) return errors;
      !this.$v.file.required && errors.push("File is required.");
      this.fileIsEmpty && errors.push("File is empty.");

      this.fileIsInvalid = false;

      // if (this.file) {
      //   let file_type = [];
      //   let ctr = 0;

      //   if (this.file.name) {
      //     file_type = this.file.name.split(".");
      //     ctr = file_type.length - 1;
      //   }

      //   if (file_type[ctr] != "mp4") {
      //     this.fileIsInvalid = true;
      //   }
      // }

      this.fileIsInvalid && errors.push("File type must be 'mp4'.");
      return errors;
    },
    titleErrors() {
      const errors = [];
      if (!this.$v.editedItem.title.$dirty) return errors;
      !this.$v.editedItem.title.required && errors.push("Title is required.");
      return errors;
    },
    permittedPositionsErrors() {
      const errors = [];
      if (!this.$v.editedItem.permitted_positions.$dirty) return errors;
      !this.$v.editedItem.permitted_positions.required &&
        errors.push("Permitted Positions is required.");
      return errors;
    },
    ...mapState("auth", ["user", "userIsLoaded"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasAnyRole", "hasPermission", "hasAnyPermission"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getFiles();
  },
};
</script>