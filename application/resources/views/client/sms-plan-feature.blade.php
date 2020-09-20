@extends('client')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{$sms_plan->plan_name}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{$sms_plan->plan_name}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table table-ultra-responsive">
                                <thead>
                                <tr>
                                    <th style="width: 60%;"></th>
                                    <th style="width: 40%;" class="text-center">{{$sms_plan->plan_name}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($plan_feature as $feature)
                                    <tr>
                                        <td data-label="feature name">{{ $feature->feature_name }}</td>
                                        <td data-label="value" class="text-center"><p>{{$feature->feature_value}}</p></td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td></td>
                                    <td><button class="btn btn-success center-block" data-toggle="modal" data-target="#purchase_now"><i class="fa fa-shopping-cart"></i> {{language_data('Purchase Now',Auth::guard('client')->user()->lan_id)}}</button> </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>


            <div class="modal fade" id="purchase_now" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">{{language_data('Purchase SMS Plan',Auth::guard('client')->user()->lan_id)}}</h4>
                        </div>
                        <div class="modal-body">

                            <form class="form-some-up" role="form" action="{{url('user/sms/post-purchase-sms-plan')}}" method="post">

                                <div class="form-group">
                                    <label>{{language_data('Select Payment Method',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="selectpicker form-control" name="gateway">
                                        @foreach($payment_gateways as $pg)
                                            <option value="{{$pg->id}}">{{$pg->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="text-right">
                                    <input type="hidden" value="{{$sms_plan->id}}" name="cmd">
                                    <button type="button" class="btn btn-warning btn-sm" data-dismiss="modal">{{language_data('Close',Auth::guard('client')->user()->lan_id)}}</button>
                                    <button type="submit" class="btn btn-success btn-sm">{{language_data('Purchase Now',Auth::guard('client')->user()->lan_id)}}</button>
                                </div>
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