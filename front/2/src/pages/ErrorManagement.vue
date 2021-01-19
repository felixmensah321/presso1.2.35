<template>
  <div>
    <HeaderPart :header-data="LoginHeader" />

    <div class="not-found-block">
      <h1>{{ errorType }}</h1>
      <p
        v-if="errorMessageLine1 && errorMessageLine1.length > 0"
      >
        {{ errorMessageLine1 }}
      </p>
      <p
        v-if="errorMessageLine2 && errorMessageLine2.length > 0"
      >
        {{ errorMessageLine2 }}
      </p>
      <a
        v-if="errorLink.length > 0"
        :href="errorLink"
      >{{ errorLink }}</a>
    </div>
  </div>
</template>

<script>
import HeaderPart from 'layouts/HeaderPart'
import CONFIG from '../static/general/config.json'
import APICONFIG from '../static/api/config.json'

export default {
  name: 'ErrorManagement',
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
      orderDatas: null,
      errorType: null,
      errorMessageLine1: null,
      errorMessageLine2: null,
      errorLink: null
    }
  },
  mounted () {
  },
  created () {
    this.getErrorMessage()
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
    getErrorMessage () {
      this.errorType = this.$route.params.errorType !== undefined ? this.$t('errorManagement.' + this.$route.params.errorType + '.title') : this.$t('errorManagement.genericTitle')
      this.errorMessageLine1 = this.$route.params.errorType !== undefined ? this.$t('errorManagement.' + this.$route.params.errorType + '.message1') : this.$t('errorManagement.genericMessage')
      this.errorMessageLine2 = this.$route.params.errorType !== undefined ? this.$t('errorManagement.' + this.$route.params.errorType + '.message2') : this.$t('errorManagement.genericMessage')
      this.errorLink = this.$route.params.errorType !== undefined ? this.$t('errorManagement.' + this.$route.params.errorType + '.link') : null
    }
  }
}
</script>

<style lang="scss">
  @import '../assets/scss/mixins';

  body.error-management {
    background: url('../static/img/bg.jpg') $black no-repeat center center;
    background-size: cover;
  }

  .not-found-block {
    @include abs(50vh, null, null, 50vw);
    transform: translateX(-50%) translateY(-50%);
    text-align: center;
    width: calc(100% - 40px);
    max-width: 750px;
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
