<template>
  <header>
    <div
      v-if="!cartView"
      class="logos"
      :class="{ 'logos--horizontal' : horizontal }"
    >
      <img
        :src="'static/img/logo_' + layout + '.svg'"
        class="logo"
      >
    </div>
    <div
      v-else
      class="logos"
      :class="{ 'logos--horizontal--left' : horizontal }"
    >
      <img
        :src="'static/img/logo_' + layout + '.svg'"
        class="logo-left"
      >
      <div
        v-if="!isCartVisible"
        class="cart-view"
        @click="cartPreviewClicked()"
      >
        <p class="qty">
          {{ totalQty }}
        </p>
      </div>
      <div
        v-else
        class="close"
        @click="cartPreviewClicked()"
      >
        <span>
          Close
        </span>
        <img
          :src="'static/img/close.svg'"
        >
      </div>
    </div>
  </header>
</template>

<script>
export default {
  name: 'HeaderPart',
  components: {
  },
  props: {
    headerData: { type: Object, default: null },
    cartView: { type: Boolean, default: false }
  },
  data () {
    return {
      layout: this.headerData.layout,
      link: this.headerData.link,
      horizontal: this.headerData.horizontal,
      isCartVisible: false
    }
  },
  computed: {
    totalQty () {
      return Number(this.$store.state.totalQty)
    }
  },
  watch: {
    totalQty (newVal, oldVal) {
    }
  },
  beforeMount () {
  },
  methods: {
    cartPreviewClicked () {
      this.isCartVisible = !this.isCartVisible
      this.$emit('cartPreview', this.isCartVisible)
    }
  }
}
</script>

<style lang="scss" scoped>
  @import '../assets/scss/mixins';

  header {
    position: relative;
    overflow: hidden;
  }

  .logos {
    margin-top: 20px;
    display: flex;
    flex-direction: row;

    .logo {
      display: block;
      width: 200px;
      margin: 40px auto 15px;
    }

    &.logos--horizontal {
      flex-direction: row;
      align-items: center;
      margin-top: 10px;

      .logo {
        width: 130px;
        margin: 20px auto;
      }
    }

    .logo-left {
      display: block;
      width: 150px;
      margin: 20px 0 15px;
    }

    &.logos--horizontal-left {
      flex-direction: row;
      align-items: center;
      margin-top: 20px;

      .logo-left {
        width: 130px;
        margin: 20px auto;
      }
    }
  }

  .cart-view {
    background: url("../static/img/bag.svg") no-repeat center center;
    max-width: 40px;
    width: 32px;
    height: 35px;
    display: flex;
    color: white;
    justify-content: center;
    margin: auto 0 auto auto;
  }

  .qty {
    display: flex;
    justify-content: center;
    align-content: center;
    font-size: 12px;
    font-weight: bold;
    letter-spacing: 0;
    line-height: 16px;
    text-align: center;
  }

  .close {
    display: flex;
    width: 60px;
    align-items: center;
    margin-left: auto;
    span {
      font-size: 14px;
      font-weight: bold;
      letter-spacing: 1px;
      line-height: 21px;
      text-align: right;
    }
  }
</style>
