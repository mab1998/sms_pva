@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Add Operator')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">

            @include('notification.notify')
            <div class="row">

                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Add Operator')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" action="{{url('sms/post-add-operator')}}" method="post">


                                <div class="form-group">
                                    <label>{{language_data('Operator Name')}}/{{language_data('Area Name')}}</label>
                                    <input type="text" class="form-control" required name="operator_name"/>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Sample Phone Number')}}</label>
                                    <span class="help">{{language_data('Enter a real phone number like')}} 13479500000</span>
                                    <input type="number" class="form-control" required name="operator_code" placeholder="13479500000"/>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Plain')}} {{language_data('Price')}}</label>
                                    <span class="help">{{language_data('Cost for per SMS')}}</span>
                                    <input type="text" class="form-control" required name="plain_price" value="{{$coverage->plain_tariff}}"/>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Voice')}} {{language_data('Price')}}</label>
                                    <span class="help">{{language_data('Cost for per SMS')}}</span>
                                    <input type="text" class="form-control" required name="voice_price" value="{{$coverage->voice_tariff}}"/>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('MMS')}} {{language_data('Price')}}</label>
                                    <span class="help">{{language_data('Cost for per SMS')}}</span>
                                    <input type="text" class="form-control" required name="mms_price" value="{{$coverage->mms_tariff}}"/>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Status')}}</label>
                                    <select class="form-control selectpicker" name="status">
                                        <option value="active">{{language_data('Active')}}</option>
                                        <option value="inactive">{{language_data('Inactive')}}</option>
                                    </select>
                                </div>

                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="coverage_id" value="{{$coverage->id}}">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Save')}}</button>
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
