<template>
  <div class="product-card">
    <div class="prd-container">
      <div class="cell cell-1">
        <img
          :src="this.$store.state.config.site + 'pub/media/catalog/product' + productPic"
          class="product-img"
        >
      </div>
      <div class="cell cell-2">
        <h4 class="prod-name">
          {{ productName }}
        </h4>
      </div>
      <div class="cell cell-3">
        <select
          v-model="quantity"
          @change="onChange($event)"
        >
          <option
            v-for="item in listArray"
            :key="item * multiplicator"
            :value="item * multiplicator"
          >
            {{ item * multiplicator }}
          </option>
        </select>
      </div>
      <div class="cell cell-4">
        <div>
          <span class="price">Fr. {{ formatPrice(productPrice) }}</span>
        </div>
      </div>
      <div class="cell cell-5">
        <img
          :src="'static/img/close-grey.png'"
          class="price-div"
          @click="removeProduct()"
        >
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Product',
  components: {
  },
  props: {
    productData: { type: Object, default: null }
  },
  data () {
    return {
      listArray: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
      productName: this.productData.name,
      productPic: null,
      productDescr: null,
      productLabelEnergetic: null,
      productPrice: this.productData.price,
      quantity: 0,
      user: null,
      storeID: null,
      order: {},
      clickOnSendOrder: false,
      click: 0,
      labelClass: null,
      packaging: null,
      nbCaps: null,
      multiplicator: 0
    }
  },
  created () {
    let nbCaps = this.productData.custom_attributes.filter(caps => caps.attribute_code === 'nb_capsules')[0]
    let multiple = this.productData.custom_attributes.filter(caps => caps.attribute_code === 'multiple_product')[0]
    if (nbCaps) {
      this.multiplicator = nbCaps.value
    } else if (multiple) {
      this.multiplicator = multiple.value
    } else {
      this.multiplicator = 1
    }

    let arr = this.$store.state.purchases.filter(object => object.sku === this.productData.sku)
    if (arr.length > 0) {
      this.quantity = arr[0].quantity
    }
    if (this.productData.custom_attributes.filter(pack => pack.attribute_code === 'packaging')[0]) {
      this.packaging = this.productData.custom_attributes.filter(pack => pack.attribute_code === 'packaging')[0].value
    }
    if (this.productData.custom_attributes.filter(caps => caps.attribute_code === 'nb_capsules')[0]) {
      this.nbCaps = this.productData.custom_attributes.filter(caps => caps.attribute_code === 'nb_capsules')[0].value
    }
    if (this.productData.media_gallery_entries.filter(pic => pic.media_type === 'image')[0]) {
      this.productPic = this.productData.media_gallery_entries.filter(pic => pic.media_type === 'image')[0].file
    }
    if (this.productData.custom_attributes.filter(short => short.attribute_code === 'short_description')[0]) {
      this.productDescr = this.productData.custom_attributes.filter(short => short.attribute_code === 'short_description')[0].value
    }
    if (this.productData.custom_attributes.filter(short => short.attribute_code === 'energetic_label')[0] &&
    this.productData.custom_attributes.filter(short => short.attribute_code === 'energetic_label')[0].value !== '0') {
      this.getEnergeticLabelOptions(this.productData.custom_attributes.filter(short => short.attribute_code === 'energetic_label')[0].value)
    }
  },
  methods: {
    removeProduct () {
      let idx = 0
      let arr = this.$store.state.purchases
      for (let i = 0; i < arr.length; i++) {
        if (arr[i].sku === this.productData.sku) {
          idx = i
          break
        }
      }
      arr.splice(idx, 1)
      this.calculatePrice(arr)
    },
    formatPrice (value) {
      let val = (value / 1).toFixed(2).replace(',', '.')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "'")
    },
    calculatePrice (arr) {
      let totalPrice = 0
      let totalQty = 0
      arr.forEach(product => {
        totalPrice += (product.price * product.quantity)
        totalQty += Number(product.quantity)
      })
      this.$store.commit('storeTotalPrice', totalPrice)
      this.$store.commit('storeTotalQty', Number(totalQty))
    },
    onChange ($event) {
      this.quantity = Number($event.target.value)
      let arr = this.$store.state.purchases
      let purchaseObj = { allDatas: this.productData, sku: this.productData.sku, quantity: this.quantity, price: this.productData.price }
      if (this.quantity > 0) {
        if (!this.productInArray(arr, this.productData.sku)) {
          arr.push(purchaseObj)
        } else {
          arr.filter(
            product => {
              if (product.sku === this.productData.sku) {
                product.quantity = this.quantity
                return true
              }
              return false
            }
          )
        }
        this.calculatePrice(arr)
      } else {
        this.removeProduct()
      }
      this.$store.commit('storePurchases', arr)
    },
    productInArray (arr, value) {
      return arr.some(function (el) {
        return el.sku === value
      })
    }
  }
}
</script>

<style lang="scss">
body.product {
  padding: 0 0 160px;
}
</style>

<style lang="scss" scoped>
  @import '../assets/scss/mixins';

  .product-card {
    border-top: 1px solid #cccccc;
    display: block;

    h4 {
      @include fontsize(14px);
      margin: 0 0 0 0;
      font-weight: bold;
      line-height: 21px;
      letter-spacing: 1px;
      margin: auto 0 auto 4px;
    }

    article {
      @include fontsize(14px);
      color: $grey;
      letter-spacing: 1px;
      line-height: 1.4;
    }

    .product-img {
      display: block;
      max-width: 90%;
    }

    .price-div {
      display: flex;
      min-height: 20px;
      justify-content: flex-end;
      align-items: center;
    }
    .price {
      display: flex;
      justify-content: flex-end;
      color: #3D8705;
      @include fontsize(14px);
      font-weight: bold;
      letter-spacing: 1px;
      line-height: 21px;
    }

    .qty {
      display: flex;
      justify-content: flex-end;
      align-items: flex-end;

      .action-btn {
        -webkit-appearance: none;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 4px;
        border: 0;
        background: $green;
        touch-action: manipulation;

        span {
          @include fontsize(20px);
          color: $white;
        }

        &.action-btn--inactive {
          background: rgba($grey, 0.25);
          pointer-events: none;
        }
      }

      span {
        flex-grow: 1;
        @include fontsize(14px);

        mark {
          background: transparent;
        }
      }
    }
  }
  .prd-container {
    margin: 10px 0 10px 0;
    display: grid;
    grid-template-columns: 0.5fr 1.75fr 1fr 1fr 0.25fr;
    grid-template-rows: 1fr;
    min-height: 40px;
  }

  .cell-1 {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 33px;
  }
  .cell-2 {
    display: grid;
  }
  .cell-3 {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: auto;

    select {
      width: 100%;
      height: 40px;
      align-items: center;
      background-position-y: 50%;
      padding-left: 5px;
      @include fontsize(14px);
      color: #666666;
      letter-spacing: 1px;
      line-height: 21px;
    }
  }
.cell-4 {
    display: flex;
    align-items: center;
    margin: auto;
  }
  .cell-5 {
        display: flex;
        align-items: center;
        margin: auto;
  }
  .arrow-right {
    width: 0;
    height: 0;
    border-top: 60px solid transparent;
    border-bottom: 60px solid transparent;
    border-left: 60px solid green;
  }
</style>
