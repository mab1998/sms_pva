@extends('client')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Export and Import Clients',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            <div class="show_notification"></div>
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Export Clients',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">
                            <ul class="info-list">
                                <li>
                                    <span class="info-list-title">{{language_data('Export Clients',Auth::guard('client')->user()->lan_id)}}</span><span class="info-list-des"><a href="{{url('user/export-user')}}" class="btn btn-success btn-xs">{{language_data('Export Clients as CSV',Auth::guard('client')->user()->lan_id)}}</a></span>
                                </li>
                                <li>
                                    <span class="info-list-title">{{language_data('Sample File',Auth::guard('client')->user()->lan_id)}}</span><span class="info-list-des"><a href="{{url('user/download-sample-csv')}}" class="btn btn-complete btn-xs">{{language_data('Download Sample File',Auth::guard('client')->user()->lan_id)}}</a> </span>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Import Clients',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">
                            <div id="import-clients">
                                <form id="import-client-from-file"  role="form" method="post" action="{{url('user/post-new-user-csv')}}" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label>{{language_data('Client Group',Auth::guard('client')->user()->lan_id)}}</label>
                                        <select class="selectpicker form-control" name="client_group"  data-live-search="true">
                                            <option value="0">{{language_data('None',Auth::guard('client')->user()->lan_id)}}</option>
                                            @foreach($client_groups as $cg)
                                                <option value="{{$cg->id}}">{{$cg->group_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>{{language_data('SMS Gateway',Auth::guard('client')->user()->lan_id)}}</label>
                                        <select class="selectpicker form-control" name="sms_gateway[]"  data-live-search="true" multiple>
                                            @foreach($sms_gateways as $sg)
                                                <option value="{{$sg->id}}">{{$sg->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>


                                    @if(\Auth::guard('client')->user()->reseller=='Yes')
                                        <div class="form-group">
                                            <label>{{language_data('Reseller Panel',Auth::guard('client')->user()->lan_id)}}</label>
                                            <select class="selectpicker form-control" name="reseller_panel">
                                                <option value="Yes">{{language_data('Yes',Auth::guard('client')->user()->lan_id)}}</option>
                                                <option value="No">{{language_data('No',Auth::guard('client')->user()->lan_id)}}</option>
                                            </select>
                                        </div>
                                    @endif


                                    @if(\Auth::guard('client')->user()->api_access=='Yes')
                                        <div class="form-group">
                                            <label>{{language_data('Api Access',Auth::guard('client')->user()->lan_id)}}</label>
                                            <select class="selectpicker form-control" name="api_access">
                                                <option value="Yes">{{language_data('Yes',Auth::guard('client')->user()->lan_id)}}</option>
                                                <option value="No">{{language_data('No',Auth::guard('client')->user()->lan_id)}}</option>
                                            </select>
                                        </div>
                                    @endif


                                    <div class="form-group">
                                        <label>{{language_data('Import Clients',Auth::guard('client')->user()->lan_id)}}</label>
                                        <div class="form-group input-group input-group-file">
                                            <span class="input-group-btn">
                                                <span class="btn btn-primary btn-file">
                                                    {{language_data('Browse',Auth::guard('client')->user()->lan_id)}} <input type="file" class="form-control" name="import_client" @change="handleImportClients">
                                                </span>
                                            </span>
                                            <input type="text" class="form-control" readonly="">
                                        </div>
                                    </div>



                                    <div id='loadingmessage' style='display:none' class="form-group">
                                        <label>{{language_data('File Uploading.. Please wait',Auth::guard('client')->user()->lan_id)}}</label>
                                        <div class="progress">
                                            <div class="progress-bar-indeterminate"></div>
                                        </div>
                                    </div>

                                    <div class="coder-checkbox">
                                        <input type="checkbox" name="header_exist" :checked="form.header_exist" v-model="form.header_exist">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('First Row As Header',Auth::guard('client')->user()->lan_id)}}</label>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('First name',Auth::guard('client')->user()->lan_id)}} {{language_data('Column',Auth::guard('client')->user()->lan_id)}}</label>
                                        <span class="help">Required</span>
                                        <select class="selectpicker form-control" ref="first_name_column" name="first_name_column" data-live-search="true" v-model="first_name_column">
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Last name',Auth::guard('client')->user()->lan_id)}} {{language_data('Column',Auth::guard('client')->user()->lan_id)}}</label>
                                        <select class="selectpicker form-control" ref="last_name_column" name="last_name_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Company',Auth::guard('client')->user()->lan_id)}} {{language_data('Column',Auth::guard('client')->user()->lan_id)}}</label>
                                        <select class="selectpicker form-control" ref="company_column" name="company_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Website',Auth::guard('client')->user()->lan_id)}} {{language_data('Column',Auth::guard('client')->user()->lan_id)}}</label>
                                        <select class="selectpicker form-control" ref="website_column" name="website_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Email',Auth::guard('client')->user()->lan_id)}} {{language_data('Address',Auth::guard('client')->user()->lan_id)}} {{language_data('Column',Auth::guard('client')->user()->lan_id)}}</label>
                                        <span class="help">Required</span>
                                        <select class="selectpicker form-control" ref="email_address_column" name="email_address_column" data-live-search="true" v-model="email_address_column">
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('User name',Auth::guard('client')->user()->lan_id)}} {{language_data('Column',Auth::guard('client')->user()->lan_id)}}</label>
                                        <span class="help">Required</span>
                                        <select class="selectpicker form-control" ref="user_name_column" name="user_name_column" data-live-search="true" v-model="user_name_column">
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Password',Auth::guard('client')->user()->lan_id)}} {{language_data('Column',Auth::guard('client')->user()->lan_id)}}</label>
                                        <span class="help">Required</span>
                                        <select class="selectpicker form-control" ref="password_column" name="password_column" data-live-search="true" v-model="password_column">
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Phone Number',Auth::guard('client')->user()->lan_id)}} {{language_data('Column',Auth::guard('client')->user()->lan_id)}}</label>
                                        <span class="help">Required</span>
                                        <select class="selectpicker form-control" ref="number_column" name="number_column" data-live-search="true" v-model="number_column">
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Address',Auth::guard('client')->user()->lan_id)}} {{language_data('Column',Auth::guard('client')->user()->lan_id)}}</label>
                                        <select class="selectpicker form-control" ref="address_column" name="address_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('More Address',Auth::guard('client')->user()->lan_id)}} {{language_data('Column',Auth::guard('client')->user()->lan_id)}}</label>
                                        <select class="selectpicker form-control" ref="more_address_column" name="more_address_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('State',Auth::guard('client')->user()->lan_id)}} {{language_data('Column',Auth::guard('client')->user()->lan_id)}}</label>
                                        <select class="selectpicker form-control" ref="state_column" name="state_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('City',Auth::guard('client')->user()->lan_id)}} {{language_data('Column',Auth::guard('client')->user()->lan_id)}}</label>
                                        <select class="selectpicker form-control" ref="city_column" name="city_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Postcode',Auth::guard('client')->user()->lan_id)}} {{language_data('Column',Auth::guard('client')->user()->lan_id)}}</label>
                                        <select class="selectpicker form-control" ref="postcode_column" name="postcode_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('Country',Auth::guard('client')->user()->lan_id)}} {{language_data('Column',Auth::guard('client')->user()->lan_id)}}</label>
                                        <span class="help">Required</span>
                                        <select class="selectpicker form-control" ref="country_column" name="country_column" data-live-search="true" v-model="country_column">
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="client_columns.length > 0">
                                        <label>{{language_data('SMS Limit',Auth::guard('client')->user()->lan_id)}} {{language_data('Column',Auth::guard('client')->user()->lan_id)}}</label>
                                        <span class="help">Required</span>
                                        <select class="selectpicker form-control" ref="sms_limit_column" name="sms_limit_column" data-live-search="true" v-model="sms_limit_column">
                                            <option v-for="column in client_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div id='uploadContact' style='display:none' class="form-group">
                                        <label>{{language_data('Contact importing.. Please wait',Auth::guard('client')->user()->lan_id)}}</label>
                                        <div class="progress">
                                            <div class="progress-bar-indeterminate"></div>
                                        </div>
                                    </div>


                                    <p class="text-uppercase text-complete help">{{language_data('It will take few minutes. Please do not reload the page',Auth::guard('client')->user()->lan_id)}}</p>
                                    <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-download"></i> {{language_data('Import',Auth::guard('client')->user()->lan_id)}} </button>
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
