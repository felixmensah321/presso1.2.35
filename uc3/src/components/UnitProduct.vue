<template>
  <div
    v-observe-visibility="{
      callback:
        visibilityChanged,
      once:true,
    }"
    class="product-card"
  >
    <modal
      v-show="isModalVisible"
      :product-data="productData"
      @close="closeModal"
    />
    <modal-image
      v-if="productLabelEnergetic"
      v-show="isModalImageVisible"
      :product-data="productData"
      @closeImage="closeModalImage"
    />
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
        <div>
          <article v-html="productDescr" />
        </div>

        <a
          v-if="productLabelEnergetic"
          rel="nofollow"
          :class="labelClass"
          @click="showEnergeticImage()"
        />
      </div>
      <div class="cell cell-3">
        <div calss="price-div">
          <span class="price">Fr. {{ formatPrice(productPrice) }}</span>
        </div>
        <div class="qty-contain">
          <div class="qty">
            <button
              class="action-btn"
              @click="increment()"
            >
              <span
                v-if="quantity <= 0"
                data-icon="plus"
              />
              <span v-else>
                {{ quantity }}
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../axios'
import modal from './QuantityPopIn.vue'
import modalImage from './EnergeticImageModal.vue'
import CONFIG from '../static/general/config.json'

export default {
  name: 'Product',
  components: {
    modal,
    modalImage
  },
  props: {
    productData: { type: Object, default: null }
  },
  data () {
    return {
      isModalVisible: false,
      isModalImageVisible: false,
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
      nbCaps: null
    }
  },
  created () {
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
    visibilityChanged (isVisible, entry) {
      if (isVisible) {
        let sku = null
        let objectData = this.$store.state.plpDatapushed
        sku = this.productData.sku
        objectData[sku] = {
          'name': this.productData.name,
          'id': this.productData.sku,
          'price': this.productData.price,
          'category': null,
          'variant': '',
          'brand': CONFIG.gtm.brand,
          'list': CONFIG.gtm.list,
          'position': null,
          'dimension53': this.productData.id,
          'dimension54': this.productData.name,
          'dimension55': null,
          'dimension56': null,
          'dimension57': this.productData.type_id
        }
        this.$store.commit('storePlpDatapushed', objectData)
        console.log(objectData)
      }
    },
    pushGtmDataObject (productData, qty) {
      window.gtmDataObject.push({
        'event': 'addToCart',
        'currencyCode': CONFIG.gtm.currency,
        'ecommerce': {
          'add': {
            'actionField': {},
            'products': [{
              'name': productData.name,
              'id': productData.sku,
              'quantity': qty,
              'price': productData.price,
              'category': null,
              'variant': '',
              'brand': CONFIG.gtm.brand,
              'dimension53': productData.id,
              'dimension54': productData.name,
              'dimension55': null,
              'dimension56': null,
              'dimension57': productData.type_id,
              'metric10': null,
              'metric11': null,
              'metric12': null,
              'metric37': null
            }]
          }
        }
      })
    },
    getEnergeticLabelOptions (value) {
      axios.get(this.$store.state.endAPIRequest + 'nespresso/energetic_labels')
        .then(response => {
          let optionsArray = response.data
          this.productLabelEnergetic = optionsArray.filter(item => item.value === value)[0].label
          switch (this.productLabelEnergetic) {
            case 'A+++':
              this.labelClass = 'appp-label'
              break
            case 'A++':
              this.labelClass = 'app-label'
              break
            case 'A+':
              this.labelClass = 'ap-label'
              break
            case 'A':
              this.labelClass = 'a-label'
              break
            case 'B':
              this.labelClass = 'b-label'
              break
            case 'C':
              this.labelClass = 'c-label'
              break
            case 'D':
              this.labelClass = 'd-label'
              break
            default:
              break
          }
        })
        .catch(error => {
          console.log(error)
        })
    },
    showEnergeticImage () {
      this.isModalImageVisible = true
    },
    formatPrice (value) {
      let val = (value / 1).toFixed(2).replace(',', '.')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "'")
    },
    increment () {
      this.isModalVisible = true
    },
    showModal () {
      this.isModalVisible = true
    },
    closeModal (qty) {
      this.isModalVisible = false
      this.quantity = qty
      this.pushGtmDataObject(this.productData, this.quantity)
    },
    closeModalImage () {
      this.isModalImageVisible = false
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
    display: block;

    h4 {
      @include fontsize(14px);
      margin: 0;
      text-transform: uppercase;
      line-height: 21px;
      letter-spacing: 1px;
    }

    article {
      @include fontsize(14px);
      color: $grey;
      margin-left: 0;
      letter-spacing: 1px;
      line-height: 1.4;

      p {
      margin-top: 0;
      }
    }

    .product-img {
      display: block;
      max-width: 85%;
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
      color: $green;
      font-size: 14px;
      font-weight: 700;
      min-height: 30px;
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
    margin: 5px 0 5px 0;
    display: grid;
    grid-template-columns: 1fr 2fr 0.75fr;
    grid-template-rows: 1fr;
  }
  .cell {

  }
  .cell-1 {
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .cell-2 {
    display: grid;
  }
  .cell-3 {
    display: grid;
  }

  .arrow-right {
    width: 0;
    height: 0;
    border-top: 60px solid transparent;
    border-bottom: 60px solid transparent;
    border-left: 60px solid green;
  }

  .appp-label {
    min-height: 17px;
    background: url('../static/img/televisions-4--A+++--right--10.png') no-repeat;
  }
  .app-label {
      min-height: 17px;
    background: url('../static/img/televisions-4--A++--right--10.png') no-repeat;
  }
  .ap-label {
      min-height: 17px;
    background: url('../static/img/televisions-4--A+--right--10.png') no-repeat;
  }
  .a-label {
      min-height: 17px;
    background: url('../static/img/televisions-4--A--right--10.png') no-repeat;
  }
  .b-label {
      min-height: 17px;
    background: url('../static/img/televisions-4--B--right--10.png') no-repeat;
  }
  .c-label {
      min-height: 17px;
    background: url('../static/img/televisions-4--C--right--10.png') no-repeat;
  }
  .d-label {
      min-height: 17px;
    background: url('../static/img/televisions-4--D--right--10.png') no-repeat;
  }
</style>
