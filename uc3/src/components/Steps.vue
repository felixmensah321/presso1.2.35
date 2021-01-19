<template>
  <div>
    <div v-if="current === id">
      <div
        id="circle-current"
      >
        <span class="value">{{ id }}</span>
      </div>
      <span class="step-name">{{ stepName }}</span>
    </div>
    <div v-else-if="id < current">
      <div
        id="circle-passed"
      />
      <span class="step-name">{{ stepName }}</span>
    </div>
    <div v-else>
      <div
        id="circle-grey"
      >
        <span class="next-value">{{ id }}</span>
      </div>
      <span class="next-step-name">{{ stepName }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Steps',
  components: {},
  props: {
    id: { type: Number, default: 0 },
    current: { type: Number, default: 0 },
    stepName: { type: String, default: null }
  },
  data () {
    return {
      isPassed: false
    }
  },
  watch: {
    current (newVal, old) {
      this.current = newVal
    }
  }
}
</script>

<style lang="scss">
  @import '../assets/scss/mixins';

#circle-grey {
    border: 1px solid grey;
    margin: auto;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    text-align: center;
}

#circle-current {
  align-items: center;
    background: $green;
    margin: auto;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    text-align: center;
}

#circle-passed{
    margin: auto;
    content: " ";
    display: block;
    width: 24px;
    height: 24px;
    background: url("../static/img/valid.svg") $white no-repeat center center;
    border-radius: 50%;
}

.step-name {
    display: inline-block;
    max-width: 70px;
    text-align: center;
    @include fontsize(12px);
    padding-top: 7px;
}

.next-step-name {
    display: inline-block;
    max-width: 70px;
    color: grey;
    text-align: center;
    @include fontsize(12px);
    padding-top: 7px;
}

.value{
    color: white;
    line-height: 25px;
    text-align: center;
    @include fontsize(12px);
}

.next-value{
    color: grey;
    line-height: 23px;
    text-align: center;
    @include fontsize(12px);
}
</style>
