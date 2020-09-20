@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('View Invoice')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')

            <div class="panel">
                <div class="panel-body p-20">
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
                                    <h3 class="panel-title">{{language_data('Invoice To')}}: </h3>
                                    <h3 class="invoice-to-client-name">{{$inv->client_name}}</h3>
                                </div>

                                <address>
                                    {{$client->address1}} <br>
                                    {{$client->address2}} <br>
                                    {{$client->state}}, {{$client->city}} - {{$client->postcode}}
                                    , {{$client->country}}
                                    <br><br>
                                    {{language_data('Phone')}}: {{$client->phone}}
                                    <br>
                                    {{language_data('Email')}}: {{$client->email}}
                                </address>

                            </div>

                            <div class="col-lg-6 col-md-3 col-sm-3 col-xs-12">
                                <div class="btn-group pull-right" aria-label="...">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn  btn-success btn-sm dropdown-toggle"
                                                data-toggle="dropdown"
                                                aria-expanded="false">{{language_data('Mark As')}} <span
                                                    class="caret"></span></button>
                                        <ul class="dropdown-menu" role="menu">
                                            @if($inv->status!='Paid')
                                                <li><a href="#" id="mark_paid"
                                                       data-value="{{$inv->id}}">{{language_data('Paid')}}</a></li>
                                            @endif
                                            @if($inv->status!='Unpaid')
                                                <li><a href="#" id="mark_unpaid"
                                                       data-value="{{$inv->id}}">{{language_data('Unpaid')}}</a>
                                                </li>
                                            @endif
                                            @if($inv->status!='Partially Paid')
                                                <li><a href="#" id="mark_partially_paid"
                                                       data-value="{{$inv->id}}">{{language_data('Partially Paid')}}</a>
                                                </li>
                                            @endif
                                            @if($inv->status!='Cancelled')
                                                <li><a href="#" id="mark_cancelled"
                                                       data-value="{{$inv->id}}">{{language_data('Cancelled')}}</a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                    <a href="{{url('invoices/client-iview/'.$inv->id)}}" target="_blank" class="btn btn-danger  btn-sm"><i class="fa fa-paper-plane-o"></i> {{language_data('Preview')}}</a>
                                    <a href="{{url('invoices/edit/'.$inv->id)}}" class="btn btn-warning  btn-sm"><i
                                                class="fa fa-pencil"></i> {{language_data('Edit')}}</a>
                                    <a href="#" data-toggle="modal" data-target="#send-email-invoice"
                                       class="btn btn-complete  btn-sm send-email"><i
                                                class="fa fa-envelope"></i> {{language_data('Send')}} {{language_data('Email')}}
                                    </a>
                                    <a href="{{url('invoices/download-pdf/'.$inv->id)}}"
                                       class="btn btn-pdf  btn-sm download-pdf"><i
                                                class="fa fa-file-pdf-o"></i> {{language_data('PDF')}}</a>
                                    <a href="{{url('invoices/iprint/'.$inv->id)}}" target="_blank"
                                       class="btn btn-primary  btn-sm"><i
                                                class="fa fa-print"></i> {{language_data('Print')}}</a>
                                    <br>
                                    <br>

                                    <div class="modal fade" id="send-email-invoice" tabindex="-1" role="dialog"
                                         aria-labelledby="myModalLabel">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"
                                                        id="myModalLabel">{{language_data('Send Invoice')}}</h4>
                                                </div>
                                                <div class="modal-body">

                                                    <form class="form-some-up" role="form"
                                                          action="{{url('invoices/send-invoice-email')}}"
                                                          method="post">

                                                        <div class="form-group">
                                                            <label>{{language_data('Subject')}}</label>
                                                            <input type="text" class="form-control" name="subject"
                                                                   required="">
                                                        </div>

                                                        <div class="form-group">
                                                            <label>{{language_data('Message')}}</label>
                                                            <textarea class="form-control" rows="5"
                                                                      name="message"></textarea>
                                                        </div>

                                                        <div class="text-right">
                                                            <input type="hidden" value="{{$inv->id}}" name="cmd">
                                                            <button type="button" class="btn btn-warning btn-sm"
                                                                    data-dismiss="modal">{{language_data('Close')}}</button>
                                                            <button type="submit"
                                                                    class="btn btn-success btn-sm">{{language_data('Send')}}</button>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="m-t-20">
                                        <div class="bill-data">
                                            <p class="m-b-5">
                                                    <span class="bill-data-title">{{language_data('Invoice No')}}
                                                        :</span>
                                                <span class="bill-data-value">#{{$inv->id}}</span>
                                            </p>
                                            <p class="m-b-5">
                                                    <span class="bill-data-title">{{language_data('Invoice Status')}}
                                                        :</span>
                                                @if($inv->status=='Unpaid')
                                                    <span class="bill-data-value"><span
                                                                class="bill-data-status label-warning">{{language_data('Unpaid')}}</span></span>
                                                @elseif($inv->status=='Paid')
                                                    <span class="bill-data-value"><span
                                                                class="bill-data-status label-success">{{language_data('Paid')}}</span></span>
                                                @elseif($inv->status=='Partially Paid')
                                                    <span class="bill-data-value"><span
                                                                class="bill-data-status label-info">{{language_data('Partially Paid')}}</span></span>
                                                @else
                                                    <span class="bill-data-value"><span
                                                                class="bill-data-status label-danger">{{language_data('Cancelled')}}</span></span>
                                                @endif
                                            </p>
                                            <p class="m-b-5">
                                                    <span class="bill-data-title">{{language_data('Invoice Date')}}
                                                        :</span>
                                                <span class="bill-data-value">{{get_date_format($inv->created)}}</span>
                                            </p>
                                            <p class="m-b-5">
                                                <span class="bill-data-title">{{language_data('Due Date')}}:</span>
                                                <span class="bill-data-value">{{get_date_format($inv->duedate)}}</span>
                                            </p>
                                            @if($inv->status=='Paid')
                                                <p class="m-b-5">
                                                        <span class="bill-data-title">{{language_data('Paid Date')}}
                                                            :</span>
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
    </section>

