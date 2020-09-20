@extends('client')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Pay with Credit Card',Auth::guard('client')->user()->lan_id)}}</h2>
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
                            <form class="" role="form" method="post" action="{{url($post_url)}}">
                                {{ csrf_field() }}
                                <script
                                        src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                                        data-key="{{$gat_info->value}}"
                                        data-amount="{{$stripe_amount}}"
                                        data-currency="{{app_config('Currency')}}"
                                        data-name="{{$plan_name}}"
                                        data-description="Purchase: {{$plan_name}}"
                                        data-image="{{asset(app_config('AppLogo'))}}">
                                </script>
                                <input type="hidden" name="cmd" value="{{$cmd}}">
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