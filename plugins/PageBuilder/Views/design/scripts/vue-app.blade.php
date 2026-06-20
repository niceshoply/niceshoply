<script>
  // Element Plus 常用 API 解构
  const { ElMessage, ElMessageBox, ElNotification } = ElementPlus;

  const PageBuilderApp = Vue.createApp({
    data() {
      return {
        form: {
          modules: []
        },
        source: {
          locale: (typeof $locale !== 'undefined' ? $locale : null) || 'zh_cn',
          modules: []
        },
        lang: lang,
        pages: @json($pages ?? []),
        currentPage: '{{ $page ?? "home" }}',
        design: {
          type: 'pc',
          editType: 'add',
          sidebar: true,
          editingModuleIndex: 0,
          ready: false,
          moduleLoadCount: 0,
          editorInitialized: false,
        },
        showPropertyConsole: false,
        saveStatus: 'saved',
        saveStatusText: lang.saved,
        lastSavedTime: null,
        moduleSearch: '',
        selectedCategory: null,
      };
    },

    computed: {
      previewUrl() {
        if (this.currentPage === 'home') {
          return '{{ front_route("home.index") }}';
        } else {
          const page = this.pages.find(p => (p.slug || p.id) === this.currentPage);
          if (page && page.url) {
            return page.url;
          }
          return '{{ front_route("home.index") }}';
        }
      },
      
      editingModuleComponent() {
        if (!this.form.modules ||
          !this.form.modules.length ||
          this.design.editingModuleIndex < 0 ||
          !this.form.modules[this.design.editingModuleIndex] ||
          !this.form.modules[this.design.editingModuleIndex].code) {
          return null;
        }

        const module = this.form.modules[this.design.editingModuleIndex];
        return 'module-editor-' + module.code.replace('_', '-');
      },
      
      moduleCategories() {
        return [
          { value: 'product', label: lang.product_module },
          { value: 'media', label: lang.media_module },
          { value: 'content', label: lang.content_module },
          { value: 'layout', label: lang.layout_module }
        ];
      },
      
      filteredModules() {
        let modules = this.source.modules;
        
        if (this.selectedCategory) {
          modules = modules.filter(module => {
            const category = this.getModuleCategory(module.code);
            return category === this.selectedCategory;
          });
        }
        
        if (this.moduleSearch) {
          const search = this.moduleSearch.toLowerCase();
          modules = modules.filter(module => {
            const title = (module.title || module.name || '').toLowerCase();
            const code = (module.code || '').toLowerCase();
            return title.includes(search) || code.includes(search);
          });
        }
        
        return modules;
      }
    },

    watch: {
      'design.editingModuleIndex': function(newVal) {
        if (newVal >= 0) {
          this.showPropertyConsole = true;
        }
      },

      'form.modules': {
        handler: function(newVal) {
          if (newVal.length === 0) {
            this.showPropertyConsole = false;
            this.design.editingModuleIndex = -1;
          }
        },
        deep: true
      }
    },

    methods: {
      switchPage(page) {
        if (!page) return;
        
        let newUrl;
        if (page === 'home') {
          newUrl = '{{ console_route("pbuilder.index") }}';
        } else {
          newUrl = '{{ console_route("pbuilder.page.index", ["page" => ":page"]) }}'.replace(':page', page);
        }
        
        window.location.href = newUrl;
      },
      
      moduleUpdated: inno.debounce(function(val) {
        if (!this.design || !this.design.editorInitialized) {
          if (this.design) {
            this.design.moduleLoadCount = 1;
            this.design.editorInitialized = true;
          }
          return;
        }
        
        this.form.modules[this.design.editingModuleIndex].content = val;
        const data = this.form.modules[this.design.editingModuleIndex];
        
        this.saveStatus = 'unsaved';
        this.saveStatusText = lang.unsaved;
        
        const page = '{{ $page ?? "home" }}';
        const url = page === 'home' ? '{{ console_route('pbuilder.modules.preview') }}' : '{{ console_route('pbuilder.page.modules.preview', ['page' => ':page']) }}'.replace(':page', page);
        axios.post(url + '?design=1', data).then((res) => {
          $(previewWindow.document).find('#module-' + data.module_id).replaceWith(res);
          $(previewWindow.document).find('.tooltip').remove();
          const tooltipTriggerList = previewWindow.document.querySelectorAll('[data-bs-toggle="tooltip"]')
          const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new previewWindow.bootstrap.Tooltip(tooltipTriggerEl))
          
          // 重新初始化拖拽功能，确保更新后的模块可以拖拽
          if (typeof initModulesBoxSortable === 'function') {
            setTimeout(function() {
              initModulesBoxSortable();
            }, 100);
          }
        }).catch((error) => {
          let errorMessage = lang.failed_to_update_module;
          
          if (error.response) {
            const status = error.response.status;
            const data = error.response.data;
            
            if (status === 404) {
              errorMessage = lang.module_template_not_found;
            } else if (status === 500) {
              errorMessage = lang.internal_server_error;
            } else if (status === 422) {
              errorMessage = lang.module_data_format_error + ': ' + (data.message || lang.unknown_error);
            } else if (data && data.message) {
              errorMessage = data.message;
            } else {
              errorMessage = lang.request_failed + ' (' + status + ')';
            }
          } else if (error.request) {
            errorMessage = lang.network_connection_failed;
          } else {
            errorMessage = error.message || lang.unknown_error;
          }
          
          layer.msg(errorMessage, {
            icon: 2,
            time: 3000,
            shade: [0.3, '#000']
          });
          
          console.error(lang.failed_to_update_module + ':', error);
        })
      }, 300),

      addModuleButtonClicked(code, moduleItemIndex = null, callback = null) {
        const sourceModule = this.source.modules.find(e => e.code == code)
        const module_id = randomString(16)
        const _data = {
          code: code,
          content: sourceModule.make || sourceModule.content,
          module_id: module_id,
          name: sourceModule.title || sourceModule.name,
          view_path: sourceModule.view_path || '',
        }

        this.saveStatus = 'unsaved';
        this.saveStatusText = lang.unsaved;

        const page = '{{ $page ?? "home" }}';
        const url = page === 'home' ? '{{ console_route('pbuilder.modules.preview') }}' : '{{ console_route('pbuilder.page.modules.preview', ['page' => ':page']) }}'.replace(':page', page);
        axios.post(url + '?design=1', _data).then((res) => {
          if (moduleItemIndex === null) {
            $(previewWindow.document).find('.modules-box').append(res);
            this.form.modules.push(_data);
            this.design.editingModuleIndex = this.form.modules.length - 1;
            this.design.editType = 'module';
          } else {
            $(previewWindow.document).find('.modules-box').children().eq(moduleItemIndex).before(res);
            this.form.modules.splice(moduleItemIndex, 0, _data);
            this.design.editingModuleIndex = moduleItemIndex;
            this.design.editType = 'module';
          }

          setTimeout(() => {
            const moduleElement = $(previewWindow.document).find('#module-' + module_id);
            if (moduleElement.length > 0 && moduleElement.offset()) {
              $(previewWindow.document).find("html, body").animate({
                scrollTop: moduleElement.offset().top - 96
              }, 50);
            }
            // 重新初始化拖拽功能，确保新添加的模块可以拖拽
            if (typeof initModulesBoxSortable === 'function') {
              initModulesBoxSortable();
            }
          }, 200)
        }).catch((error) => {
          let errorMessage = lang.failed_to_add_module;
          
          if (error.response) {
            const status = error.response.status;
            const data = error.response.data;
            
            if (status === 404) {
              errorMessage = lang.module_template_not_found;
            } else if (status === 500) {
              errorMessage = lang.internal_server_error;
            } else if (status === 422) {
              errorMessage = lang.module_data_format_error + ': ' + (data.message || lang.unknown_error);
            } else if (data && data.message) {
              errorMessage = data.message;
            } else {
              errorMessage = lang.request_failed + ' (' + status + ')';
            }
          } else if (error.request) {
            errorMessage = lang.network_connection_failed;
          } else {
            errorMessage = error.message || lang.unknown_error;
          }
          
          layer.msg(errorMessage, {
            icon: 2,
            time: 3000,
            shade: [0.3, '#000']
          });
          
          console.error(lang.failed_to_add_module + ':', error);
        }).finally(() => {
          if (callback) {
            callback();
          }
        })
      },

      editModuleButtonClicked(index) {
        if (this.design) {
          if (this.design.editingModuleIndex === index && this.design.editType === 'module') {
            console.log(lang.already_editing_module, index);
            return;
          }
          
          this.design.moduleLoadCount = 0;
          this.design.editingModuleIndex = index;
          this.design.editType = 'module';
          this.design.editorInitialized = false;
        }
      },

      saveButtonClicked() {
        this.saveStatus = 'saving';
        this.saveStatusText = lang.saving;
        
        const page = '{{ $page ?? "home" }}';
        const url = page === 'home' ? '{{ console_route('pbuilder.modules.update') }}' : '{{ console_route('pbuilder.page.modules.update', ['page' => ':page']) }}'.replace(':page', page);
        
        axios.put(url, this.form).then((res) => {
          this.saveStatus = 'saved';
          this.saveStatusText = lang.saved;
          this.lastSavedTime = new Date();
          layer.msg(res.message, {icon: 1});
        }).catch((error) => {
          this.saveStatus = 'unsaved';
          this.saveStatusText = lang.save_failed;
          layer.msg(lang.save_failed + ': ' + (error.response?.data?.message || error.message), {icon: 2});
        });
      },

      importDemoData() {
        const page = '{{ $page ?? "home" }}';
        if (page !== 'home') {
          layer.msg(lang.demo_data_home_only);
          return;
        }
        
        if (confirm(lang.confirm_import_demo)) {
          const url = '{{ console_route('pbuilder.demo.import', ['page' => 'home']) }}';
          axios.post(url).then((res) => {
            layer.msg(res.message);
            setTimeout(() => {
              location.reload();
            }, 1000);
          }).catch((error) => {
            layer.msg(lang.import_failed + ': ' + (error.response?.data?.message || error.message));
          });
        }
      },

      viewHome() {
        location = '{{ front_route('home.index') }}';
      },

      isIcon(code) {
        return typeof code === 'string' && (code.indexOf('<i') === 0 || code.indexOf('&#') === 0);
      },
      
      getCurrentModuleIcon() {
        if (!this.form.modules || 
            !this.form.modules.length || 
            this.design.editingModuleIndex < 0 || 
            !this.form.modules[this.design.editingModuleIndex]) {
          return null;
        }
        
        const currentModule = this.form.modules[this.design.editingModuleIndex];
        const sourceModule = this.source.modules.find(module => module.code === currentModule.code);
        
        return sourceModule ? sourceModule.icon : null;
      },
      
      getModuleCategory(code) {
        const moduleCategories = {
          'slideshow': 'media',
          'single-image': 'media',
          'four-image': 'media',
          'four-image-plus': 'media',
          'multi-row-images': 'media',
          'video': 'media',
          'custom-products': 'product',
          'category-products': 'product',
          'latest-products': 'product',
          'brand-products': 'product',
          'card-slider': 'product',
          'rich-text': 'content',
          'article': 'content',
          'brands': 'content',
          'left-image-right-text': 'layout',
          'image-text-list': 'layout'
        };
        
        return moduleCategories[code] || 'layout';
      },
      
      getModuleName(code) {
        const moduleNameMap = {
          'slideshow': lang.module_slideshow,
          'custom-products': lang.module_custom_products,
          'category-products': lang.module_category_products,
          'latest-products': lang.module_latest_products,
          'rich-text': lang.module_rich_text,
          'single-image': lang.module_single_image,
          'four-image': lang.module_four_image,
          'left-image-right-text': lang.module_left_image_right_text,
          'brands': lang.module_brands,
          'brand-products': lang.module_brand_products,
          'card-slider': lang.module_card_slider,
          'multi-row-images': lang.module_multi_row_images,
          'image-text-list': lang.module_image_text_list,
          'four-image-plus': lang.module_four_image_plus,
          'article': lang.module_article,
          'video': lang.module_video
        };
        
        return moduleNameMap[code] || code;
      },

      showAllModuleButtonClicked() {
        if (this.design) {
          this.design.editType = 'add';
          this.design.editingModuleIndex = 0;
        }
      },

      switchDevice(type) {
        if (this.design) {
          this.design.type = type;
        }
        const iframe = document.getElementById('preview-iframe');
        const previewContainer = document.querySelector('.preview-iframe');
        
        if (!previewContainer) {
          console.warn('Preview container not found');
          return;
        }
        
        previewContainer.classList.remove('device-pc', 'device-mobile');
        
        if (type === 'mobile') {
          previewContainer.classList.add('device-mobile');
          if (iframe) {
            iframe.style.width = '375px';
            iframe.style.height = '667px';
            iframe.style.maxWidth = '375px';
            iframe.style.maxHeight = '667px';
          }
        } else {
          previewContainer.classList.add('device-pc');
          if (iframe) {
            iframe.style.width = '100%';
            iframe.style.height = '100%';
            iframe.style.maxWidth = 'none';
            iframe.style.maxHeight = 'none';
          }
        }
      },

      initKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
          if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            return;
          }
          
          if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            this.saveButtonClicked();
          }
          
          if (e.key === 'Delete' && this.design.editingModuleIndex >= 0) {
            e.preventDefault();
            this.deleteCurrentModule();
          }
          
          if (e.ctrlKey && e.key === 'z') {
            e.preventDefault();
          }
          
          if (e.ctrlKey && e.key === 'y') {
            e.preventDefault();
          }
          
          if (e.key === 'Escape') {
            e.preventDefault();
            this.showAllModuleButtonClicked();
          }
        });
      },
      
      deleteCurrentModule() {
        if (this.design.editingModuleIndex >= 0 && this.form.modules[this.design.editingModuleIndex]) {
          if (confirm(lang.confirm_delete_module)) {
            this.design.editType = 'add';
            this.design.editingModuleIndex = 0;
            this.form.modules.splice(this.design.editingModuleIndex, 1);
            this.saveStatus = 'unsaved';
            this.saveStatusText = lang.unsaved;
          }
        }
      }
    },
    
    created () {
      this.form = @json($design_settings ?: ['modules' => []]);
      this.source.modules = @json($source['modules'] ?? []);
    },
    
    mounted () {
      this.switchDevice(this.design.type);
      
      setTimeout(() => {
        if (this.design) {
          this.design.ready = true;
        }
      }, 1000);
      
      this.initKeyboardShortcuts();
    },
  });

  /*
   * Vue 3 + Element Plus 启动与 Vue 2 兼容层
   * 说明：本设计器由 1 个主 app + 16 个编辑器组件 + 若干公共组件构成，
   * 编辑器/组件脚本通过全局 Vue.component(...) 注册，并在主模板中以
   * <component :is="..."> 动态渲染。Vue 3 中 :is 在渲染期解析组件，
   * 因此「先挂载、后注册」依旧有效；这里提供以下兼容 shim，避免逐个改写脚本：
   *   - Vue.component  -> 注册到当前 app 实例
   *   - Vue.set/delete -> Vue 3 已是 Proxy 响应式，直接赋值/删除即可
   *   - this.$message / $confirm 等 -> 映射到 Element Plus
   */
  PageBuilderApp.use(ElementPlus);

  // 注册 Element Plus 全部 SVG 图标为全局组件（<el-icon><Picture/></el-icon>）
  if (window.ElementPlusIconsVue) {
    for (const [iconName, iconComp] of Object.entries(ElementPlusIconsVue)) {
      PageBuilderApp.component(iconName, iconComp);
    }
  }

  // 注册 vuedraggable@4 为全局 <draggable> 组件（UMD 暴露于 window.vuedraggable）
  const __PbDraggable = window.vuedraggable && (window.vuedraggable.default || window.vuedraggable);
  if (__PbDraggable) {
    PageBuilderApp.component('draggable', __PbDraggable);
  }

  // 全局属性兼容：保持编辑器内 this.$message / this.$confirm 等调用不变
  PageBuilderApp.config.globalProperties.$message = ElMessage;
  PageBuilderApp.config.globalProperties.$msgbox = ElMessageBox;
  PageBuilderApp.config.globalProperties.$confirm = ElMessageBox.confirm;
  PageBuilderApp.config.globalProperties.$alert = ElMessageBox.alert;
  PageBuilderApp.config.globalProperties.$prompt = ElMessageBox.prompt;
  PageBuilderApp.config.globalProperties.$notify = ElNotification;

  // 实例级 Vue 2 兼容：this.$set / this.$delete（Vue 3 为 Proxy 响应式，直接赋值即可）
  PageBuilderApp.config.globalProperties.$set = function (target, key, value) {
    target[key] = value;
    return value;
  };
  PageBuilderApp.config.globalProperties.$delete = function (target, key) {
    if (Array.isArray(target)) {
      target.splice(key, 1);
    } else {
      delete target[key];
    }
  };

  // Vue 2 全局 API 兼容 shim：供后续 editors/components 的脚本继续使用
  Vue.component = (name, definition) => PageBuilderApp.component(name, definition);
  Vue.set = (target, key, value) => { target[key] = value; return value; };
  Vue.delete = (target, key) => {
    if (Array.isArray(target)) {
      target.splice(key, 1);
    } else {
      delete target[key];
    }
  };

  // 挂载并暴露根实例（iframe-events 等外部脚本通过 window.app 调用其方法）
  const app = PageBuilderApp.mount('#app');
  window.app = app;
</script>