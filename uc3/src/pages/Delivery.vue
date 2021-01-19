<template>
  <div>
    <div>
      <HeaderPart :header-data="LoginHeader" />
      <Hello
        :user-data="userData"
        :position="2"
      />
      <p class="instructions p2">
        {{ $t('address.message') }}
      </p>
      <ShippingMethod
        :shipping-props="shippingProperties"
        @click.native="showDelivery"
      />
      <Address
        v-if="!guestMode"
        v-show="showDeliveryForm"
      />
      <GuestAddress
        v-if="guestMode"
        v-show="showDeliveryForm"
      />
    </div>
    <div id="continuButtonDiv">
      <div
        v-if="scrolledToBottom"
        class="logout"
      >
        <button
          class="logout-btn"
          @click="logout"
        >
          {{ $t('logout') }}
        </button>
      </div>
      <button
        :key="buttonKey"
        class="btn btn--primary continuButton"
        :class="{ 'btn--inactive' : !success }"
        @click.once="continueAction"
      >
        {{ continueText }}
      </button>
    </div>
  </div>
</template>

<script>
import HeaderPart from 'layouts/HeaderPart'
import Hello from 'components/Hello'
import Address from 'components/Address'
import ShippingMethod from 'components/ShippingMethod'
import GuestAddress from '../components/GuestAddress'
import router from '../router'
import axios from '../axios'
import CONFIG from '../static/general/config.json'
import APICONFIG from '../static/api/config.json'
const MAX_ADDRESS_LENGTH = 35

