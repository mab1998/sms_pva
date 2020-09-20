@extends('client')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('View Invoice',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')

            <div class="panel">
                <div class="panel-body p-none">
                    <div class="p-20">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="col-lg-6 col-md-3 col-sm-3 col-xs-12">
                                    <div class="m-b-5">
                                        <img src="<?php echo asset(app_config('AppLogo')); ?>" alt="Logo">
                                    </div>
                                    <address>
                                        {!!app_config('Address')!!}
                                    </address>

                                    <div class="m-t-20">
                                        <h3 class="panel-title">{{language_data('Invoice To',Auth::guard('client')->user()->lan_id)}}: </h3>
                                        <h3 class="invoice-to-client-name">{{$inv->client_name}}</h3>
                                    </div>

                                    <address>
                                        {{$client->address1}} <br>
                                        {{$client->address2}} <br>
                                        {{$client->state}}, {{$client->city}} - {{$client->postcode}},  {{$client->country}}
                                        <br><br>
                                        {{language_data('Phone',Auth::guard('client')->user()->lan_id)}}: {{$client->phone}}
                                        <br>
                                        {{language_data('Email',Auth::guard('client')->user()->lan_id)}}: {{$client->email}}
                                    </address>

                                </div>

                                <div class="col-lg-6 col-md-3 col-sm-3 col-xs-12">


                                    <div class="btn-group pull-right" aria-label="...">

                                        @if($inv->status=='Unpaid' || $inv->status=='Partially Paid')
                                            <a href="#" data-toggle="modal" data-target="#pay-invoice" class="btn btn-success  btn-sm pay-invoice"><i class="fa fa-check"></i> {{language_data('Pay',Auth::guard('client')->user()->lan_id)}}</a>
                                        @endif

                                        <a href="{{url('user/invoices/client-iview/'.$inv->id)}}" target="_blank" class="btn btn-danger  btn-sm"><i class="fa fa-paper-plane-o"></i> {{language_data('Preview',Auth::guard('client')->user()->lan_id)}}</a>

                                        <a href="{{url('user/invoices/download-pdf/'.$inv->id)}}" class="btn btn-pdf  btn-sm download-pdf"><i class="fa fa-file-pdf-o"></i> {{language_data('PDF',Auth::guard('client')->user()->lan_id)}}</a>
                                        <a href="{{url('user/invoices/iprint/'.$inv->id)}}" target="_blank" class="btn btn-primary  btn-sm"><i class="fa fa-print"></i> {{language_data('Print',Auth::guard('client')->user()->lan_id)}}</a>
                                        <br>
                                        <br>

                                        <div class="modal fade" id="pay-invoice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title" id="myModalLabel">{{language_data('Pay Invoice',Auth::guard('client')->user()->lan_id)}}</h4>
                                                    </div>
                                                    <div class="modal-body">

                                                        <form class="form-some-up" role="form" action="{{url('user/invoices/pay-invoice')}}" method="post">

                                                            <div class="form-group">
                                                                <label>{{language_data('Select Payment Method',Auth::guard('client')->user()->lan_id)}}</label>
                                                                <select class="selectpicker form-control" name="gateway">
                                                                    @foreach($payment_gateways as $pg)
                                                                        <option value="{{$pg->id}}">{{$pg->name}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="text-right">
                                                                <input type="hidden" value="{{$inv->id}}" name="cmd">
                                                                <button type="button" class="btn btn-warning btn-sm" data-dismiss="modal">{{language_data('Close',Auth::guard('client')->user()->lan_id)}}</button>
                                                                <button type="submit" class="btn btn-success btn-sm">{{language_data('Pay',Auth::guard('client')->user()->lan_id)}}</button>
                                                            </div>
                                                        </form>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                            <div class="m-t-20">
                                                <div class="bill-data">
                                                    <p class="m-b-5">
                                                        <span class="bill-data-title">{{language_data('Invoice No',Auth::guard('client')->user()->lan_id)}}:</span>
                                                        <span class="bill-data-value">#{{$inv->id}}</span>
                                                    </p>
                                                    <p class="m-b-5">
                                                        <span class="bill-data-title">{{language_data('Invoice Status',Auth::guard('client')->user()->lan_id)}}:</span>
                                                        @if($inv->status=='Unpaid')
                                                            <span class="bill-data-value"><span class="bill-data-status label-warning">{{language_data('Unpaid',Auth::guard('client')->user()->lan_id)}}</span></span>
                                                        @elseif($inv->status=='Paid')
                                                            <span class="bill-data-value"><span class="bill-data-status label-success">{{language_data('Paid',Auth::guard('client')->user()->lan_id)}}</span></span>
                                                        @elseif($inv->status=='Partially Paid')
                                                            <span class="bill-data-value"><span class="bill-data-status label-info">{{language_data('Partially Paid',Auth::guard('client')->user()->lan_id)}}</span></span>
                                                        @else
                                                            <span class="bill-data-value"><span class="bill-data-status label-danger">{{language_data('Cancelled',Auth::guard('client')->user()->lan_id)}}</span></span>
                                                        @endif
                                                    </p>
                                                    <p class="m-b-5">
                                                        <span class="bill-data-title">{{language_data('Invoice Date',Auth::guard('client')->user()->lan_id)}}:</span>
                                                        <span class="bill-data-value">{{get_date_format($inv->created)}}</span>
                                                    </p>
                                                    <p class="m-b-5">
                                                        <span class="bill-data-title">{{language_data('Due Date',Auth::guard('client')->user()->lan_id)}}:</span>
                                                        <span class="bill-data-value">{{get_date_format($inv->duedate)}}</span>
                                                    </p>
                                                    @if($inv->status=='Paid')
                                                        <p class="m-b-5">
                                                            <span class="bill-data-title">{{language_data('Paid Date',Auth::guard('client')->user()->lan_id)}}:</span>
                                                            <span class="bill-data-value">{{get_date_format($inv->datepaid)}}</span>
                                                        </p>
                                                    @endif

                                                </div>
                                            </div>

                                    </div>


                                </div>

                            </div>

                            <div class="col-lg-12 col-md-3 col-sm-3 col-xs-12">
                                <table class="table table-hover table-ultra-responsive">
                                    <thead>
                                    <tr class="h5 text-dark">
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 65%;">{{language_data('Item',Auth::guard('client')->user()->lan_id)}}</th>
                                        <th style="width: 10%;">{{language_data('Price',Auth::guard('client')->user()->lan_id)}}</th>
                                        <th style="width: 10%;">{{language_data('Quantity',Auth::guard('client')->user()->lan_id)}}</th>
                                        <th style="width: 10%;">{{language_data('Total',Auth::guard('client')->user()->lan_id)}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($inv_items as $it)
                                        <tr>
                                            <td data-label="{{language_data('Item',Auth::guard('client')->user()->lan_id)}}">{{$loop->iteration}}</td>
                                            <td data-label="{{language_data('Price',Auth::guard('client')->user()->lan_id)}}">{{$it->item}}</td>
                                            <td data-label="{{language_data('Quantity',Auth::guard('client')->user()->lan_id)}}">{{app_config('CurrencyCode')}} {{$it->price}}</td>
                                            <td data-label="{{language_data('Total',Auth::guard('client')->user()->lan_id)}}">{{$it->qty}}</td>
                                            <td data-label="{{language_data('Subtotal',Auth::guard('client')->user()->lan_id)}}">{{app_config('CurrencyCode')}} {{$it->subtotal}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-lg-12 col-md-3 col-sm-3 col-xs-12">
                                <div class="invoice-summary">
                                    <div class="row">
                                        <div class="col-lg-2 col-md-3 col-sm-3 col-xs-12">
                                            <div class="inv-block">
                                                <h3 class="count-title">{{language_data('Subtotal',Auth::guard('client')->user()->lan_id)}}</h3>
                                                <p>{{app_config('CurrencyCode')}} {{$inv->subtotal}}</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                            <div class="inv-block">
                                                <h3 class="count-title">{{language_data('Tax',Auth::guard('client')->user()->lan_id)}}</h3>
                                                <p>{{app_config('CurrencyCode')}} {{$tax_sum}}</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                            <div class="inv-block">
                                                <h3 class="count-title">{{language_data('Discount',Auth::guard('client')->user()->lan_id)}}</h3>
                                                <p>{{app_config('CurrencyCode')}} {{$dis_sum}}</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 col-lg-offset-2 col-md-offset-1 col-sm-offset-1 text-right">
                                            <div class="inv-block last">
                                                <h3 class="count-title">{{language_data('Grand Total',Auth::guard('client')->user()->lan_id)}}</h3>
                                                <p>{{app_config('CurrencyCode')}} {{$inv->total}}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                @if($inv->note!='')
                                    <div class="well m-t-5 col-lg-12 col-md-3 col-sm-3 col-xs-12"><b>{{language_data('Invoice Note',Auth::guard('client')->user()->lan_id)}}: </b>{{$inv->note}}</div>
                                @endif

                            </div>

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
