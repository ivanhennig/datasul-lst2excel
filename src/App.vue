<template>
  <div class="container">
    <div class="title mb-3">
      Conversor arquivos Datasul LST para Excel
    </div>
    <div class="mb-3">
      <label>Planejamento de Programação de Férias</label>
      <UploadSingleEditor v-model="upload_prog"/>
    </div>
    <div>
      <button class="btn btn-primary" @click="download">Download</button>
    </div>
  </div>
</template>

<script>
import UploadSingleEditor from './components/UploadSingleEditor'
import { H } from 'hennig-common'

export default {
  name: 'App',
  components: {
    UploadSingleEditor
  },
  data () {
    return {
      upload_prog: ''
    }
  },
  methods: {
    download () {
      H.setup({ prefix: process.env.VUE_APP_RPC_PREFIX })
      H.rpc('Datasul', 'convert', [this.upload_prog], r => {
        if (r) {
          window.open(r)
        }
      })
    }
  }
}
</script>

<style lang="scss">
@import "~bootstrap/scss/bootstrap";

#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}

.title {
  font-size: 1.2rem;
}
</style>
