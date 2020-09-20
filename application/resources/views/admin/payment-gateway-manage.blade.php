@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Manage Payment Gateway')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Manage Payment Gateway')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('settings/post-payment-gateway-manage')}}">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label>{{language_data('Gateway Name')}}</label>
                                    <input type="text" class="form-control" name="gateway_name" value="{{$pg->name}}" required>
                                </div>

                                <div class="form-group">
                                    @if($pg->settings=='payu' || $pg->settings=='2checkout' || $pg->settings=='paypal' || $pg->settings=='gopay')
                                        <label>{{language_data('Client ID')}}</label>
                                    @elseif($pg->settings=='stripe')
                                        <label>{{language_data('Publishable Key')}}</label>
                                    @elseif($pg->settings=='manualpayment')
                                        <label>{{language_data('Bank Details')}}</label>
                                    @elseif($pg->settings=='authorize_net')
                                        <label>{{language_data('Api Login ID')}}</label>
                                    @elseif($pg->settings=='slydepay' )
                                        <label>Merchant Email</label>
                                    @elseif($pg->settings=='paynow' )
                                        <label>Integration ID</label>
                                    @elseif($pg->settings=='webxpay' )
                                        <label>Secret Key</label>
                                    @elseif($pg->settings=='alipay' )
                                        <label>Merchant Private Key</label>
                                    @elseif($pg->settings=='cinetpay' )
                                        <label>API Key</label>
                                    @elseif($pg->settings=='wechatpay' || $pg->settings=='coinpayments' )
                                        <label>Merchant ID</label>
                                    @elseif($pg->settings=='paystack' || $pg->settings=='pagopar')
                                        <label>Public Key</label>
                                    @elseif($pg->settings=='yandexmoney')
                                        <label>Wallet Number</label>
                                    @elseif($pg->settings=='moka')
                                        <label>Dealer Code</label>
                                    @else
                                        <label>{{language_data('Value')}}</label>
                                    @endif
                                    <input type="text" class="form-control" name="pg_value" value="{{$pg->value}}">
                                </div>



                                @if($pg->settings!='coinpayments' && $pg->settings=='stripe' || $pg->settings=='authorize_net' ||  $pg->settings=='slydepay' || $pg->settings=='payu' || $pg->settings=='paystack' || $pg->settings=='pagopar' || $pg->settings=='paynow' || $pg->settings == 'webxpay' || $pg->settings=='alipay' || $pg->settings=='wechatpay' || $pg->settings=='paypal' || $pg->settings=='gopay' || $pg->settings=='cinetpay' || $pg->settings=='moka')
                                    <div class="form-group">
                                        @if($pg->settings=='stripe' || $pg->settings=='paystack')
                                            <label>{{language_data('Secret_Key_Signature')}}</label>
                                        @elseif($pg->settings=='authorize_net')
                                            <label>{{language_data('Transaction Key')}}</label>
                                        @elseif($pg->settings=='payu' || $pg->settings=='paypal' || $pg->settings=='gopay')
                                            <label>{{language_data('Client Secret')}}</label>
                                        @elseif($pg->settings=='slydepay')
                                            <label>Merchant Secret</label>
                                        @elseif($pg->settings=='paynow' )
                                            <label>Integration Key</label>
                                        @elseif($pg->settings=='webxpay' )
                                            <label>Public Key</label>
                                        @elseif($pg->settings=='pagopar')
                                            <label>Private Key</label>
                                        @elseif($pg->settings=='alipay' )
                                            <label>AliPay Public Key</label>
                                        @elseif($pg->settings=='cinetpay' )
                                            <label>Site ID</label>
                                        @elseif($pg->settings=='wechatpay' )
                                            <label>Api Key</label>
                                        @elseif($pg->settings=='moka' )
                                            <label>User Name</label>
                                        @else
                                            <label>{{language_data('Extra Value')}}</label>
                                        @endif

                                        @if($pg->settings == 'webxpay')
                                            <textarea name="pg_extra_value" class="form-control" rows="6">{{$pg->extra_value}}</textarea>
                                        @else
                                            <input type="text" class="form-control" name="pg_extra_value" value="{{$pg->extra_value}}">
                                        @endif
                                    </div>
                                @endif


                                @if($pg->settings=='paystack' || $pg->settings=='alipay'  || $pg->settings=='wechatpay'   || $pg->settings=='gopay'  || $pg->settings=='moka' )
                                    <div class="form-group">
                                        @if($pg->settings=='paystack')
                                            <label>Merchant Email</label>
                                        @elseif($pg->settings=='gopay')
                                            <label>Go ID</label>
                                        @elseif($pg->settings=='alipay'  || $pg->settings=='wechatpay')
                                            <label>App ID</label>
                                        @else
                                            <label>{{language_data('Password')}}</label>
                                        @endif
                                        <input type="text" class="form-control"  name="pg_password"  value="{{$pg->password}}">
                                    </div>
                                @endif

                                @if($pg->settings=='wechatpay')
                                    <div class="form-group">
                                        <label>Api Secret</label>
                                        <input type="text" class="form-control" name="pg_custom_one" value="{{$pg->custom_one}}">
                                    </div>
                                @endif

                                @if($pg->settings=='paypal')
                                    <div class="form-group">
                                        <label>Payment Mode</label>
                                        <select class="selectpicker form-control" name="pg_mode">
                                            <option value="live" @if(env('PAYPAL_MODE')=='live') selected @endif>Live</option>
                                            <option value="sandbox"  @if(env('PAYPAL_MODE')=='sandbox') selected @endif>Sandbox</option>
                                        </select>
                                    </div>
                                @endif
                                @if($pg->settings=='moka')
                                    <div class="form-group">
                                        <label>Payment Mode</label>
                                        <select class="selectpicker form-control" name="pg_custom_one">
                                            <option value="live" @if($pg->custom_one =='live') selected @endif>Live</option>
                                            <option value="sandbox"  @if($pg->custom_one =='sandbox') selected @endif>Sandbox</option>
                                        </select>
                                    </div>
                                @endif

                                @if($pg->settings=='gopay')
                                    <div class="form-group">
                                        <label>Payment Mode</label>
                                        <select class="selectpicker form-control" name="pg_mode">
                                            <option value="live" @if(config('gopay.mode')=='live') selected @endif>Live</option>
                                            <option value="sandbox"  @if(config('gopay.mode')=='sandbox') selected @endif>Sandbox</option>
                                        </select>
                                    </div>
                                @endif

                                <div class="form-group">
                                    <label>{{language_data('Status')}}</label>
                                    <select class="selectpicker form-control" name="status">
                                        <option value="Active" @if($pg->status=='Active') selected @endif>{{language_data('Active')}}</option>
                                        <option value="Inactive"  @if($pg->status=='Inactive') selected @endif>{{language_data('Inactive')}}</option>
                                    </select>
                                </div>

                                <input type="hidden" value="{{$pg->id}}" name="cmd">
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
