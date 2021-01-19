<template>
  <div class="login">
    <HeaderPart
      :header-data="LoginHeader"
    />
    <LoginAutofill
      v-if="receiveID && CurrentUserFirstName"
      :user-data="CurrentUserFirstName"
      :loader="loading"
      :login-error="LoginError"
      @login="login"
    />
    <!-- This component is always displayed if no card is set in TWINT app -->
    <LoginForm
      v-if="!receiveID"
      :loader="loading"
      :login-error="LoginError"
      @login="login"
    />
    <!-- This componenet is displayed only if we are in UC1 and no card in TWINT app -->
    <LoginPasserBy
      v-if="!receiveID && isInPurchasePoint"
      :loader="loading"
      @login="passerByConnection"
    />
    <!-- This component is displayed if we are in UC2 or UC3 and the parameter Guest is present and set to yes in the QRCode -->
    <LoginAsGuest
      v-if="!receiveID && !isInPurchasePoint && displayGuest"
      :loader="loading"
      @login="guestConnection"
    />
    <Loader v-if="loading" />
  </div>
</template>

<script>
import axios from '../axios'
import router from '../router'
import HeaderPart from 'layouts/HeaderPart'
import LoginAutofill from 'components/LoginAutofill'
import LoginForm from 'components/LoginForm'
import Loader from '../components/Loader'
import LoginPasserBy from 'components/LoginPasserBy'
import LoginAsGuest from 'components/LoginAsGuest'
import CONFIG from '../static/general/config.json'
import APICONFIG from '../static/api/config.json'

