@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css") !!}
@endsection

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Update')}} {{language_data('Schedule SMS')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Update')}} {{language_data('Schedule SMS')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form role="form" action="{{url('sms/post-update-schedule-sms')}}" method="post">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label>{{language_data('Phone Number')}}</label>
                                    <span class="help">({{language_data('Remain country code at the beginning of the number')}})</span>
                                    <input type="text" class="form-control" name="phone_number" id="phone_number" value="{{$sh->number}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Schedule Time')}}</label>
                                    <input type="text" class="form-control dateTimePicker" name="schedule_time" value="{{date('m/d/y H:i y',strtotime($sh->submitted_time))}}">
                                </div>


                                <div class="form-group">
                                    <label>{{language_data('Message')}}</label>
                                    <textarea class="form-control" name="message" rows="5" id="message">{{$sh->message}}</textarea>
                                </div>

                                <input type="hidden" value="{{$sh->id}}" name="cmd">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('update')}} </button>
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
@endsection
