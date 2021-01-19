<template>
  <div>
    <div>
      <div
        class="
            field-item"
        :class="{ 'field-item--valid' : validEmail(guestAddressForm.email) }"
      >
        <float-label>
          <input
            v-model="guestAddressForm.email"
            type="email"
            :placeholder="$t('address.email')"
            :class="{'emailError': (emailError)}"
            @keyup="inputChange"
            @focus="currentlyActiveField = 'none'"
          >
        </float-label>
      </div>
      <div class="error-field-item">
        <span
          v-show="emailError"
          class="emailErrorText"
        >{{ $t('errorManagement.accountExist') }}</span>
      </div>

      <div
        class="field-item"
        :class="{ 'field-item--valid' : ( guestAddressForm.gender !== null )}"
      >
        <float-label
          :label="$t('address.gender')"
          @focus="currentlyActiveField = 'none'"
        >
          <select
            v-model="guestAddressForm.gender"
            required
            @change="inputChange"
          >
            <option
              :value="null"
              disabled
              selected
            >
              {{ $t('address.gender') }}
            </option>
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
        :class="{ 'field-item--valid' : guestAddressForm.lastName }"
      >
        <float-label>
          <input
            v-model="guestAddressForm.lastName"
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
        :class="{ 'field-item--valid' : guestAddressForm.lastName }"
      >
        <float-label>
          <input
            v-model="guestAddressForm.lastName"
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
        :class="{ 'field-item--valid' : guestAddressForm.firstName }"
      >
        <float-label>
          <input
            v-model="guestAddressForm.firstName"
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
        :class="{ 'field-item--valid' : guestAddressForm.company }"
      >
        <float-label>
          <input
            v-model="guestAddressForm.company"
            type="text"
            :placeholder="$t('address.company')"
            @keyup="inputChange"
            @focus="currentlyActiveField = 'none'"
          >
        </float-label>
      </div>
      <div
        v-if="!custData"
        class="field-item"
        :class="{ 'field-item--valid' : guestAddressForm.zip }"
      >
        <float-label>
          <input
            id="zipCode"
            v-model="guestAddressForm.zip"
            class="autocomplete-select"
            type="number"
            :placeholder="$t('address.zip')"
            :autocomplete="off"
            @keyup="inputChange()"
            @change="inputChange()"
            @focus="currentlyActiveField = 'zip'"
            @zipSelected="zipSelected"
          >
        </float-label>
      </div>
      <div
        v-if="!custData"
        class="field-item"
        :class="{ 'field-item--valid' : guestAddressForm.city }"
      >
        <float-label>
          <input
            id="zipCity"
            v-model="guestAddressForm.city"
            type="text"
            :placeholder="$t('address.city')"
            :disabled="!tokenDenied"
            :autocomplete="off"
            @change="inputChange()"
            @focus="currentlyActiveField = 'none'"
            @citySelected="citySelected"
          >
        </float-label>
      </div>
      <div
        v-if="!custData"
        class="field-item"
        :class="{ 'field-item--valid' : guestAddressForm.address }"
      >
        <float-label>
          <input
            id="streetName"
            v-model="guestAddressForm.address"
            class="autocomplete-select"
            type="text"
            maxlength="35"
            :placeholder="$t('address.address')"
            :disabled="isDisabled"
            :autocomplete="off"
            @keyup="inputChange()"
            @change="inputChange()"
            @focus="currentlyActiveField = 'street'"
            @addressSelected="addressSelected"
          >
        </float-label>
      </div>
      <modal
        v-if="showErrorModal"
        v-show="showErrorModal"
        :error-data="showErrorMessage"
        @close="closeModal"
      />

      <div
        v-if="displayOptIn"
        :class="{ 'field-item--valid' : guestAddressForm.optIn }"
      >
        <label
          for="opt-in"
          class="lbl-checkboxes"
        >
          <input
            id="opt-in"
            v-model="guestAddressForm.optIn"
            class="checkboxes"
            type="checkbox"
            @change="inputChange"
          >
          {{ $t('delivery.optIn') }}
        </label>
      </div>
      <div
        v-if="displayRegistration"
        :class="{ 'field-item--valid' : guestAddressForm.createNewAccount }"
      >
        <label
          for="create-new-account"
          class="lbl-checkboxes"
        >
          <input
            id="create-new-account"
            v-model="guestAddressForm.createNewAccount"
            class="checkboxes"
            type="checkbox"
            :disabled="isRegistrationMandatory"
            @change="inputChange"
          >
          <span v-html="$t('delivery.createNewAccount')" />
        </label>
      </div>
    </div>
  </div>
</template>

<script>

import axios from '../axios'
import autompleteHelper from '../helper/autocomplete'
import modal from './ErrorModal'

