<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{language_data('Invoice No')}}: {{$inv->id}}</title>
    <link rel="icon" type="image/x-icon" href="<?php echo asset(app_config('AppFav')); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{--Global StyleSheet Start--}}
    <link href='http://fonts.googleapis.com/css?family=Roboto:400,300,500,700' rel='stylesheet' type='text/css'>
    {!! Html::style("assets/libs/bootstrap/css/bootstrap.min.css") !!}
    {!! Html::style("assets/libs/font-awesome/css/font-awesome.min.css") !!}
    {!! Html::style("assets/css/style.css") !!}
    {!! Html::style("assets/css/admin.css") !!}
    {!! Html::style("assets/css/responsive.css") !!}

</head>

<body class="m-t-20">
<section class="wrapper-bottom-sec">
    <div class="p-30 p-t-none p-b-none">
        <div class="panel">
            <div class="panel-body p-none">
                <div class="p-20">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="col-lg-6 p-t-20">
                                <div class="m-b-5">
                                    <img src="<?php echo asset(app_config('AppLogo')); ?>" alt="Logo">
                                </div>
                                <address>
                                    {!!app_config('Address')!!}
                                </address>

                                <div class="m-t-20">
                                    <h3 class="panel-title">{{language_data('Invoice To')}}: </h3>
                                    <h3 class="invoice-to-client-name">{{$inv->client_name}}</h3>
                                </div>

                                <address>
                                    {{$client->address1}} <br>
                                    {{$client->address2}} <br>
                                    {{$client->state}}, {{$client->city}} - {{$client->postcode}}, {{$client->country}}
                                    <br><br> {{language_data('Phone')}}: {{$client->phone}}
                                    <br> {{language_data('Email')}}: {{$client->email}}
                                </address>

                            </div>

                            <div class="col-lg-6 p-t-20">


                                <div class="btn-group pull-right" aria-label="...">
                                    <a href="{{url('invoices/iprint/'.$inv->id)}}" target="_blank" class=""><i class="fa fa-print"></i> {{language_data('Printable Version')}}</a>
                                    <br> <br>
                                    <div class="m-t-20">
                                        <div class="bill-data">
                                            <p class="m-b-5">
                                                <span class="bill-data-title">{{language_data('Invoice No')}}:</span>
                                                <span class="bill-data-value">#{{$inv->id}}</span>
                                            </p>
                                            <p class="m-b-5">
                                                <span class="bill-data-title">{{language_data('Invoice Status')}}:</span>
                                                @if($inv->status=='Unpaid')
                                                    <span class="bill-data-value"><span class="bill-data-status label-warning">{{language_data('Unpaid')}}</span></span>
                                                @elseif($inv->status=='Paid')
                                                    <span class="bill-data-value"><span class="bill-data-status label-success">{{language_data('Paid')}}</span></span>
                                                @elseif($inv->status=='Partially Paid')
                                                    <span class="bill-data-value"><span class="bill-data-status label-info">{{language_data('Partially Paid')}}</span></span>
                                                @else
                                                    <span class="bill-data-value"><span class="bill-data-status label-danger">{{language_data('Cancelled')}}</span></span>
                                                @endif
                                            </p>
                                            <p class="m-b-5">
                                                <span class="bill-data-title">{{language_data('Invoice Date')}}:</span>
                                                <span class="bill-data-value">{{get_date_format($inv->created)}}</span>
                                            </p>
                                            <p class="m-b-5">
                                                <span class="bill-data-title">{{language_data('Due Date')}}:</span>
                                                <span class="bill-data-value">{{get_date_format($inv->duedate)}}</span>
                                            </p>
                                            @if($inv->status=='Paid')
                                                <p class="m-b-5">
                                                    <span class="bill-data-title">{{language_data('Paid Date')}}:</span>
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
                                    <th style="width: 5%;" >{{language_data('SL')}}</th>
                                    <th style="width: 65%;" >{{language_data('Item')}}</th>
                                    <th style="width: 10%;" >{{language_data('Price')}}</th>
                                    <th style="width: 10%;" >{{language_data('Quantity')}}</th>
                                    <th style="width: 10%;" >{{language_data('Total')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($inv_items as $it)
                                    <tr>
                                        <td data-label="{{language_data('SL')}}">{{$loop->iteration}}</td>
                                        <td data-label="{{language_data('Item')}}">{{$it->item}}</td>
                                        <td data-label="{{language_data('Price')}}">{{app_config('CurrencyCode')}} {{$it->price}}</td>
                                        <td data-label="{{language_data('Quantity')}}">{{$it->qty}}</td>
                                        <td data-label="{{language_data('Total')}}">{{app_config('CurrencyCode')}} {{$it->subtotal}}</td>
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
                                            <h3 class="count-title">{{language_data('Subtotal')}}</h3>
                                            <p>{{app_config('CurrencyCode')}} {{$inv->subtotal}}</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                        <div class="inv-block">
                                            <h3 class="count-title">{{language_data('Tax')}}</h3>
                                            <p>{{app_config('CurrencyCode')}} {{$tax_sum}}</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                                        <div class="inv-block">
                                            <h3 class="count-title">{{language_data('Discount')}}</h3>
                                            <p>{{app_config('CurrencyCode')}} {{$dis_sum}}</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 col-lg-offset-2 col-md-offset-1 col-sm-offset-1 text-right">
                                        <div class="inv-block last">
                                            <h3 class="count-title">{{language_data('Grand Total')}}</h3>
                                            <p>{{app_config('CurrencyCode')}} {{$inv->total}}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            @if($inv->note!='')
                                <div class="well m-t-5 col-lg-12 col-md-3 col-sm-3 col-xs-12"><b>{{language_data('Invoice Note')}}: </b>{{$inv->note}}
                                </div>
                            @endif

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{--Global JavaScript Start--}}
{!! Html::script("assets/libs/jquery-1.10.2.min.js") !!}
{!! Html::script("assets/libs/jquery.slimscroll.min.js") !!}
{!! Html::script("assets/libs/bootstrap/js/bootstrap.min.js") !!}

</body>

</html>
