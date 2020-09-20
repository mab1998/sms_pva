@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Keyword Settings')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-7">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Keyword Settings')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" action="{{url('keywords/post-keyword-setting')}}" method="post">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label>{{language_data('Show In Client')}}</label>
                                    <select class="selectpicker form-control" name="show_keyword_in_client">
                                        <option value="1" @if(app_config('show_keyword_in_client')=='1') selected @endif>{{language_data('Yes')}}</option>
                                        <option value="0" @if(app_config('show_keyword_in_client')=='0') selected @endif>{{language_data('No')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="fname">{{language_data('Opt in SMS Keyword')}}</label>
                                    <span class="help">{{language_data('Insert keyword using comma')}} (,)</span>
                                    <textarea class="form-control" name="opt_in_sms_keyword" rows="5">{{app_config('opt_in_sms_keyword')}}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="fname">{{language_data('Opt Out SMS Keyword')}}</label>
                                    <span class="help">{{language_data('Insert keyword using comma')}} (,)</span>
                                    <textarea class="form-control" name="opt_out_sms_keyword" rows="5">{{app_config('opt_out_sms_keyword')}}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="fname">{{language_data('Custom Gateway Success Response Status')}}</label>
                                    <span class="help">{{language_data('Insert keyword using comma')}} (,)</span>
                                    <textarea class="form-control" name="custom_gateway_response_status" rows="5">{{app_config('custom_gateway_response_status')}}</textarea>
                                </div>

                                <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-edit"></i> {{language_data('Update')}}</button>
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
