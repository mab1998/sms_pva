@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css") !!}
    <style>
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
            <h2 class="page-title">{{language_data('Send Bulk SMS',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Send Bulk SMS',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">

                            <form class="" role="form" method="post" action="{{url('user/sms/post-bulk-sms')}}"
                                  enctype="multipart/form-data">
                                {{ csrf_field() }}


                                <div class="form-group">
                                    <label>{{language_data('SMS Gateway',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="selectpicker form-control" name="sms_gateway"
                                            data-live-search="true">
                                        @foreach($sms_gateways as $gateway)
                                            <option value="{{$gateway->id}}">{{$gateway->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @if(app_config('show_keyword_in_client') == 1)
                                <div class="form-group">
                                    <label>{{language_data('Campaign Keyword',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="selectpicker form-control" name="keyword[]" data-live-search="true" multiple>
                                        @foreach($keyword as $kw)
                                            <option value="{{$kw->keyword_name}}">{{$kw->title}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <div class="form-group">
                                    <label>{{language_data('SMS Templates',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="selectpicker form-control" name="sms_template"
                                            data-live-search="true" id="sms_template">
                                        <option>{{language_data('Select Template',Auth::guard('client')->user()->lan_id)}}</option>
                                        @foreach($sms_templates as $st)
                                            <option value="{{$st->id}}">{{$st->template_name}}</option>
                                        @endforeach
                                    </select>
                                </div>


                                @if(app_config('sender_id_verification') == 1)
                                    @if($sender_ids)
                                        <div class="form-group">
                                            <label>{{language_data('Sender ID',Auth::guard('client')->user()->lan_id)}}</label>
                                            <select class="selectpicker form-control sender_id" name="sender_id"
                                                    data-live-search="true">
                                                @foreach($sender_ids as $si)
                                                    <option value="{{$si}}">{{$si}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <div class="form-group">
                                            <label>{{language_data('Sender ID',Auth::guard('client')->user()->lan_id)}}</label>
                                            <p><a href="{{url('user/sms/sender-id-management')}}"
                                                  class="text-uppercase">{{language_data('Request New Sender ID',Auth::guard('client')->user()->lan_id)}}</a>
                                            </p>
                                        </div>
                                    @endif
                                @else
                                    <div class="form-group">
                                        <label>{{language_data('Sender ID',Auth::guard('client')->user()->lan_id)}}</label>
                                        <input type="text" class="form-control sender_id" name="sender_id">
                                    </div>
                                @endif


                                <div class="form-group">
                                    <label>{{language_data('Select Contact Type',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="selectpicker form-control" name="contact_type" id="contact_type">
                                        <option value="phone_book">{{language_data('Phone Book',Auth::guard('client')->user()->lan_id)}}</option>
                                        @if(Auth::guard('client')->user()->reseller=='Yes')
                                            <option value="client_group">{{language_data('Client Group',Auth::guard('client')->user()->lan_id)}}</option>
                                        @endif
                                    </select>
                                </div>

                                @if(Auth::guard('client')->user()->reseller=='Yes')
                                    <div class="form-group client-group-area">
                                        <label>{{language_data('Client Group',Auth::guard('client')->user()->lan_id)}}</label>
                                        <select class="selectpicker form-control select_client_group"
                                                name="client_group_id[]" multiple data-live-search="true">
                                            @foreach($client_group as $cg)
                                                <option value="{{$cg->id}}">{{$cg->group_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div class="form-group contact-list-area">
                                    <label>{{language_data('Contact List',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="form-control selectpicker select_contact_group"
                                            name="contact_list_id[]" data-live-search="true" multiple>
                                        @foreach($phone_book as $pb)
                                            <option value="{{$pb->id}}">{{$pb->group_name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Country Code',Auth::guard('client')->user()->lan_id)}}</label>
                                    <span class="help">({{language_data('Work only for Recipients number',Auth::guard('client')->user()->lan_id)}}
                                        )</span>
                                    <select class="selectpicker form-control" name="country_code" data-live-search="true">
                                        <option value="0" @if(app_config('send_sms_country_code') == 0) selected @endif >{{language_data('Exist on phone number',Auth::guard('client')->user()->lan_id)}}</option>
                                        @foreach($country_code as $code)
                                            <option value="{{$code->country_code}}" @if(app_config('send_sms_country_code') == $code->country_code) selected @endif >{{$code->country_name}} ({{$code->country_code}})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Recipients',Auth::guard('client')->user()->lan_id)}}</label>
                                    <textarea class="form-control" rows="4" name="recipients" id="recipients"></textarea>
                                    <span class="help text-uppercase pull-right">{{language_data('Total Number Of Recipients',Auth::guard('client')->user()->lan_id)}}
                                        : <span class="number_of_recipients bold text-success m-r-5">0</span></span>
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('Choose delimiter',Auth::guard('client')->user()->lan_id)}}: </label>
                                    <div class="btn-group btn-group-sm" data-toggle="buttons">

                                        <label class="btn btn-default active">
                                            <input type="radio" name="delimiter" value="automatic" checked="">{{language_data('Automatic',Auth::guard('client')->user()->lan_id)}}
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
                                            <input type="radio" name="delimiter" value="tab">{{language_data('Tab',Auth::guard('client')->user()->lan_id)}}
                                        </label>

                                        <label class="btn btn-default">
                                            <input type="radio" name="delimiter" value="new_line">{{language_data('New Line',Auth::guard('client')->user()->lan_id)}}
                                        </label>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('Remove Duplicate',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="selectpicker form-control" name="remove_duplicate">
                                        <option value="yes">{{language_data('Yes',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="no">{{language_data('No',Auth::guard('client')->user()->lan_id)}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Message Type',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="selectpicker form-control message_type" name="message_type">
                                        <option value="plain">{{language_data('Plain',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="unicode">{{language_data('Unicode',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="arabic">{{language_data('Arabic',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="voice">{{language_data('Voice',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="mms">{{language_data('MMS',Auth::guard('client')->user()->lan_id)}}</option>
                                    </select>
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('Insert Merge Filed')}}</label>
                                    <select class="form-control selectpicker" id="merge_value">
                                        <option value="" disabled selected style="display:none;">{{language_data('Select Merge Field')}}</option>
                                        <option value="<%Phone Number%>">{{language_data('Phone Number')}}</option>
                                        <option value="<%Email Address%>">{{language_data('Email')}} {{language_data('Address')}}</option>
                                        <option value="<%User Name%>">{{language_data('User Name')}}</option>
                                        <option value="<%Company%>">{{language_data('Company')}}</option>
                                        <option value="<%First Name%>">{{language_data('First Name')}}</option>
                                        <option value="<%Last Name%>">{{language_data('Last Name')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Message',Auth::guard('client')->user()->lan_id)}}</label>
                                    <textarea class="form-control" name="message" rows="5" id="message"></textarea>
                                    <span class="help text-uppercase" id="remaining">160 {{language_data('characters remaining',Auth::guard('client')->user()->lan_id)}}</span>
                                    <span class="help text-success" id="messages">1 {{language_data('message',Auth::guard('client')->user()->lan_id)}}
                                        (s)</span>
                                </div>



                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" value="yes" name="unsubscribe_sms" class="unsubscribe_sms">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Generate unsubscribe message',Auth::guard('client')->user()->lan_id)}}</label>
                                    </div>
                                </div>

                                <div class="form-group send-mms">
                                    <label>{{language_data('Select File',Auth::guard('client')->user()->lan_id)}}</label>
                                    <div class="form-group input-group input-group-file">
                                        <span class="input-group-btn">
                                            <span class="btn btn-primary btn-file">
                                                {{language_data('Browse',Auth::guard('client')->user()->lan_id)}} <input
                                                        type="file" class="form-control" name="image"
                                                        accept="audio/*,video/*,image/*">
                                            </span>
                                        </span>
                                        <input type="text" class="form-control" readonly="">
                                    </div>
                                </div>


                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" name="send_later" @if($schedule_sms) checked @endif class="send_later" value="on">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Send Later',Auth::guard('client')->user()->lan_id)}}</label>
                                    </div>
                                </div>


                                <div class="schedule_time">
                                    <div class="form-group">
                                        <label>{{language_data('Schedule Time',Auth::guard('client')->user()->lan_id)}}</label>
                                        <input type="text" class="form-control dateTimePicker" name="schedule_time" value="{{date('m/d/y H:i y', strtotime('2 minute'))}}">
                                    </div>
                                </div>

                                <input type="hidden" value="{{$schedule_sms}}" id="schedule_sms_status"
                                       name="schedule_sms_status">
                                <button type="submit" class="btn btn-success btn-sm" name="action" value="send_now"><i
                                            class="fa fa-send"></i> {{language_data('Send',Auth::guard('client')->user()->lan_id)}}
                                </button>
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
    {!! Html::script("assets/libs/moment/moment.min.js")!!}
    {!! Html::script("assets/libs/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js")!!}
    {!! Html::script("assets/js/dom-rules.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}

    <script>
        $(document).ready(function () {

            var number_of_recipients_ajax = 0,
                number_of_recipients_manual = 0,
                $get_recipients = $('#recipients'),
                $get_msg = $("#message"),
                $remaining = $('#remaining'),
                $messages = $remaining.next(),
                message_type = 'plain',
                maxCharInitial = 160,
                maxChar = 157,
                messages = 1,
                schedule_sms_status = $('#schedule_sms_status').val(),
                _url = $("#_url").val(),
                unsubscribe_message = $('#_unsubscribe_message').val(),
                merge_state = $('#merge_value');

            if (schedule_sms_status) {
                $('.schedule_time').show();
            } else {
                $('.schedule_time').hide();
            }

            $('.send_later').change(function () {
                $('.schedule_time').fadeToggle();
            });


            function get_character() {
                var totalChar = $get_msg[0].value.length;
                var remainingChar = maxCharInitial;

                if ( totalChar <= maxCharInitial ) {
                    remainingChar = maxCharInitial - totalChar;
                    messages = 1;
                } else {
                    totalChar = totalChar - maxCharInitial;
                    messages = Math.ceil( totalChar / maxChar );
                    remainingChar = messages * maxChar - totalChar;
                    messages = messages + 1;
                }

                $remaining.text(remainingChar + " {!! language_data('characters remaining') !!}");
                $messages.text(messages + " {!! language_data('message') !!}"+ '(s)');
            }

            $('.message_type').on('change', function () {
                message_type = $(this).val();
                $get_msg.css('direction','ltr');

                if (message_type == 'unicode') {
                    maxCharInitial = 70;
                    maxChar = 67;
                    messages = 1;
                }

                if (message_type == 'arabic') {
                    maxCharInitial = 70;
                    maxChar = 67;
                    messages = 1;
                    $get_msg.css('direction','rtl');
                }

                if (message_type == 'plain' || message_type == 'voice') {
                    maxCharInitial = 160;
                    maxChar = 157;
                    messages = 1;
                }

                get_character();
            });


            $("#sms_template").change(function () {
                var id = $(this).val();
                var dataString = 'st_id=' + id;
                $.ajax
                ({
                    type: "POST",
                    url: _url + '/user/sms/get-template-info',
                    data: dataString,
                    cache: false,
                    success: function (data) {
                        $("#sender_id").val(data.from);

                        var totalChar = $get_msg.val(data.message).val().length;
                        var remainingChar = maxCharInitial;

                        if (totalChar <= maxCharInitial) {
                            remainingChar = maxCharInitial - totalChar;
                            messages = 1;
                        } else {
                            totalChar = totalChar - maxCharInitial;
                            messages = Math.ceil(totalChar / maxChar);
                            remainingChar = messages * maxChar - totalChar;
                            messages = messages + 1;
                        }

                        $remaining.text(remainingChar + " {!! language_data('characters remaining') !!}");
                        $messages.text(messages + " {!! language_data('message') !!}"+ '(s)');
                    }
                });
            });


            function get_delimiter(){
                return $('input[name=delimiter]:checked').val();
            }

            function get_recipients_count(){

                var recipients_value = $get_recipients[0].value.trim();

                if (recipients_value) {
                    var delimiter = get_delimiter();

                    if (delimiter == 'automatic'){
                        number_of_recipients_manual = splitMulti(recipients_value,[',','\n',';','|']).length;
                    } else if (delimiter == ';'){
                        number_of_recipients_manual = recipients_value.split(';').length;
                    } else if (delimiter == ','){
                        number_of_recipients_manual = recipients_value.split(',').length;
                    } else if (delimiter == '|'){
                        number_of_recipients_manual = recipients_value.split('|').length;
                    } else if (delimiter == 'tab'){
                        number_of_recipients_manual = recipients_value.split(' ').length;
                    } else if (delimiter == 'new_line'){
                        number_of_recipients_manual = recipients_value.split('\n').length;
                    }else{
                        number_of_recipients_manual = 0;
                    }
                } else {
                    number_of_recipients_manual = 0;
                }
                var total = number_of_recipients_manual + Number(number_of_recipients_ajax);

                $('.number_of_recipients').text(total);
            }


            function isDoubleByte(str) {
                for (var i = 0, n = str.length; i < n; i++) {
                    if (str.charCodeAt(i) > 255) {
                        return true;
                    }
                }
                return false;
            }

            function get_message_type() {
                if ($get_msg[0].value !== null) {
                    if (isDoubleByte($get_msg[0].value) === true) {
                        $('.message_type').val('unicode').change();
                    } else {
                        $('.message_type').val('plain').change();
                    }
                }
            }

            $(".unsubscribe_sms").change(function () {
                if (this.checked == true) {
                    $('#message').val(function (_, v) {
                        return v + unsubscribe_message;
                    });
                } else {
                    $('#message').val(function (_, v) {
                        return v.replace(unsubscribe_message, '');
                    });
                }
                get_character();
            });

            merge_state.on('change', function () {
                var caretPos = $get_msg[0].selectionStart;
                var textAreaTxt = $get_msg.val();
                var txtToAdd = this.value;

                $get_msg.val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos) );
            });

            $get_msg.keyup(get_message_type);
            $get_msg.keyup(get_character);
            $get_recipients.keyup(get_recipients_count);

            $("input[name='delimiter']").change(function(){
                get_recipients_count();
            });


            var domRules = $.createDomRules({

                parentSelector: 'body',
                scopeSelector: 'form',
                showTargets: function (rule, $controller, condition, $targets, $scope) {
                    $targets.fadeIn();
                    $('.number_of_recipients').text(0);
                },
                hideTargets: function (rule, $controller, condition, $targets, $scope) {
                    $targets.fadeOut();
                    $('.number_of_recipients').text(0);
                },

                rules: [
                    {
                        controller: '#contact_type',
                        value: 'phone_book',
                        condition: '==',
                        targets: '.contact-list-area',
                    },
                    {
                        controller: '#contact_type',
                        value: 'client_group',
                        condition: '==',
                        targets: '.client-group-area',
                    },
                    {
                        controller: '.message_type',
                        value: 'mms',
                        condition: '==',
                        targets: '.send-mms',
                    }
                ]
            });


            $('.select_client_group').on('hide.bs.select', function (e) {

                var vals = [];

                $(this).find(':selected').each(function () {
                    vals.push($(this).val());
                });

                if (vals.length) {

                    vals = vals.map(function (val) {
                        return Number(val);
                    });

                    $.ajax({
                        url: _url + '/user/sms/get-contact-list-ids',
                        type: 'GET',
                        data: {
                            'client_group_ids': vals
                        }
                    })
                        .done(function (data, response) {

                            number_of_recipients_manual = Number(number_of_recipients_manual);

                            if (response == 'success' && data.status == 'success') {

                                number_of_recipients_ajax = Number(data.data);

                                var total = number_of_recipients_manual + number_of_recipients_ajax;

                                $('.number_of_recipients').text(total);

                                return;
                            }

                            $('.number_of_recipients').text(number_of_recipients_manual);

                        })
                        .fail(function () {

                            number_of_recipients_manual = Number(number_of_recipients_manual);

                            $('.number_of_recipients').text(number_of_recipients_manual);

                        })

                } else {

                    number_of_recipients_ajax = 0;

                    var total = Number(number_of_recipients_manual) + number_of_recipients_ajax;

                    $('.number_of_recipients').text(total);
                }

            });


            $('.select_contact_group').on('hide.bs.select', function (e) {

                var vals = [];

                $(this).find(':selected').each(function () {
                    vals.push($(this).val());
                });

                if (vals.length) {

                    vals = vals.map(function (val) {
                        return Number(val);
                    });

                    $.ajax({
                        url: _url + '/user/sms/get-contact-list-ids',
                        type: 'GET',
                        data: {
                            'contact_list_ids': vals
                        }
                    })
                        .done(function (data, response) {

                            number_of_recipients_manual = Number(number_of_recipients_manual);

                            if (response == 'success' && data.status == 'success') {

                                number_of_recipients_ajax = Number(data.data);

                                var total = number_of_recipients_manual + number_of_recipients_ajax;

                                $('.number_of_recipients').text(total);

                                return;
                            }

                            $('.number_of_recipients').text(number_of_recipients_manual);

                        })
                        .fail(function () {

                            number_of_recipients_manual = Number(number_of_recipients_manual);

                            $('.number_of_recipients').text(number_of_recipients_manual);

                        });

                } else {

                    number_of_recipients_ajax = 0;

                    var total = Number(number_of_recipients_manual) + number_of_recipients_ajax;

                    $('.number_of_recipients').text(total);
                }

            });


        });
    </script>
@endsection
