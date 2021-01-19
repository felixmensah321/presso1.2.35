<template>
  <div>
    <VuePageVisibility
      @documentInactive="documentInactive"
      @documentActive="documentActive"
    />
    <HeaderPart :header-data="LoginHeader" />

    <div class="loading-block">
      <Loader />
      <h1>{{ $t('loading.title') }}</h1>
      <p>{{ $t('loading.message') }}</p>
    </div>
  </div>
</template>

<script>
import axios from '../axios'
import router from '../router'
import HeaderPart from 'layouts/HeaderPart'
import VuePageVisibility from 'vue-page-visibility-awesome'
import Loader from 'components/Loader'
import CONFIG from '../static/general/config.json'
import APICONFIG from '../static/api/config.json'

export default {
  name: 'Loading',
  components: {
    HeaderPart,
    Loader,
    VuePageVisibility
  },
  data () {
    return {
      LoginHeader: {
        layout: 'black',
        link: false,
        horizontal: false
      },
      orderDatas: null,
      pageIsVisible: true,
      orderId: null
    }
  },
  mounted () {
    this.createOrder()
  },
  created () {
    history.pushState(null, null, location.href)
    window.onpopstate = function () {
      history.go(1)
    }
  },
  methods: {
    documentInactive () {
      this.pageIsVisible = false
    },
    documentActive () {
      this.pageIsVisible = true
      this.getOrderStatus(this.orderId)
    },
    getOrderStatus (orderId) {
      if (orderId) {
        axios.get(this.$store.state.endAPIRequest + 'orders/' + orderId)
          .then(response => {
            if (response.data.status === 'complete') {
              this.$store.commit('storeCompletedOrder', response.data)
              router.push({ name: 'Success', query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign } })
            } else if (response.data.status === 'pending') {
              if (this.pageIsVisible) {
                setTimeout(() => {
                  this.getOrderStatus(orderId)
                }, 1000)
              }
            } else if (response.data.status === 'canceled') {
              router.push({ name: 'ErrorPayment', query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign } })
            }
          })
          .catch(error => {
            console.log(error)
            router.push({ name: 'ErrorPayment', query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign } })
          })
      }
    },
    created () {
      window.gtmDataObject.push({
        'event': 'event_pageView',
        'isEnvironmentProd': APICONFIG.isEnvProd,
        'pageName': 'Checkout Process | Twint',
        'pageType': 'Checkout Process', // Other values include “Product List”, “Product Details”, “Checkout”, “User Account” etc.
        'pageCategory': '', // If available, please populate. Example ‘Coffee’ this is the value Nessoft nsp.page.pagessubsection1
        'pageSubCategory': '', // If available, please populate. this is the value Nessoft nsp.page.pagessubsection2
        'pageTechnology': CONFIG.gtm.pageTechnology, // Other values include “VertuoLine”, “Original Line”, “Pro”, or “None”
        'market': CONFIG.gtm.market,
        'version': '', // Twint application version
        'landscape': CONFIG.gtm.landscape,
        'segmentBusiness': CONFIG.gtm.segmentBusiness,
        'currency': CONFIG.gtm.currency,
        'eventCategory': 'User Engagement',
        'eventAction': 'Checkout Process - Twint',
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
    },
    createOrder () {
      axios.post('/V1/nespresso/order/create', this.$store.state.orders)
        .then(response => {
          let jsonParsed = JSON.parse(response.data)
          this.orderId = jsonParsed.orderId
          if (this.orderId !== null || this.orderId !== undefined) {
            this.pushGtmDataObject(this.orderId)
          }
          this.sendToken(jsonParsed.token)
        })
        .catch(error => {
          console.log(error)
          router.push({ name: 'NotFound', query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign } })
        })
    },
    pushGtmDataObject (orderId) {
      let products = []
      for (let i = 0; i < this.$store.state.orders.products.length; i++) {
        products.push({
          'name': this.$store.state.orders.products[i].allDatas.name,
          'id': this.$store.state.orders.products[i].sku,
          'quantity': this.$store.state.orders.products[i].quantity,
          'price': this.$store.state.orders.products[i].allDatas.price,
          'category': null,
          'brand': CONFIG.gtm.brand,
          'dimension44': null, // False Signifies if this produc0:t was a part of a discovery offer in this order or not. October release 2016.
          'dimension53': this.$store.state.orders.products[i].allDatas.id, // Local market id for product
          'dimension54': this.$store.state.orders.products[i].allDatas.name,
          'dimension55': null,
          'dimension56': null, // Product technology according to Nespresso categorization
          'dimension57': this.$store.state.orders.products[i].allDatas.custom_attributes[3].value, // Product type e.g. Capsule, Bundle, Assortment etc.
          'dimension59': null, // Product was added by a user, not club Action = True
          'dimension153': null,
          'metric6': this.$store.state.orders.products[i].quantity, // Insert capsule quantity purchased here
          'metric5': null, // Insert machine quantity purchased here
          'metric9': null // Insert accessories quantity purchased here
        })
      }
      window.gtmDataObject.push({
        'event': 'transaction', // This is required and critical
        'currencyCode': CONFIG.gtm.currency,
        'productsReOrdered': null,
        'contactPreferenceSelected': null,
        'keepMeInformed': null,
        'transactionTotal': this.getTotalTransaction(this.$store.state.orders.products), // Total amount of order, including tax, shipping and discounts/coupons
        'userCreditAmountUsed': null, // IF user has used credit on his Nespresso account for payment
        'ecommerce': {
          'purchase': { // This is required and critical
            'actionField': {
              'id': orderId, // This is required and critical, transaction ID
              'affiliation': CONFIG.gtm.affiliation, // This is static value if a regular order. For future use to compare other retail outlets (boutique stores, 3rd party retailers, etc.)
              'revenue': this.getTotalTransaction(this.$store.state.orders.products), // Total revenue (subTotal amount on order), NOT including tax and shipping
              'tax': null,
              'shipping': '0',
              'coupon': null, // Example of a coupon applied on checkout by combination of clubActionIDs combined with a pipe "|||" separator
              'clubActionTotalAmount': null, // Example of a coupon amount applied  by combination of clubActionIDs values
              'rebateAmount': '0', // Amount of the rebate applied on order.
              'shippingAddressCity': this.$store.state.orders.address.city,
              'shippingAddressState': '', // Leave blank if no “state” or “province” value.
              'shippingAddressCountry': CONFIG.gtm.country,
              'shippingAddressPostalCode': this.$store.state.orders.address.zip,
              'billingAddressCity': this.$store.state.orders.address.city,
              'billingAddressState': '', // Leave blank if no “state” or “province” value.
              'billingAddressCountry': CONFIG.gtm.country,
              'billingAddressPostalCode': this.$store.state.orders.address.zip,
              'clubActionID': null,
              'checkoutMainPaymentMethod': CONFIG.gtm.checkoutMainPaymentMethod,
              'checkoutPaymentMethods': CONFIG.gtm.checkoutPaymentMethods,
              'checkoutShippingMethodID': null,
              'deliveryOption_Priority': null,
              'deliveryOption_Signature': null,
              'deliveryOption_Recycling': null
            },
            'products': [ // Array of product details for each product in cart
              products
            ]
          }
        }
      })
    },
    getTotalTransaction (products) {
      let total = 0
      for (let i = 0; i < products.length; i++) {
        total += (products[i].price * products[i].quantity)
      }
      return total
    },
    sendToken (orderToken) {
      let token = orderToken
      let device = this.$store.state.originApp.device
      let origin = this.$store.state.originApp.originKey

      if (device === 'ios' && token != null) {
        let pairing = {
          'app_action_type': 'TWINT_PAYMENT',
          'extras': {
            'code': token
          },
          'referer_app_link': {
            'app_name': 'SMALL_BUSINESS_SOLUTION',
            'target_url': '',
            'url': ''
          },
          'version': '6.0'
        }

        window.location.replace(origin + '://applinks/?al_applink_data=' + JSON.stringify(pairing))
      } else if (device === 'android' && token != null) {
        window.location.replace('intent://payment#Intent;package=' + origin + ';action=ch.twint.action.TWINT_PAYMENT;scheme=twint;S.code=' + token + ';S.startingOrigin=SMALL_BUSINESS_SOLUTION;end')
      }
    }
  }
}
</script>

<style lang="scss" scoped>
  @import '../assets/scss/mixins';

  body.loading {
    background: url('../static/img/bg.jpg') $black no-repeat center center;
    background-size: cover;
  }

  .loading-block {
    @include abs(50vh, null, null, 50vw);
    transform: translateX(-50%) translateY(-50%);
    text-align: center;
    width: calc(100% - 40px);
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 30px;

    h1 {
      margin: 25px 0 0;
      @include fontsize(18px);
      font-weight: 700;
      color: $black;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    p {
      margin: 25px 0 0;
      @include fontsize(18px);
      font-weight: 300;
      color: $black;
      letter-spacing: 1px;
    }
  }
</style>