@endsection

{{--External Style Section--}}
@section('script')
    {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    {!! Html::script("assets/js/bootbox.min.js")!!}

    <script>
      $(document).ready(function () {
        /*For Invoice mark paid*/
        $('#mark_paid').click(function (e) {
          e.preventDefault()
          var id = $(this).data('value')

              bootbox.confirm("{!! language_data('Are you sure') !!}?", function (result) {
            if (result) {
              var _url = $('#_url').val()
              window.location.href = _url + '/invoices/mark-paid/' + id
            }
          })
        })

        /*For Invoice mark as unpaid*/
        $('#mark_unpaid').click(function (e) {
          e.preventDefault()
          var id = $(this).data('value')

              bootbox.confirm("{!! language_data('Are you sure') !!}?", function (result) {
            if (result) {
              var _url = $('#_url').val()
              window.location.href = _url + '/invoices/mark-unpaid/' + id
            }
          })
        })

        /*For Invoice mark as partially paid*/
        $('#mark_partially_paid').click(function (e) {
          e.preventDefault()
          var id = $(this).data('value')

              bootbox.confirm("{!! language_data('Are you sure') !!}?", function (result) {
            if (result) {
              var _url = $('#_url').val()
              window.location.href = _url + '/invoices/mark-partially-paid/' + id
            }
          })
        })

        /*For Invoice mark as cancelled*/
        $('#mark_cancelled').click(function (e) {
          e.preventDefault()
          var id = $(this).data('value')

              bootbox.confirm("{!! language_data('Are you sure') !!}?", function (result) {
            if (result) {
              var _url = $('#_url').val()
              window.location.href = _url + '/invoices/mark-cancelled/' + id
            }
          })
        })

      })
    </script>


@endsection
