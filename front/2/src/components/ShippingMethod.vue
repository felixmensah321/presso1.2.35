<template>
  <div>
    <p class="p2 shippName">
      {{ shippingName }}
    </p>
    <p class="p3 shipcost">
      {{ price }}
    </p>
    <p class="description">
      {{ description }}
    </p>
  </div>
</template>
<script>
export default {
  name: 'ShippingMethod',
  props: {
    shippingProps: { type: Object, default: null }
  },
  data () {
    return {
      shippingName: this.shippingProps.name,
      price: this.shippingProps.price,
      description: this.shippingProps.description
    }
  },
  methods: {

  }
}
</script>

<style lang="scss">
  @import '../assets/scss/mixins';

.p2 {
    font-size: 18px;
    line-height: 24px;
    letter-spacing: 1px;
}

.shippName {
font-weight: bold;
    text-transform: uppercase;
    margin: 0;
}

.p3 {
    font-size: 16px;
    line-height: 24px;
    letter-spacing: 1px;
}

.description {
  color: #666666;
  margin-top: 0;
}

.shipcost {
    margin: 0;
    color: $gold;
    font-weight: bold;
}
</style>
