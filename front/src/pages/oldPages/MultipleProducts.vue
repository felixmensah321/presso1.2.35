<template>
  <div>
    <HeaderPart
      :header-data="ProductHeader"
      :cart-view="true"
      @cartPreview="cartClicked()"
    />
    <Hello
      :user-data="CurrentUserDatas"
      :prd-data="prdDatas"
      :position="pos"
      :no-hello="true"
    />

    <div v-if="!isCartVisible">
      <div v-if="isSku">
        <div>
          <UnitProduct :product-data="CurrentProductDatas[0]" />
        </div>
      </div>
      <div v-else-if="isOneLevel">
        <div
          v-for="product in CurrentCategory.items"
          :key="product.id"
        >
          <UnitProduct :product-data="product" />
        </div>
      </div>
      <div v-else>
        <div class="wrapper">
          <article>{{ $t('product.listLabel') }}</article>
          <select
            v-model="CurrentCategoryId"
            class="category-selector"
            @change="onChange($event)"
          >
            <option
              v-for="category in CategoryArray"
              :key="category.name"
              class="item"
              :value="category.id"
            >
              <p class="p4">
                {{ category.name }}
              </p>
            </option>
          </select>
        </div>
        <div v-if="CurrentCategory.hasChild">
          <div
            v-for="child in CurrentCategory.children"
            :key="child.id"
            class="child"
          >
            <div class="section">
              <span class="title">{{ child.name }} </span>
            </div>
            <div
              v-for="product in child.items"
              :key="product.id"
              class="pdtlist"
            >
              <UnitProduct :product-data="product" />
            </div>
          </div>
        </div>
        <div v-else>
          <div
            v-for="product in CurrentCategory.items"
            :key="product.id"
            class="pdtlist"
          >
            <UnitProduct :product-data="product" />
          </div>
        </div>
      </div>
    </div>
    <div v-else>
      <Cart />
    </div>
    <div class="btn-wrapper">
      <button
        class="btn btn--primary"
        :class="{ 'btn--inactive' : !totalPrice }"
        @click="sendOrder()"
      >
        {{ $t('product.btn') }} (Fr. {{ formatPrice(totalPrice) }})
      </button>
    </div>
  </div>
</template>

<script>
import router from '../router'
import HeaderPart from 'layouts/HeaderPart'
import Hello from 'components/Hello'
import UnitProduct from 'components/UnitProduct'
import Cart from 'components/Cart'
import CONFIG from '../static/general/config.json'
import APICONFIG from '../static/api/config.json'

