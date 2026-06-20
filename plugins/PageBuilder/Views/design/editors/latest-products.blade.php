{{-- 最新商品编辑模块 --}}
<template id="module-editor-latest-products-template">
  <div class="editor-container">
    <div class="top-spacing"></div>
    
    {{-- 模块宽度设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <el-icon><Monitor /></el-icon>
        @{{ lang.module_width }}
      </div>
      <div class="section-content">
        <div class="segmented-buttons">
          <div 
            :class="['segmented-btn', { active: form.width === 'narrow' }]" 
            @click="form.width = 'narrow'"
          >
            <el-icon><CopyDocument /></el-icon>
            @{{ lang.narrow_screen }}
          </div>
          <div 
            :class="['segmented-btn', { active: form.width === 'wide' }]" 
            @click="form.width = 'wide'"
          >
            <el-icon><CopyDocument /></el-icon>
            @{{ lang.wide_screen }}
          </div>
          <div 
            :class="['segmented-btn', { active: form.width === 'full' }]" 
            @click="form.width = 'full'"
          >
            <el-icon><FullScreen /></el-icon>
            @{{ lang.full_screen }}
          </div>
        </div>
      </div>
    </div>

    {{-- 模块标题设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <el-icon><Edit /></el-icon>
        @{{ lang.module_title }}
      </div>
      <div class="section-content">
        <text-i18n v-model="form.title" @change="onChange" :placeholder="lang.enter_module_title"></text-i18n>
      </div>
    </div>

    {{-- 显示设置 --}}
    <div class="editor-section">
      <div class="section-title">
        <el-icon><Setting /></el-icon>
        @{{ lang.display_settings }}
      </div>
      <div class="section-content">
        {{-- 每行显示数量设置 --}}
        <div class="setting-group">
          <div class="setting-label">@{{ lang.items_per_row }}</div>
          <div class="segmented-buttons">
            <div 
              :class="['segmented-btn', { active: form.columns === 3 }]" 
              @click="form.columns = 3"
            >
              <el-icon><Grid /></el-icon>
              @{{ lang.items_3 }}
            </div>
            <div 
              :class="['segmented-btn', { active: form.columns === 4 }]" 
              @click="form.columns = 4"
            >
              <el-icon><Grid /></el-icon>
              @{{ lang.items_4 }}
            </div>
            <div 
              :class="['segmented-btn', { active: form.columns === 6 }]" 
              @click="form.columns = 6"
            >
              <el-icon><Grid /></el-icon>
              @{{ lang.items_6 }}
            </div>
          </div>
        </div>

        {{-- 商品数量设置 --}}
        <div class="setting-group">
          <div class="setting-label">@{{ lang.product_quantity }}</div>
          <el-input 
            v-model="form.limit" 
            type="number" 
            size="small" 
            :placeholder="lang.enter_product_quantity"
            style="width: 100%;"
          ></el-input>
          <div class="setting-tip">
            <el-icon><InfoFilled /></el-icon>
            @{{ lang.show_latest_products_count }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

{{-- 最新商品编辑模块脚本 --}}
<script type="text/javascript">
  Vue.component('module-editor-latest-products', {
    template: '#module-editor-latest-products-template',
    props: ['module'],
    data: function() {
      return {
        debounceTimer: null,
        form: {
          title: {},
          limit: 8,
          columns: 4,
          width: 'wide'
        }
      }
    },

    watch: {
      form: {
        handler: function(val) {
          this.onChange();
        },
        deep: true
      }
    },

    created: function() {
      if (this.module) {
        this.form = JSON.parse(JSON.stringify(this.module));
      }

      if (!this.form.title) {
        this.$set(this.form, 'title', this.languagesFill(''));
      }

      if (!this.form.width) {
        this.$set(this.form, 'width', 'wide');
      }

      if (!this.form.columns) {
        this.$set(this.form, 'columns', 4);
      }

      if (!this.form.limit) {
        this.$set(this.form, 'limit', 8);
      }

      this.$emit('on-changed', this.form);
    },

    methods: {
      onChange() {
        // 清除之前的定时器
        if (this.debounceTimer) {
          clearTimeout(this.debounceTimer);
        }
        
        // 设置新的定时器
        this.debounceTimer = setTimeout(() => {
          this.$emit('on-changed', this.form);
        }, 300);
      },

      languagesFill(text) {
        const obj = {};
        $languages.forEach(e => {
          obj[e.code] = text;
        });
        return obj;
      }
    }
  });
</script> 