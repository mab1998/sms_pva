@extends('client')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">Yandex Money Payment</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Pay with Credit Card',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">
                            <form method="POST" action="https://money.yandex.ru/quickpay/confirm.xml">
                                <input type="hidden" name="receiver" value="{{$gat_info->value}}">
                                <input type="hidden" name="quickpay-form" value="shop">
                                <input type="hidden" name="targets" value="{{$plan_name}}">
                                <input type="hidden" name="sum" value="{{$amount}}" data-type="number">
                                <input type="hidden" name="formcomment" value="{{app_config('AppName').": ".$plan_name}}">
                                <input type="hidden" name="short-dest" value="{{app_config('AppName').": ".$plan_name}}">
                                <input type="hidden" name="label" value="{{$return_salt}}">
                                <input type="hidden" name="successURL" value="{{$success_url}}">
                                <input type="hidden" name="need-fio" value="true">
                                <input type="hidden" name="need-email" value="true">
                                <input type="hidden" name="need-phone" value="true">
                                <input type="hidden" name="need-address" value="false">

                                <div class="form-group">
                                    <div class="coder-radiobox">
                                        <input type="radio" name="paymentType" value="PC">
                                        <span class="co-radio-ui"></span>
                                        <label>Yandex.Money wallet</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-radiobox">
                                        <input type="radio" name="paymentType" value="AC">
                                        <span class="co-radio-ui"></span>
                                        <label>Bank card</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="coder-radiobox">
                                        <input type="radio" name="paymentType" value="MC">
                                        <span class="co-radio-ui"></span>
                                        <label>Mobile balance</label>
                                    </div>
                                </div>

                                <input type="submit" value="Pay" class="btn btn-success">
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
