@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css") !!}

    <style>
        .progress-bar-indeterminate {
            background: url('../assets/img/progress-bar-complete.svg') no-repeat top left;
            width: 100%;
            height: 100%;
            background-size: cover;
        }
    </style>

@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Send Bulk SMS')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            <div class="show_notification"></div>
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Send Bulk SMS')}}</h3>
                        </div>
                        <div class="panel-body">

                            <div class="form-group">
                                <div class="form-group">
                                    <a href="{{url('sms/download-sample-sms-file')}}" class="btn btn-complete"><i
                                                class="fa fa-download"></i> {{language_data('Download Sample File')}}
                                    </a>
                                </div>
                            </div>

                            <div id="send-sms-file-wrapper">
                                <form id="send-sms-file-form" class="" role="form" method="post"
                                      action="{{url('sms/post-sms-from-file')}}" enctype="multipart/form-data">
                                    {{ csrf_field() }}

                                    <div class="form-group">
                                        <label>{{language_data('Import Numbers')}}</label>
                                        <div class="form-group input-group input-group-file">
                                        <span class="input-group-btn">
                                            <span class="btn btn-primary btn-file">
                                                {{language_data('Browse')}} <input type="file" class="form-control" name="import_numbers"
                                                                                   @change="handleImportNumbers">
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
                                        <select class="selectpicker form-control" ref="number_column"
                                                name="number_column" data-live-search="true" v-model="number_column">
                                            <option v-for="column in number_columns" :value="column.key"
                                                    v-text="column.value"></option>
                                        </select>
                                    </div>


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
                                        <label>{{language_data('Campaign Keyword')}}</label>
                                        <span class="help">{{language_data('Only work with two way sms gateway provider')}}</span>
                                        <select class="selectpicker form-control" name="keyword[]" data-live-search="true" multiple>
                                            @foreach($keyword as $kw)
                                                <option value="{{$kw->keyword_name}}">{{$kw->title}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>{{language_data('Sender ID')}}</label>
                                        <input type="text" class="form-control" name="sender_id" id="sender_id">
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
                                        <textarea class="form-control" name="message" rows="5" id="message" ref="message"></textarea>
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


                                    <div class="row">

                                        <div class="col-sm-6" v-show="number_columns.length > 0">
                                            <div class="form-group">
                                                <label>{{language_data('Select Merge Field')}}</label>
                                                <select class="selectpicker form-control" ref="merge_field"
                                                        data-live-search="true" v-model="number_column">
                                                    <option v-for="column in number_columns" :value="column.key"
                                                            v-text="column.value"></option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label>{{language_data('SMS Templates')}}</label>
                                                <select class="selectpicker form-control" name="sms_template"
                                                        data-live-search="true" id="sms_template">
                                                    <option>{{language_data('Select Template')}}</option>
                                                    @foreach($sms_templates as $st)
                                                        <option value="{{$st->id}}">{{$st->template_name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                    </div>


                                    <div class="form-group">
                                        <div class="coder-checkbox">
                                            <input type="checkbox" name="send_later" @if($schedule_sms) checked
                                                   @endif class="send_later" value="on">
                                            <span class="co-check-ui"></span>
                                            <label>{{language_data('Send Later')}}</label>
                                        </div>
                                    </div>


                                    <div class="schedule_time">

                                        <div class="form-group">
                                            <label>{{language_data('Schedule Time Type')}}</label>
                                            <select class="selectpicker form-control schedule_time_type" ref="schedule_time_type" name="schedule_time_type">
                                                <option value="from_date">{{language_data('Schedule Time Using Date')}}</option>
                                                <option value="from_file">{{language_data('Schedule Time Using File')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group from_date">
                                            <label>{{language_data('Schedule Time')}}</label>
                                            <input type="text" class="form-control dateTimePicker" name="schedule_time" value="{{date('m/d/y H:i y', strtotime('2 minute'))}}">
                                            <span class="help text-danger text-uppercase">{{language_data('Schedule Time must contain this format')}} ( dd/mm/yyyy h:m AM ) Ex. {{date('m/d/Y h:i A')}}</span>
                                        </div>

                                        <div class="form-group from_file" v-show="number_columns.length > 0 && schedule_time_type == 'from_file'">
                                            <label>{{language_data('Schedule Time')}} {{language_data('Column')}}</label>
                                            <select class="selectpicker form-control" ref="schedule_time_column" name="schedule_time_column" data-live-search="true" v-model="schedule_time_column">
                                                <option v-for="column in number_columns" :value="column.key" v-text="column.value"></option>
                                            </select>
                                            <span class="help text-danger text-uppercase">{{language_data('Schedule Time must contain this format')}} ( dd/mm/yyyy h:m AM ) Ex. {{date('m/d/Y h:i A')}}</span>
                                        </div>

                                    </div>


                                    <div id='uploadContact' style='display:none' class="form-group">
                                        <label>{{language_data('Message adding in Queue.. Please wait')}}</label>
                                        <div class="progress">
                                            <div class="progress-bar-indeterminate"></div>
                                        </div>
                                    </div>

                                    <input type="hidden" value="{{$schedule_sms}}" id="schedule_sms_status" name="schedule_sms_status">
                                    <span class="text-uppercase text-complete help">{{language_data('After click on Send button, do not refresh your browser')}}</span>

                                    <button type="submit" id="submitContact" class="btn btn-success btn-sm pull-right"><i class="fa fa-send"></i> {{language_data('Send')}} </button>

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
    {!! Html::script("assets/libs/moment/moment.min.js")!!}
    {!! Html::script("assets/libs/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js")!!}
    {!! Html::script("assets/js/vue.js") !!}
    {!! Html::script("assets/js/file_upload.js") !!}
    {!! Html::script("assets/js/form-elements-page.js")!!}

    <script>
        $(document).ready(function () {


            $('#submitContact').click(function(){
                $(this).hide();
                $('#uploadContact').show();
            });


            var $get_msg = $("#message"),
                $remaining = $('#remaining'),
                $messages = $remaining.next(),
                message_type = 'plain',
                maxCharInitial = 160,
                maxChar = 157,
                messages = 1,
                schedule_sms_status = $('#schedule_sms_status').val(),
                _url = $("#_url").val(),
                unsubscribe_message = $('#_unsubscribe_message').val();

            if (schedule_sms_status) {
                $('.schedule_time').show();
            } else {
                $('.schedule_time').hide();
            }

            $('.send_later').change(function () {
                $('.schedule_time').fadeToggle();
            });

          $('.schedule_time_type').on('change', function () {
            if (this.value == 'from_date') {
              $('.from_file').hide();
              $('.from_date').show();
            } else {
              $('.from_date').hide();
              $('.from_file').show();
            }
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

          $('.send-mms').hide();
          $('.message_type').on('change', function () {
            message_type = $(this).val();
            $get_msg.css('direction','ltr');

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
              $get_msg.css('direction','rtl');
              get_character()
            }

            if (message_type == 'plain' || message_type == 'voice') {
              maxCharInitial = 160;
              maxChar = 157;
              messages = 1;
              $('.send-mms').hide();
              get_character()
            }

            if (message_type == 'mms'){
              $('.send-mms').show();
            }

          });


            $("#sms_template").change(function () {
                var id = $(this).val();
                var dataString = 'st_id=' + id;
                $.ajax
                ({
                    type: "POST",
                    url: _url + '/sms/get-template-info',
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

            $get_msg.keyup(get_message_type);
            $get_msg.keyup(get_character);

        });
    </script>
@endsection
