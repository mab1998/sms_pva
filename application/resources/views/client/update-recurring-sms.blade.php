@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Update Period',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Update Period',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">

                            <form class="" role="form" method="post" action="{{url('user/sms/post-update-recurring-sms')}}">
                                {{ csrf_field() }}


                                <div class="form-group">
                                    <label>{{language_data('SMS Gateway',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="selectpicker form-control" name="sms_gateway" data-live-search="true">
                                        @foreach($sms_gateways as $sg)
                                            <option @if($sg->id == $recurring->use_gateway) selected @endif value="{{$sg->id}}">{{$sg->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Sender ID',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" name="sender_id" id="sender_id" value="{{$recurring->sender}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Recurring Period',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="selectpicker form-control" id="period" name="period">
                                        <option value="day" @if($recurring->recurring == 'day') selected @endif>{{language_data('Daily',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="week1" @if($recurring->recurring == 'week1') selected @endif>{{language_data('Weekly',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="weeks2"  @if($recurring->recurring == 'weeks2') selected @endif>{{language_data('2 Weeks',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="month1"  @if($recurring->recurring == 'month1') selected @endif>{{language_data('Month',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="months2"  @if($recurring->recurring == 'months2') selected @endif>{{language_data('2 Months',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="months3"  @if($recurring->recurring == 'months3') selected @endif>{{language_data('3 Months',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="months6"  @if($recurring->recurring == 'months6') selected @endif>{{language_data('6 Months',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="year1"  @if($recurring->recurring == 'year1') selected @endif>{{language_data('Year',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="years2"  @if($recurring->recurring == 'years2') selected @endif>{{language_data('2 Years',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="years3"  @if($recurring->recurring == 'years3') selected @endif>{{language_data('3 Years',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="0" @if($recurring->recurring == '0') selected @endif>{{language_data('Custom Date',Auth::guard('client')->user()->lan_id)}}</option>
                                    </select>
                                </div>

                                <div class="form-group recurring-time">
                                    <label>{{language_data('Recurring Time',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control timePicker" name="recurring_time" value="{{date('h:i A', strtotime($recurring->recurring_date))}}">
                                </div>


                                <div class="schedule_time">
                                    <div class="form-group">
                                        <label>{{language_data('Schedule Time',Auth::guard('client')->user()->lan_id)}}</label>
                                        <input type="text" class="form-control dateTimePicker" name="schedule_time" value="{{date('m/d/Y h:i A',strtotime($recurring->recurring_date))}}">
                                    </div>
                                </div>
                                <input type="hidden" value="{{$recurring->id}}" name="cmd">
                                <button type="submit" class="btn btn-success btn-sm" name="action" value="send_now"><i class="fa fa-save"></i> {{language_data('Update',Auth::guard('client')->user()->lan_id)}} </button>
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
    {!! Html::script("assets/js/form-elements-page.js")!!}

    <script>
      $(document).ready(function () {
        var period = $('#period');

        if (period.val() == 0){
          $('.schedule_time').show()
          $('.recurring-time').hide()
        }else {
          $('.schedule_time').hide()
          $('.recurring-time').show()
        }

        period.on('change', function () {
          if (this.value == 0) {
            $('.schedule_time').show()
            $('.recurring-time').hide()
          } else {
            $('.schedule_time').hide()
            $('.recurring-time').show()
          }
        })
      });
    </script>
@endsection