@extends('client')


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Purchase keyword',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Purchase keyword',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('users/keywords/post-purchase-keyword')}}">

                                <div class="form-group">
                                    <label>{{language_data('Amount to Pay',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" readonly name="pay_amount" id="pay_amount" value="{{$keyword->price}}">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Select Payment Method',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="selectpicker form-control" name="gateway">
                                        @foreach($payment_gateways as $pg)
                                            <option value="{{$pg->id}}">{{$pg->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" value="{{$keyword->id}}" name="keyword_id">
                                <button type="submit" class="btn btn-success btn-sm pull-right purchase_button"><i
                                            class="fa fa-plus"></i> {{language_data('Purchase Now',Auth::guard('client')->user()->lan_id)}} </button>
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
