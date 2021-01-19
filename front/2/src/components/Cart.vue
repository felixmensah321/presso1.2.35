<template>
  <div>
    <div class="cart-title">
      {{ $t('cart.title') }}
    </div>
    <div v-if="list.length">
      <div
        v-for="product in list"
        :key="product.allDatas.id"
        class="pdtlist"
      >
        <UnitProductCart
          :product-data="product.allDatas"
        />
      </div>
    </div>
    <div
      v-else
      class="empty-cart"
    >
      {{ $t('cart.empty') }}
    </div>
    <div
      v-if="list.length"
      class="total"
    >
      <span>{{ $t('cart.total') }}</span><span class="price">
        Fr. {{ formatPrice(totalPrice) }}
      </span>
    </div>
  </div>
</template>

<script>
import UnitProductCart from 'components/UnitProductCart'
export default {
  name: 'Cart',
  components: {
    UnitProductCart
  },
  props: {
  },
  data () {
    return {
      currentCategoryDatas: []
    }
  },
  computed: {
    list () {
      return this.$store.state.purchases
    },
    totalPrice () {
      return this.$store.state.totalPrice
    }
  },
  watch: {
    list (newlist, oldList) {
      this.fillTab()
    },
    totalPrice (newPrice, oldPrice) {
    }
  },
  created () {
    this.fillTab()
  },
  methods: {
    fillTab () {
      this.$store.state.purchases.forEach(element => {
        this.currentCategoryDatas.push(element.allDatas)
      })
    },
    formatPrice (value) {
      let val = (value / 1).toFixed(2).replace(',', '.')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "'")
    }
  }
}
</script>

<style lang="scss" scoped>
  @import '../assets/scss/mixins';

  .pdtlist:last-child {
    border-bottom: 1px solid #cccccc;
  }

  .cart-title {
    @include fontsize(18px);
    letter-spacing: 3px;
    line-height: 24px;
    font-weight: bold;
    text-transform: uppercase;
  }

  .total {
    @include fontsize(16px);
    font-weight: bold;
    letter-spacing: 1px;
    line-height: 24px;

    .price{
      position: absolute;
      right: 20px;
    }
  }
</style>
