@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('View Operator')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">

            @include('notification.notify')
            <div class="row">

                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('View Operator')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" action="{{url('sms/post-manage-operator')}}" method="post">


                                <div class="form-group">
                                    <label>{{language_data('Operator Name')}}/Area Code</label>
                                    <input type="text" class="form-control" required name="operator_name" value="{{$op->operator_name}}"/>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Sample Phone Number')}}</label>
                                    <span class="help">{{language_data('Enter a real phone number like')}} 13479500000</span>
                                    <input type="number" class="form-control" required name="operator_code" placeholder="13479500000"  value="{{$op->operator_code}}"/>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Plain')}} {{language_data('Price')}}</label>
                                    <span class="help">{{language_data('Cost for per SMS')}}</span>
                                    <input type="text" class="form-control" required name="plain_price"  value="{{$op->plain_price}}"/>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Voice')}} {{language_data('Price')}}</label>
                                    <span class="help">{{language_data('Cost for per SMS')}}</span>
                                    <input type="text" class="form-control" required name="voice_price"  value="{{$op->voice_price}}"/>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('MMS')}} {{language_data('Price')}}</label>
                                    <span class="help">{{language_data('Cost for per SMS')}}</span>
                                    <input type="text" class="form-control" required name="mms_price"  value="{{$op->mms_price}}"/>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Status')}}</label>
                                    <select class="form-control selectpicker" name="status">
                                        <option value="active" @if($op->status == 'active') selected @endif>{{language_data('Active')}}</option>
                                        <option value="inactive"  @if($op->status == 'inactive') selected @endif>{{language_data('Inactive')}}</option>
                                    </select>
                                </div>

                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="coverage_id" value="{{$op->coverage_id}}">
                                <input type="hidden" name="id" value="{{$op->id}}">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-save"></i> {{language_data('Update')}}</button>
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
