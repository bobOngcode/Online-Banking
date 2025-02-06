<template>
  <div>
    <v-data-table
      :headers="headers"
      :items="product_models.data"
      :search="search"
      :loading="loading"
      loading-text="Loading... Please wait"
      :page.sync="page"
      :footer-props="footerProps"
      @page-count="pageCount = $event"
      :items-per-page="itemsPerPage"
      @update:items-per-page="getItemPerPage"
      v-if="hasPermission('product-model-list')"
      show-first-last-page
    >
      <template v-slot:item.actions="{ item }">
        <v-icon
          small
          class="mr-2"
          color="green"
          @click="editProductModel(item)"
          v-if="hasPermission('product-model-edit')"
        >
          mdi-pencil
        </v-icon>
        <v-icon
          small
          color="red"
          @click="showConfirmAlert(item)"
          v-if="hasPermission('product-model-delete')"
        >
          mdi-delete
        </v-icon>
      </template>
      <template v-slot:footer.page-text>
        <!-- <v-btn color="primary" dark class="ma-2"> Button </v-btn> -->
        {{
          product_models.from
            ? product_models.from +
              " - " +
              product_models.to +
              " of " +
              product_models.total
            : ""
        }}
        <v-btn
          icon
          class="ml-4"
          @click="firstPage()"
          :disabled="firstBtnDisable"
          ><v-icon> mdi-chevron-double-left </v-icon></v-btn
        >
        <v-btn
          icon
          @click="prevPage()"
          :disabled="prevBtnDisable"
          ><v-icon> mdi-chevron-left </v-icon></v-btn
        >
          <strong> {{ page }} </strong>
        <v-btn icon @click="nextPage()" :disabled="lastBtnDisable"
          ><v-icon> mdi-chevron-right </v-icon></v-btn
        >
        <v-btn icon @click="lastPage()" :disabled="lastBtnDisable"
          ><v-icon> mdi-chevron-double-right </v-icon></v-btn
        >
      </template>
    </v-data-table>
  </div>
</template>
<script>

export default {
  name: "DataTable",
  props: [
    'headers',
    'items'
  ],
  data() {
    return {
      page: 1,
      pageCount: 0,
      itemsPerPage: 10,
      footerProps: {
        "items-per-page-options": [10, 20, 30, 50, 100, 500],
        pagination: {},
        showFirstLastPage: true,
        firstIcon: null,
        lastIcon: null,
        prevIcon: null,
        nextIcon: null,
      },
      last_page: 0,
      prevBtnDisable: true,
      nextBtnDisable: true,
      firstBtnDisable: true,
      lastBtnDisable: false,
    }
  },
  methods: {
    nextPage() {
      if (this.page < this.last_page) {
        this.page = this.page + 1;
      }
      this.getData();
    },
    prevPage() {
      if (this.page > 1) {
        this.page = this.page - 1;
      }
      this.getData();
    },
    firstPage() {
      this.page = 1;
      this.prevBtnDisable = true;
      this.firstBtnDisable = true;

      this.getData();
    },

    lastPage() {
      this.page = this.last_page;
      this.nexBtnDisable = true;
      this.lastBtnDisable = true;

      this.getData();
    },
  },
  computed: {

  },
}

</script>
