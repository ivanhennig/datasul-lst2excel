<template>
  <div class="container-sm pt-4">
    <div class="card p-4 mb-4">
      <div class="title">Conversor arquivos Datasul LST para Excel</div>
    </div>
    <div class="card">
      <div class="card-header">Planejamento de Programação de Férias</div>
      <div class="card-body">
        <div class="mb-3">
          <UploadSingleEditor v-model="upload_prog"/>
        </div>
        <div>
          <button class="btn btn-primary" :disabled="!hasUploadedFR0800()" @click="download">Download</button>
        </div>
      </div>
    </div>
    <notifications position="top center" group="main"></notifications>
  </div>
</template>

<script>
import UploadSingleEditor from './components/UploadSingleEditor'
import { H, showError } from 'hennig-common'

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
    hasUploadedFR0800 () {
      return !!this.upload_prog
    },
    download () {
      if (!this.hasUploadedFR0800()) {
        showError('Nenhum arquivo foi enviado')
      }
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

html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  background-image: url(~@/assets/excel-bg.jpg);
  background-size: cover;
  background-repeat: no-repeat;
}

#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
}

.title {
  font-size: 1.2rem;
}
</style>
