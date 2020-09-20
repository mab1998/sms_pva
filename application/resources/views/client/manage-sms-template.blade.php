@extends('client')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Manage SMS Template',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">

            @include('notification.notify')
            <div class="row">

                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Manage SMS Template',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" action="{{url('user/sms/post-manage-sms-template')}}" method="post">


                                <div class="form-group">
                                    <label>{{language_data('Template Name',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" required name="template_name" value="{{$st->template_name}}"/>
                                </div>


                                @if(app_config('sender_id_verification') == 1)
                                    @if($sender_ids)
                                        <div class="form-group">
                                            <label>{{language_data('From',Auth::guard('client')->user()->lan_id)}}</label>
                                            <select class="selectpicker form-control" name="from"  data-live-search="true" id="from">
                                                @foreach($sender_ids as $si)
                                                    <option value="{{$si}}" @if($st->from == $si) selected @endif>{{$si}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <div class="form-group">
                                            <label>{{language_data('Sender ID',Auth::guard('client')->user()->lan_id)}}</label>
                                            <p><a href="{{url('user/sms/sender-id-management')}}" class="text-uppercase">{{language_data('Request New Sender ID',Auth::guard('client')->user()->lan_id)}}</a> </p>
                                        </div>
                                    @endif
                                @else
                                    <div class="form-group">
                                        <label>{{language_data('From',Auth::guard('client')->user()->lan_id)}}</label>
                                        <input type="text" class="form-control" name="from" value="{{$st->from}}"/>
                                    </div>
                                @endif
                                


                                <div class="form-group">
                                    <label>{{language_data('Status',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="form-control selectpicker" name="status">
                                        <option value="active" @if($st->status=='active') selected @endif>{{language_data('Active',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="inactive"  @if($st->status=='inactive') selected @endif>{{language_data('Inactive',Auth::guard('client')->user()->lan_id)}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Insert Merge Filed',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="form-control selectpicker" id="merge_value">
                                        <option value="<%Phone Number%>">{{language_data('Phone Number',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="<%Email Address%>">{{language_data('Email')}} {{language_data('Address',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="<%User Name%>">{{language_data('User Name',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="<%Company%>">{{language_data('Company',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="<%First Name%>">{{language_data('First Name',Auth::guard('client')->user()->lan_id)}}</option>
                                        <option value="<%Last Name%>">{{language_data('Last Name',Auth::guard('client')->user()->lan_id)}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Message',Auth::guard('client')->user()->lan_id)}}</label>
                                    <textarea class="form-control" id="message" name="message" rows="8">{{$st->message}}</textarea>
                                </div>

                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" value="{{$st->id}}" name="cmd">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Save',Auth::guard('client')->user()->lan_id)}}</button>
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

            var merge_state = $('#merge_value');

            merge_state.on('change', function () {
                var merge_value = this;

                $('#message').val(function (_, v) {
                    return v + merge_value.value;
                });
            });


        });
    </script>

@endsection