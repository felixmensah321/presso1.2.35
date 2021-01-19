<template>
  <div class="loader">
    <div class="spinner" />
  </div>
</template>

<script>
export default {
  name: 'Loader',
  components: {},
  metaInfo () {
    return {
      title: this.$i18n.t('meta.loading'),
      titleTemplate: '%s | Nespresso'
    }
  }
}
</script>

<style lang="scss" scoped>
  @import '../assets/scss/mixins';

  .spinner {
    height: 64px;
    width: 64px;
    border-radius: 50%;
    border-top: 3px solid $black;
    border-right: 3px solid transparent;
    animation: spinner 1000ms linear infinite;
  }
  @keyframes spinner {
    to { transform: rotate(360deg); }
  }
</style>
