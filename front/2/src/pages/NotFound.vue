<template>
  <div>
    <HeaderPart :header-data="LoginHeader" />

    <div class="not-found-block">
      <h1>{{ $t('notfound.title') }}</h1>
      <p>{{ $t('notfound.message') }}</p>
      <p>{{ $t('notfound.line2') }}</p>
      <p>{{ $t('notfound.line3') }}</p>
    </div>
  </div>
</template>

<script>
import HeaderPart from 'layouts/HeaderPart'
import CONFIG from '../static/general/config.json'
import APICONFIG from '../static/api/config.json'

export default {
  name: 'NotFound',
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
  created () {
    window.gtmDataObject.push({
      'event': 'event_pageView',
      'isEnvironmentProd': APICONFIG.isEnvProd,
      'pageName': 'Not Found | Twint',
      'pageType': 'Not Found', // Other values include “Product List”, “Product Details”, “Checkout”, “User Account” etc.
      'pageCategory': '', // If available, please populate. Example ‘Coffee’ this is the value Nessoft nsp.page.pagessubsection1
      'pageSubCategory': '', // If available, please populate. this is the value Nessoft nsp.page.pagessubsection2
      'pageTechnology': CONFIG.gtm.pageTechnology, // Other values include “VertuoLine”, “Original Line”, “Pro”, or “None”
      'market': CONFIG.gtm.market,
      'version': '', // Twint application version
      'landscape': CONFIG.gtm.landscape,
      'segmentBusiness': CONFIG.gtm.segmentBusiness,
      'currency': CONFIG.gtm.currency,
      'eventCategory': 'User Engagement',
      'eventAction': 'Not Found - Twint',
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
  }
}
</script>

<style lang="scss">
  @import '../assets/scss/mixins';

  body.not-found {
    background: url('../static/img/bg.jpg') $black no-repeat center center;
    background-size: cover;
  }

  .not-found-block {
    @include abs(50vh, null, null, 50vw);
    transform: translateX(-50%) translateY(-50%);
    text-align: center;
    width: calc(100% - 40px);
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 30px;

    h1 {
      margin: 25px 0 0;
      @include fontsize(30px);
      font-weight: 300;
      color: $black;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    p {
      margin: 25px 0 0;
      @include fontsize(18px);
      font-weight: 300;
      color: $black;
      letter-spacing: 1px;
    }
  }
</style>
