<template>
  <div class="btn-wrapper">
    <router-link
      :to="{ name: linkSlug, params: { sku: linkSku, cID: linkID } }"
      tag="button"
      :class="['btn', 'btn--'+layout]"
    >
      {{ text }}
    </router-link>
  </div>
</template>

<script>
export default {
  name: 'Button',
  components: {},
  props: {
    buttonData: { type: Object, default: null }
  },
  data () {
    return {
      layout: this.buttonData.layout,
      text: this.buttonData.text,
      linkSlug: this.buttonData.slug,
      linkSku: this.buttonData.sku,
      linkID: this.buttonData.cID
    }
  }
}
</script>

<style lang="scss">
  @import '../assets/scss/mixins';

  .btn {
    position: relative;
    width: 100%;
    padding: 12px 30px;
    border: 1px solid;
    border-radius: 4px;
    @include fontsize(14px);
    font-weight: 700;
    text: {
      align: center;
      transform: uppercase;
    }

    &:focus { outline: 0; }

    &::after {
      @include icon-base();
      content: "\e902";
      color: $white;
      @include fontsize(24px);
      @include abs(50%, 10px, null, null);
      transform: translateY(-50%);
    }

    &.btn--primary {
      border-color: $green;
      background: $green;
      color: $white;
    }

    &.btn--secondary {
      border-color: $black;
      background: transparent;
      color: $black;

      &.btn--inverted {
        border-color: $black;
        color: $black;
      }
    }

    &.btn--inactive {
      pointer-events: none;
      opacity: 0.5;
      cursor: not-allowed;
    }
  }

  .btn-wrapper {
    width: 100%;
    padding: 20px;
    background: $lightgrey;
    position: fixed;
    bottom: 0;
    left: 0;
    z-index: 5;

    .login & {
      background: transparent;
    }
  }
</style>
