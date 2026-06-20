{{-- 批量设置价格模态框 --}}
<el-dialog v-model="dialogVisible.price"
           :title="`{{ __('console/product.bulk_set_price') }} (${checkedIds.length} {{ __('console/product.items') }})`"
           width="500">
  <el-form>
    <p>{{ __('console/product.select_bulk_method') }}</p>
    <el-radio-group v-model="bulkFormData.price.mode" class="d-block">
      <el-radio value="reset" size="large" class="d-block mt-2">{{ __('console/product.reset_price') }}</el-radio>
      <el-radio value="increase" size="large" class="d-block mt-2">{{ __('console/product.increase_price') }}</el-radio>
      <el-radio value="decrease" size="large" class="d-block mt-2">{{ __('console/product.decrease_price') }}</el-radio>
    </el-radio-group>
    <el-input v-model="bulkFormData.price.value" type="number" class="mt-3"
              placeholder="{{ __('console/product.input_price') }}"></el-input>
  </el-form>
  <template #footer>
    <div class="dialog-footer">
      <el-button @click="dialogVisible.price = false">{{ __('console/common.cancel') }}</el-button>
      <el-button type="primary" @click="submitBulkUpdate('price')">{{ __('console/common.save') }}</el-button>
    </div>
  </template>
</el-dialog>

{{-- 批量设置分类模态框 --}}
<el-dialog v-model="dialogVisible.categories"
           :title="`{{ __('console/product.bulk_set_categories') }} (${checkedIds.length} {{ __('console/product.items') }})`"
           width="500">
  <el-form>
    <el-cascader
      v-model="bulkFormData.categories"
      :options="categoryCascaderOptions"
      :props="{ multiple: true, checkStrictly: true, emitPath: false }"
      clearable
      filterable
      placeholder="{{ __('console/product.search_select_categories') }}"
      style="width: 100%"></el-cascader>
  </el-form>
  <template #footer>
    <div class="dialog-footer">
      <el-button @click="dialogVisible.categories = false">{{ __('console/common.cancel') }}</el-button>
      <el-button type="primary" @click="submitBulkUpdate('categories')">{{ __('console/common.save') }}</el-button>
    </div>
  </template>
</el-dialog>

{{-- 批量设置库存模态框 --}}
<el-dialog v-model="dialogVisible.quantity"
           :title="`{{ __('console/product.bulk_set_quantity') }} (${checkedIds.length} {{ __('console/product.items') }})`"
           width="500">
  <el-form>
    <p>{{ __('console/product.select_bulk_method') }}</p>
    <el-radio-group v-model="bulkFormData.quantity.mode" class="d-block">
      <el-radio value="reset" size="large" class="d-block mt-2">{{ __('console/product.reset_quantity') }}</el-radio>
      <el-radio value="increase" size="large"
                class="d-block mt-2">{{ __('console/product.increase_quantity') }}</el-radio>
      <el-radio value="decrease" size="large"
                class="d-block mt-2">{{ __('console/product.decrease_quantity') }}</el-radio>
    </el-radio-group>
    <el-input v-model="bulkFormData.quantity.value" type="number" class="mt-3"
              placeholder="{{ __('console/product.input_quantity') }}"></el-input>
  </el-form>
  <template #footer>
    <div class="dialog-footer">
      <el-button @click="dialogVisible.quantity = false">{{ __('console/common.cancel') }}</el-button>
      <el-button type="primary" @click="submitBulkUpdate('quantity')">{{ __('console/common.save') }}</el-button>
    </div>
  </template>
</el-dialog>

{{-- 批量发布确认模态框 --}}
<el-dialog v-model="dialogVisible.publish"
           :title="`{{ __('console/product.publish_selected') }} (${checkedIds.length} {{ __('console/product.items') }})`"
           width="500">
  <span>{{ __('console/product.publish_confirm') }}</span>
  <template #footer>
    <div class="dialog-footer">
      <el-button @click="dialogVisible.publish = false">{{ __('console/common.cancel') }}</el-button>
      <el-button type="primary" @click="submitBulkUpdate('publish')">{{ __('console/common.confirm') }}</el-button>
    </div>
  </template>
</el-dialog>

{{-- 批量取消发布确认模态框 --}}
<el-dialog v-model="dialogVisible.unpublish"
           :title="`{{ __('console/product.unpublish_selected') }} (${checkedIds.length} {{ __('console/product.items') }})`"
           width="500">
  <span>{{ __('console/product.unpublish_confirm') }}</span>
  <template #footer>
    <div class="dialog-footer">
      <el-button @click="dialogVisible.unpublish = false">{{ __('console/common.cancel') }}</el-button>
      <el-button type="primary" @click="submitBulkUpdate('unpublish')">{{ __('console/common.confirm') }}</el-button>
    </div>
  </template>
</el-dialog> 