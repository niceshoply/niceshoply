<div class="tab-pane fade show active" id="info-tab-pane" role="tabpanel" tabindex="0">
  <form class="needs-validation" novalidate id="app-form"
        action="{{ $customer->id ? console_route('customers.update', [$customer->id]) : console_route('customers.store') }}"
        method="POST">
    @csrf
    @method($customer->id ? 'PUT' : 'POST')

    <div class="row">
      <div class="col-md-6">
        <div class="mb-3">
          <x-common-form-image title="{{ __('console/customer.avatar') }}" name="avatar" value="{{ old('avatar', $customer->avatar) }}" required/>
        </div>
        <div class="mb-3">
          <x-common-form-input title="{{ __('console/customer.from') }}" name="from" value="{{ old('from', $customer->from) }}" placeholder="{{ __('console/customer.from') }}"/>
        </div>
        <div class="mb-3">
          <x-common-form-input title="{{ __('console/customer.name') }}" name="name" value="{{ old('name', $customer->name) }}" required placeholder="{{ __('console/customer.name') }}"/>
        </div>
        <div class="mb-3">
          <x-common-form-input title="{{ __('console/customer.password') }}" name="password" value="" placeholder="{{ __('console/customer.password') }}"/>
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <x-common-form-input title="{{ __('console/customer.email') }}" name="email" value="{{ old('email', $customer->email) }}" required placeholder="{{ __('console/customer.email') }}"/>
        </div>
        <div class="mb-3 customersmt">
          <x-common-form-select title="{{ __('console/customer.group') }}" name="customer_group_id" :options="$groups" key="id" label="name" value="{{ old('customer_group_id', $customer->customer_group_id) }}"/>
        </div>
        @hookinsert('console.customer.form.group.after')
        <div class="mb-3">
          <x-common-form-select title="{{ __('console/customer.locale') }}" name="locale" :options="$locales" key="code" label="name" value="{{ old('locale', $customer->locale) }}"/>
        </div>
        <div class="mb-3">
          <x-common-form-switch-radio title="{{ __('console/common.whether_enable') }}" name="active" :value="old('active', $page->active ?? true)" placeholder="{{ __('console/common.whether_enable') }}"/>
        </div>
      </div>
    </div>

    <div class="text-center mt-3">
      <button type="submit" class="btn btn-primary">{{ __('提交') }}</button>
    </div>
  </form>
</div> 