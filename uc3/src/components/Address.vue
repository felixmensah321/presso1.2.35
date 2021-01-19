<template>
  <div>
    <div>
      <div
        class="
            field-item"
        :class="{ 'field-item--valid' : validEmail(addressForm.email) }"
      >
        <float-label>
          <input
            v-model="addressForm.email"
            type="email"
            :placeholder="$t('address.email')"
            @keyup="inputChange"
            @focus="currentlyActiveField = 'none'"
          >
        </float-label>
      </div>
      <div
        class="field-item"
        :class="{ 'field-item--valid' : ( addressForm.gender !== null) }"
      >
        <float-label
          :label="$t('address.gender')"
          @focus="currentlyActiveField = 'none'"
        >
          <select
            v-model="addressForm.gender"
          >
            <option :value="0">
              {{ $t('gender.empty') }}
            </option>
            <option :value="1">
              {{ $t('gender.male') }}
            </option>
            <option :value="2">
              {{ $t('gender.female') }}
            </option>
            <option :value="3">
              {{ $t('gender.unspecified') }}
            </option>
          </select>
        </float-label>
      </div>
      <div
        v-if="!isCompany"
        class="
            field-item"
        :class="{ 'field-item--valid' : addressForm.lastName }"
      >
        <float-label>
          <input
            v-model="addressForm.lastName"
            type="text"
            :placeholder="$t('address.lastName')"
            @keyup="inputChange"
            @focus="currentlyActiveField = 'none'"
          >
        </float-label>
      </div>
      <div
        v-else
        class="
            field-item"
        :class="{ 'field-item--valid' : addressForm.lastName }"
      >
        <float-label>
          <input
            v-model="addressForm.lastName"
            type="text"
            :placeholder="$t('address.contactName')"
            @keyup="inputChange"
            @focus="currentlyActiveField = 'none'"
          >
        </float-label>
      </div>
      <div
        v-if="!isCompany"
        class="field-item"
        :class="{ 'field-item--valid' : addressForm.firstName }"
      >
        <float-label>
          <input
            v-model="addressForm.firstName"
            type="text"
            :placeholder="$t('address.firstName')"
            @keyup="inputChange"
            @focus="currentlyActiveField = 'none'"
          >
        </float-label>
      </div>
      <div
        v-else
        class="field-item"
        :class="{ 'field-item--valid' : addressForm.company }"
      >
        <float-label>
          <input
            v-model="addressForm.company"
            type="text"
            :placeholder="$t('address.company')"
            @keyup="inputChange"
            @focus="currentlyActiveField = 'none'"
          >
        </float-label>
      </div>
      <div
        class="field-item"
        :class="{ 'field-item--valid' : addressForm.zip }"
      >
        <float-label>
          <input
            id="zipCode"
            v-model="addressForm.zip"
            class="autocomplete"
            type="number"
            :placeholder="$t('address.zip')"
            @keyup="inputChange()"
            @change="inputChange()"
            @focus="currentlyActiveField = 'zip'"
            @zipSelected="zipSelected"
          >
        </float-label>
      </div>
      <div
        class="field-item"
        :class="{ 'field-item--valid' : addressForm.city }"
      >
        <float-label>
          <input
            id="zipCity"
            v-model="addressForm.city"
            type="text"
            :placeholder="$t('address.city')"
            :disabled="!tokenDenied"
            @focus="currentlyActiveField = 'none'"
            @citySelected="citySelected"
          >
        </float-label>
      </div>
      <div
        class="field-item"
        :class="{ 'field-item--valid' : addressForm.address }"
      >
        <float-label>
          <input
            id="streetName"
            v-model="addressForm.address"
            class="autocomplete-select"
            type="text"
            maxlength="35"
            :placeholder="$t('address.address')"
            :disabled="isDisabled"
            @keyup="inputChange()"
            @change="inputChange()"
            @focus="currentlyActiveField = 'street'"
            @addressSelected="addressSelected"
          >
        </float-label>
      </div>
    </div>
  </div>
</template>

<script>

import axios from '../axios'
import autompleteHelper from '../helper/autocomplete'

