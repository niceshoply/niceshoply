<div :class="['sidebar-edit-wrap', !design.sidebar ? 'v-hide' : '']" v-cloak v-loading="!design.ready">
  <div class="switch-design" :class="['hide-design', !design.sidebar ? 'v-hide' : '']" @click="design.sidebar = !design.sidebar">
    <el-icon v-if="design.sidebar"><ArrowLeft /></el-icon>
    <el-icon v-else><ArrowRight /></el-icon>
  </div>

  <div class="design-head" v-if="design.editType != 'add'">
    <div class="module-nav">
      <div class="nav-left">
        <div class="current-module-info">
          <div class="module-icon">
            <div v-if="isIcon(getCurrentModuleIcon())" v-html="getCurrentModuleIcon()"></div>
            <div class="img-icon" v-else><img :src="getCurrentModuleIcon()" class="img-fluid"></div>
          </div>
          <div class="module-name">@{{ getModuleName(form.modules[design.editingModuleIndex].code) || form.modules[design.editingModuleIndex].name || form.modules[design.editingModuleIndex].title || lang.module_edit }}</div>
        </div>
      </div>
      <div class="nav-right">
        <span class="nav-item back-btn" @click="showAllModuleButtonClicked">
          <el-icon><Back /></el-icon>
          @{{ lang.back }}
        </span>
      </div>
    </div>
  </div>
  
  <div class="module-edit" v-if="form.modules.length > 0 && design.editType == 'module'">
    <component
      :is="editingModuleComponent"
      :key="design.editingModuleIndex"
      :module="form.modules[design.editingModuleIndex].content"
      @on-changed="moduleUpdated"
    ></component>
  </div>

  <div class="modules-list" :class="{ 'with-design-head': design.editType != 'add' }">
    <div class="modules-header">
      <div class="modules-header-top">
        <div class="modules-title">
          <el-icon><Collection /></el-icon> @{{ lang.module_library }}
        </div>
        
        <div class="modules-search">
          <el-input
            v-model="moduleSearch"
            placeholder="{{ __('PageBuilder::common.search_modules') }}"
            size="small"
            clearable
          ></el-input>
        </div>
      </div>
      
      <div class="modules-categories">
        <el-tag
          v-for="category in moduleCategories"
          :key="category.value"
          :type="selectedCategory === category.value ? 'primary' : 'info'"
          size="small"
          @click="selectedCategory = category.value"
          style="margin: 2px; cursor: pointer;"
        >
          @{{ category.label }}
        </el-tag>
      </div>
    </div>

    <div class="modules-content" v-show="design.editType == 'add'">
      <el-row id="module-list-wrap">
        <el-col :span="12" v-for="(item, index) in filteredModules" :key="index" class="iframe-modules-sortable-ghost">
          <div @click="addModuleButtonClicked(item.code)" class="module-list" :data-code="item.code">
            <div class="module-info">
              <div class="icon">
                <div v-if="isIcon(item.icon)" v-html="item.icon"></div>
                <div class="img-icon" v-else><img :src="item.icon" class="img-fluid"></div>
              </div>
              <div class="name">@{{ getModuleName(item.code) || item.title || item.name }}</div>
            </div>
          </div>
        </el-col>
      </el-row>
    </div>
    
    <div v-if="filteredModules.length === 0 && (moduleSearch || selectedCategory !== 'all')" class="no-results">
      <el-icon><Search /></el-icon>
      <p>@{{ lang.no_matching_modules }}</p>
    </div>
  </div>
</div>