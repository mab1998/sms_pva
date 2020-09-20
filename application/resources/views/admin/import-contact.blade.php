@extends('admin')

{{--External Style Section--}}
@section('style')

    <style>
        .progress-bar-indeterminate {
            background: url('../assets/img/progress-bar-complete.svg') no-repeat top left;
            width: 100%;
            height: 100%;
            background-size: cover;
        }

        label.active.btn.btn-default {
            color: #ffffff !important;
            background-color: #7E57C2 !important;
            border-color: #7E57C2 !important;
        }
    </style>
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Import Contacts')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            <div class="show_notification"></div>
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Import Contact By File')}}</h3>
                        </div>
                        <div class="panel-body">

                            <div class="form-group">
                                <div class="form-group">
                                    <a href="{{url('sms/download-contact-sample-file')}}" class="btn btn-complete"><i
                                                class="fa fa-download"></i> {{language_data('Download Sample File')}}
                                    </a>
                                </div>
                            </div>
                            <div id="send-sms-file-wrapper">
                                <form class="" id="send-sms-file-form" role="form" method="post" action="{{url('sms/post-import-file-contact')}}" enctype="multipart/form-data">

                                    <div class="form-group">
                                        <label>{{language_data('Import Numbers')}}</label>
                                        <div class="form-group input-group input-group-file">
                                            <span class="input-group-btn">
                                                <span class="btn btn-primary btn-file">
                                                    {{language_data('Browse')}} <input type="file" class="form-control" name="import_numbers" @change="handleImportNumbers">
                                                </span>
                                            </span>
                                            <input type="text" class="form-control" readonly="">
                                        </div>

                                        <div id='loadingmessage' style='display:none' class="form-group">
                                            <label>{{language_data('File Uploading.. Please wait')}}</label>
                                            <div class="progress">
                                                <div class="progress-bar-indeterminate"></div>
                                            </div>
                                        </div>

                                        <div class="coder-checkbox">
                                            <input type="checkbox" name="header_exist" :checked="form.header_exist"
                                                   v-model="form.header_exist">
                                            <span class="co-check-ui"></span>
                                            <label>{{language_data('First Row As Header')}}</label>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label>{{language_data('Country Code')}}</label>
                                        <select class="selectpicker form-control" name="country_code" data-live-search="true">
                                            <option value="0" @if(app_config('send_sms_country_code') == 0) selected @endif >{{language_data('Exist on phone number')}}</option>
                                            @foreach($country_code as $code)
                                                <option value="{{$code->country_code}}" @if(app_config('send_sms_country_code') == $code->country_code) selected @endif >{{$code->country_name}} ({{$code->country_code}})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="number_columns.length > 0">
                                        <label>{{language_data('Phone Number')}} {{language_data('Column')}}</label>
                                        <select class="selectpicker form-control" ref="number_column" name="number_column" data-live-search="true" v-model="number_column">
                                            <option v-for="column in number_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="number_columns.length > 0">
                                        <label>{{language_data('Email')}} {{language_data('Address')}} {{language_data('Column')}}</label>
                                        <select class="selectpicker form-control" ref="email_address_column" name="email_address_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in number_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="number_columns.length > 0">
                                        <label>{{language_data('User name')}} {{language_data('Column')}}</label>
                                        <select class="selectpicker form-control" ref="user_name_column" name="user_name_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in number_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>


                                    <div class="form-group" v-show="number_columns.length > 0">
                                        <label>{{language_data('Company')}} {{language_data('Column')}}</label>
                                        <select class="selectpicker form-control" ref="company_column" name="company_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in number_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>


                                    <div class="form-group" v-show="number_columns.length > 0">
                                        <label>{{language_data('First name')}} {{language_data('Column')}}</label>
                                        <select class="selectpicker form-control" ref="first_name_column" name="first_name_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in number_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>

                                    <div class="form-group" v-show="number_columns.length > 0">
                                        <label>{{language_data('Last name')}} {{language_data('Column')}}</label>
                                        <select class="selectpicker form-control" ref="last_name_column" name="last_name_column" data-live-search="true">
                                            <option :value="0"></option>
                                            <option v-for="column in number_columns" :value="column.key" v-text="column.value"></option>
                                        </select>
                                    </div>


                                    <div class="form-group">
                                        <label>{{language_data('Import List into')}}</label>
                                        <select class="selectpicker form-control" data-live-search="true"
                                                name="group_name">
                                            @foreach($phone_book as $pb)
                                                <option value="{{$pb->id}}">{{$pb->group_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>


                                    <div id='uploadContact' style='display:none' class="form-group">
                                        <label>{{language_data('Contact importing.. Please wait')}}</label>
                                        <div class="progress">
                                            <div class="progress-bar-indeterminate"></div>
                                        </div>
                                    </div>

                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button type="submit" id="submitContact" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> {{language_data('Add')}} </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Import By Numbers')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('sms/post-multiple-contact')}}">


                                <div class="form-group">
                                    <label>{{language_data('Country Code')}}</label>
                                    <select class="selectpicker form-control" name="country_code" data-live-search="true">
                                        <option value="0" @if(app_config('send_sms_country_code') == 0) selected @endif >{{language_data('Exist on phone number')}}</option>
                                        @foreach($country_code as $code)
                                            <option value="{{$code->country_code}}" @if(app_config('send_sms_country_code') == $code->country_code) selected @endif >{{$code->country_name}} ({{$code->country_code}})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Paste Numbers')}}</label>
                                    <textarea class="form-control" rows="5" name="import_numbers"></textarea>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Choose delimiter')}}: </label>
                                    <div class="btn-group btn-group-sm" data-toggle="buttons">

                                        <label class="btn btn-default active">
                                            <input type="radio" name="delimiter" value="automatic" checked="">{{language_data('Automatic')}}
                                        </label>

                                        <label class="btn btn-default">
                                            <input type="radio" name="delimiter" value=";">;
                                        </label>

                                        <label class="btn btn-default">
                                            <input type="radio" name="delimiter" value=",">,
                                        </label>

                                        <label class="btn btn-default">
                                            <input type="radio" name="delimiter" value="|">|
                                        </label>

                                        <label class="btn btn-default">
                                            <input type="radio" name="delimiter" value="tab">{{language_data('Tab')}}
                                        </label>

                                        <label class="btn btn-default">
                                            <input type="radio" name="delimiter" value="new_line">{{language_data('New Line')}}
                                        </label>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('Import List into')}}</label>
                                    <select class="selectpicker form-control" data-live-search="true" name="group_name">
                                        @foreach($phone_book as $pb)
                                            <option value="{{$pb->id}}">{{$pb->group_name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> {{language_data('Add')}} </button>
                            </form>
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
    {!! Html::script("assets/js/vue.js") !!}
    {!! Html::script("assets/js/import-contact.js") !!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    <script>
        $('#submitContact').click(function(){
            $(this).hide();
            $('#uploadContact').show();
        });
    </script>
@endsection
