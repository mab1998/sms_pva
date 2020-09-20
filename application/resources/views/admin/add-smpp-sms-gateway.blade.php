@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Add SMS Gateway')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Add SMS Gateway')}}</h3>
                        </div>
                        <div class="panel-body">

                            <form role="form" method="post" action="{{url('sms/post-new-smpp-sms-gateway')}}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                <div class="form-group">
                                    <label>{{language_data('Gateway Name')}}</label>
                                    <input type="text" class="form-control" required name="gateway_name">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Gateway API Link')}}</label>
                                    <input type="text" class="form-control" required name="gateway_link">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('SMS Api User name')}} / System ID</label>
                                    <input type="text" class="form-control" name="gateway_user_name" required>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('SMS Api Password')}}</label>
                                    <input type="text" class="form-control" name="gateway_password" required>
                                </div>

                                <div class="form-group">
                                    <label>Port</label>
                                    <input type="text" class="form-control" name="port">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Status')}}</label>
                                    <select class="selectpicker form-control" name="status">
                                        <option value="Active">{{language_data('Active')}}</option>
                                        <option value="Inactive">{{language_data('Inactive')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Schedule SMS')}}</label>
                                    <select class="selectpicker form-control" name="schedule">
                                        <option value="Yes">{{language_data('Yes')}}</option>
                                        <option value="No">{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> {{language_data('Add')}} </button>

                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

@endsection

{{--External Script Section--}}
@section('script')
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
@endsection