export default {
  name: 'Address',
  components: {
  },
  data () {
    return {
      LoginHeader: {
        layout: 'black',
        link: false,
        horizontal: false
      },
      empty: '',
      CurrentUserDatas: null,
      CurrentURLDatas: this.$route.query,
      CurrentProductDatas: null,
      receiveID: false,
      DataReturned: null,
      TokenReturned: null,
      currentlyActiveField: null,
      addressForm: {
        email: null,
        gender: null,
        lastName: null,
        firstName: null,
        address: null,
        zip: null,
        city: null,
        company: null,
        isAddressChanged: false
      },
      statusBtn: false,
      returnError: false,
      isCompany: false,
      disableStreet: false,
      tokenDenied: true
    }
  },
  computed: {
    isDisabled () {
      return this.disableStreet
    }
  },
  metaInfo () {
    return {
      title: this.$i18n.t('meta.login'),
      titleTemplate: '%s | Nespresso'
    }
  },
  created () {
    this.addressForm = this.$store.state.customerAddress
    if (!this.addressForm.gender || this.addressForm.gender === undefined) {
      this.addressForm.gender = 0
    }
    if (this.addressForm.company) {
      this.isCompany = true
    }
    if (this.addressForm.zip && this.addressForm.zip.length >= 4) {
      this.disableStreet = false
    }
  },
  methods: {
    changeGender (newValue) {
      this.addressForm.gender = newValue
    },
    zipSelected (event) {
      this.addressForm.zip = event.detail
    },
    citySelected (event) {
      this.addressForm.city = event.detail
    },
    addressSelected (event) {
      this.addressForm.address = event.detail
    },
    inputChange () {
      if (this.addressForm.zip && this.addressForm.zip.length >= 4) {
        this.disableStreet = false
      } else {
        this.disableStreet = true
      }
      this.$store.commit('storeCustomerAddress', this.addressForm)
      if (!this.TokenReturned) {
        this.getAccessToken()
      }
      if (this.TokenReturned) {
        if (this.currentlyActiveField === 'street' && this.addressForm.address.length > 0) {
          let selector = 'streetName'
          let zip = this.addressForm.zip
          axios.get(this.$store.state.config.swisspost + 'streets?name=' + this.addressForm.address + '&zip=' + this.addressForm.zip, {
            headers: { 'Authorization': 'Bearer ' + this.TokenReturned.accessToken }
          }).then(response => {
            let streetData = response.data
            const sData = streetData.streets.map(item => {
              const rObj = {}
              rObj[item] = item
              return rObj
            })
            if (zip) {
              autompleteHelper.autocomplete(selector, sData, this.currentlyActiveField)
            }
          })
            .catch(error => {
              this.getAccessToken()
              console.log(error)
            })
        }

        if (this.currentlyActiveField === 'zip' && this.addressForm.zip.length > 0) {
          let selector = 'zipCode'
          axios.get(this.$store.state.config.swisspost + 'zips?zipCity=' + this.addressForm.zip + '&type=DOMICILE', {
            headers: { 'Authorization': 'Bearer ' + this.TokenReturned.accessToken }
          }).then(response => {
            let zipCityData = response.data
            const zData = zipCityData.zips.map(obj => {
              const rObj = {}
              rObj[obj.zip] = obj.zip + ' ' + obj.city18
              return rObj
            })
            autompleteHelper.autocomplete(selector, zData, this.currentlyActiveField)
          })
            .catch(error => {
              this.getAccessToken()
              console.log(error)
            })
        }
      }
    },
    getAccessToken: function () {
      axios.get(this.$store.state.endAPIRequest + 'nespresso/av')
        .then(response => {
          this.TokenReturned = JSON.parse(response.data)
          this.inputChange()
        })
        .catch(error => {
          this.tokenDenied = true
          console.log(error)
        })
    },
    validEmail: function (email) {
      if (this.addressForm.email) {
        this.addressForm.email.trim()
      }
      var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
      return re.test(email)
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
  .instructions {
    margin-top: 80px;
    text-align: center;
    @include fontsize(18px);
    font-weight: 300;
    letter-spacing: 1px;
    color: $black;
  }

  .field-item {
    display: flex;
    min-height: 20px;
    flex-direction: column;
    border-radius: 3px;
    border: 1px solid rgba($black, 0.15);
    position: relative;

    input {
      padding-left: 5px;
      -webkit-appearance: none;
      background: transparent;
      width: 100%;
      border: 0;
      padding-top: 15px;
      padding-bottom: 5px;
      @include fontsize(16px);
      font-weight: 500;
      letter-spacing: 1px;
      color: $grey;

      @include placeholder () { color: rgba($black, 0.5); }

      &:focus { outline: 0; }
    }

    select {
      padding-left: 5px;
      -webkit-appearance: none;
      background: transparent;
      width: 100%;
      border: 0;
      padding-top: 15px;
      padding-bottom: 5px;
      @include fontsize(16px);
      font-weight: 500;
      letter-spacing: 1px;

      @include placeholder () { color: rgba($black, 0.5); }

      &:focus { outline: 0; }
    }

    .vfl-label-on-input {
      top: 0;
      padding-left: 5px;
      color: $gold;
    }

    .vfl-label-on-focus {
    color: $gold;
    }

    .vfl-label + input {
      padding-left: 5px;
      font-size: 100%;
      transition: border 0.2s;
    }

    &.field-item--valid {
      &::after {
        content: "\0020";
        @include abs(null, 10px, 20px, null);
        top: 0px;
        transform: translateY(50%);
        display: block;
        width: 22px;
        height: 22px;
        background: url("../static/img/valid.svg") $white no-repeat center center;
        background-size: 110%;
        border-radius: 100%;
      }
    }

    &.field-item--error {
      &::after {
        content: "\0020";
        @include abs(null, 10px, 20px, null);
        transform: translateY(50%);
        display: block;
        width: 22px;
        height: 22px;
        background: url("../static/img/error.svg") $white no-repeat center center;
        background-size: 110%;
        border-radius: 100%;
      }
    }

    & + .field-item { margin-top: 8px; }
    & + .btn { margin-top: 25px; }
  }

  .autocomplete-select {
    &:focus{
      border: solid 1px black !important;
    }
  }

  .autocomplete-item {
    min-height: 30px;
    border: solid 0.1rem #d5d5d5;
    border-top: none;
    padding: 5px;
  }

  /*the container must be positioned relative:*/
  .autocomplete {
    position: relative;
    display: inline-block;
  }

  .autocomplete-items {
    position: absolute;
    border: 1px solid #d4d4d4;
    border-bottom: none;
    border-top: none;
    z-index: 99;
    /*position the autocomplete items to be the same width as the container:*/
    top: 100%;
    left: 0;
    right: 0;
  }

  .autocomplete-items div {
    padding: 10px;
    cursor: pointer;
    background-color: #fff;
    border-bottom: 1px solid #d4d4d4;
  }

  /*when hovering an item:*/
  .autocomplete-items div:hover {
    background-color: #e9e9e9;
  }

  /*when navigating through the items using the arrow keys:*/
  .autocomplete-active {
    background-color: DodgerBlue !important;
    color: #ffffff;
  }
</style>
