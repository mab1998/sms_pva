@extends('admin')


{{--External Style Section--}}
@section('style')
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
            <h2 class="page-title">{{language_data('Send Quick SMS')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Send Quick SMS')}}</h3>
                        </div>
                        <div class="panel-body">

                            <form class="" role="form" method="post" action="{{url('sms/post-quick-sms')}}" enctype="multipart/form-data">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label>{{language_data('SMS Gateway')}}</label>
                                    <select class="selectpicker form-control" name="sms_gateway"
                                            data-live-search="true">
                                        @foreach($gateways as $sg)
                                            <option value="{{$sg->id}}">{{$sg->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Sender ID')}}</label>
                                    <input type="text" class="form-control" name="sender_id" id="sender_id">
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

                                <div class="form-group">
                                    <label>{{language_data('Recipients')}}</label>
                                    <textarea class="form-control" rows="4" name="recipients" id="recipients"></textarea>
                                    <span class="help text-uppercase pull-right">{{language_data('Total Number Of Recipients')}}
                                        : <span class="number_of_recipients bold text-success m-r-5">0</span></span>
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
                                    <label>{{language_data('Remove Duplicate')}}</label>
                                    <select class="selectpicker form-control" name="remove_duplicate">
                                        <option value="yes">{{language_data('Yes')}}</option>
                                        <option value="no">{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Message Type')}}</label>
                                    <select class="selectpicker form-control message_type" name="message_type">
                                        <option value="plain">{{language_data('Plain')}}</option>
                                        <option value="unicode">{{language_data('Unicode')}}</option>
                                        <option value="arabic">{{language_data('Arabic')}}</option>
                                        <option value="voice">{{language_data('Voice')}}</option>
                                        <option value="mms">{{language_data('MMS')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Message')}}</label>
                                    <textarea class="form-control" name="message" rows="5" id="message"></textarea>
                                    <span class="help text-uppercase" id="remaining">160 {{language_data('characters remaining')}}</span>
                                    <span class="help text-success" id="messages">1 {{language_data('message')}} (s)</span>
                                </div>


                                <div class="form-group">
                                    <div class="coder-checkbox">
                                        <input type="checkbox" value="yes" name="unsubscribe_sms" class="unsubscribe_sms">
                                        <span class="co-check-ui"></span>
                                        <label>{{language_data('Generate unsubscribe message')}}</label>
                                    </div>
                                </div>

                                <div class="form-group send-mms">
                                    <label>{{language_data('Select File')}}</label>
                                    <div class="form-group input-group input-group-file">
                                        <span class="input-group-btn">
                                            <span class="btn btn-primary btn-file">
                                                {{language_data('Browse')}} <input type="file" class="form-control" name="image" accept="audio/*,video/*,image/*">
                                            </span>
                                        </span>
                                        <input type="text" class="form-control" readonly="">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-send"></i> {{language_data('Send')}} </button>
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
                unsubscribe_message = $('#_unsubscribe_message').val();

            function get_character() {
                var totalChar = $get_msg[0].value.length;
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
                $messages.text(messages + " {!! language_data('message') !!}" + '(s)');
            }

            function get_delimiter() {
                return $('input[name=delimiter]:checked').val();
            }

            $('.send-mms').hide();
            $('.message_type').on('change', function () {
                message_type = $(this).val();
                $get_msg.css('direction', 'ltr');

                if (message_type == 'unicode') {
                    maxCharInitial = 70;
                    maxChar = 67;
                    messages = 1;
                    $('.send-mms').hide();
                    get_character()
                }

                if (message_type == 'arabic') {
                    maxCharInitial = 70;
                    maxChar = 67;
                    messages = 1;
                    $('.send-mms').hide();
                    $get_msg.css('direction', 'rtl');
                    get_character()
                }

                if (message_type == 'plain' || message_type == 'voice') {
                    maxCharInitial = 160;
                    maxChar = 157;
                    messages = 1;
                    $('.send-mms').hide();
                    get_character()
                }

                if (message_type == 'mms') {
                    $('.send-mms').show();
                }

            });

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
                    if (isDoubleByte($get_msg[0].value) === true){
                        $('.message_type').val('unicode').change();
                    } else  {
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


            function get_recipients_count() {

                var recipients_value = $get_recipients[0].value.trim();

                if (recipients_value) {
                    var delimiter = get_delimiter();

                    if (delimiter == 'automatic') {
                        number_of_recipients_manual = splitMulti(recipients_value, [',', '\n', ';', '|']).length;
                    } else if (delimiter == ';') {
                        number_of_recipients_manual = recipients_value.split(';').length;
                    } else if (delimiter == ',') {
                        number_of_recipients_manual = recipients_value.split(',').length;
                    } else if (delimiter == '|') {
                        number_of_recipients_manual = recipients_value.split('|').length;
                    } else if (delimiter == 'tab') {
                        number_of_recipients_manual = recipients_value.split(' ').length;
                    } else if (delimiter == 'new_line') {
                        number_of_recipients_manual = recipients_value.split('\n').length;
                    } else {
                        number_of_recipients_manual = 0;
                    }
                } else {
                    number_of_recipients_manual = 0;
                }
                var total = number_of_recipients_manual + Number(number_of_recipients_ajax);

                $('.number_of_recipients').text(total);
            }

            $get_msg.keyup(get_message_type);
            $get_msg.keyup(get_character);
            $get_recipients.keyup(get_recipients_count);


            $("input[name='delimiter']").change(function () {
                get_recipients_count();
            });

        });
    </script>
@endsection
