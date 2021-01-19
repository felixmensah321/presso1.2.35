<template>
  <div class="full">
    <div class="container">
      <transition name="fade">
        <RouterView />
      </transition>
    </div>
  </div>
</template>

<script>
import APICONFIG from './static/api/config.json'
import CONFIG from './static/general/config.json'

export default {
  name: 'App',
  components: {},
  data () {
    return {
      currentSku: this.$route.query.sku,
      getWebsites: null,
      CurrentWebsiteID: null,
      getGTM: null
    }
  },
  beforeMount () {
    const currentLang = navigator.language || navigator.userLanguage

    if (currentLang.indexOf('en') >= 0) {
      this.$i18n.locale = 'en'
    } else if (currentLang.indexOf('fr') >= 0) {
      this.$i18n.locale = 'fr'
    } else if (currentLang.indexOf('de') >= 0) {
      this.$i18n.locale = 'de'
    } else if (currentLang.indexOf('it') >= 0) {
      this.$i18n.locale = 'it'
    } else {
      this.$i18n.locale = 'en'
    }

    // Config
    this.$store.commit('storeConfig', APICONFIG)
    this.$store.commit('storeDefaultPurchasePointId', CONFIG.defaultPurchasePointId)
  }
}
</script>

<style lang="scss">
  @import 'assets/scss/common';

.full{
      background-color: $lightgrey;
}
  body {
    &::before {
      content: "\0020";
      display: block;
      @include abs(0, null, null, 0);
      width: 100%;
      height: 50px;
      pointer-events: none;
      background: url('static/img/logo_twint_banner.png') no-repeat top center;
      background-size: 100%;
    }
  }
@media screen and (min-width: 450px) {
  .full{
    border-top: black solid 25px;
    margin-right: 2px;
  }
    body {
    &::before {
      content: "\0020";
      display: block;
      @include abs(0, null, null, 0);
      width: 100%;
      height: 80px;
      pointer-events: none;
      background: url('static/img/logo_twint_banner.png') no-repeat top right;
    }
  }
}
</style>
