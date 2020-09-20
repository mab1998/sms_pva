@extends('client')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Update SMS data',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Update SMS data',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">

                            <form class="" role="form" method="post" action="{{url('user/sms/post-update-recurring-sms-contact-data')}}">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label>{{language_data('Phone Number',Auth::guard('client')->user()->lan_id)}}</label>
                                    <span class="help">({{language_data('Remain country code at the beginning of the number',Auth::guard('client')->user()->lan_id)}})</span>
                                    <input type="text" class="form-control" name="phone_number" id="phone_number" value="{{$recurring->receiver}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Message Type',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="selectpicker form-control message_type" name="message_type" disabled>
                                        <option value="plain" @if($recurring->type == 'plain') selected @endif>{{language_data('Plain',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="unicode" @if($recurring->type == 'unicode') selected @endif>{{language_data('Unicode',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="voice" @if($recurring->type == 'voice') selected @endif>{{language_data('Voice',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="mms" @if($recurring->type == 'mms') selected @endif>{{language_data('MMS',Auth::guard('client')->user()->lan_id)}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Message')}}</label>
                                    <textarea class="form-control" name="message" rows="5" id="message">{{$recurring->message}} </textarea>
                                    <span class="help text-uppercase" id="remaining">160 {{language_data('characters remaining',Auth::guard('client')->user()->lan_id)}}</span>
                                    <span class="help text-success" id="messages">1 {{language_data('message',Auth::guard('client')->user()->lan_id)}} (s)</span>
                                </div>

                                <input type="hidden" value="{{$recurring->id}}" name="contact_id">
                                <input type="hidden" value="{{$recurring->campaign_id}}" name="recurring_id">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Update',Auth::guard('client')->user()->lan_id)}} </button>
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

            var $get_msg = $("#message"),
                $remaining = $('#remaining'),
                $messages = $remaining.next(),
                message_type = 'plain',
                maxCharInitial = 160,
                maxChar = 157,
                messages = 1;


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

              $remaining.text(remainingChar + " {!! language_data('characters remaining',Auth::guard('client')->user()->lan_id) !!}");
              $messages.text(messages + " {!! language_data('message',Auth::guard('client')->user()->lan_id) !!}"+ '(s)');
          }

          $('.send-mms').hide();
          $('.message_type').on('change', function () {
            message_type = $(this).val();

            if (message_type == 'unicode') {
              maxCharInitial = 70;
              maxChar = 67;
              messages = 1;
              $('.send-mms').hide();
              get_character()
            }

            if (message_type == 'plain' || message_type == 'voice') {
              maxCharInitial = 160;
              maxChar = 160;
              messages = 1;
              $('.send-mms').hide();
              get_character()
            }
          });

            $get_msg.keyup(get_character);

        });
    </script>
@endsection