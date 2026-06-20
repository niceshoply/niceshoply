<template id="single-image-selector">
  <div class="image-selector">
    <div class="image-preview" @click="openSelector">
      <img v-if="displayImage" :src="displayImage" class="preview-img" :alt="lang.preview_image">
      <div v-else class="placeholder">
        <el-icon><Picture /></el-icon>
        <span>@{{ lang.click_select_image }}</span>
      </div>
    </div>
    
    <div class="image-controls">
      <el-button type="primary" size="small" @click="openSelector">
        <el-icon><Picture /></el-icon> @{{ lang.select_image }}
      </el-button>
      <el-button v-if="displayImage" type="danger" size="small" @click="clearImage">
        <el-icon><Delete /></el-icon> @{{ lang.clear }}
      </el-button>
    </div>
    
    <!-- 多语言图片选择 -->
    <div v-if="showLanguageTabs" class="language-tabs">
      <el-tabs v-model="activeLanguage" type="card" size="small">
        <el-tab-pane v-for="lang in languages" :key="lang.code" :label="lang.name" :name="lang.code">
          <div class="lang-image-selector">
            <div class="image-preview" @click="openSelector(lang.code)">
              <img v-if="getLangImage(lang.code)" :src="getLangImage(lang.code)" class="preview-img" :alt="lang.preview_image">
              <div v-else class="placeholder">
                <el-icon><Picture /></el-icon>
                <span>@{{ lang.click_select_image }}</span>
              </div>
            </div>
            <div class="image-controls">
              <el-button type="primary" size="small" @click="openSelector(lang.code)">
                <el-icon><Picture /></el-icon> @{{ lang.select_image }}
              </el-button>
              <el-button v-if="getLangImage(lang.code)" type="danger" size="small" @click="clearLangImage(lang.code)">
                <el-icon><Delete /></el-icon> @{{ lang.clear }}
              </el-button>
            </div>
          </div>
        </el-tab-pane>
      </el-tabs>
    </div>
  </div>
</template>

<script>
Vue.component('single-image-selector', {
  template: '#single-image-selector',
  // Vue 3 v-model：modelValue + update:modelValue（兼容原 value/input 调用方）
  emits: ['update:modelValue', 'change'],
  props: {
    modelValue: {
      type: [String, Object],
      default: ''
    },
    multiLanguage: {
      type: Boolean,
      default: false
    },
    aspectRatio: {
      type: Number,
      default: null
    },
    targetWidth: {
      type: Number,
      default: null
    },
    targetHeight: {
      type: Number,
      default: null
    }
  },
  data() {
    return {
      activeLanguage: $locale || 'zh_cn',
      languages: $languages || [],
      showLanguageTabs: false
    }
  },
  computed: {
    displayImage() {
      if (this.multiLanguage) {
        return this.getLangImage(this.activeLanguage);
      }
      
      if (typeof this.modelValue === 'string') {
        return this.modelValue;
      }
      
      if (typeof this.modelValue === 'object' && this.modelValue) {
        return this.modelValue[this.activeLanguage] || Object.values(this.modelValue)[0] || '';
      }
      
      return '';
    }
  },
  watch: {
    multiLanguage: {
      immediate: true,
      handler(val) {
        this.showLanguageTabs = val && this.languages.length > 1;
      }
    }
  },
  methods: {
    openSelector(langCode = null) {
      const targetLang = langCode || this.activeLanguage;
      
      // 使用NiceShoply核心的文件管理器
      if (window.inno && window.inno.fileManagerIframe) {
        window.inno.fileManagerIframe((file) => {
          console.log("File selected:", file);
          
          // 修复URL
          let fileUrl = file.origin_url || file.path;
          if (fileUrl && !fileUrl.match(/^https?:\/\//)) {
            if (!fileUrl.startsWith("/")) {
              fileUrl = "/" + fileUrl;
            }
            fileUrl = window.location.origin + fileUrl;
          }
          
          this.setImage(fileUrl, targetLang);
        }, {
          type: 'image',
          multiple: false
        });
      } else {
        console.error('File manager not available');
        this.$message.error(lang.file_manager_unavailable);
      }
    },
    
    setImage(imagePath, langCode = null) {
      const targetLang = langCode || this.activeLanguage;
      
      if (this.multiLanguage) {
        // 多语言模式
        if (typeof this.modelValue !== 'object') {
          this.$emit('update:modelValue', {});
        }
        
        const newValue = { ...this.modelValue };
        newValue[targetLang] = imagePath;
        this.$emit('update:modelValue', newValue);
      } else {
        // 单语言模式
        this.$emit('update:modelValue', imagePath);
      }
      
      this.$emit('change');
    },
    
    clearImage(langCode = null) {
      const targetLang = langCode || this.activeLanguage;
      
      if (this.multiLanguage) {
        const newValue = { ...this.modelValue };
        delete newValue[targetLang];
        this.$emit('update:modelValue', newValue);
      } else {
        this.$emit('update:modelValue', '');
      }
      
      this.$emit('change');
    },
    
    getLangImage(langCode) {
      if (typeof this.modelValue === 'object' && this.modelValue) {
        return this.modelValue[langCode] || '';
      }
      return '';
    },
    
    clearLangImage(langCode) {
      this.clearImage(langCode);
    }
  }
});
</script>

<style scoped>
.image-selector {
  width: 100%;
}

.image-preview {
  width: 100%;
  height: 120px;
  border: 2px dashed #d9d9d9;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s;
  margin-bottom: 10px;
}

.image-preview:hover {
  border-color: #409eff;
  background-color: #f0f9ff;
}

.preview-img {
  max-width: 100%;
  max-height: 100%;
  object-fit: cover;
  border-radius: 4px;
}

.placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #909399;
}

.placeholder i {
  font-size: 24px;
  margin-bottom: 5px;
}

.placeholder span {
  font-size: 12px;
}

.image-controls {
  display: flex;
  gap: 8px;
  margin-bottom: 10px;
}

.language-tabs {
  margin-top: 15px;
}

.lang-image-selector {
  padding: 10px 0;
}

.el-tabs--card .el-tabs__header .el-tabs__item {
  border: 1px solid #e4e7ed;
  border-bottom: none;
  border-radius: 4px 4px 0 0;
  margin-right: 5px;
}

.el-tabs--card .el-tabs__header .el-tabs__item.is-active {
  border-bottom-color: #fff;
  background-color: #fff;
}
</style>
