<template>
  <div>
    <HeaderPart :header-data="LoginHeader" />

    <div class="loading-block">
      <img
        :src="'static/img/Invoice.svg'"
        class=""
      >
      <h1>{{ $t('order.thank') }}</h1>
      <p class="validation-text">
        {{ $t('order.message') }}
      </p>

      <div class="detail-wrapper">
        <p class="order-details">
          {{ $t('order.details') }}
        </p>
        <div class="block">
          <div
            v-for="order in orderDatas.items"
            :key="order.sku"
            class="order-grid"
          >
            <div class="cell-1">
              {{ order.name }}
            </div> <div class="cell-2">
              {{ order.qty_ordered }} x Fr. {{ formatPrice(order.price_incl_tax) }}
            </div>
            <div class="cell-3">
              <span class="green price">Fr. {{ formatPrice(order.row_total_incl_tax) }}</span>
            </div>
          </div>
        </div>
        <div class="bottom-tab">
          <span>{{ $t('order.payed') }} {{ orderDatas.updated_at | moment("DD/MM/YYYY") }} </span><span class="total-price">
            Fr. {{ formatPrice(orderDatas.total_due) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import HeaderPart from 'layouts/HeaderPart'
import CONFIG from '../static/general/config.json'
import APICONFIG from '../static/api/config.json'

export default {
  name: 'Success',
  components: {
    HeaderPart
  },
  data () {
    return {
      LoginHeader: {
        layout: 'black',
        link: false,
        horizontal: false
      }
    }
  },
  computed: {
    orderDatas () {
      return this.$store.state.completedOrder
    }
  },
  created () {
    window.gtmDataObject.push({
      'event': 'event_pageView',
      'isEnvironmentProd': APICONFIG.isEnvProd,
      'pageName': 'Order Success | Twint',
      'pageType': 'Success', // Other values include “Product List”, “Product Details”, “Checkout”, “User Account” etc.
      'pageCategory': '', // If available, please populate. Example ‘Coffee’ this is the value Nessoft nsp.page.pagessubsection1
      'pageSubCategory': '', // If available, please populate. this is the value Nessoft nsp.page.pagessubsection2
      'pageTechnology': CONFIG.gtm.pageTechnology, // Other values include “VertuoLine”, “Original Line”, “Pro”, or “None”
      'market': CONFIG.gtm.market,
      'version': '', // Twint application version
      'landscape': CONFIG.gtm.landscape,
      'segmentBusiness': CONFIG.gtm.segmentBusiness,
      'currency': CONFIG.gtm.currency,
      'eventCategory': 'User Engagement',
      'eventAction': 'Order Success - Twint',
      'loginMethod': 'Phone', // is this required
      'clubMemberID': this.$store.state.userDatas === null || this.$store.state.userDatas.custom_attributes === undefined ? null : this.$store.state.userDatas.custom_attributes[2].value,
      'clubMemberStatus': null,
      'clubMemberLevel': null,
      'clubMemberLevelID': null,
      'clubMemberTitle': null,
      'clubMemberLoginStatus': this.$store.state.customers.id === null || this.$store.state.customers.id === undefined ? 'false' : 'true',
      'machineOwned': null,
      'preferredTechnology': null
    })
  },
  methods: {
    formatPrice (value) {
      let val = (value / 1).toFixed(2).replace(',', '.')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "'")
    }
  }
}
</script>

<style lang="scss" scoped>
  @import '../assets/scss/mixins';

body.success {
  background: url('../static/img/bg.jpg') $black no-repeat center center;
    background-size: cover;
}
    .loading-block {
        @include abs(50vh, null, null, 50vw);
        transform: translateX(-50%) translateY(-50%);
        text-align: center;
        width: calc(100% - 40px);
        max-height: 60%;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 30px;

        h1 {
          margin: 20px 0 0;
          @include fontsize(16px);
          font-weight: 700;
          color: $black;
          text-transform: uppercase;
          text-align: center;
          letter-spacing: 2px;
        }

        .validation-text {
          margin: 15px 0 0;
          @include fontsize(18px);
          font-weight: 300;
          color: $black;
          letter-spacing: 1px;
          line-height: 24px;
          text-align: center;
        }
    }

    .order-grid {
        min-height: 33px;
        border-top: 1px solid #E6E6E6;
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        grid-template-rows: 1fr;
    }

    .cell-1, .cell-2, .cell-3 {
      display: flex;
      color: #666666;
      align-items: center;
       @include fontsize(12px);
      letter-spacing: 0;
      line-height: 16px;
    }

    .cell-2 {
      justify-content: center;
    }

    .cell-3 {
      justify-content: flex-end;
      color: #3D8705;
      font-weight: bold;
    }

  .detail-wrapper {
    padding: 0 20px;
    background-color: white;
    width: calc(100% + 40px);
    margin-top: 40px;
    text-align: left;

    .block {
      .order-grid:last-child {
        border-bottom: 1px solid #E6E6E6;
      }
    }
  }

  .bottom-tab {
    margin: 8px 0 23px 0;
    @include fontsize(12px);
    font-weight: bold;
    letter-spacing: 0;
    line-height: 16px;

    .total-price {
      position: absolute;
      right: 0;
    }
  }

  p.order-details {
    margin: 16px 0 14px 0;
    text-align: left;
    @include fontsize(14px);
    letter-spacing: 1px;
    line-height: 21px;
    font-weight: bold;
  }
  .green {
    color: $green;
  }
</style>
