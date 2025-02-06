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
        <v-skeleton-loader
          v-if="skeleton_loading"
          type="table-heading, table-thead, table-tbody, table-row"
          :types=" {'table-thead': 'heading@7', 'table-tbody': 'table-row-divider@5', 'table-row': 'table-cell@7'} "
        ></v-skeleton-loader>
        <v-row class="fill-height" v-if="!skeleton_loading">
          <v-col>
            <v-sheet height="64">
              <v-toolbar
                flat
              >
                <v-btn
                  outlined
                  class="mr-4"
                  color="grey darken-2"
                  @click="setToday"
                >
                  Today
                </v-btn>
                <v-btn
                  fab
                  text
                  small
                  color="grey darken-2"
                  @click="prev"
                >
                  <v-icon small>
                    mdi-chevron-left
                  </v-icon>
                </v-btn>
                <v-btn
                  fab
                  text
                  small
                  color="grey darken-2"
                  @click="next"
                >
                  <v-icon small>
                    mdi-chevron-right
                  </v-icon>
                </v-btn>
                <v-toolbar-title v-if="$refs.calendar">
                  {{ $refs.calendar.title }}
                </v-toolbar-title>
                <v-spacer></v-spacer>
                <v-menu
                  bottom
                  right
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn
                      outlined
                      color="grey darken-2"
                      v-bind="attrs"
                      v-on="on"
                    >
                      <span>{{ typeToLabel[type] }}</span>
                      <v-icon right>
                        mdi-menu-down
                      </v-icon>
                    </v-btn>
                  </template>
                  <v-list>
                    <v-list-item @click="type = 'day'">
                      <v-list-item-title>Day</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="type = 'week'">
                      <v-list-item-title>Week</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="type = 'month'">
                      <v-list-item-title>Month</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="type = '4day'">
                      <v-list-item-title>4 days</v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </v-toolbar>
            </v-sheet>
            <v-sheet height="600">
              <v-calendar
                ref="calendar"
                v-model="focus"
                color="primary"
                :events="events"
                :event-color="getEventColor"
                :type="type"
                @click:event="showEvent"
                @click:more="viewDay"
                @click:date="viewDay"
                @change="updateRange"
              ></v-calendar>
              <v-menu
                v-model="selectedOpen"
                :close-on-content-click="false"
                :activator="selectedElement"
                offset-x
                max-width="420px"
              >
                <v-card olor="grey lighten-4" min-width="420px" flat>
                  <v-toolbar :color="selectedEvent.color" dark>
                    <v-toolbar-title v-html="selectedEvent.name"></v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon @click="selectedOpen = false">
                      <v-icon>mdi-close-circle</v-icon>
                    </v-btn>
                  </v-toolbar>
                  <v-card-text class="py-6">
                    <v-row>
                      <v-col class="mt-2 py-1" cols="12" md="4">
                        <h6 class="font-weight-bold">Branch:</h6>
                      </v-col>
                      <v-col class="py-1" cols="12" md="8">
                        <span class="text-subtitle-1 font-weight-bold">{{ selectedData.branch }}</span>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col class="mt-2 py-1" cols="12" md="4">
                        <h6 class="font-weight-bold">Date Period:</h6>  
                      </v-col>
                      <v-col class="py-1" cols="12" md="8">
                        <span class="text-subtitle-1 font-weight-bold">{{ selectedData.date_period }}</span>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col class="mt-2 py-1" cols="12" md="4">
                        <h6 class="font-weight-bold">Operating Time:</h6>   
                      </v-col>
                      <v-col class="py-1" cols="12" md="8">
                        <span class="text-subtitle-1 font-weight-bold">{{ selectedData.operating_time}}</span> 
                      </v-col>
                    </v-row>
                    <v-row v-if="selectedData.sponsor">
                      <v-col class="mt-2 py-1" cols="12" md="4">
                        <h6 class="font-weight-bold">Sponsor:</h6>   
                      </v-col>
                      <v-col class="py-1" cols="12" md="8">
                        <span class="text-subtitle-1 font-weight-bold">{{ selectedData.sponsor}}</span> 
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col class="mt-2 py-1" cols="12" md="4">
                        <h6 class="font-weight-bold">Venue:</h6>   
                      </v-col>
                      <v-col class="py-1" cols="12" md="8">
                        <span class="text-subtitle-1 font-weight-bold">{{ selectedData.venue}}</span> 
                      </v-col>
                    </v-row>
                  </v-card-text>
                  <v-divider class="my-0"></v-divider>
                  <v-card-actions class="pl-4 pb-2">
                    <v-btn
                      color="primary"
                      @click="viewTactical()"
                    >
                      View
                    </v-btn>
                    <v-btn color="#E0E0E0" @click="selectedOpen = false"> Cancel </v-btn>
                  </v-card-actions>
                </v-card>
              </v-menu>
              <!-- <v-dialog v-model="selectedOpen" width="420px">
                <v-card color="grey lighten-4" flat>
                  <v-toolbar :color="selectedEvent.color" dark>
                    <v-toolbar-title v-html="selectedEvent.name"></v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon @click="selectedOpen = false">
                      <v-icon>mdi-close-circle</v-icon>
                    </v-btn>
                  </v-toolbar>
                  <v-card-text class="py-6">
                    <v-row>
                      <v-col class="mt-2 py-1" cols="12" md="4">
                        <h6 class="font-weight-bold">Branch:</h6>
                      </v-col>
                      <v-col class="py-1" cols="12" md="8">
                        <v-chip class="text-subtitle-1">{{ selectedData.branch }}</v-chip>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col class="mt-2 py-1" cols="12" md="4">
                        <h6 class="font-weight-bold">Date Period:</h6>  
                      </v-col>
                      <v-col class="py-1" cols="12" md="8">
                        <v-chip class="text-subtitle-1">{{ selectedData.date_period }}</v-chip>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col class="mt-2 py-1" cols="12" md="4">
                        <h6 class="font-weight-bold">Operating Time:</h6>   
                      </v-col>
                      <v-col class="py-1" cols="12" md="8">
                        <v-chip class="text-subtitle-1">{{ selectedData.operating_time}}</v-chip> 
                      </v-col>
                    </v-row>
                  </v-card-text>
                  <v-divider class="my-0"></v-divider>
                  <v-card-actions class="pl-4 pb-2">
                    <v-btn
                      color="primary"
                      @click="viewTactical()"
                    >
                      View
                    </v-btn>
                    <v-btn color="#E0E0E0" @click="selectedOpen = false"> Cancel </v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog> -->
            </v-sheet>
          </v-col>
        </v-row>
      </v-main>
    </div>
  </div>
