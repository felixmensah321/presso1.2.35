<template>
  <div>
    <h1>
      {{ $t('login.hello') }}<br>
      {{ userData.firstname }}
    </h1>
    <p class="instructions">
      {{ $t('login.autofill') }}
    </p>
    <form
      action="#"
      method="post"
      @submit="submitHandler"
      @keydown.enter.prevent
    >
      <div
        class="field-item"
        :class="{ 'field-item--valid' : userZipCode && !returnError, 'field-item--error' : returnError }"
      >
        <float-label>
          <input
            type="number"
            :placeholder="$t('login.zip')"
            @input="changeNID($event.target.value)"
          >
        </float-label>
      </div>
      <button
        class="btn btn--primary"
        type="submit"
        :class="{ 'btn--inactive' : !statusButton }"
      >
        {{ $t('login.btn') }}
      </button>
    </form>
  </div>
</template>

<script>
import router from '../router'

export default {
  name: 'LoginAutofill',
  components: {},
  props: {
    userData: { type: Object, default: null },
    urlData: { type: Object, default: null }
  },
  data () {
    return {
      userZipCode: null,
      userNespressoID: null,
      statusBtn: false,
      returnError: false,
      CurrentUserDatas: null,
      userFirstName: null
    }
  },
  computed: {
    statusButton: function () {
      return this.userZipCode && !this.returnError
    }
  },
  watch: {
    'userData': 'fetchData',
    'urlData': 'fetchData'
  },
  methods: {
    fetchData () {
      this.userNespressoID = this.userData.custom_attributes[0].value
      this.userFirstName = this.userData.firstname
    },
    submitHandler () {
      event.preventDefault()
      let purchasePointId = this.$store.state.purchasePointId
      this.$store.commit('storeCustomer', this.CurrentUserDatas)
      if (this.urlData.cID) {
        if (this.$store.state.defaultPurchasePointId.includes(purchasePointId)) {
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
      } else if (!this.urlData.cID && this.userNespressoID) {
        if (this.$store.state.defaultPurchasePointId.includes(purchasePointId)) {
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
    },
    validateZip (val, zip) {
      return val.length > 4 ? zip : zip.substring(0, 4)
    },
    changeNID (val) {
      let purchasePointId = this.$store.state.purchasePointId
      this.userNespressoID = this.userData.custom_attributes[2].value
      if (this.timeout) clearTimeout(this.timeout)
      let zip = this.validateZip(val, this.userData.addresses[0].postcode)
      if (val === zip) {
        if (this.$store.state.defaultPurchasePointId.includes(purchasePointId)) {
          let add = {
            email: this.userData.email,
            gender: this.userData.gender,
            firstName: this.userData.addresses[0].firstname,
            city: this.userData.addresses[0].city,
            lastName: this.userData.addresses[0].lastname,
            zip: this.userData.addresses[0].postcode,
            address: this.userData.addresses[0].street[0]
          }
          this.$store.commit('storeCustomerAddress', add)
        }
        this.userZipCode = val
        this.returnError = false
      } else {
        this.userZipCode = val
        this.returnError = true
      }
    }
  }
}
</script>

<style lang="scss" scoped>
  @import '../assets/scss/mixins';

  .instructions {
      margin: 40px 0 40px 0;
    text-align: center;
    font-weight: 300;
    color: $grey;
  }

  .field-item {
    display: flex;
    flex-direction: column;
    border-radius: 3px;
    border: 1px solid rgba($black, 0.15);
    position: relative;

    input {
      padding-left: 5px;
      -webkit-appearance: none;
      background: transparent;
      border: 0;
      padding-top: 15px;
      padding-bottom: 5px;
      @include fontsize(16px);
      font-weight: 500;
      letter-spacing: 1px;
      color: $grey;
      @include placeholder () { color: rgba($black, 0.5);
      }

      &:focus {
        outline: 0;
      }
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
      border: 0;
      border-bottom: 0;
      transition: border 0.2s;
    }

    .vfl-label-on-focus + input {
      border-bottom: 0;
    }

    &.field-item--valid {
      &::after {
        content: "\0020";
        @include abs(null, 10px, 20px, null);
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

    & + .field-item {
      margin-top: 8px;
    }
    & + .btn {
      margin-top: 25px;
    }
  }

  .instructions {
    margin-top: 80px;
    text-align: center;
    @include fontsize(18px);
    font-weight: 300;
    letter-spacing: 1px;
    color: $black;
  }

  h1 {
    margin: 80px 0 25px;
    @include fontsize(30px);
    font-weight: 300;
    letter-spacing: 6px;
    line-height: 40px;
    text-align: center;
    text-transform: uppercase;
    color: $black;

    & + .instructions {
      margin-top: 0;
      padding: 0 30px;
    }
  }

</style>
