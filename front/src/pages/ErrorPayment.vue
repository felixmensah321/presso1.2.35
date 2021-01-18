<template>
  <div>
    <HeaderPart :header-data="LoginHeader" />

    <div class="loading-block">
      <img
        :src="'static/img/icon_alert.svg'"
        class=""
      >
      <h1 class="payment-error">
        {{ $t('errorPayment.noPayment') }}
      </h1>
      <p>{{ $t('errorPayment.scanAgain') }}</p>
    </div>
  </div>
</template>

<script>
import HeaderPart from 'layouts/HeaderPart'
import CONFIG from '../static/general/config.json'
import APICONFIG from '../static/api/config.json'

export default {
  name: 'ErrorPayment',
  components: {
    HeaderPart
  },
  data () {
    return {
      LoginHeader: {
        layout: 'black',
        link: false,
        horizontal: false
      },
      orderDatas: null
    }
  },
  mounted () {
  },
  created () {
    window.gtmDataObject.push({
      'event': 'event_pageView',
      'isEnvironmentProd': APICONFIG.isEnvProd,
      'pageName': 'Error Payment | Twint',
      'pageType': 'Error Payment', // Other values include “Product List”, “Product Details”, “Checkout”, “User Account” etc.
      'pageCategory': '', // If available, please populate. Example ‘Coffee’ this is the value Nessoft nsp.page.pagessubsection1
      'pageSubCategory': '', // If available, please populate. this is the value Nessoft nsp.page.pagessubsection2
      'pageTechnology': CONFIG.gtm.pageTechnology, // Other values include “VertuoLine”, “Original Line”, “Pro”, or “None”
      'market': CONFIG.gtm.market,
      'version': '', // Twint application version
      'landscape': CONFIG.gtm.landscape,
      'segmentBusiness': CONFIG.gtm.segmentBusiness,
      'currency': CONFIG.gtm.currency,
      'eventCategory': 'User Engagement',
      'eventAction': 'Error Payment - Twint',
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
  }
}
</script>

<style lang="scss">
  @import '../assets/scss/mixins';
  body.error-payment   {
    background: url('../static/img/bg.jpg') $black no-repeat center center;
    background-size: cover;
  }
</style>

<style lang="scss" scoped>
  @import '../assets/scss/mixins';

  .loading-block {
    @include abs(50vh, null, null, 50vw);
    transform: translateX(-50%) translateY(-50%);
    text-align: center;
    width: calc(100% - 40px);
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 30px;

  .payment-error{
    color: #CC7E00;
    @include fontsize(18px);
    font-weight: bold;
    letter-spacing: 1px;
    line-height: 24px;
    margin-top: 15px;
    text-transform: none;
  }

    p {
      margin: 25px 0 0;
      @include fontsize(18px);
      font-weight: 300;
      color: $black;
      letter-spacing: 1px;
      line-height: 24px;
      text-align: center;
    }
  }
</style>
