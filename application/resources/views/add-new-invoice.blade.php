@extends('client')

@section('style')
    {{--External Style Section--}}
    {!! Html::style("assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css") !!}
@endsection

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Add New Invoice')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Add New Invoice')}}</h3>
                        </div>
                        <div class="panel-body">

                            <form method="post" action="{{url('invoices/post-new-invoice')}}">
                                <input type="hidden" name="_order_id" value="{{$sms_plan->id}}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="row">
                                <div class="col-lg-4">
                                  

                                    <div class="form-group">
                                        <label>{{language_data('Invoice Type')}}</label>
                                        <select class="selectpicker form-control invoice-type" name="invoice_type">
                                            <!-- <option value="one_time">{{language_data('One Time')}}</option> -->
                                            <option value="recurring">{{language_data('Recurring')}}</option>
                                        </select>
                                    </div>

                                    <!-- <div class="form-group">
                                        <label>{{language_data('Invoice Date')}}</label>
                                        <input type="text" class="form-control datePicker" name="invoice_date">
                                    </div> -->

                                    <!-- <div class="show-one-time">

                                        <div class="form-group">
                                            <label>{{language_data('Due Date')}}</label>
                                            <input type="text" class="form-control datePicker" name="due_date">
                                        </div>


                                        <div class="form-group">
                                            <label>{{language_data('Paid Date')}}</label>
                                            <input type="text" class="form-control datePicker" name="paid_date">
                                        </div>
                                    </div> -->


                                    <!-- <div class="show-recurring"> -->
                                        <div class="form-group">
                                            <label>{{language_data('Repeat Every')}}</label>
                                            <select class="selectpicker form-control" id="repeat_type" name="repeat_type">
                                                <option value="week1">{{language_data('Week')}}</option>
                                                <option value="weeks2">{{language_data('2 Weeks')}}</option>
                                                <option value="month1" selected>{{language_data('Month')}}</option>
                                                <option value="months2">{{language_data('2 Months')}}</option>
                                                <option value="months3">{{language_data('3 Months')}}</option>
                                                <option value="months6">{{language_data('6 Months')}}</option>
                                                <option value="year1">{{language_data('Year')}}</option>
                                                <option value="years2">{{language_data('2 Years')}}</option>
                                                <option value="years3">{{language_data('3 Years')}}</option>
                                            </select>
                                        </div>
                                        <input type="hidden" value="0" name="paid_date_recurring">

                                    <!-- </div> -->

                                    <h3>Price : </h3> <h3 id="price_unit">{{$sms_plan->price}}</h3>
                                    <h3>Total : </h3> <h3 id="price_total">{{$sms_plan->price}}</h3>
                                    
                                </div>
                                <div class="col-lg-8">
                                


                                    <div class="p-30">
            <h2 class="page-title">{{$sms_plan->plan_name}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                  
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

                <textarea class="form-control" name="notes" rows="3" placeholder="{{language_data('Invoice Note')}}"></textarea>


            </div>


            <div class="modal fade" id="purchase_now" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">{{language_data('Purchase SMS Plan',Auth::guard('client')->user()->lan_id)}}</h4>
                        </div>
                        <div class="modal-body">

                            <!-- <form class="form-some-up" role="form" action="{{url('user/sms/post-purchase-sms-plan')}}" method="post"> -->

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
                            <!-- </form> -->

                        </div>
                    </div>
                </div>
            </div>

        </div>


                                    <br>
                                    <div class="text-right">
                                        <button class="btn btn-success" type="submit"><i class="fa fa-save"></i> {{language_data('Create Invoice')}}</button>
                                    </div>

                                </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

@endsection

{{--External Script Section--}}
@section('script')
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/libs/moment/moment.min.js")!!}
    {!! Html::script("assets/libs/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    {!! Html::script("assets/js/invoice.js")!!}
    <script>
        var $time_list = $('#repeat_type');
        var $price_unit =$('#price_unit');
        var $price_total =$('#price_total');

        var $show_recurring_invoice = $('.show-recurring');
        var $show_one_time_invoice = $('.show-one-time');
        // function changeStateOne(val) {
        //     if( val =='months2') {
        //         $show_recurring_invoice.hide();
        //         $show_one_time_invoice.show();
        //     } else {
        //         $show_one_time_invoice.hide();
        //         $show_recurring_invoice.show();
        //     }
        // }
        $time_list.on('change', function (e) {

            console.log($time_list.val())
            if ($time_list.val()=="months2"){
                
                $price_total.html($price_unit.text()*2)
            }
            if ($time_list.val()=="months3"){
                
                $price_total.html($price_unit.text()*3)
            }
            if ($time_list.val()=="months6"){
                
                $price_total.html($price_unit.text()*6)
            }
            if ($time_list.val()=="year1"){
                
                $price_total.html($price_unit.text()*12)
            }
        });
        // changeStateOne( $invoice_type.val() );

    </script>

@endsection