</template>
<script>
import axios from "axios";
export default {
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
          text: "Tactical Requisition Calendar",
          disabled: true,
        },
      ],
      disabled: false,
      focus: '',
      type: 'month',
      typeToLabel: {
        month: 'Month',
        week: 'Week',
        day: 'Day',
        '4day': '4 Days',
      },
      selectedEvent: {},
      selectedElement: null,
      selectedOpen: false,
      events: [],
      colors: ['blue', 'indigo', 'deep-purple', 'cyan', 'green', 'orange', 'grey darken-1'],
      names: ['Meeting', 'Holiday', 'PTO', 'Travel', 'Event', 'Birthday', 'Conference', 'Party'],
      tactical_requisitions: [],
      skeleton_loading: true,
      selectedData: {
        branch: "",
        date_period: "",
        operating_time: "",
        sponsor: "",
        venue: "",
      }
    }
    
  },
  methods: {
    getTacticalRequisition() {
      this.loading = true;
      axios.get("/api/tactical_requisition/index").then(
        (response) => {
          
          let data = response.data
        
          this.tactical_requisitions = data.tactical_requisitions;

          this.skeleton_loading = false;
        },
        (error) => {
          this.isUnauthorized(error);
        }
      );
    },
    viewTactical()
    {
      this.$router.push({
        name: "tactical.view",
        params: { tactical_requisition_id: this.selectedEvent.data.id },
      });
    },
    viewDay ({ date }) {
      this.focus = date
      this.type = 'day'
    },
    getEventColor (event) {
      return event.color
    },
    setToday () {
      this.focus = ''
    },
    prev () {
      this.$refs.calendar.prev()
    },
    next () {
      this.$refs.calendar.next()
    },
    showEvent ({ nativeEvent, event }) {

      const open = () => {
        this.selectedEvent = event
        this.selectedElement = nativeEvent.target
        requestAnimationFrame(() => requestAnimationFrame(() => this.selectedOpen = true))
      }

      if (this.selectedOpen) {
        this.selectedOpen = false
        requestAnimationFrame(() => requestAnimationFrame(() => open()))
      } else {
        open()
      }

      nativeEvent.stopPropagation();

      let data = event.data;
      let hr_from = data.operating_from.split(':')[0];
      let hr_to = data.operating_to.split(':')[0];

      this.selectedData = { 
        branch: data.branch.name,  
        date_period: this.formatDate(data.period_from) + ' -  ' + this.formatDate(data.period_to),
        operating_time:  this.timeMeridiem(hr_from)  + ' - ' + this.timeMeridiem(hr_to),
        sponsor: data.sponsor,
        venue: data.venue,
      };

    },
    updateRange ({ start, end }) {
      const events = []

      // const min = new Date(`${start.date}T00:00:00`)
      // const max = new Date(`${end.date}T23:59:59`)

      this.tactical_requisitions.forEach(value => {
        let start = new Date(`${value.period_from}T${value.operating_from}`);
        let end = new Date(`${value.period_to}T${value.operating_to}`)
        events.push({
          name: value.marketing_event.event_name,
          start: start,
          end: end,
          color: this.colors[this.rnd(0, this.colors.length - 1)],
          timed: true,
          data: value,
        })
      });
      console.log(events);
      this.events = events;
    },

    formatDate(date) {
      if (!date) return null;
      const [year, month, day] = date.split("-");
      return `${month}/${day}/${year}`;
    },

    rnd (a, b) {
      return Math.floor((b - a + 1) * Math.random()) + a
    },

    timeMeridiem(hr) {
      let meridiem = hr < 12 || hr > 23 ? 'AM' : 'PM'; 
      let hour = hr > 12 ? hr - 12 : hr; 
      return String(hour) + ":00 " + meridiem;
    },

  },
  mounted() {
    axios.defaults.headers.common["Authorization"] =
      "Bearer " + localStorage.getItem("access_token");
    this.getTacticalRequisition();
    // this.websocket();
  },
}
</script>