export default {
  name: 'Delivery',
  components: {
    HeaderPart,
    Hello,
    Address,
    ShippingMethod,
    GuestAddress
  },
  data () {
    return {
      LoginHeader: {
        layout: 'black',
        link: false,
        horizontal: false,
        displayLogout: true
      },
      oldAddressForm: Object.assign({}, this.$store.state.customerAddress),
      addressForm: null,
      userData: this.$store.state.customers,
      showDeliveryForm: true,
      returnError: false,
      shippingProperties: null,
      success: false,
      scrolledToBottom: false,
      guestMode: false,
      continueText: null,
      accountCreationErrorMsg: null,
      accountCreationError: false,
      buttonKey: 1
    }
  },
  metaInfo () {
    return {
      title: this.$i18n.t('meta.login'),
      titleTemplate: '%s | Nespresso'
    }
  },
  mounted () {
    this.scroll()
  },
  created () {
    this.defineShippingMethode()
    window.gtmDataObject.push({
      'event': 'event_pageView',
      'isEnvironmentProd': APICONFIG.isEnvProd,
      'pageName': 'Delivery | Twint',
      'pageType': 'Delivery', // Other values include “Product List”, “Product Details”, “Checkout”, “User Account” etc.
      'pageCategory': '', // If available, please populate. Example ‘Coffee’ this is the value Nessoft nsp.page.pagessubsection1
      'pageSubCategory': '', // If available, please populate. this is the value Nessoft nsp.page.pagessubsection2
      'pageTechnology': CONFIG.gtm.pageTechnology, // Other values include “VertuoLine”, “Original Line”, “Pro”, or “None”
      'market': CONFIG.gtm.market,
      'version': '', // Twint application version
      'landscape': CONFIG.gtm.landscape,
      'segmentBusiness': CONFIG.gtm.segmentBusiness,
      'currency': CONFIG.gtm.currency,
      'eventCategory': 'User Engagement',
      'eventAction': 'Delivery - Twint',
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
    this.addressForm = this.$store.state.customerAddress
    this.continueText = this.$t('address.continue')
    this.renderEmailFieldEmpty(this.addressForm.email) // render field empty for fake email
    this.success = this.validEmail(this.addressForm.email) && (this.addressForm.gender >= 0) && this.validFieldSize(this.addressForm.lastName) &&
     this.validFieldSize(this.addressForm.firstName) && this.validFieldSize(this.addressForm.zip) &&
      this.validFieldSize(this.addressForm.city) && this.validAddressSize(this.addressForm.address) && this.validTitle(this.addressForm.gender)
    this.unsubscribe = this.$store.subscribe((mutation, state) => {
      if (mutation.type === 'storeCustomerAddress') {
        this.addressForm = state.customerAddress
        this.continueText = (this.addressForm.createNewAccount && this.addressForm.createNewAccount === true) ? this.$t('address.guestContinue') : this.$t('address.continue')
        this.success = this.validEmail(this.addressForm.email) && (this.addressForm.gender >= 0) && this.validFieldSize(this.addressForm.lastName) &&
     this.validFieldSize(this.addressForm.firstName) && this.validFieldSize(this.addressForm.zip) &&
      this.validFieldSize(this.addressForm.city) && this.validAddressSize(this.addressForm.address) && this.validTitle(this.addressForm.gender)
      }
    })
  },
  beforeDestroy () {
    this.unsubscribe()
  },
  methods: {
    defineShippingMethode () {
      if (Object.keys(this.$store.state.customerAddress).length === 0) {
        this.guestMode = true
        this.shippingProperties = { 'name': this.$i18n.t('delivery.guest'), 'price': '', 'description': this.$i18n.t('delivery.guestConditions') }
      } else {
        this.shippingProperties = { 'name': this.$i18n.t('delivery.name'), 'price': this.$i18n.t('delivery.price'), 'description': this.$i18n.t('address.conditions') }
      }
    },
    scroll () {
      window.onscroll = () => {
        let bottomOfWindow = Math.max(window.pageYOffset, document.documentElement.scrollTop, document.body.scrollTop) + window.innerHeight === document.documentElement.offsetHeight
        if (bottomOfWindow) {
          this.scrolledToBottom = true
        } else {
          this.scrolledToBottom = false
        }
      }
    },
    logout () {
      localStorage.removeItem('customerRelationUuid')
      router.push({
        name: 'Login', query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign }
      })
    },
    continueAction () {
      this.guestMode === true ? this.createCustomer(this.addressForm) : this.goToProduct()
    },
    createCustomer (addressData) {
      let formData = {}
      formData.email = addressData.email
      formData.title = addressData.gender
      formData.lastName = addressData.lastName
      formData.firstName = addressData.firstName
      formData.address = addressData.address
      formData.city = addressData.city
      formData.postalCode = addressData.zip
      formData.optin = addressData.optIn
      formData.guestMode = !addressData.createNewAccount
      axios.post('V1/nespresso/customer/create', formData
      )
        .then(response => {
          let datas = JSON.parse(response.data)
          if (datas.customer) {
            this.$store.commit('storeCustomer', datas.customer)
          }
          this.$store.commit('storeCustomerAddress', this.addressForm)
          router.push({
            name: 'MultiProducts',
            query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign }
          })
        })
        .catch(error => {
          if (error.response.data.message === 'Account Already Exist') {
            this.$alert(error.response.data.message, this.$t('errorManagement.accountExist'), 'warning').then(r => {
              router.push({
                name: 'Login',
                query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign }
              })
            })
          } else {
            this.$alert(error.response.data.message, this.$t('errorManagement.genericTitle'), 'error')
          }
          console.log(error)
          this.buttonKey++
        })
    },
    goToProduct () {
      if (this.isAddressUpdated(this.oldAddressForm, this.addressForm)) {
        this.addressForm.isAddressChanged = true
        this.$store.commit('storeCustomerAddress', this.addressForm)
      } else {
        this.addressForm.isAddressChanged = false
        this.$store.commit('storeCustomerAddress', this.addressForm)
      }
      router.push({
        name: 'MultiProducts',
        query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign }
      })
    },
    isAddressUpdated (oldAddressData, AddressData) {
      let oldAddress = JSON.stringify(oldAddressData.address + oldAddressData.zip.trim() + oldAddressData.city).split(' ').join('').toLowerCase()
      let Address = JSON.stringify(AddressData.address + AddressData.zip + AddressData.city).split(' ').join('').toLowerCase()
      return oldAddress !== Address
    },
    validEmail (email) {
      var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
      if (re.test(email)) {
        return email !== CONFIG.fakeEmail
      }
    },
    validAddressSize (field) {
      return field.length > 0 && field.length <= MAX_ADDRESS_LENGTH
    },
    validFieldSize (field) {
      return field.length > 0
    },
    validTitle (field) {
      return field !== null && field >= 0
    },
    showDelivery () {
      this.showDeliveryForm = !this.showDeliveryForm
    },
    renderEmailFieldEmpty (fakeEmail) {
      if (fakeEmail) {
        fakeEmail.trim()
        if (fakeEmail === CONFIG.fakeEmail) {
          this.addressForm.email = ''
        }
      }
    }
  }
}
</script>

<style lang="scss">
  @import '../assets/scss/mixins';

.continuButton{
  margin-left: 20px;
  width: calc(100% - 40px);
}

.logout {
  text-align: center;
  margin:10px
}

.logout-btn {
  border: 0;
  background-color: transparent;
}

#continuButtonDiv {
    background: white;
    padding-bottom: 11px;
    padding-top: 10px;
    position: fixed;
    bottom: -1px;
    width: 100%;
    margin-left: -20px;
}

  body.login {
    background: url('../static/img/bg.jpg') $black no-repeat center center;
    background-size: cover;
  }
  .instructions {
      margin: 40px 0 40px 0;
    text-align: center;
    font-weight: 300;
    color: $grey;
  }
  .p2 {
    font-size: 18px;
    line-height: 24px;
    letter-spacing: 1px;
}

</style>
