<template>
  <transition name="modal-fade">
    <div
      class="modal-backdrop"
      @click="close()"
    >
      <img
        :src="this.$store.state.config.site + 'pub/media/catalog/product' + energeticPicture"
        class="product-img"
      >
    </div>
  </transition>
</template>

<script>
export default {
  name: 'Modal',
  props: {
    productData: { type: Object, default: null }
  },
  data () {
    return {
      energeticPicture: null
    }
  },
  created () {
    if (this.productData.custom_attributes.filter(short => short.attribute_code === 'label_energetic_image')[0]) {
      this.energeticPicture = this.productData.custom_attributes.filter(short => short.attribute_code === 'label_energetic_image')[0].value
    }
  },
  methods: {
    close () {
      this.$emit('closeImage')
    }
  }
}
</script>

<style lang="scss">
  @import '../assets/scss/mixins';
  .modal-backdrop {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: rgba(0, 0, 0, 0.3);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1;

    img {
      max-width: 80%;
    }
  }

   .modal-fade-enter,
  .modal-fade-leave-active {
    opacity: 0;
  }

  .modal-fade-enter-active,
  .modal-fade-leave-active {
    transition: opacity .5s ease
  }
</style>