export default {
  name: 'MultiProductPage',
  components: {
    HeaderPart,
    Hello,
    UnitProduct,
    Cart
  },
  data () {
    return {
      isSku: this.$store.state.isSku,
      fullData: null,
      isOneLevel: false,
      CurrentCategoryId: null,
      CurrentCategory: null,
      CategoryArray: null,
      CurrentProductDatas: this.$store.state.allProducts,
      CurrentUserDatas: this.$store.state.customers,
      prdDatas: { toShow: true },
      total: 0,
      click: 0,
      pos: 0,
      ProductHeader: {
        layout: 'black',
        link: false,
        horizontal: false
      },
      clickOnSendOrder: false,
      isCartVisible: false
    }
  },
  computed: {
    totalPrice () {
      return this.$store.state.totalPrice
    }
  },
  watch: {
    totalPrice (newPrice, oldPrice) {
    }
  },
  metaInfo () {
    return {
      title: this.CurrentProductDatas.name,
      titleTemplate: '%s | Nespresso'
    }
  },
  created () {
    var impressionsOnScroll = {
      'currencyCode': CONFIG.gtm.currency
    }
    this.$store.commit('storePlpDatapushed', impressionsOnScroll)
    window.gtmDataObject.push({
      'event': 'event_pageView',
      'isEnvironmentProd': APICONFIG.isEnvProd,
      'pageName': 'Products | Twint',
      'pageType': 'Products', // Other values include “Product List”, “Product Details”, “Checkout”, “User Account” etc.
      'pageCategory': '', // If available, please populate. Example ‘Coffee’ this is the value Nessoft nsp.page.pagessubsection1
      'pageSubCategory': '', // If available, please populate. this is the value Nessoft nsp.page.pagessubsection2
      'pageTechnology': CONFIG.gtm.pageTechnology, // Other values include “VertuoLine”, “Original Line”, “Pro”, or “None”
      'market': CONFIG.gtm.market,
      'version': '', // Twint application version
      'landscape': CONFIG.gtm.landscape,
      'segmentBusiness': CONFIG.gtm.segmentBusiness,
      'currency': CONFIG.gtm.currency,
      'eventCategory': 'Products',
      'eventAction': 'Products - Twint',
      'loginMethod': 'Phone', // is this required
      'clubMemberID': this.$store.state.userDatas === null || this.$store.state.userDatas.custom_attributes === undefined ? null : this.$store.state.userDatas.custom_attributes[2].value,
      'clubMemberStatus': null,
      'clubMemberLevel': null,
      'clubMemberLevelID': null,
      'clubMemberTitle': null,
      'clubMemberLoginStatus': this.$store.state.customers.id === null || this.$store.state.customers.id === undefined ? 'false' : 'true',
      'machineOwned': null,
      'preferredTechnology': null
    })
    this.$store.commit('storePurchases', [])
    this.$store.commit('storeTotalPrice', 0)
    this.$store.commit('storeTotalQty', 0)
    if (this.$store.state.defaultPurchasePointId.includes(this.$store.state.purchasePointId)) {
      this.pos = 3
    } else {
      this.pos = 2
    }
    this.CurrentProductDatas = this.$store.state.allProducts
    this.mappProductsByCategory()
  },
  beforeMount () {
    this.CurrentProductDatas = this.$store.state.allProducts
  },
  methods: {
    getCategoryById (categoryId) {
      this.CategoryArray.forEach(category => {
        if (Number(categoryId) === Number(category.id)) {
          this.CurrentCategory = category
        }
      })
    },
    cartClicked () {
      this.isCartVisible = !this.isCartVisible
    },
    onChange ($event) {
      var impressionsOnScroll = {
        'currencyCode': CONFIG.gtm.currency
      }
      this.$store.commit('storePlpDatapushed', impressionsOnScroll)
      this.CurrentCategoryId = $event.target.value
      this.getCategoryById(this.CurrentCategoryId)
    },
    mappProductsByCategory () {
      let categoryArchitecture = this.$store.state.categoryArchitecture
      if (categoryArchitecture.hasChild) {
        this.CategoryArray = categoryArchitecture.children
        this.CurrentCategory = categoryArchitecture.children[0]
        this.CurrentCategoryId = categoryArchitecture.children[0].id
      } else {
        this.isOneLevel = true
        this.CurrentCategory = categoryArchitecture
        this.CurrentCategoryId = categoryArchitecture.id
      }

      this.fullData = categoryArchitecture
    },
    sendOrder () {
      if (this.click < 1) {
        this.clickOnSendOrder = true

        if (this.$store.state.customers.id) {
          this.user = this.$store.state.customers.id
        } else {
          this.user = 'guest'
        }

        if (this.$store.state.customers.store_id) {
          this.storeID = this.$store.state.customers.store_id
        } else {
          this.storeID = '1'
        }

        this.order = {
          session_id: this.$store.state.sessionId,
          customer_id: this.user,
          store_id: this.storeID,
          address: this.$store.state.customerAddress,
          products: this.$store.state.purchases,
          purchase_point_id: this.$store.state.purchasePointId
        }

        setTimeout(() => {
          this.$store.commit('storeOrder', this.order)

          router.push({ name: 'Loading', query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign } })
        }, 0)

        this.click++
      } else {
        return false
      }
    },
    formatPrice (value) {
      let val = (value / 1).toFixed(2).replace(',', '.')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "'")
    }
  }
}
</script>
<style lang="scss">
@import "../assets/scss/mixins";

.pdtlist:not(:last-child) {
  border-bottom: 1px solid #cccccc;
}

img {
  display: block;
  max-width: 100%;
}

.wrapper {
  border-top: 1px solid #d8d8d8;
  border-bottom: 1px solid #d8d8d8;
  margin-left: -21px;
  width: calc(100% + 42px);
}

article {
  @include fontsize(14px);
  letter-spacing: 1px;
  line-height: 21px;
  margin-left: 20px;
}

select {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background: transparent;
  background-image: url("/static/img/chevron-down.svg");
  background-repeat: no-repeat;
  background-position-x: 90%;
  background-position-y: -7px;
  border: 1px solid #dfdfdf;
  border-radius: 2px;
  padding: 0;
}

.category-selector {
  background-color: unset;
  border: unset;
  width: 100%;
   font-weight: bold;
  @include fontsize(18px);
  line-height: 24px;
  letter-spacing: 1px;
  margin-left: 20px;
}

.category-selector:focus {
  outline: unset;
}

.item {
  font-weight: bold;
  @include fontsize(16px);
  margin-left: 20px;
  width: 100%;
}

.section {
  display: flex;
  height: 52px;
  background-color: #E6E6E6;
  align-items: center;
  width: calc(100% + 40px);
  margin-left: -20px;
  .title {
    margin-left: 20px;
    @include fontsize(18px);
    font-weight: bold;
    letter-spacing: 3px;
    line-height: 24px
  }
}
</style>
