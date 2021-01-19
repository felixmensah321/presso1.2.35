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
      <div v-if="isSku && CurrentProductDatas.length">
        <div>
          <UnitProduct :product-data="CurrentProductDatas[0]" />
        </div>
      </div>
      <div v-else-if="isOneLevel && CurrentCategory">
        <div
          v-for="product in CurrentCategory.items"
          :key="product.id"
        >
          <UnitProduct :product-data="product" />
        </div>
      </div>
      <div v-else-if="CurrentCategoryId && CategoryArray">
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
        <div v-if="CurrentCategory && CurrentCategory.hasChild">
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
import axios from '../axios'
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
      DataReturned: this.$store.state.dataReturned,
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
      this.ProductHeader.displayLogout = true
    }
    this.getInformations()
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
    },
    // Gathering all informations
    // /!\ The way we recover informations should change in UC3
    getInformations () {
      if (this.DataReturned.sku) {
        this.$store.commit('storeisSku', true)
        this.isSku = true
        this.getSKU(this.DataReturned.sku)
      } else if (this.DataReturned.categoryId) {
        this.getCategoryArchitecture(this.DataReturned.categoryId, 1)
      } else {
        this.getCategoryArchitecture(52, 1)
        this.getAllProducts()
      }
    },
    // All the following methods should be removed on UC3
    getSKU (sku) {
      axios.get(this.$store.state.endAPIRequest + 'nespresso/products/' + sku)
        .then(response => {
          if (response.data.status !== 1) {
            router.push({
              name: 'NotFound',
              query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign }
            })
          }
          this.isSku = true
          this.CurrentProductDatas = [response.data]
          this.mappProductsByCategory()
        })
        .catch(error => {
          router.push({
            name: 'NotFound',
            query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign }
          })
          console.log(error)
        })
    },
    getCategoryProducts (categoryId) {
      axios.get(this.$store.state.endAPIRequest + 'nespresso/products?searchCriteria[filter_groups][0][filters][0][field]=category_id&searchCriteria[filter_groups][0][filters][0][value]=' + categoryId + '&searchCriteria[filter_groups][0][filters][0][condition_type]=eq&searchCriteria[filter_groups][0][filters][1][field]=status&searchCriteria[filter_groups][0][filters][1][value]=1&searchCriteria[filter_groups][0][filters][1][condition_type]=eq&searchCriteria[sortOrders][0][field]=position&searchCriteria[sortOrders][0][direction]=DESC')
        .then(response => {
          this.assignProductToCategory(categoryId, response.data.items)
          this.mappProductsByCategory()
          return response.data.items
        })
        .catch(error => {
          router.push({
            name: 'NotFound',
            query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign }
          })
          console.log(error)
        })
    },
    sortProductsByPosition (currentCategory, prdList) {
      return prdList.sort((left, right) => {
        let positionLeft = 0
        let positionRight = 0

        let categoryLinksLeft = left['extension_attributes']['category_links']
        let categoryLinksRight = right['extension_attributes']['category_links']

        for (let i = 0; i < categoryLinksLeft.length; i++) {
          if (categoryLinksLeft[i].category_id === currentCategory) {
            positionLeft = categoryLinksLeft[i].position
          }
        }
        for (let i = 0; i < categoryLinksRight.length; i++) {
          if (categoryLinksRight[i].category_id === currentCategory) {
            positionRight = categoryLinksRight[i].position
          }
        }
        if (positionLeft < positionRight) {
          return -1
        }
        if (positionLeft > positionRight) {
          return 1
        }
        return 0
      })
    },
    assignProductToCategory (categoryId, items) {
      if (this.categoriesArchitecture.id === categoryId) {
        this.categoriesArchitecture.items = this.sortProductsByPosition(categoryId, items)
      } else {
        if (this.categoriesArchitecture.hasChild) {
          for (let i = 0; i < this.categoriesArchitecture.children.length; i++) {
            if (this.categoriesArchitecture.children[i].id === categoryId) {
              this.categoriesArchitecture.children[i].items = this.sortProductsByPosition(categoryId, items)
              break
            } else {
              for (let j = 0; j < this.categoriesArchitecture.children[i].children.length; j++) {
                if (this.categoriesArchitecture.children[i].children[j].id === categoryId) {
                  this.categoriesArchitecture.children[i].children[j].items = this.sortProductsByPosition(categoryId, items)
                  break
                }
              }
            }
          }
        }
      }
      this.$store.commit('storeCategoryArchitecture', this.categoriesArchitecture)
      this.categoriesFilled++
    },
    getAllProducts () {
      axios.get(this.$store.state.endAPIRequest + 'products?searchCriteria[pageSize]=0&searchCriteria[filter_groups][0][filters][0][field]=status&searchCriteria[filter_groups][0][filters][0][value]=1&searchCriteria[filter_groups][0][filters][0][condition_type]=eq')
        .then(response => {
          this.CurrentProductDatas = response.data.items
          this.CurrentProductDatas = response.data
          this.mappProductsByCategory()
        })
        .catch(error => {
          router.push({
            name: 'NotFound',
            query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign }
          })
          console.log(error)
        })
    },
    getCategoryArchitecture (categoryId, level) {
      axios.get(this.$store.state.endAPIRequest + 'nespresso/categories/' + categoryId).then(response => {
        let obj = {
          id: categoryId,
          name: response.data.name,
          position: response.data.position,
          children: [],
          items: [],
          level: level,
          hasChild: false
        }

        let parsedChildren = response.data.children.split(',')
        if (parsedChildren.length > 0 && level < 3) {
          if (parsedChildren[0] === '' && level === 1) {
            this.categoriesArchitecture = obj
            this.categoriesToFill++
            this.getCategoryProducts(obj.id)
          } else {
            if (response.data.children !== '') {
              obj.hasChild = true
            } else {
              this.categoriesToFill++
              this.getCategoryProducts(obj.id)
            }
            switch (level) {
              case 1:
                this.categoriesArchitecture = obj
                break
              case 2:
                this.categoriesArchitecture.children.push(obj)
                break
              default:
            }
            if (obj.hasChild) {
              for (let i = 0; i < parsedChildren.length; i++) {
                this.getCategoryArchitecture(parsedChildren[i], level + 1)
              }
            }
          }
        } else {
          if (level === 3) {
            this.categoriesToFill++
            this.getCategoryProducts(obj.id)
            for (let i = 0; i < this.categoriesArchitecture.children.length; i++) {
              if (Number(this.categoriesArchitecture.children[i].id) === response.data.parent_id) {
                this.categoriesArchitecture.children[i].children.push(obj)
              }
            }
          }
        }
        if (this.categoriesArchitecture.hasChild) {
          this.categoriesArchitecture.children = this.categoriesArchitecture.children.sort((a, b) => (a.position > b.position) ? 1 : -1)
          for (let i = 0; i < this.categoriesArchitecture.children.length; i++) {
            if (this.categoriesArchitecture.children[i].hasChild) {
              this.categoriesArchitecture.children[i].children = this.categoriesArchitecture.children[i].children.sort((a, b) => (a.position > b.position) ? 1 : -1)
            }
          }
        }
        this.$store.commit('storeCategoryArchitecture', this.categoriesArchitecture)
        this.mappProductsByCategory()
      })
        .catch(error => {
          console.log(error)
        })
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
  background-position-x: 95%;
  background-position-y: -7px;
  border: 1px solid #dfdfdf;
  border-radius: 2px;
  padding: 0;
}

.category-selector {
  background-color: unset;
  border: unset;
  width: calc(100% - 21px);
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

.wrapper {
  border-top: 1px solid #d8d8d8;
  border-bottom: 1px solid #d8d8d8;
  margin-left: -21px;
  width: calc(100% + 40px);
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
