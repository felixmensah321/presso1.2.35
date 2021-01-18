<template>
  <transition name="modal-fade">
    <div class="modal-backdrop">
      <div
        id="Modal__wrapper"
        class="Modal"
        role="dialog"
        aria-labelledby="Modal__title"
        aria-describedby="Modal__description"
      >
        <div class="Modal__container">
          <div class="Modal__popin">
            <div
              class="ModalCustomField__container"
            >
              <span class="caution" />
              <span class="errorText text-danger"> {{ errorData }}</span>
              <span
                class="cancel"
                @click="closeModal"
              />
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
    errorData: {
      type: String,
      default: null
    }
  },
  data () {
    return {
    }
  },
  methods: {
    closeModal () {
      this.$emit('close')
    }
  }
}
</script>
<style scoped>
    .Modal__popin {
        padding: .625rem;
        width: 27.25rem;
        font-size: .75rem;
        background-color: #f9f9f9;
        color: #2e2e2e;
        border: 1px solid #d5d5d5;
        border-radius: 2px;
    }
    .Modal {
      letter-spacing: normal;
      font-weight: 400;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%,-50%) scale(1.3);
      z-index: 2002;
    }

    .Modal, .Modal__container {
      line-height: normal;
    }
    .ModalCustomField__container {
      display: flex;
      margin: 10px 0 0;
    }
    .errorText{
        font-size: 16px;
        font-weight: bolder;
        margin: 8px auto;
    }
    .cancel{
        margin: 7px auto;
        cursor: pointer;
    }
    .cancel::before{
        content: '\2715';
        font-size: 18px;
        font-weight: bolder;
    }
    .text-danger{
        color: #DD3232 !important;
    }
    .caution{
        height: 20%;
        width: 10%;
        border: 2px solid #DD3232;
        border-radius: 100px;
        background-color: #DD3232;
        position: relative;
        top: -5.5px;
    }
    .caution::before{
        content: '\0021';
        text-align: center;
        font-size: 30px;
        color:#fff;
        margin: 0 40% auto;
    }
</style>
