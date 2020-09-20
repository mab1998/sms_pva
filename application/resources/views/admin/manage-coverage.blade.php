@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Manage Coverage')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Manage Coverage')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('sms/post-manage-coverage')}}" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label>{{language_data('Country')}}</label>
                                    <input type="text" class="form-control" disabled value="{{$coverage->country_name}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('ISO Code')}}</label>
                                    <input type="text" class="form-control"  disabled value="{{$coverage->iso_code}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Country Code')}}</label>
                                    <input type="text" class="form-control"  disabled value="{{$coverage->country_code}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Plain')}} {{language_data('Tariff')}}</label>
                                    <span class="help">{{language_data('Cost for per SMS')}}</span>
                                    <input type="text" class="form-control" name="plain_tariff" required value="{{$coverage->plain_tariff}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Voice')}} {{language_data('Tariff')}}</label>
                                    <span class="help">{{language_data('Cost for per SMS')}}</span>
                                    <input type="text" class="form-control" name="voice_tariff" required value="{{$coverage->voice_tariff}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('MMS')}} {{language_data('Tariff')}}</label>
                                    <span class="help">{{language_data('Cost for per SMS')}}</span>
                                    <input type="text" class="form-control" name="mms_tariff" required value="{{$coverage->mms_tariff}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Status')}}</label>
                                    <select class="selectpicker form-control" name="status">
                                        <option value="1" @if($coverage->active=='1') selected @endif>{{language_data('Live')}}</option>
                                        <option value="0" @if($coverage->active=='0') selected @endif>{{language_data('Offline')}}</option>
                                    </select>
                                </div>

                                <input type="hidden" value="{{$coverage->id}}" name="cmd">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Update')}} </button>

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
@endsection
