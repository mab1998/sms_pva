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

                                <div class="form-group">
                                    <label>{{language_data('Amount',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" disabled class="form-control" value="{{$amount}}">
                                </div>

                                <div class="form-group">
                                    <label>Name on Credit Card</label>
                                    <input type="text" class="form-control" name="card_holder_name" required>
                                </div>

                                <div class="form-group">
                                    <label>Credit Card Number</label>
                                    <input type="text" class="form-control" name="credit_card_number" required>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Expiration Month (Ex. 06 or 12):</label>
                                            <input type="number" class="form-control" name="expiration_month" required>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Expiration Year (Ex. 20 or 22):</label>
                                            <input type="number" class="form-control" name="expiration_year" required>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label>Security Code (The last 3 digits behind the card)</label>
                                    <input type="text" class="form-control" name="security_code" required>
                                </div>

                                <input type="hidden" name="cmd" value="{{$cmd}}">
                                <input type="hidden" name="user_token" value="{{$token}}">


                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-send"></i> {{language_data('Pay',Auth::guard('client')->user()->lan_id)}} </button>
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
