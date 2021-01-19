<template>
  <transition name="modal-fade">
    <div class="modal-backdrop">
      <div
        id="QuantitySelector__wrapper"
        class="QuantitySelector"
        role="dialog"
        aria-labelledby="QuantitySelector__title"
        aria-describedby="QuantitySelector__description"
      >
        <span
          id="QuantitySelector__title"
          class="VisuallyHidden"
        >
          Choix de la quantité
        </span>
        <span
          id="QuantitySelector__description"
          class="VisuallyHidden"
        >
          Choisir une quantité prédéfinie ci-dessous
        </span>
        <div class="QuantitySelector__container">
          <div class="QuantitySelector__popin">
            <div v-if="!hasError">
              <ul class="PredefinedQuantityList">
                <li
                  v-for="item in listArray"
                  :key="item"
                  class="PredefinedQuantityList__quantity"
                >
                  <button
                    class="PredefinedQuantityList__quantity-button"
                    @click="addProductToCart(item * multiplicator)"
                  >
                    <span class="VisuallyHidden">
                      Mettre à jour {{ item * multiplicator }}
                    </span>
                    <span
                      aria-hidden="true"
                      class="notranslate"
                    >
                      {{ item * multiplicator }}
                    </span>
                  </button>
                </li>
              </ul>
            </div>
            <div v-else>
              <div class="CustomQuantityError__error-message">
                {{ $t('popIn.errorText', {multi: multiplicator}) }}
              </div>
              <div class="CustomQuantityError__rounding-message">
                {{ $t('popIn.errorReplace', {multi: manualQty}) }}
              </div>
            </div>
            <div
              class="QuantitySelectorCustomField__container"
            >
              <div class="field-item">
                <float-label>
                  <input
                    v-model="manualQty"
                    type="number"
                    :placeholder="$t('popIn.chooseQty')"
                  >
                </float-label>
              </div>
              <button
                class="QuantitySelectorCustomField__button-ok"
                @click="qtySubmit"
              >
                OK
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>

export default {
  name: 'Modal',
  props: {
    productData: { type: Object, default: null }
  },
  data () {
    return {
      listArray: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
      multiplicator: null,
      manualQty: '',
      hasError: false
    }
  },
  created () {
    let nbCaps = this.productData.custom_attributes.filter(caps => caps.attribute_code === 'nb_capsules')[0]
    let multiple = this.productData.custom_attributes.filter(caps => caps.attribute_code === 'multiple_product')[0]
    if (nbCaps) {
      this.multiplicator = nbCaps.value
    } else if (multiple) {
      this.multiplicator = multiple.value
    } else {
      this.multiplicator = 1
    }
  },
  methods: {
    close () {
      this.$emit('close')
    },
    qtySubmit () {
      if (Number(this.manualQty) % this.multiplicator !== 0) {
        this.hasError = true
        this.manualQty = (Number(this.manualQty) - (Number(this.manualQty) % this.multiplicator) + Number(this.multiplicator))
      } else {
        this.hasError = false
        this.addProductToCart(this.manualQty)
      }
    },
    addProductToCart (qty) {
      this.addOrUpdatePurchases(qty)
      this.$emit('close', qty)
    },
    removeProduct () {
      let idx = 0
      let arr = this.$store.state.purchases
      for (let i = 0; i < arr.length; i++) {
        if (arr[i].sku === this.productData.sku) {
          idx = i
          break
        }
      }
      arr.splice(idx, 1)
      this.calculatePrice(arr)
    },
    calculatePrice (arr) {
      let totalPrice = 0
      let totalQty = 0
      arr.forEach(product => {
        totalPrice += (product.price * product.quantity)
        totalQty += Number(product.quantity)
      })
      this.$store.commit('storeTotalPrice', totalPrice)
      this.$store.commit('storeTotalQty', Number(totalQty))
    },
    addOrUpdatePurchases (qty) {
      let arr = this.$store.state.purchases
      let purchaseObj = { allDatas: this.productData, sku: this.productData.sku, quantity: qty, price: this.productData.price }
      if (qty > 0) {
        if (!this.productInArray(arr, this.productData.sku)) {
          arr.push(purchaseObj)
        } else {
          arr.filter(
            product => {
              if (product.sku === this.productData.sku) {
                product.quantity = qty
                return true
              }
              return false
            }
          )
        }
        this.calculatePrice(arr)
      } else {
        this.removeProduct()
      }
      this.$store.commit('storePurchases', arr)
    },
    productInArray (arr, value) {
      return arr.some(function (el) {
        return el.sku === value
      })
    }
  }
}
</script>

