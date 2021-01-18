import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    customers: {},
    allProducts: [],
    orders: null,
    purchases: [],
    sessionId: null,
    config: {},
    defaultPurchasePointId: [],
    plpDatapushed: {},
    originApp: {},
    customerAddress: {},
    purchasePointId: null,
    totalPrice: null,
    totalQty: 0,
    utm_source: null,
    utm_campaign: null,
    utm_medium: null,
    userDatas: {},
    completedOrder: {},
    categoryArchitecture: {},
    isSku: false,
    endAPIRequest: null,
    dataReturned: {},
    guest: null,
    reg: null,
    optIn: null,
    custData: null
  },
  getters: {
    totalPrice (state) {
      return state.totalPrice
    }
  },
  mutations: {
    storeConfig (state, config) {
      Object.assign(state.config, config)
    },
    storeSessionId (state, sessionId) {
      state.sessionId = sessionId
    },
    storePurchasePointId (state, purchasePointId) {
      state.purchasePointId = purchasePointId
    },
    storeDefaultPurchasePointId (state, defaultPurchasePointId) {
      state.defaultPurchasePointId = defaultPurchasePointId
    },
    storeOriginApp (state, originApp) {
      Object.assign(state.originApp, originApp)
    },
    storeCustomer (state, customer) {
      Object.assign(state.customers, customer)
    },
    storeCustomerAddress (state, customerAddress) {
      Object.assign(state.customerAddress, customerAddress)
    },
    storeAllProducts (state, allProduct) {
      Object.assign(state.allProducts, allProduct)
    },
    storeOrder (state, order) {
      state.orders = order
    },
    storePurchases (state, purchases) {
      state.purchases = purchases
    },
    storeTotalPrice (state, totalPrice) {
      state.totalPrice = totalPrice
    },
    storeTotalQty (state, totalQty) {
      state.totalQty = totalQty
    },
    storeUtmMedium (state, utmMedium) {
      state.utm_medium = utmMedium
    },
    storeUtmSource (state, utmSource) {
      state.utm_source = utmSource
    },
    storeUtmCampaign (state, utmCampaign) {
      state.utm_campaign = utmCampaign
    },
    storeUserDatas (state, datas) {
      state.userDatas = datas
    },
    storeCompletedOrder (state, order) {
      state.completedOrder = order
    },
    storeCategoryArchitecture (state, categoryArchitecture) {
      state.categoryArchitecture = categoryArchitecture
    },
    storeisSku (state, isSku) {
      state.isSku = isSku
    },
    storeEndAPIRequest (state, store) {
      state.endAPIRequest = store + '/V1/'
    },
    storePlpDatapushed (state, datas) {
      state.plpDatapushed = datas
    },
    storeDataReturned (state, datas) {
      state.dataReturned = datas
    },
    storeGuest (state, guest) {
      state.guest = guest
    },
    storeReg (state, reg) {
      state.reg = reg
    },
    storeOptIn (state, optin) {
      state.optIn = optin
    },
    storeCustData (state, custData) {
      state.custData = custData
    }
  }
})
