@extends('admin')
@section('style')
    {{--External Style Section--}}
    {!! Html::style("assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css") !!}
@endsection

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Edit Invoice')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Edit Invoice')}}</h3>
                        </div>
                        <div class="panel-body">

                            <form method="post" action="{{url('invoices/post-edit-invoice')}}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{language_data('Client')}}</label>
                                            <select class="selectpicker form-control" disabled>
                                                    <option value="{{$client->id}}" @if($client->id==$inv->cl_id) selected @endif>{{$client->fname}} {{$client->lname}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Invoice Type')}}</label>
                                            <select class="selectpicker form-control invoice-type" name="invoice_type">
                                                <option value="one_time" @if($inv->recurring==0) selected @endif>{{language_data('One Time')}}</option>
                                                <option value="recurring" @if($inv->recurring!=0) selected @endif>{{language_data('Recurring')}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>{{language_data('Invoice Date')}}</label>
                                            <input type="text" class="form-control datePicker" name="invoice_date" value="{{$inv->created}}">
                                        </div>


                                        <div class="show-one-time">
                                            <div class="form-group">
                                                <label>{{language_data('Due Date')}}</label>
                                                <input type="text" class="form-control datePicker" name="due_date" value="{{$inv->duedate}}">
                                            </div>


                                            <div class="form-group">
                                                <label>{{language_data('Paid Date')}}</label>
                                                <input type="text" class="form-control datePicker" name="paid_date" value="{{$inv->datepaid}}">
                                            </div>
                                        </div>

                                        <div class="show-recurring">
                                            <div class="form-group">
                                                <label>{{language_data('Repeat Every')}}</label>
                                                <select class="selectpicker form-control" name="repeat_type">
                                                    <option value="week1" @if($inv->recurring=='+1 week') selected @endif>{{language_data('Week')}}</option>
                                                    <option value="weeks2" @if($inv->recurring=='+2 weeks')selected @endif>{{language_data('2 Weeks')}}</option>
                                                    <option value="month1" @if($inv->recurring=='+1 month')selected @endif>{{language_data('Month')}}</option>
                                                    <option value="months2" @if($inv->recurring=='+2 months')selected @endif>{{language_data('2 Months')}}</option>
                                                    <option value="months3" @if($inv->recurring=='+3 months')selected @endif>{{language_data('3 Months')}}</option>
                                                    <option value="months6" @if($inv->recurring=='+6 months')selected @endif>{{language_data('6 Months')}}</option>
                                                    <option value="year1" @if($inv->recurring=='+1 year')selected @endif>{{language_data('Year')}}</option>
                                                    <option value="years2" @if($inv->recurring=='+2 years')selected @endif>{{language_data('2 Years')}}</option>
                                                    <option value="years3" @if($inv->recurring=='+3 years')selected @endif>{{language_data('3 Years')}}</option>
                                                </select>
                                            </div>
                                            <input type="hidden" value="0" name="paid_date_recurring">

                                        </div>
                                    </div>
                                    <div class="col-lg-8">
                                        <table class="table table-hover table-ultra-responsive" id="invoice_items">
                                            <thead>
                                            <tr>
                                                <th width="30%">{{language_data('Item Name')}}</th>
                                                <th width="15%">{{language_data('Price')}}</th>
                                                <th width="13%">{{language_data('Qty')}}</th>
                                                <th width="12%">{{language_data('Tax')}}</th>
                                                <th width="10%">{{language_data('Discount')}}</th>
                                                <th width="20%">{{language_data('Per Item Total')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($inv_items as $it)
                                            <tr class="info">
                                                <td data-label="{{language_data('Item Name')}}"><input type="text" class="form-control item_name" name="desc[]" value="{{$it->item}}"></td>
                                                <td data-label="{{language_data('Price')}}"><input type="text" class="form-control item_price" name="amount[]" value="{{$it->price}}"></td>
                                                <td data-label="{{language_data('Qty')}}"><input type="text" class="form-control qty" value="{{$it->qty}}" name="qty[]"></td>
                                                <td data-label="{{language_data('Tax')}}"><input type="text" class="form-control tax" name="taxed[]" value="{{$it->tax}}"> </td>
                                                <td data-label="{{language_data('Discount')}}"><input type="text" class="form-control discount" name="discount[]" value="{{$it->discount}}"> </td>
                                                <td data-label="{{language_data('Per Item Total')}}" class="ltotal"><input type="text" class="form-control lvtotal" readonly="" name="ltotal[]" value="{{$it->total}}"></td>
                                            </tr>
                                            @endforeach
                                            </tbody>
                                        </table>

                                        <div class="row bottom-inv-con">
                                            <div class="col-md-6 m-b-5">
                                                <button type="button" class="btn btn-success btn-sm" id="blank-add"><i class="fa fa-plus"></i> {{language_data('Add Item')}}</button>
                                                <button type="button" class="btn btn-danger btn-sm" id="item-remove"><i class="fa fa-minus-circle"></i> {{language_data('Delete')}}</button>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="grand-total-box"><strong>{{language_data('Total')}} :</strong><span id="sub_total">{{$inv->total}}</span></p>
                                            </div>
                                        </div>

                                        <textarea class="form-control" name="notes" rows="3" placeholder="{{language_data('Invoice Note')}}">{{$inv->note}}</textarea>
                                        <br>
                                        <div class="text-right">
                                            <input type="hidden" value="{{$inv->id}}" name="cmd">
                                            <input type="hidden" value="{{$inv->cl_id}}" name="client_id">
                                            <button class="btn btn-success" type="submit"><i class="fa fa-save"></i> {{language_data('Update')}} {{language_data('Invoice')}}</button>
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
    {!! Html::script("assets/js/invoice-edit.js")!!}
    <script>
        var $invoice_type = $('.invoice-type');
        var $show_recurring_invoice = $('.show-recurring');
        var $show_one_time_invoice = $('.show-one-time');
        function changeStateOne(val) {
            if( val == 'one_time') {
                $show_recurring_invoice.hide();
                $show_one_time_invoice.show();
            } else {
                $show_one_time_invoice.hide();
                $show_recurring_invoice.show();
            }
        }
        $invoice_type.on('change', function (e) {
            changeStateOne( $(this).val() );
        });
        changeStateOne( $invoice_type.val() );

    </script>

@endsection