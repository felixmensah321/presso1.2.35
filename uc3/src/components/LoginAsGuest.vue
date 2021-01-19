<template>
  <div>
    <p
      v-if="!loader"
      class="instructions"
    >
      <button
        v-if="!loader"
        class="btn btn--secondary btn--inverted"
        @click="loginAsGuest"
      >
        {{ $t('guest.btn') }}
      </button>
    </p>
  </div>
</template>

<script>

export default {
  name: 'LoginAsGuest',
  components: {},
  props: {
    loader: { type: Boolean, default: false }
  },
  methods: {
    loginAsGuest () {
      this.$emit('login')
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
