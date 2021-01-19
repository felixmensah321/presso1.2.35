<template>
  <div class="login">
    <HeaderPart
      :header-data="LoginHeader"
    />
    <LoginAutofill
      v-if="receiveID && CurrentUserDatas && (isSku || (categoriesArchitecture && categoriesToFill && (categoriesToFill === categoriesFilled)))"
      :user-data="CurrentUserDatas"
      :url-data="CurrentURLDatas"
    />
    <LoginForm
      v-if="!receiveID && (isSku || (categoriesArchitecture && categoriesToFill && (categoriesToFill === categoriesFilled)))"
      :url-data="CurrentURLDatas"
    />
    <LoginGuest
      v-if="!receiveID && (isSku || (categoriesArchitecture && categoriesToFill && (categoriesToFill === categoriesFilled))) && isInPurchasePoint"
      :url-data="CurrentURLDatas"
    />
  </div>
</template>

<script>
import axios from 'axios'
import router from '../router'
import HeaderPart from 'layouts/HeaderPart'
import LoginAutofill from 'components/LoginAutofill'
import LoginForm from 'components/LoginForm'
import LoginGuest from 'components/LoginGuest'
import CONFIG from '../static/general/config.json'
import APICONFIG from '../static/api/config.json'

export default {
  name: 'Login',
  components: {
    HeaderPart,
    LoginForm,
    LoginGuest,
    LoginAutofill
  },
  data () {
    return {
      LoginHeader: {
        layout: 'black',
        link: false,
        horizontal: false
      },
      isSku: false,
      CurrentUserDatas: null,
      CurrentURLDatas: this.$route.query,
      AllProducts: [],
      CategoriesDatas: null,
      receiveID: false,
      DataReturned: null,
      isInPurchasePoint: false,
      categoriesArchitecture: null,
      currentStoreCode: null,
      categoriesToFill: 0,
      categoriesFilled: 0
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
    let browserLanguage = navigator.language
    browserLanguage = browserLanguage.substr(0, 2)
    let storeTab = CONFIG.storeMapping
    if (storeTab.hasOwnProperty(browserLanguage)) {
      this.currentStoreCode = storeTab[browserLanguage]
    } else {
      this.currentStoreCode = storeTab['en']
    }
    this.$store.commit('storeEndAPIRequest', this.currentStoreCode)
    let urlString = window.location.href
    let url = new URL(urlString)
    let originIos = url.searchParams.get('returnAppScheme')
    let originAndroid = url.searchParams.get('returnAppPackage')

    let sessionIdFromTwint = this.$route.query.sessionId

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
    if (CONFIG.envDev) {
      this.informationsGathering(null)
    } else {
      if (this.$route.query.sessionId) {
        axios.get(this.$store.state.config.path + this.$store.state.endAPIRequest + 'nespresso/checkandinvalidate/' + sessionIdFromTwint, {
          headers: { 'Authorization': 'Bearer ' + this.$store.state.config.bearer }
        })
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
        router.push({
          name: 'ErrorManagement',
          params: { errorType: 'sessionError' }
        })
      }
    }
  },
  methods: {
    getCategoryArchitecture (categoryId, level) {
      axios.get(this.$store.state.config.path + this.$store.state.endAPIRequest + 'categories/' + categoryId, {
        headers: { 'Authorization': 'Bearer ' + this.$store.state.config.bearer }
      }).then(response => {
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
      })
        .catch(error => {
          console.log(error)
        })
    },
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
    informationsGathering (response) {
      if (response) {
        this.DataReturned = JSON.parse(response.data)
      } else {
        if (CONFIG.envDev) {
          this.DataReturned = CONFIG.DataReturned
        }
      }
      if (this.DataReturned.sku) {
        this.$store.commit('storeisSku', true)
        this.getSKU(this.DataReturned.sku)
      } else if (this.DataReturned.categoryId) {
        this.getCategoryArchitecture(this.DataReturned.categoryId, 1)
      } else {
        this.getCategoryArchitecture(52, 1)
        this.getAllProducts()
      }
      if (this.DataReturned.nespressoId) {
        this.getUser(this.DataReturned.nespressoId)
      }
      this.$store.commit('storeSessionId', this.$route.query.sessionId)
      if (!this.$store.state.defaultPurchasePointId.includes(this.DataReturned.purchasePointId)) {
        this.isInPurchasePoint = true
      }
      this.$store.commit('storePurchasePointId', this.DataReturned.purchasePointId)
      this.$store.commit('storeUtmMedium', this.DataReturned.utm_medium)
      this.$store.commit('storeUtmSource', this.DataReturned.utm_source)
      this.$store.commit('storeUtmCampaign', this.DataReturned.utm_campaign)
    },
    getSKU (sku) {
      axios.get(this.$store.state.config.path + this.$store.state.endAPIRequest + 'products/' + sku, {
        headers: { 'Authorization': 'Bearer ' + this.$store.state.config.bearer }
      })
        .then(response => {
          if (response.data.status !== 1) {
            router.push({
              name: 'NotFound',
              query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign }
            })
          }
          this.AllProducts.push(response.data)
          this.isSku = true
          this.$store.commit('storeAllProducts', this.AllProducts)
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
      axios.get(this.$store.state.config.path + this.$store.state.endAPIRequest + 'products?searchCriteria[filter_groups][0][filters][0][field]=category_id&searchCriteria[filter_groups][0][filters][0][value]=' + categoryId + '&searchCriteria[filter_groups][0][filters][0][condition_type]=eq&searchCriteria[filter_groups][0][filters][1][field]=status&searchCriteria[filter_groups][0][filters][1][value]=1&searchCriteria[filter_groups][0][filters][1][condition_type]=eq&searchCriteria[sortOrders][0][field]=position&searchCriteria[sortOrders][0][direction]=DESC', {
        headers: { 'Authorization': 'Bearer ' + this.$store.state.config.bearer }
      })
        .then(response => {
          this.assignProductToCategory(categoryId, response.data.items)
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
      axios.get(this.$store.state.config.path + this.$store.state.endAPIRequest + 'products?searchCriteria[pageSize]=0&searchCriteria[filter_groups][0][filters][0][field]=status&searchCriteria[filter_groups][0][filters][0][value]=1&searchCriteria[filter_groups][0][filters][0][condition_type]=eq', {
        headers: { 'Authorization': 'Bearer ' + this.$store.state.config.bearer }
      })
        .then(response => {
          this.AllProducts = response.data.items
          this.$store.commit('storeAllProducts', this.AllProducts)
        })
        .catch(error => {
          router.push({
            name: 'NotFound',
            query: { utm_source: this.$store.state.utm_source, utm_medium: this.$store.state.utm_medium, utm_campaign: this.$store.state.utm_campaign }
          })
          console.log(error)
        })
    },
    getUser (user) {
      this.receiveID = this.DataReturned.nespressoId
      axios.get(this.$store.state.config.path + this.$store.state.endAPIRequest + 'nespresso/customer/' + user, {
        headers: { 'Authorization': 'Bearer ' + this.$store.state.config.bearer }
      })
        .then(response => {
          if (!Array.isArray(response.data)) {
            this.$store.commit('storeUserDatas', response.data)
            this.$store.commit('storeCustomer', response.data)
            this.CurrentUserDatas = response.data
            this.pushGtmDataObject(this.CurrentUserDatas)
          }
        })
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
</style>