export default {
  name: 'Login',
  components: {
    HeaderPart,
    LoginForm,
    LoginPasserBy,
    LoginAsGuest,
    LoginAutofill,
    Loader
  },
  data () {
    return {
      LoginHeader: {
        layout: 'black',
        link: false,
        horizontal: false,
        login: true
      },
      LoginError: false,
      CurrentUserDatas: null,
      CurrentUserFirstName: null,
      receiveID: false,
      DataReturned: null,
      isInPurchasePoint: false,
      currentStoreCode: null,
      customerRelationId: null,
      loading: false,
      propData: false,
      displayGuest: false
    }
  },
  metaInfo () {
    return {
      title: this.$i18n.t('meta.login'),
      titleTemplate: '%s | Nespresso'
    }
  },
  created () {
    this.pushGtmDataObject(null)
    this.getCurrentStoreCode()
    this.getOriginApp()
    this.isSessionValid(this.$route.query.sessionId)
  },
  methods: {
    // Recover the Magento storecode from the browser language
    // It's based on the mapping tab in general config
    getCurrentStoreCode () {
      let browserLanguage = navigator.language
      browserLanguage = browserLanguage.substr(0, 2)
      let storeTab = CONFIG.storeMapping
      if (storeTab.hasOwnProperty(browserLanguage)) {
        this.currentStoreCode = storeTab[browserLanguage]
      } else {
        // The default store is english
        this.currentStoreCode = storeTab['en']
      }
      this.$store.commit('storeEndAPIRequest', this.currentStoreCode)
    },
    // Retrieve the mobile informations
    getOriginApp () {
      let urlString = window.location.href
      let url = new URL(urlString)
      let originIos = url.searchParams.get('returnAppScheme')
      let originAndroid = url.searchParams.get('returnAppPackage')
      if (this.$route.query.returnAppScheme != null) {
        let iOS = {
          'device': 'ios',
          'originKey': this.$route.query.returnAppScheme
        }
        this.$store.commit('storeOriginApp', iOS)
      } else if (this.$route.query.returnAppPackage != null) {
        let android = {
          'device': 'android',
          'originKey': this.$route.query.returnAppPackage
        }
        this.$store.commit('storeOriginApp', android)
      } else if (originIos) {
        let iOS = {
          'device': 'ios',
          'originKey': originIos
        }
        this.$store.commit('storeOriginApp', iOS)
      } else if (originAndroid) {
        let android = {
          'device': 'android',
          'originKey': originAndroid
        }
        this.$store.commit('storeOriginApp', android)
      }
    },
    // Check if the session is validate
    isSessionValid (sessionId) {
      if (CONFIG.envDev) {
        this.informationsGathering(null)
      } else {
        if (sessionId) {
          axios.get(this.$store.state.endAPIRequest + 'nespresso/checkandinvalidate/' + sessionId)
            .then(response => {
              this.informationsGathering(response)
            })
            .catch(error => {
              router.push({
                name: 'ErrorManagement',
                params: { errorType: 'sessionError' }
              })
              console.log(error)
            })
        } else {
          if (!this.$store.state.sessionId) {
            router.push({
              name: 'ErrorManagement',
              params: { errorType: 'sessionError' }
            })
          }
        }
      }
    },
    // We keep in store all the informations needed for the rest of application lifecycle
    informationsGathering (response) {
      if (response) {
        let params = JSON.parse(response.data)
        this.DataReturned = params.customParams
        this.customerRelationId = params.customerRelationId
      } else {
        if (CONFIG.envDev) {
          this.DataReturned = CONFIG.DataReturned
        }
      }
      this.$store.commit('storeDataReturned', this.DataReturned)
      if (this.DataReturned.nespressoId) {
        this.receiveID = this.DataReturned.nespressoId
        this.getCustomerFirstName(this.receiveID)
      }
      this.$store.commit('storeSessionId', this.$route.query.sessionId)
      if (!this.$store.state.defaultPurchasePointId.includes(this.DataReturned.purchasePointId)) {
        this.isInPurchasePoint = true
      }
      if (this.DataReturned.guest && this.DataReturned.guest === 'yes') {
        this.displayGuest = true
      }
      this.$store.commit('storePurchasePointId', this.DataReturned.purchasePointId)
      this.$store.commit('storeUtmMedium', this.DataReturned.utm_medium)
      this.$store.commit('storeUtmSource', this.DataReturned.utm_source)
      this.$store.commit('storeUtmCampaign', this.DataReturned.utm_campaign)
      this.$store.commit('storeGuest', this.DataReturned.guest)
      this.$store.commit('storeReg', this.DataReturned.reg)
      this.$store.commit('storeOptIn', this.DataReturned['opt-in'])
      this.$store.commit('storeCustData', this.DataReturned['cust-data'])
      if (localStorage.customerRelationUuid) {
        this.autoLogin()
      }
    },
    // This is the login method fired on event login emited
    login (props) {
      this.LoginError = false
      this.loading = true
      let formData = {}
      if (props.nespressoId) {
        formData.memberId = props.nespressoId
      } else {
        formData.memberId = this.DataReturned.nespressoId
      }
      formData.postalCode = props.zip
      formData.customerRelationId = this.customerRelationId
      axios.post('V1/nespresso/customer/authorize',
        formData
      )
        .then(response => {
          if (!Array.isArray(response.data)) {
            this.$store.commit('storeUserDatas', response.data)
            this.$store.commit('storeCustomer', response.data)
            this.CurrentUserDatas = response.data
            if (!this.isInPurchasePoint) {
              let add = {
                email: response.data.email,
                gender: response.data.gender,
                firstName: response.data.addresses[0].firstname,
                city: response.data.addresses[0].city,
                lastName: response.data.addresses[0].lastname,
                zip: response.data.addresses[0].postcode,
                address: response.data.addresses[0].street[0],
                company: response.data.addresses[0].company
              }
              this.$store.commit('storeCustomerAddress', add)
            }
            let relationAttribute = response.data.custom_attributes.filter(d => d.attribute_code === 'customer_relation_id')[0]
            if (relationAttribute) {
              localStorage.customerRelationUuid = relationAttribute.value
            } else if (this.customerRelationId) {
              localStorage.customerRelationUuid = this.customerRelationId
            }
            this.pushGtmDataObject(this.CurrentUserDatas)
            this.routeNextPage()
          } else {
            this.LoginError = true
            this.loading = false
          }
        })
        .catch(error => {
          console.log(error)
          this.LoginError = true
          this.loading = false
        })
    },
    // Autologin
    autoLogin () {
      this.loading = true
      let formData = {}
      formData.sessionId = this.$route.query.sessionId
      formData.customerRelationId = localStorage.customerRelationUuid
      axios.post('V1/nespresso/customer/autoAuthorize', formData
      )
        .then(response => {
          if (!Array.isArray(response.data)) {
            this.$store.commit('storeUserDatas', response.data)
            this.$store.commit('storeCustomer', response.data)
            this.CurrentUserDatas = response.data
            if (!this.isInPurchasePoint) {
              let add = {
                email: response.data.email,
                gender: response.data.gender,
                firstName: response.data.addresses[0].firstname,
                city: response.data.addresses[0].city,
                lastName: response.data.addresses[0].lastname,
                zip: response.data.addresses[0].postcode,
                address: response.data.addresses[0].street[0]
              }
              this.$store.commit('storeCustomerAddress', add)
            }
            this.pushGtmDataObject(this.CurrentUserDatas)
            this.routeNextPage()
          } else {
            this.loading = false
          }
        })
        .catch(error => {
          this.loading = false
          console.log(error)
        })
    },
    // Here we are connected as PasserBy
    passerByConnection () {
      router.push({
        name: 'MultiProducts', query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign }
      })
    },
    // Here we are connected as Guest
    guestConnection () {
      if(this.$store.state.reg !== null || this.$store.state.custData !== null){
        router.push({
          name: 'Delivery',
          query: {
            utm_source: this.$store.state.utm_source,
            utm_medium: this.$store.state.utm_medium,
            utm_campaign: this.$store.state.utm_campaign
          }
        })
      }else {
        router.push({
          name: 'MultiProducts', query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign }
        })
      }
    },
    // Get the customer name from the nespresso Id
    getCustomerFirstName (nespressoId) {
      // TODO improve the way we get informations. Here we just need the customer firstname
      axios.get(this.$store.state.endAPIRequest + 'nespresso/customer/' + nespressoId)
        .then(response => {
          if (!Array.isArray(response.data)) {
            this.CurrentUserFirstName = response.data
            this.pushGtmDataObject(this.CurrentUserDatas)
          }
        })
    },
    // Google Tag Manager
    pushGtmDataObject (currentUserDatas) {
      window.gtmDataObject.push({
        'event': 'userLogin',
        'isEnvironmentProd': APICONFIG.isEnvProd,
        'pageName': 'Login | Twint',
        'pageType': 'login', // Other values include “Product List”, “Product Details”, “Checkout”, “User Account” etc.
        'pageCategory': '', // If available, please populate. Example ‘Coffee’ this is the value Nessoft nsp.page.pagessubsection1
        'pageSubCategory': '', // If available, please populate. this is the value Nessoft nsp.page.pagessubsection2
        'pageTechnology': CONFIG.gtm.pageTechnology, // Other values include “VertuoLine”, “Original Line”, “Pro”, or “None”
        'market': CONFIG.gtm.market,
        'version': '', // Twint application version
        'landscape': CONFIG.gtm.landscape,
        'segmentBusiness': CONFIG.gtm.segmentBusiness,
        'currency': CONFIG.gtm.currency,
        'eventCategory': 'User Engagement',
        'eventAction': 'User Login - Twint',
        'loginMethod': 'Phone', // is this required
        'clubMemberID': currentUserDatas === null || currentUserDatas.custom_attributes === undefined ? null : currentUserDatas.custom_attributes[2].value,
        'clubMemberStatus': null,
        'clubMemberLevel': null,
        'clubMemberLevelID': null,
        'clubMemberTitle': null,
        'clubMemberLoginStatus': currentUserDatas === null || currentUserDatas === undefined ? 'false' : 'true',
        'machineOwned': null,
        'preferredTechnology': null,
        'machineOwner': null
      })
    },
    routeNextPage () {
      if (!this.isInPurchasePoint) {
        router.push({
          name: 'Delivery',
          query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign }
        })
      } else {
        router.push({
          name: 'MultiProducts',
          query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign }
        })
      }
    }
  }
}
</script>

<style lang="scss">
  @import '../assets/scss/mixins';

  body.login {
    background: url('../static/img/bg.jpg') $black no-repeat center center;
    background-size: cover;
  }
</style>

<style lang="scss">
  .container {
    padding: 20px;
  }
  .text-error{
    color:#ff0000;
    text-align: center;
    margin: 2%;
  }
  .spinner{
    height: 40px;
    width: 40px;
    border-radius: 50%;
    margin: 5% 40% 0 40% !important;
    border-top: 3px solid #000000;
    border-right: 3px solid transparent;
    animation: spinner-data-v-04a0d67a 1000ms linear infinite;
  }

</style>
