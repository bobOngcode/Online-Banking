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
            <v-spacer></v-spacer>
          </v-card-title>

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
                            <v-btn
                              rounded
                              class="primary pa-4"
                              @click="playVideo(item)"
                            >
                              <v-icon class="mr-1">mdi-play-circle</v-icon> Play
                              Video</v-btn
                            >
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
                      <v-btn color="info" x-small @click="fileDownload(item)">
                        <v-icon small class="mr-1"> mdi-download </v-icon>
                        download
                      </v-btn>
                    </template>
                  </v-data-table>
                </v-container>
              </v-tab-item>
            </v-tabs>
          </v-card-text>
        </v-card>

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
      loading: true,
      headers: [
        { text: "Title", value: "title" },
        { text: "File Type", value: "file_type" },
        { text: "Actions", value: "actions", sortable: false, width: "120px" },
      ],
      items: [
        {
          text: "Home",
          disabled: false,
          link: "/",
        },
        {
          text: "Videos & Files",
          disabled: true,
        },
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
      dialog_video: false,
      search: "",
      autoplay: false,
      title: "",
      description: "",
      files: [],
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
    };
  },
  methods: {
    getFiles() {
      axios.get("api/training/files").then(
        (response) => {
          console.log(response.data);
          this.files = response.data.files;
          this.loaded = true;
          this.loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
          console.log(error);
        }
      );
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
    ...mapState("auth", ["user", "userIsLoaded"]),
    ...mapGetters("userRolesPermissions", ["hasRole", "hasPermission"]),
  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getFiles();
  },
};
</script>