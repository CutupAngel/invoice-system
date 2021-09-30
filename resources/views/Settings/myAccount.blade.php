@extends ('Common.template')

@section('title', ' Settings - My Account')

@section('page.title', 'Settings')
@section('page.subtitle', 'My Account')

@section('breadcrumbs')
	Settings
	<li class="breadcrumb-item active">{{ trans('backend.myc-welcome') }}</li>
@stop

@section('content')
@if (count($errors))
	<div class="alert alert-error">
		<ul>
			@foreach ($errors->all() as $error)
				<li>{{$error}}</li>
			@endforeach
		</ul>
	</div>
@endif
    @if (!empty($status))
        <div class="alert alert-info">
            <ul>
                @foreach ($status->all() as $status)
                    <li>{{$status}}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-12 contacts" id="contacts_account">
                    <form method="post" id="form_account_info">
                        <input type="hidden" name="address_type" value="0">
                        <input type="hidden" name="address_id" value="{{ $contacts[0]['id'] }}">
                        <div class="card">
                            <div class="card-header" data-card-widget="collapse">
                                <h3 class="card-title">
    														Account Contact Information
																</h3>
                                    <div class="card-tools">
																			<button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fa fa-minus"></i></button>
                                    </div>
                            </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="top">{{ trans('backend.myc-businessname') }}</div>
                                            <div class="form-group"><input class="form-control" name="business_name" required
                                                                       value="{{$contacts[0]['business_name']}}"
                                                                       placeholder="{{ trans('backend.myc-businessname') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-contactname') }}</div>
                                            <div class="form-group"><input class="form-control" name="contact_name" required
                                                                       value="{{$contacts[0]['contact_name']}}"
                                                                       placeholder="{{ trans('backend.myc-contactname') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-phone') }}</div>
                                            <div class="form-group"><input class="form-control" name="phone" required
                                                                       value="{{$contacts[0]['phone']}}"
                                                                       placeholder="{{ trans('backend.myc-phone') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-fax') }}</div>
                                            <div class="form-group"><input class="form-control" name="fax"
                                                                       value="{{$contacts[0]['fax']}}"
                                                                       placeholder="{{ trans('backend.myc-fax') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="top">{{ trans('backend.myc-email') }}</div>
                                            <div class="form-group"><input class="form-control" name="email" required
                                                                       value="{{$contacts[0]['email']}}"
                                                                       placeholder="{{ trans('backend.myc-email') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address1') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_1" required
                                                                       value="{{$contacts[0]['address_1']}}"
                                                                       placeholder="{{ trans('backend.myc-address1') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address2') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_2"
                                                                       value="{{$contacts[0]['address_2']}}"
                                                                       placeholder="{{ trans('backend.myc-address2') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address3') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_3"
                                                                       value="{{$contacts[0]['address_3']}}"
                                                                       placeholder="{{ trans('backend.myc-address3') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address4') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_4"
                                                                       value="{{$contacts[0]['address_4']}}"
                                                                       placeholder="{{ trans('backend.myc-address4') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-city') }}</div>
                                            <div class="form-group"><input class="form-control" name="city" required
                                                                       value="{{$contacts[0]['city']}}"
                                                                       placeholder="{{ trans('backend.myc-city') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-region') }}</div>
                                            <div class="form-group">
                                                <select class="selRegion form-control" name="county_id" required>
													@foreach($countries as $index)
														@if($index->id == $contacts[0]->country_id)
															@foreach ($index->counties as $county)
																<option value="{{$county->id}}" {{ ($contacts[0]->county_id === $county->id) ? 'selected' : '' }}>{{$county->name}}</option>
															@endforeach
														@endif
													@endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-country') }}</div>
                                            <div class="form-group">
                                                <select class="selCountry form-control" name="country_id" required>
                                                    @foreach ($countries as $country)
                                                        <option value="{{$country->id}}" {{ $contacts[0]['country_id'] === $country->id ? 'selected' : '' }}>{{$country->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-postal') }}</div>
                                            <div class="form-group"><input class="form-control" name="postal_code" required
                                                                       value="{{$contacts[0]['postal_code']}}"
                                                                       placeholder="Account Postal Code"/></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="button" id="btn_form_account_info" class="btn btn-success float-right" onclick="DoSubmit('form_account_info');">
																			<i id="progress_form_account_info" class="fas fa-edit"></i> {{ trans('backend.myc-update') }}
                                    </button>
																		<div id="div_alert_form_account_info" class="alert alert-error float-left"></div>
                                </div>
                        </div>
                    </form>
                </div>

                <div class="col-md-12 contacts" id="contacts_mailing">
                    <form method="post" id="form_contacts_mailing">
                        <input type="hidden" name="address_type" value="1">
                        <input type="hidden" name="address_id" value="{{ $contacts[1]['id'] }}">
                        <div class="card collapsed-card">
                            <div class="card-header" data-card-widget="collapse">
                                <h3 class="card-title">
																	{{ trans('backend.myc-mailing') }}
																</h3>
                                    <div class="card-tools">
																			<button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                                    </div>
                                </a>
                            </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-businessname') }}</div>
                                            <div class="form-group"><input class="form-control" name="business_name" required
                                                                       value="{{$contacts[1]['business_name']}}"
                                                                       placeholder="{{ trans('backend.myc-businessname') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-contactname') }}</div>
                                            <div class="form-group"><input class="form-control" name="contact_name" required
                                                                       value="{{$contacts[1]['contact_name']}}"
                                                                       placeholder="{{ trans('backend.myc-contactname') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-phone') }}</div>
                                            <div class="form-group"><input class="form-control" name="phone" required
                                                                       value="{{$contacts[1]['phone']}}"
                                                                       placeholder="{{ trans('backend.myc-phone') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-fax') }}</div>
                                            <div class="form-group"><input class="form-control" name="fax"
                                                                       value="{{$contacts[1]['fax']}}"
                                                                       placeholder="{{ trans('backend.myc-fax') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="top">{{ trans('backend.myc-email') }}</div>
                                            <div class="form-group"><input class="form-control" name="email" required
                                                                       value="{{$contacts[1]['email']}}"
                                                                       placeholder="{{ trans('backend.myc-email') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address1') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_1"
                                                                       value="{{$contacts[1]['address_1']}}"
                                                                       placeholder="{{ trans('backend.myc-address1') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address2') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_2"
                                                                       value="{{$contacts[1]['address_2']}}"
                                                                       placeholder="{{ trans('backend.myc-address2') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address3') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_3"
                                                                       value="{{$contacts[1]['address_3']}}"
                                                                       placeholder="{{ trans('backend.myc-address3') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address4') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_4"
                                                                       value="{{$contacts[1]['address_4']}}"
                                                                       placeholder="{{ trans('backend.myc-address4') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-city') }}</div>
                                            <div class="form-group"><input class="form-control" name="city"
                                                                       value="{{$contacts[1]['city']}}"
                                                                       placeholder="{{ trans('backend.myc-city') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-region') }}</div>
                                            <div class="form-group">
                                                <select class="selRegion form-control" name="county_id">
													@foreach($countries as $index)
														@if($index->id == $contacts[1]->country_id)
															@foreach ($index->counties as $county)
																<option value="{{$county->id}}" {{ ($contacts[1]->county_id === $county->id) ? 'selected' : '' }}>{{$county->name}}</option>
															@endforeach
														@endif
													@endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-country') }}</div>
                                            <div class="form-group">
                                                <select class="selCountry form-control" name="country_id">
                                                    @foreach ($countries as $country)
                                                        <option value="{{$country->id}}" {{ $contacts[1]->country_id === $country->id ? 'selected' : '' }}>{{$country->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-postal') }}</div>
                                            <div class="form-group"><input class="form-control" name="postal_code"
                                                                       value="{{$contacts[1]['postal_code']}}"
                                                                       placeholder="{{ trans('backend.myc-postal') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="button" id="btn_form_contacts_mailing" class="btn btn-success float-right" onclick="DoSubmit('form_contacts_mailing');">
																			<i id="progress_form_contacts_mailing" class="fas fa-edit"></i> {{ trans('backend.myc-update') }}
                                    </button>
																		<div id="div_alert_form_contacts_mailing" class="alert alert-error float-left"></div>
                                </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-12 contacts" id="contacts_billing">
                    <form method="post" id="form_contacts_billing">
                        <input type="hidden" name="address_type" value="2">
                        <input type="hidden" name="address_id" value="{{ $contacts[2]['id'] }}">
												<div class="card collapsed-card">
												    <div class="card-header" data-card-widget="collapse">
												        <h3 class="card-title">
                                <h3 class="card-title">{{ trans('backend.myc-billing') }}</h3>
																<div class="card-tools">
																	<button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
																</div>
                            </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-businessname') }}</div>
                                            <div class="form-group"><input class="form-control" name="business_name" required
                                                                       value="{{$contacts[2]['business_name']}}"
                                                                       placeholder="{{ trans('backend.myc-businessname') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-contactname') }}</div>
                                            <div class="form-group"><input class="form-control" name="contact_name" required
                                                                       value="{{$contacts[2]['contact_name']}}"
                                                                       placeholder="{{ trans('backend.myc-contactname') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-phone') }}</div>
                                            <div class="form-group"><input class="form-control" name="phone" required
                                                                       value="{{$contacts[2]['phone']}}"
                                                                       placeholder="{{ trans('backend.myc-phone') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-fax') }}</div>
                                            <div class="form-group"><input class="form-control" name="fax"
                                                                       value="{{$contacts[2]['fax']}}"
                                                                       placeholder="{{ trans('backend.myc-fax') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="top">{{ trans('backend.myc-email') }}</div>
                                            <div class="form-group"><input class="form-control" name="email" required
                                                                       value="{{$contacts[2]['email']}}"
                                                                       placeholder="{{ trans('backend.myc-email') }}"/>
                                            </div>
                                        </div>
                                        <div class="fakeborder"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address1') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_1"
                                                                       value="{{$contacts[2]['address_1']}}"
                                                                       placeholder="{{ trans('backend.myc-address1') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address2') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_2"
                                                                       value="{{$contacts[2]['address_2']}}"
                                                                       placeholder="{{ trans('backend.myc-address2') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address3') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_3"
                                                                       value="{{$contacts[2]['address_3']}}"
                                                                       placeholder="{{ trans('backend.myc-address3') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address4') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_4"
                                                                       value="{{$contacts[2]['address_4']}}"
                                                                       placeholder="{{ trans('backend.myc-address4') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-city') }}</div>
                                            <div class="form-group"><input class="form-control" name="city"
                                                                       value="{{$contacts[2]['city']}}"
                                                                       placeholder="{{ trans('backend.myc-city') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-region') }}</div>
                                            <div class="form-group">
                                                <select class="selRegion form-control" name="county_id">
													@foreach($countries as $index)
														@if($index->id == $contacts[2]->country_id)
															@foreach ($index->counties as $county)
																<option value="{{$county->id}}" {{ ($contacts[2]->county_id === $county->id) ? 'selected' : '' }}>{{$county->name}}</option>
															@endforeach
														@endif
													@endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-country') }}</div>
                                            <div class="form-group">
                                                <select class="selCountry form-control" name="country_id">
                                                    @foreach ($countries as $country)
                                                        <option value="{{$country->id}}" {{ $contacts[2]->country_id === $country->id ? 'selected' : '' }}>{{$country->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-postal') }}</div>
                                            <div class="form-group"><input class="form-control" name="postal_code"
                                                                       value="{{$contacts[2]['postal_code']}}"
                                                                       placeholder="{{ trans('backend.myc-postal') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="button" id="btn_form_contacts_billing" class="btn btn-success float-right" onclick="DoSubmit('form_contacts_billing');">
																			<i id="progress_form_contacts_billing" class="fas fa-edit"></i> {{ trans('backend.myc-update') }}
																		</button>
																	<div id="div_alert_form_contacts_billing" class="alert alert-error float-left"></div>
																</div>
                        </div>
                    </form>
                </div>
                <div class="col-md-12 contacts" id="contacts_admin">
                    <form method="post" id="form_contacts_admin">
                        <input type="hidden" name="address_type" value="3">
                        <input type="hidden" name="address_id" value="{{ $contacts[3]['id'] }}">
												<div class="card collapsed-card">
												    <div class="card-header" data-card-widget="collapse">
												        <h3 class="card-title">
																	{{ trans('backend.myc-admin') }}
																</h3>
																<div class="card-tools">
																	<button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
																</div>
                            </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-businessname') }}</div>
                                            <div class="form-group"><input class="form-control" name="business_name" required
                                                                       value="{{$contacts[3]['business_name']}}"
                                                                       placeholder="{{ trans('backend.myc-businessname') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-contactname') }}</div>
                                            <div class="form-group"><input class="form-control" name="contact_name" required
                                                                       value="{{$contacts[3]['contact_name']}}"
                                                                       placeholder="{{ trans('backend.myc-contactname') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-phone') }}</div>
                                            <div class="form-group"><input class="form-control" name="phone" required
                                                                       value="{{$contacts[3]['phone']}}"
                                                                       placeholder="{{ trans('backend.myc-phone') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-fax') }}</div>
                                            <div class="form-group"><input class="form-control" name="fax"
                                                                       value="{{$contacts[3]['fax']}}"
                                                                       placeholder="{{ trans('backend.myc-fax') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="top">{{ trans('backend.myc-email') }}</div>
                                            <div class="form-group"><input class="form-control" name="email" required
                                                                       value="{{$contacts[3]['email']}}"
                                                                       placeholder="{{ trans('backend.myc-email') }}"/>
                                            </div>
                                        </div>
                                        <div class="fakeborder"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address1') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_1"
                                                                       value="{{$contacts[3]['address_2']}}"
                                                                       placeholder="{{ trans('backend.myc-address1') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address2') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_2"
                                                                       value="{{$contacts[3]['address_2']}}"
                                                                       placeholder="{{ trans('backend.myc-address2') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address3') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_3"
                                                                       value="{{$contacts[3]['address_3']}}"
                                                                       placeholder="{{ trans('backend.myc-address3') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address4') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_4"
                                                                       value="{{$contacts[3]['address_4']}}"
                                                                       placeholder="{{ trans('backend.myc-address4') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-city') }}</div>
                                            <div class="form-group"><input class="form-control" name="city"
                                                                       value="{{$contacts[3]['city']}}"
                                                                       placeholder="{{ trans('backend.myc-city') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-region') }}</div>
                                            <div class="form-group">
                                                <select class="selRegion form-control" name="county_id">
													@foreach($countries as $index)
														@if($index->id == $contacts[3]->country_id)
															@foreach ($index->counties as $county)
																<option value="{{$county->id}}" {{ ($contacts[3]->county_id === $county->id) ? 'selected' : '' }}>{{$county->name}}</option>
															@endforeach
														@endif
													@endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-country') }}</div>
                                            <div class="form-group">
                                                <select class="selCountry form-control" name="country_id">
                                                    @foreach ($countries as $country)
                                                        <option value="{{$country->id}}" {{ $contacts[3]->country_id === $country->id ? 'selected' : '' }}>{{$country->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-postal') }}</div>
                                            <div class="form-group"><input class="form-control" name="postal_code"
                                                                       value="{{$contacts[3]['postal_code']}}"
                                                                       placeholder="{{ trans('backend.myc-postal') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" id="btn_form_contacts_admin" class="btn btn-success float-right" onclick="DoSubmit('form_contacts_admin');">
																			<i id="progress_form_contacts_admin" class="fas fa-edit"></i> {{ trans('backend.myc-update') }}
                                    </button>
																		<div id="div_alert_form_contacts_admin" class="alert alert-error float-left"></div>
																</div>
                        </div>
                    </form>
                </div>
                <div class="col-md-12 contacts" id="contacts_tech">
                    <form method="post" id="form_contacts_tech">
                        <input type="hidden" name="address_type" value="4">
                        <input type="hidden" name="address_id" value="{{ $contacts[4]['id'] }}">
												<div class="card collapsed-card">
												    <div class="card-header" data-card-widget="collapse">
															<h3 class="card-title">
																{{ trans('backend.myc-tech') }}
															</h3>
															<div class="card-tools">
																<button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
															</div>
                            </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-businessname') }}</div>
                                            <div class="form-group"><input class="form-control" name="business_name" required
                                                                       value="{{$contacts[4]['business_name']}}"
                                                                       placeholder="{{ trans('backend.myc-businessname') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-contactname') }}</div>
                                            <div class="form-group"><input class="form-control" name="contact_name" required
                                                                       value="{{$contacts[4]['contact_name']}}"
                                                                       placeholder="{{ trans('backend.myc-contactname') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-phone') }}</div>
                                            <div class="form-group"><input class="form-control" name="phone" required
                                                                       value="{{$contacts[4]['phone']}}"
                                                                       placeholder="{{ trans('backend.myc-phone') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-fax') }}</div>
                                            <div class="form-group"><input class="form-control" name="fax"
                                                                       value="{{$contacts[4]['fax']}}"
                                                                       placeholder="{{ trans('backend.myc-fax') }}"/>
                                            </div>
                                        </div>
                                        <div class="fakeborder"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="top">{{ trans('backend.myc-email') }}</div>
                                            <div class="form-group"><input class="form-control" name="email" required
                                                                       value="{{$contacts[4]['email']}}"
                                                                       placeholder="{{ trans('backend.myc-email') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address1') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_1"
                                                                       value="{{$contacts[4]['address_1']}}"
                                                                       placeholder="{{ trans('backend.myc-address1') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address2') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_2"
                                                                       value="{{$contacts[4]['address_2']}}"
                                                                       placeholder="{{ trans('backend.myc-address2') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address3') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_3"
                                                                       value="{{$contacts[4]['address_3']}}"
                                                                       placeholder="{{ trans('backend.myc-address3') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-address4') }}</div>
                                            <div class="form-group"><input class="form-control" name="address_4"
                                                                       value="{{$contacts[4]['address_4']}}"
                                                                       placeholder="{{ trans('backend.myc-address4') }}"/>
                                            </div>
                                        </div>
                                        <div class="fakeborder"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-city') }}</div>
                                            <div class="form-group"><input class="form-control" name="city"
                                                                       value="{{$contacts[4]['city']}}"
                                                                       placeholder="{{ trans('backend.myc-city') }}"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-region') }}</div>
                                            <div class="form-group">
                                                <select class="selRegion form-control" name="county_id">
													@foreach($countries as $index)
														@if($index->id == $contacts[4]->country_id)
															@foreach ($index->counties as $county)
																<option value="{{$county->id}}" {{ ($contacts[4]->county_id === $county->id) ? 'selected' : '' }}>{{$county->name}}</option>
															@endforeach
														@endif
													@endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-country') }}</div>
                                            <div class="form-group">
                                                <select class="selCountry form-control" name="country_id">
                                                    @foreach ($countries as $country)
                                                        <option value="{{$country->id}}" {{ $contacts[4]->country_id === $country->id ? 'selected' : '' }}>{{$country->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="top">{{ trans('backend.myc-postal') }}</div>
                                            <div class="form-group"><input class="form-control" name="postal_code"
                                                                       value="{{$contacts[4]['postal_code']}}"
                                                                       placeholder="{{ trans('backend.myc-postal') }}"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" id="btn_form_contacts_tech" class="btn btn-success float-right" onclick="DoSubmit('form_contacts_tech');">
																			<i id="progress_form_contacts_tech" class="fas fa-edit"></i> {{ trans('backend.myc-update') }}
                                    </button>
																		<div id="div_alert_form_contacts_tech" class="alert alert-error float-left"></div>
																</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-12 center">
                    <div class="card" id="account_info">
                        <div class="card-header with-border">
                            <h3 class="card-title">{{ trans('backend.myc-bsaccount') }}</h3>
                        </div>
											<div class="card-body">
                        <div class="card-header with-border">
                          <div class="row">
														<div class="col-sm-12 center-block">
															<div class="form-group">
                                <a href="/settings/myaccount/gdpr-download">
                                <button class="btn btn-primary">
                                    <i class="fa fa-download"></i> {{ trans('backend.myc-bsgdrpdowload') }}
                                </button>
                                </a>
                            </div>
													</div>
												</div>
                      </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="row">
                                        @if($user->isCustomer())
                                            <div class="col-lg-6">
                                                <div class="row">
                                                    <div class="col-xs-3">
                                                        <strong>{{ trans('backend.myc-username') }}:</strong>&nbsp;
																										</div>
                                                    <div class="col-xs-3">{{$user->username}}</div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="row">
                                                    <div class="col-xs-3">
                                                        <strong>{{ trans('backend.myc-2fa') }}:</strong>&nbsp;
																										</div>
                                                    <div class="col-xs-3">{{ ($user->has2fa() ? 'Yes' : 'No') }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="col-lg-6">
                                                <div class="row">
                                                    <div class="col-xs-3">
                                                        <strong> {{ trans('backend.myc-username') }}:</strong>&nbsp;
																										</div>
                                                    <div class="col-xs-3">
																											{{$user->username}}
																										</div>
																								</div>
																								<div class="row">
                                                    <div class="col-xs-3">
                                                        <strong> {{ trans('backend.myc-2fa') }}:</strong>&nbsp;
																										</div>
                                                    <div class="col-xs-3"> {{ ($user->has2fa() ? 'Yes' : 'No') }}</div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="row">
                                                    <div class="col-xs-3">
                                                        <strong>{{ trans('backend.myc-sitename') }}:</strong>&nbsp;
																										</div>
                                                    <div class="col-xs-3">
																											{{ $user->siteSettings('name') }}
																										</div>
																								</div>
																								 <div class="row">
																									 <div class="col-xs-3">
																											 <strong>{{ trans('backend.myc-currency') }}:</strong>&nbsp;
																									 </div>
																									 <div class="col-xs-3">
																										 @php
																											 $currency_id = $user->siteSettings('defaultCurrency');
																											 $currency = \App\Currency::find($currency_id);
																										 @endphp
																										 @if($currency)
																											 {{ $currency->short_name }}
																										 @endif
																									 </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
											</div>
                        <div class="card-footer">
                            @if($user->isCustomer())
                                <a href="/settings/myaccount/edit">
                                    <button class="btn btn-success float-right"> {{ trans('backend.myc-change') }}
                                    </button>
                                </a>
                            @else
                                <a href="/settings/my-account/edit">
                                    <button class="btn btn-success float-right"> {{ trans('backend.myc-change') }}
                                    </button>
                                </a>
                            @endif
											</div>
                    </div>
                </div>
            </div>
        </div>

				<div class="col-md-4">
            <div class="row">
                <div class="col-md-12 center">
                    <div class="card" id="api_key_info">
                        <div class="card-header with-border">
                            <h3 class="card-title">{{ trans('backend.myc-api') }}</h3>
                        </div>
											<div class="card-body">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="row">
																			<label>{{ trans('backend.myc-sandbox-api') }}</label>
																			<input type="text" id="sandbox_api_key" class="form-control" value="{{ $user->sandbox_api_key }}" readonly />
                                    </div>
																		<div class="row">
																			<label>{{ trans('backend.myc-live-api') }}</label>
																			<input type="text" id="live_api_key" class="form-control" value="{{ $user->live_api_key }}" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
											</div>
                        <div class="card-footer">
                            <a href="#" class="btn btn-success" onclick="GenerateApiKey();">Generate API Key</a>
											</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@stop

@section('javascript')
<script>
	function DoSubmit(form_name) {
		var $form = $('#' + form_name);

		var $button = $('#btn_' + form_name);
		var $progress = $('#progress_' + form_name);
		var	$div_error = $('#div_alert_' + form_name);

		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/settings/my-account',
			type: 'POST',
			dataType: 'json',
			beforeSend: function() {
				$button.addClass('btn-danger').removeClass('btn-success').prop('disabled', true);
				$progress.addClass('fa-circle-notch fa-spin').removeClass('fa-edit fa-check');
			},
			data: $form.serialize()+'&action=contacts',
			success: function(data) {
				if(data.success)
				{
					$div_error.html('');
					$progress.addClass('fa-check').removeClass('fa-circle-notch fa-spin');
					$button.removeClass('btn-danger').addClass('btn-success').prop('disabled', false);
				}
				else
				{
					var errorList = '';
					$.each(data.errors, function(index, val) {
						errorList = errorList + '<li>'+ val +'</li>';
					});
					$div_error.html(errorList);
					$progress.addClass('fa-edit').removeClass('fa-circle-notch fa-spin');
				  $button.removeClass('btn-danger').addClass('btn-success').prop('disabled', false);
				}
			},
			error: function(errors) {

			}
		});
	}

	$('.collapse').on('shown.bs.collapse', function(){
		$(this).parent().find(".card-header .glyphicon-plus").removeClass("glyphicon-plus").addClass("glyphicon-minus");
	}).on('hidden.bs.collapse', function(){
		$(this).parent().find(".card-header .glyphicon-minus").removeClass("glyphicon-minus").addClass("glyphicon-plus");
	});


	$('.selCountry').change(function(){
		getCounties(this);
	});

	function getCounties(e)
	{
		$.ajax({
			headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
			url: '/settings/my-account',
			type: 'POST',
			dataType: 'json',
			data: 'country='+$(e).val()+'&action=get_region',
			success: function(data) {
				$(e).parent().parent().prev().find('.selRegion').empty().append(data.options);
			},
			fail: function(errors) {

			}
		});
	}

	function GenerateApiKey()
	{
			$.ajax({
				headers: { 'X-CSRF-Token': '{{csrf_token()}}'},
				url: '/settings/my-account/generate-api-key',
				type: 'POST',
				dataType: 'json',
				success: function(data) {
					$('#sandbox_api_key').val(data.sandbox_api_key);
					$('#live_api_key').val(data.live_api_key);
				},
				fail: function(errors) {

				}
			});
	}
</script>
@stop

@section('css')
<style>
	.col-md-12.contacts .card-body .row{
		border-form-group: 1px solid #F4F4F4;
		padding-top:8px;
		padding-form-group:8px;
	}
	.fakeborder{
		background-color: #F4F4F4;
		padding:0;
		margin:0;
		height:1px;
		width:100%;
		position: relative;
		top: 34px;
	}
	.top{
		font-weight:bold;
		height:36px;
		text-align:center;
	}
	.form-group{
		color:#777;
		height:36px;
	}
	input[type=text],select{
		border-color:transparent;
		color:#777;
		width:100%;
		text-align:left;
	}
	.plan .col-xs-3{
		vertical-align:form-group;
	}
	.left{
		text-align:left;
	}
	.center{
		text-align:center;
	}
	@media (max-width: 768px) {
		.xs-center {
			text-align: center;
		}
		.fakeborder{
			display:none;
		}
	}
	#account_info strong{
		text-decoration: underline;
	}
	@media (min-width: 768px) {
		.plan strong{
			text-decoration: underline;
		}
		.fakeborder{
			background-color: #F4F4F4;
			padding:0;
			margin:0;
			height:1px;
			width:100%;
			position: relative;
			top: 34px;
		}
	}
	.card-header a{
		color:#333;
	}
	.todo-list > li .label {
		font-size:10px;
	}
	#account_info .card-body div{
		word-break:break-all;
	}
</style>
@stop