<style lang="scss">
  @import '../assets/scss/mixins';
  .modal-backdrop {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: rgba(0, 0, 0, 0.3);
    display: flex;
    justify-content: center;
    align-items: center;
  }

   .modal-fade-enter,
  .modal-fade-leave-active {
    opacity: 0;
  }

  .modal-fade-enter-active,
  .modal-fade-leave-active {
    transition: opacity .5s ease
  }

.VisuallyHidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0,0,0,0);
    border: 0;
}

  .CustomQuantityError__error-message, .CustomQuantityError__rounding-message {
    text-align: center;
    padding: 8px;
    line-height: 1.25rem;
  }

  .CustomQuantityError__rounding-message {
    font-weight: 700;
  }

  .QuantitySelector__popin {
    padding: .625rem;
    width: 13.25rem;
    font-size: .75rem;
    background-color: #f9f9f9;
    color: #2e2e2e;
    border: 1px solid #d5d5d5;
    border-radius: 2px;
}
.PredefinedQuantityList {
    display: flex;
    flex-flow: row wrap;
}
.PredefinedQuantityList {
    list-style: none;
    margin: 0;
    padding: 0;
}

.PredefinedQuantityList__quantity:first-of-type, .PredefinedQuantityList__quantity:nth-of-type(5n)+.PredefinedQuantityList__quantity {
    border-left: 0 none;
}

.PredefinedQuantityList__quantity {
    width: 20%;
    height: 2.1875rem;
    text-align: center;
    cursor: pointer;
    letter-spacing: .1em;
    border-left: .0625rem solid #d5d5d5;
    padding: 4px;
}

.PredefinedQuantityList__quantity-button:enabled:active {
    color: #fff !important;
    background-color: #000;
}

.PredefinedQuantityList__quantity-button:enabled:hover {
    color: #000;
    border: 1px solid #000;
    cursor: pointer;
}

.PredefinedQuantityList__quantity-button {
    height: 100%;
    width: 100%;
    outline: none;
    border-radius: 3px;
}

.PredefinedQuantityList__quantity-button {
    background: none;
    border: 0;
    font-family: inherit;
    font-size: inherit;
    padding: 0;
}

.QuantitySelectorCustomField__container {
    display: flex;
    margin: 10px 0 0;
}

.QuantitySelectorCustomField__field {
    margin-top: 0;
    margin-bottom: 0;
    flex: 1;
}

.TextField {
    position: relative;
    margin-top: 20px;
    margin-bottom: 20px;
}

.QuantitySelectorCustomField__button-ok {
    outline: none;
    font-family: Arial;
    border: 1px solid #3d8705;
    background-color: #3d8705;
        height: 41px;
    color: #fff;
    padding: 0 .8em;
    max-width: 5.1em;
    border-top-right-radius: 3px;
    border-bottom-right-radius: 3px;
    float: left;
    line-height: 1.625rem;
    margin-left: -3px;
    z-index: 2003;
}

.QuantitySelectorCustomField__button-ok {
    font-size: .875rem;
    line-height: 1.2em;
    letter-spacing: 1px;
    white-space: nowrap;
}

.QuantitySelector {
    letter-spacing: normal;
    font-weight: 400;
}

.QuantitySelector, .QuantitySelector__container {
    line-height: normal;
}

.QuantitySelector {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%,-50%) scale(1.3);
    z-index: 2002;
}
  .field-item {
    display: flex;
    min-height: 20px;
    flex-direction: column;
    border-radius: 3px;
    border: 1px solid rgba($black, 0.15);
    position: relative;

    .vfl-label {
      font-family: "NespressoLucas";
    }
    input {
      padding-left: 5px;
      -webkit-appearance: none;
      background: transparent;
      width: 100%;
      border: 0;
      padding-top: 12px;
      padding-bottom: 10px;
      @include fontsize(16px);
      font-weight: 500;
      letter-spacing: normal;
      color: $black;

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
      border: 0;
      border-bottom: 0;
      transition: border 0.2s;
    }

    .vfl-label-on-focus + input {
      border-bottom: 0;
      padding-top: 15px;
      padding-bottom: 5px;
      font-size: 1rem;
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

    & + .field-item { margin-top: 8px; }
    & + .btn { margin-top: 25px; }
    }
</style>
