<template>
  <div class="UploadSingleEditor d-flex justify-content-center p-2">
    <div class="cursor" style="height: 48px; overflow: hidden; position: relative">
      <div>Selecione um arquivo LST</div>
      <div class="text-muted small">{{ name }}</div>

      <input type="file" v-on:change="upload"/>
    </div>
  </div>
</template>
<script>
import { BaseComponent, showError } from 'hennig-common'

export default {
  name: 'UploadSingleEditor',
  components: {},
  extends: BaseComponent,
  data () {
    return { name: '' }
  },
  mounted () {
    this.baseMounted()
  },
  methods: {
    remove () {
      this.current = ''
      this.$emit('update:modelValue', this.current)
    },
    upload (evnt) {
      this._upload('/upload', evnt.target.files)
    },
    _upload (aUrl, aFiles) {
      if (aFiles.length === 0) {
        showError('Nenhum arquivo selecionado')
        return
      }

      for (const file of aFiles) {
        if (file.size > 50 * 10124 * 1024) {
          showError('Arquivo muito grande. Limite 50MB')
          return
        }

        this.name = file.name
        const reader = new FileReader()
        reader.onloadend = () => {
          this.current = reader.result
          this.$emit('update:modelValue', this.current)
        }

        reader.readAsDataURL(file)
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.UploadSingleEditor {
  border: 1px solid silver;
}

.img-item {
  background-repeat: no-repeat;
  background-position: center center;
  background-size: contain;
  width: 100%;
  height: 100%;
}

.la {
  opacity: .1;
  transition: opacity .5s;
}

.img-thumbnail {
  position: relative;

  &:hover {
    .hover-blur {
      filter: blur(6px);
    }

    .label, .la {
      opacity: 1;
    }
  }
}

.cursor {
  cursor: pointer;
}

.label {
  position: absolute;
  opacity: 0;
  background: rgba(255, 255, 255, 0.75);
  width: 100%;
  top: 40px;
  left: 0;
  transition: opacity .5s;
  text-align: center;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}

input[type=file] {
  position: absolute;
  top: 0;
  left: 0;
  border: 1px solid red;
  height: 100%;
  opacity: .01;
}
</style>
