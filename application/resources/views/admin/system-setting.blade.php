@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/bootstrap3-wysihtml5-bower/bootstrap3-wysihtml5.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('System Settings')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-12">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#system-settings" aria-controls="home" role="tab" data-toggle="tab">{{language_data('System Settings')}}</a></li>
                        <li role="presentation"><a href="#system_email" aria-controls="system_email" role="tab" data-toggle="tab">{{language_data('System Email')}}</a></li>
                        <li role="presentation"><a href="#sms_settings" aria-controls="sms_settings" role="tab" data-toggle="tab">{{language_data('SMS Settings')}}</a></li>
                        <li role="presentation"><a href="#auth_settings" aria-controls="auth_settings" role="tab" data-toggle="tab">{{language_data('Authentication Settings')}}</a></li>
                    </ul>

                    <!-- Tab panes -->
                </div>
                <div class="col-lg-12">
                    <div class="tab-content panel p-20">
                        <div role="tabpanel" class="tab-pane active" id="system-settings">

                            <form class="" role="form" action="{{url('settings/post-general-setting')}}" method="post" enctype="multipart/form-data">

                                <div class="row">
                                    <div class="col-md-7">
                                        {{ csrf_field() }}
                                        <div class="form-group">
                                            <label>{{language_data('Application Name')}}</label>
                                            <input type="text" class="form-control" required name="app_name" value="{{app_config('AppName')}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Application Title')}}</label>
                                            <input type="text" class="form-control" name="app_title" required="" value="{{app_config('AppTitle')}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Address')}}</label>
                                            <textarea class="form-control textarea-wysihtml5" name="address">{{app_config('Address')}}</textarea>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('System Email')}}</label>
                                            <span class="help">{{language_data('Remember: All Email Going to the Receiver from this Email')}}</span>
                                            <input type="email" class="form-control" required name="email" value="{{app_config('Email')}}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Footer Text')}}</label>
                                            <input type="text" class="form-control" required name="footer" value="{{app_config('FooterTxt')}}">
                                        </div>


                                        <div class="form-group">
                                            <label>{{language_data('Application Logo')}}</label>
                                            <div class="input-group input-group-file">
                                                <span class="input-group-btn">
                                                    <span class="btn btn-primary btn-file">
                                                        {{language_data('Browse')}} <input type="file" class="form-control" name="app_logo" accept="image/*">
                                                    </span>
                                                </span>
                                                <input type="text" class="form-control" readonly="">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Application Favicon')}}</label>
                                            <div class="input-group input-group-file">
                                                <span class="input-group-btn">
                                                    <span class="btn btn-primary btn-file">
                                                        {{language_data('Browse')}} <input type="file" class="form-control" name="app_fav" accept="image/*">
                                                    </span>
                                                </span>
                                                <input type="text" class="form-control" readonly="">
                                            </div>
                                        </div>
                                        <hr>
                                        <button type="submit" class="btn btn-success btn-sm"><i  class="fa fa-edit"></i> {{language_data('Update')}}</button>
                                    </div>
                                </div>
                            </form>

                        </div>

                        <div role="tabpanel" class="tab-pane" id="system_email">

                            <form class="" role="form" action="{{url('settings/post-system-email-setting')}}" method="post" novalidate>
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{language_data('Email Gateway')}}</label>
                                            <select class="selectpicker form-control gateway" name="email_gateway">
                                                <option value="sendmail" @if(config('mail.driver') =='sendmail') selected @endif>{{language_data('Server Default')}}</option>
                                                <option value="smtp" @if(config('mail.driver') =='smtp') selected @endif> {{language_data('SMTP')}} </option>
                                                <option value="mailgun" @if(config('mail.driver') =='mailgun') selected @endif> Mailgun </option>
                                            </select>
                                        </div>

                                        <div class="show-smtp">
                                            <div class="form-group">
                                                <label for="fname">{{language_data('SMTP')}} {{language_data('Host Name')}}</label>
                                                <input type="text" class="form-control" required="" name="smtp_host_name" value="{{config('mail.host')}}">
                                            </div>

                                            <div class="form-group">
                                                <label for="fname">{{language_data('SMTP')}} {{language_data('User Name')}}</label>
                                                <input type="text" class="form-control" required="" name="smtp_user_name"  value="{{config('mail.username')}}">
                                            </div>

                                            <div class="form-group">
                                                <label for="fname">{{language_data('SMTP')}} {{language_data('Password')}}</label>
                                                <input type="text" class="form-control" required="" name="smtp_password"  value="{{config('mail.password')}}">
                                            </div>


                                            <div class="form-group">
                                                <label for="fname">{{language_data('SMTP')}} {{language_data('Port')}}</label>
                                                <input type="text" class="form-control" required="" name="smtp_port"  value="{{config('mail.port')}}">
                                            </div>


                                            <div class="form-group">
                                                <label for="Default Gateway">{{language_data('SMTP')}} {{language_data('Secure')}}</label>
                                                <select name="smtp_secure" class="selectpicker form-control">
                                                    <option value="tls" @if(config('mail.encryption')=='tls')  selected @endif>{{language_data('TLS')}}</option>
                                                    <option value="ssl" @if(config('mail.encryption')=='ssl')selected @endif>{{language_data('SSL')}}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="show-mail-gun">

                                            <div class="form-group">
                                                <label for="fname">Domain Name</label>
                                                <input type="text" class="form-control" required="" name="domain_name" value="{{config('services.mailgun.domain')}}">
                                            </div>

                                            <div class="form-group">
                                                <label for="fname">API Key</label>
                                                <input type="text" class="form-control" required="" name="api_key" value="{{config('services.mailgun.secret')}}">
                                            </div>


                                            <div class="form-group">
                                                <label for="fname">{{language_data('SMTP')}} {{language_data('Host Name')}}</label>
                                                <input type="text" class="form-control" required="" name="host_name" value="{{config('mail.host')}}">
                                            </div>

                                            <div class="form-group">
                                                <label for="fname">{{language_data('SMTP')}} {{language_data('User Name')}}</label>
                                                <input type="text" class="form-control" required="" name="user_name"  value="{{config('mail.username')}}">
                                            </div>

                                            <div class="form-group">
                                                <label for="fname">{{language_data('SMTP')}} {{language_data('Password')}}</label>
                                                <input type="text" class="form-control" required="" name="password"  value="{{config('mail.password')}}">
                                            </div>


                                            <div class="form-group">
                                                <label for="fname">{{language_data('SMTP')}} {{language_data('Port')}}</label>
                                                <input type="text" class="form-control" required="" name="port"  value="{{config('mail.port')}}">
                                            </div>


                                            <div class="form-group">
                                                <label for="Default Gateway">{{language_data('SMTP')}} {{language_data('Secure')}}</label>
                                                <select name="secure" class="selectpicker form-control">
                                                    <option value="tls" @if(config('mail.encryption')=='tls')  selected @endif>{{language_data('TLS')}}</option>
                                                    <option value="ssl" @if(config('mail.encryption')=='ssl')selected @endif>{{language_data('SSL')}}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <hr>
                                        <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-edit"></i> {{language_data('Update')}}</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div role="tabpanel" class="tab-pane" id="sms_settings">
                            <div class="row">
                                <div class="col-md-6">
                                    <form class="" role="form" action="{{url('settings/post-system-sms-setting')}}" method="post">
                                        {{ csrf_field() }}
                                        <div class="form-group">
                                            <label>SMS Gateway For Register User</label>
                                            <select class="selectpicker form-control" data-live-search="true" name="registration_sms_gateway">
                                                @foreach($sms_gateways as $gateway)
                                                    <option value="{{$gateway->id}}" @if(app_config('registration_sms_gateway') == $gateway->id) selected @endif>{{$gateway->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('API Permission')}}</label>
                                            <select class="selectpicker form-control" name="api_permission">
                                                <option value="1" @if(app_config('sms_api_permission')=='1') selected @endif>{{language_data('Yes')}}</option>
                                                <option value="0" @if(app_config('sms_api_permission')=='0') selected @endif>{{language_data('No')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Sender ID Verification')}}</label>
                                            <select class="selectpicker form-control" name="sender_id_verification">
                                                <option value="1" @if(app_config('sender_id_verification')=='1') selected @endif>{{language_data('Yes')}}</option>
                                                <option value="0" @if(app_config('sender_id_verification')=='0') selected @endif>{{language_data('No')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('SMS Fraud Detection')}}</label>
                                            <select class="selectpicker form-control" name="fraud_detection">
                                                <option value="1" @if(app_config('fraud_detection')=='1') selected @endif>{{language_data('Yes')}}</option>
                                                <option value="0" @if(app_config('fraud_detection')=='0') selected @endif>{{language_data('No')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Unsubscribe Message')}}</label>
                                            <textarea name="unsubscribe_message" class="form-control" rows="5">{{app_config('unsubscribe_message')}}</textarea>
                                        </div>

                                        <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-edit"></i> {{language_data('Update')}}</button>

                                    </form>
                                </div>
                            </div>
                        </div>

                        <div role="tabpanel" class="tab-pane" id="auth_settings">
                            <div class="row">
                                <div class="col-md-6">
                                    <form class="" role="form" action="{{url('settings/post-system-auth-setting')}}" method="post">
                                        {{ csrf_field() }}

                                        <div class="form-group">
                                            <label>{{language_data('Allow Client Registration')}}</label>
                                            <select class="selectpicker form-control" name="client_registration">
                                                <option value="1" @if(app_config('client_registration')=='1') selected @endif>{{language_data('Yes')}}</option>
                                                <option value="0" @if(app_config('client_registration')=='0') selected @endif>{{language_data('No')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Client Registration Verification')}}</label>
                                            <select class="selectpicker form-control" name="registration_verification">
                                                <option value="1" @if(app_config('registration_verification')=='1') selected @endif>{{language_data('Yes')}}</option>
                                                <option value="0" @if(app_config('registration_verification')=='0') selected @endif>{{language_data('No')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Captcha In Admin Login')}}</label>
                                            <select class="selectpicker form-control" name="captcha_in_admin">
                                                <option value="1" @if(app_config('captcha_in_admin')=='1') selected @endif>{{language_data('Yes')}}</option>
                                                <option value="0" @if(app_config('captcha_in_admin')=='0') selected @endif>{{language_data('No')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Captcha In Client Login')}}</label>
                                            <select class="selectpicker form-control" name="captcha_in_client">
                                                <option value="1" @if(app_config('captcha_in_client')=='1') selected @endif>{{language_data('Yes')}}</option>
                                                <option value="0" @if(app_config('captcha_in_client')=='0') selected @endif>{{language_data('No')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Captcha In Client Registration')}}</label>
                                            <select class="selectpicker form-control" name="captcha_in_client_registration">
                                                <option value="1" @if(app_config('captcha_in_client_registration')=='1') selected @endif>{{language_data('Yes')}}</option>
                                                <option value="0" @if(app_config('captcha_in_client_registration')=='0') selected @endif>{{language_data('No')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="fname">{{language_data('reCAPTCHA Site Key')}}</label>
                                            <input type="text" class="form-control" name="captcha_site_key" value="{{app_config('captcha_site_key')}}">
                                        </div>

                                        <div class="form-group">
                                            <label for="fname">{{language_data('reCAPTCHA Secret Key')}}</label>
                                            <input type="text" class="form-control" name="captcha_secret_key" value="{{app_config('captcha_secret_key')}}">
                                        </div>

                                        <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-edit"></i> {{language_data('Update')}}</button>

                                    </form>
                                </div>
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

    {!! Html::script("assets/libs/moment/moment.min.js")!!}
    {!! Html::script("assets/libs/wysihtml5x/wysihtml5x-toolbar.min.js")!!}
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/libs/bootstrap3-wysihtml5-bower/bootstrap3-wysihtml5.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    {!! Html::script("assets/js/bootbox.min.js")!!}
    <script>
        $(document).ready(function () {


            $("#select_all").click(function () {
                $('input:checkbox').not(this).prop('checked', this.checked);
            });

            let EmailGatewaySV = $('.gateway');

            if (EmailGatewaySV.val() == 'sendmail') {
                $('.show-smtp').hide();
                $('.show-mail-gun').hide();
            }else if(EmailGatewaySV.val() == 'mailgun'){
                $('.show-smtp').hide();
            }else {
                $('.show-mail-gun').hide();
            }

            EmailGatewaySV.on('change', function () {

                let value = $(this).val();
                if (value == 'smtp') {
                    $('.show-mail-gun').hide();
                    $('.show-smtp').show();
                } else if (value == 'mailgun') {
                    $('.show-mail-gun').show();
                    $('.show-smtp').hide();
                } else {
                    $('.show-smtp').hide();
                    $('.show-mail-gun').hide();
                }

            });

        });

    </script>

@endsection
