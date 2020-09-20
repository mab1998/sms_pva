@extends('admin')
<style>
    .progress-bar-indeterminate {
        background: url('../assets/img/progress-bar-complete.svg') no-repeat top left;
        width: 100%;
        height: 100%;
        background-size: cover;
    }
</style>

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Export and Import Clients')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            <div class="show_notification"></div>
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Export Clients')}}</h3>
                        </div>
                        <div class="panel-body">
                            <ul class="info-list">
                                <li>
                                    <span class="info-list-title">{{language_data('Export Clients')}}</span><span class="info-list-des"><a href="{{url('clients/export-clients')}}" class="btn btn-success btn-xs">{{language_data('Export Clients as CSV')}}</a></span>
                                </li>
                                <li>
                                    <span class="info-list-title">{{language_data('Sample File')}}</span><span class="info-list-des"><a href="{{url('clients/download-sample-csv')}}" class="btn btn-complete btn-xs">{{language_data('Download Sample File')}}</a> </span>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Import Clients')}}</h3>
                        </div>
                        <div class="panel-body">
                            <div id="import-clients">
                                <form id="import-client-from-file" role="form" method="post" action="{{url('clients/post-new-client-csv')}}" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label>{{language_data('Client Group')}}</label>
                                        <select class="selectpicker form-control" name="client_group"  data-live-search="true">
                                            <option value="0">{{language_data('None')}}</option>
                                            @foreach($client_groups as $cg)
                                                <option value="{{$cg->id}}">{{$cg->group_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>{{language_data('SMS Gateway')}}</label>
                                        <select class="selectpicker form-control" name="sms_gateway[]"  data-live-search="true" multiple>
                                            @foreach($sms_gateways as $sg)
                                                <option value="{{$sg->id}}">{{$sg->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>


                                    <div class="form-group">
                                        <label>{{language_data('Reseller Panel')}}</label>
                                        <select class="selectpicker form-control" name="reseller_panel">
                                            <option value="Yes">{{language_data('Yes')}}</option>
                                            <option value="No">{{language_data('No')}}</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>{{language_data('Api Access')}}</label>
                                        <select class="selectpicker form-control" name="api_access">
                                            <option value="Yes">{{language_data('Yes')}}</option>
                                            <option value="No">{{language_data('No')}}</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>{{language_data('Import Clients')}}</label>
                                        <div class="form-group input-group input-group-file">
                                            <span class="input-group-btn">
                                                <span class="btn btn-primary btn-file">
                                                {{language_data('Browse')}} <input type="file" class="form-control" name="import_client" @change="handleImportClients">
                                                </span>
                                            </span>
                                            <input type="text" class="form-control" readonly="">
                                        </div>
                                    </div>

                                    <div id='loadingmessage' style='display:none' class="form-group">
                                        <label>{{language_data('File Uploading.. Please wait')}}</label>
                                        <div class="progress">
                                            <div class="progress-bar-indeterminate"></div>
                                        </div>
                                    </div>

                                    <div class="coder-checkbox">
                                        <input type="checkbox" name="header_exist" :checked="form.header_exist" v-model="form.header_exist">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('First Row As Header')}}</label>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('First name')}} {{language_data('Column')}}</label>
                                        <span class="help">Required</span>
                                        <select class="selectpicker form-control" ref="first_name_column" name="first_name_column" data-live-search="true" v-model="first_name_column">
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Last name')}} {{language_data('Column')}}</label>
                                        <select class="selectpicker form-control" ref="last_name_column" name="last_name_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Company')}} {{language_data('Column')}}</label>
                                        <select class="selectpicker form-control" ref="company_column" name="company_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Website')}} {{language_data('Column')}}</label>
                                        <select class="selectpicker form-control" ref="website_column" name="website_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Email')}} {{language_data('Address')}} {{language_data('Column')}}</label>
                                        <span class="help">Required</span>
                                        <select class="selectpicker form-control" ref="email_address_column" name="email_address_column" data-live-search="true" v-model="email_address_column">
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('User name')}} {{language_data('Column')}}</label>
                                        <span class="help">Required</span>
                                        <select class="selectpicker form-control" ref="user_name_column" name="user_name_column" data-live-search="true" v-model="user_name_column">
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Password')}} {{language_data('Column')}}</label>
                                        <span class="help">Required</span>
                                        <select class="selectpicker form-control" ref="password_column" name="password_column" data-live-search="true" v-model="password_column">
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Phone Number')}} {{language_data('Column')}}</label>
                                        <span class="help">Required</span>
                                        <select class="selectpicker form-control" ref="number_column" name="number_column" data-live-search="true" v-model="number_column">
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Address')}} {{language_data('Column')}}</label>
                                        <select class="selectpicker form-control" ref="address_column" name="address_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('More Address')}} {{language_data('Column')}}</label>
                                        <select class="selectpicker form-control" ref="more_address_column" name="more_address_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('State')}} {{language_data('Column')}}</label>
                                        <select class="selectpicker form-control" ref="state_column" name="state_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('City')}} {{language_data('Column')}}</label>
                                        <select class="selectpicker form-control" ref="city_column" name="city_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Postcode')}} {{language_data('Column')}}</label>
                                        <select class="selectpicker form-control" ref="postcode_column" name="postcode_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Country')}} {{language_data('Column')}}</label>
                                        <span class="help">Required</span>
                                        <select class="selectpicker form-control" ref="country_column" name="country_column" data-live-search="true" v-model="country_column">
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('SMS Limit')}} {{language_data('Column')}}</label>
                                        <span class="help">Required</span>
                                        <select class="selectpicker form-control" ref="sms_limit_column" name="sms_limit_column" data-live-search="true" v-model="sms_limit_column">
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div id='uploadContact' style='display:none' class="form-group">
                                        <label>{{language_data('Contact importing.. Please wait')}}</label>
                                        <div class="progress">
                                            <div class="progress-bar-indeterminate"></div>
                                        </div>
                                    </div>

                                    <br>
                                    <p class="text-uppercase text-complete help">{{language_data('It will take few minutes. Please do not reload the page')}}</p>
                                    <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-download"></i> {{language_data('Import')}} </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

@endsection

{{--External Style Section--}}
@section('script')
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    {!! Html::script("assets/js/vue.js") !!}
    {!! Html::script("assets/js/import_client.js") !!}
@endsection
