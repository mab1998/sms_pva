@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title"> {{language_data('Two way')}} {{language_data('SMS Gateway Manage')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title"> {{language_data('Two way')}} {{language_data('SMS Gateway Manage')}}</h3>
                        </div>
                        <div class="panel-body">

                            <form role="form" method="post" action="{{url('sms/post-update-two-way-communication')}}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{language_data('Gateway Name')}}</label>
                                            <input type="text" class="form-control" readonly value="{{$gateway->name}}">
                                        </div>

                                        <div class="form-group">
                                            <label>Two way communication url</label>
                                            <input type="text" class="form-control" value="{{url('sms/receive-message/'.$gateway->id)}}">
                                            <span class="help text-success">Copy and paste this url on your two http callback setting</span>
                                        </div>

                                    </div>

                                    <div class="col-lg-8">
                                        <table class="table table-hover table-ultra-responsive" id="gateway_items">
                                            <thead>
                                            <tr>
                                                <th width="20%"></th>
                                                <th width="80%">{{language_data('Parameter')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                            <tr class="item-row info">
                                                <td>{{language_data('Source')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" name="source_param" class="form-control"  value="{{$source_param}}"></td>
                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Destination')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" required name="destination_param" class="form-control"  value="{{$destination_param}}"></td>
                                            </tr>

                                            <tr class="item-row info">
                                                <td>{{language_data('Message')}}</td>
                                                <td data-label="{{language_data('Parameter')}}"><input type="text" autocomplete="off" required name="message_param" class="form-control"  value="{{$message_param}}"></td>

                                            </tr>

                                            </tbody>
                                        </table>

                                        <div class="text-right">
                                            <input type="hidden" value="{{$gateway->id}}" name="gateway_id">
                                            <button class="btn btn-success" type="submit"><i class="fa fa-save"></i> {{language_data('Save')}}</button>
                                        </div>

                                    </div>
                                </div>
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