export default {
  name: 'GuestAddress',
  components: {
    modal
  },
  props: {
    emailError: {
      type: Boolean,
      default: false
    },
    showErrorModal: {
      type: Boolean,
      default: false
    },
    showErrorMessage: {
      type: String,
      default: null
    }
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
      guestAddressForm: {
        email: null,
        gender: null,
        lastName: null,
        firstName: null,
        address: null,
        zip: null,
        city: null,
        company: null,
        isAddressChanged: false,
        optIn: true,
        createNewAccount: false
      },
      statusBtn: false,
      returnError: false,
      isCompany: false,
      disableStreet: true,
      tokenDenied: false,
      displayOptIn: true,
      displayRegistration: false,
      isRegistrationMandatory: false,
      custData: null,
      off: 'nope',
      showModal: false
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
  beforeDestroy () {
    location.reload()
  },
  created () {
    if (this.guestAddressForm.company) {
      this.isCompany = true
    }
    if (this.guestAddressForm.zip && this.guestAddressForm.zip.length >= 4) {
      this.disableStreet = false
    }
    this.displayInformations()
  },
  methods: {
    closeModal () {
      this.$emit('closeErrorModal')
    },
    displayInformations () {
      // Check if we should display optin field
      this.displayOptIn = this.$store.state.optIn && (this.$store.state.optIn === 'yes' && this.$store.state.reg === 'yes')
      this.guestAddressForm.optIn = this.displayOptIn && this.$store.state.reg === 'yes'
      this.custData = this.$store.state.custData === 'yes'
      if (this.$store.state.optIn && this.$store.state.optIn === 'disable') {
        this.displayOptIn = false
      }
      // check if we should display registration checkbox and if is mandatory
      if (this.$store.state.reg && this.$store.state.reg === 'yes' && this.$store.state.guest === 'yes') {
        this.guestAddressForm.createNewAccount = true
        this.guestAddressForm.optIn = true
        this.displayRegistration = true
        this.displayOptIn = true
      } else if (this.$store.state.reg && this.$store.state.reg === 'mandatory') {
        this.displayRegistration = true
        this.guestAddressForm.createNewAccount = true
        this.isRegistrationMandatory = true
      }
      if (this.$store.state.reg && this.$store.state.reg === 'yes' && this.$store.state.optIn && this.$store.state.optIn === 'disabled') {
        this.custData = false
        this.guestAddressForm.createNewAccount = false
        this.guestAddressForm.optIn = false
        this.displayRegistration = true
        this.displayOptIn = true
      }
    },
    changeGender (newValue) {
      this.guestAddressForm.gender = newValue
    },
    zipSelected (event) {
      this.guestAddressForm.zip = event.detail
    },
    citySelected (event) {
      this.guestAddressForm.city = event.detail
    },
    addressSelected (event) {
      this.guestAddressForm.address = event.detail
    },
    inputChange () {
      if (this.guestAddressForm.zip && this.guestAddressForm.zip.length >= 4) {
        this.disableStreet = false
      } else {
        this.disableStreet = true
      }
      this.$store.commit('storeCustomerAddress', this.guestAddressForm)
      if (!this.TokenReturned) {
        this.getAccessToken()
      }
      if (!this.tokenDenied && this.TokenReturned) {
        if (this.currentlyActiveField === 'street' && this.guestAddressForm.address.length > 0) {
          let selector = 'streetName'
          let zip = this.guestAddressForm.zip
          axios.get(this.$store.state.config.swisspost + 'streets?name=' + this.guestAddressForm.address + '&zip=' + this.guestAddressForm.zip, {
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

        if (this.currentlyActiveField === 'zip' && this.guestAddressForm.zip.length > 0) {
          let selector = 'zipCode'
          axios.get(this.$store.state.config.swisspost + 'zips?zipCity=' + this.guestAddressForm.zip + '&type=DOMICILE', {
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
      if (this.guestAddressForm.email) {
        this.guestAddressForm.email.trim()
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

  .lbl-checkboxes {
    display: block;
    font-size: .8rem;
    padding-top: 10px;
    padding-left: 27px;
    text-indent: -27px;
  }
  .checkboxes {
    width: 25px;
    height: 25px;
    padding: 0;
    margin:0;
    vertical-align: bottom;
    position: relative;
    top: 10px;
    *overflow: hidden;
  }
  select:required:invalid {
    @include fontsize(16px);
    font-weight: 500;
    letter-spacing: 1px;
    color: rgba($black, 0.3);
  }
  .emailError{
    border: 1px solid #f00 !important;
  }
  .emailErrorText{
    color: #f00 !important;
  }
  .error-field-item{
    margin: .65% auto;
  }

</style>
