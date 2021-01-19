<template>
  <div class="hello">
    <p
      v-if="!noHello"
      class="hello-text hello-text--big"
    >
      {{ $t('product.hello') }}
      <br>
      {{ userFirstName }}
    </p>
    <div
      v-if="position != null"
      class="logoDiv"
    >
      <div class="step-wrapper">
        <div
          v-for="(step, index) in steps"
          :key="step"
          class="steps"
        >
          <Steps
            :id="index + 1"
            :step-name="step"
            :current="position"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Steps from './Steps'

export default {
  name: 'Hello',
  components: {
    Steps
  },
  props: {
    position: { type: Number, default: null },
    userData: { type: Object, default: null },
    noHello: { type: Boolean, default: false }
  },
  data () {
    return {
      userFirstName: this.userData.firstname,
      isAlt: false,
      steps: []
    }
  },
  watch: {
    userData: 'fetchData'
  },
  created () {
    this.steps.push(this.$t('steps.identification'))
    if (this.$store.state.defaultPurchasePointId.includes(this.$store.state.purchasePointId)) {
      this.steps.push(this.$t('steps.delivery'))
    }
    this.steps.push(this.$t('steps.cart'))
    this.steps.push(this.$t('steps.payment'))
  },
  methods: {
    fetchData () {
      this.userFirstName = this.userData.firstname
    }
  }
}
</script>

<style lang="scss" scoped>
@import "../assets/scss/mixins";

.step-wrapper {
 display: flex;
}

.logoDiv {
  background: white;
  padding-bottom: 11px;
  padding-top: 10px;
  width: calc(100% + 40px);
  margin-left: -20px;
}

.logo {
  margin-left: 20px;
  width: calc(100% - 40px);
}

.steps {
  width: 100%;
  text-align: center;
  position: relative;
  display: inline-block;
  margin: 0 auto;
}

.hello-text {
  margin: 0 0 25px;
  @include fontsize(18px);
  font-weight: 300;
  letter-spacing: 1px;
  line-height: 24px;
  text-align: center;

  &.hello-text--big {
    @include fontsize(30px);
    letter-spacing: 1px;
    line-height: 40px;
    text-transform: uppercase;
    margin-top: 10px;
  }
}

.steps:not(:last-child)::after {
  content: " ";
  display: block;
  position: absolute;
  height: 1px;
  background: #d8d8d8;
  width: calc(100% - 30px);
  left: calc(50% + 15px);
  top: 12px;
}

.steps:not(:first-child)::before {
  content: " ";
  display: block;
  position: absolute;
  height: 1px;
  background: #d8d8d8;
  width: calc(100% - 30px);
  right: calc(50% + 15px);
  top: 12px;
}
</style>
