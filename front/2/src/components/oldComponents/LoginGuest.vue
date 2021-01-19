<template>
  <div>
    <p class="instructions">
      {{ $t('guest.message') }}
    </p>

    <button
      class="btn btn--secondary btn--inverted"
      @click="guestShopping"
    >
      {{ $t('guest.btn') }}
    </button>
  </div>
</template>

<script>
import router from '../router'

export default {
  name: 'LoginGuest',
  components: {},
  props: {
    urlData: { type: Object, default: null }
  },
  methods: {
    guestShopping () {
      router.push({
        name: 'MultiProducts', query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign }
      })
    }
  }
}
</script>

<style lang="scss" scoped>
  @import '../assets/scss/mixins';

  .instructions {
    margin-top: 50px;
    text-align: center;
    @include fontsize(18px);
    font-weight: 300;
    letter-spacing: 1px;
    color: $black;
  }
</style>
