@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Buy Unit',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-5">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Recharge your account Online',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('users/post-buy-unit')}}">
                                <div class="form-group">
                                    <label>{{language_data('Number of Units',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" required name="number_unit"
                                           id="number_unit">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Unit Price',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" readonly name="unit_price" id="unit_price">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Amount to Pay',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" readonly name="pay_amount" id="pay_amount">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Select Payment Method',Auth::guard('client')->user()->lan_id)}}</label>
                                    <select class="selectpicker form-control" name="gateway">
                                        @foreach($payment_gateways as $pg)
                                            <option value="{{$pg->id}}">{{$pg->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Transaction Fee',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" readonly name="trans_fee" id="trans_fee">
                                </div>

                                <div class="form-group">
                                    <label>{{language_data('Total',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" readonly name="total" id="total">
                                </div>


                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="submit" class="btn btn-success btn-sm pull-right purchase_button"><i
                                            class="fa fa-plus"></i> {{language_data('Purchase Now',Auth::guard('client')->user()->lan_id)}} </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Price Bundles',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 35%;">{{language_data('Number of Units',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 35%;">{{language_data('Transaction Fee',Auth::guard('client')->user()->lan_id)}}(%)</th>
                                    <th style="width: 30%;">{{language_data('Price Per Unit',Auth::guard('client')->user()->lan_id)}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($bundles as $b)
                                    <tr>
                                        <td data-label="Number of units">{{ $b->unit_from }} - {{$b->unit_to}}</td>
                                        <td data-label="Transaction fee">{{ $b->trans_fee }}</td>
                                        <td data-label="Price"><p>{{$b->price}} </p></td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
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
    {!! Html::script("assets/libs/data-table/datatables.min.js")!!}
    {!! Html::script("assets/js/bootbox.min.js")!!}

    <script>
        $(document).ready(function () {
            $('.data-table').DataTable({
                "order": [[ 1, "desc" ]],
                language: {
                    url: '{!! url("assets/libs/data-table/i18n/".get_language_code(Auth::guard('client')->user()->lan_id)->language.".lang") !!}'
                },
                responsive: true
            });

            /*Transaction Loading*/

            var timer;

            $("#number_unit").on('keyup', function () {
                clearTimeout(timer);  //clear any running timeout on key up
                timer = setTimeout(function () { //then give it a second to see if the user is finished
                    var id = $("#number_unit").val();
                    var _url = $("#_url").val();
                    var dataString = 'unit_number=' + id;

                    $.ajax
                    ({
                        type: "POST",
                        url: _url + '/user/get-transaction',
                        data: dataString,
                        cache: false,
                        success: function (data) {
                            $("#unit_price").val(data.unit_price);
                            $("#pay_amount").val(data.amount_to_pay);
                            $("#trans_fee").val(data.transaction_fee);
                            $("#total").val(data.total);

                            if (data.unit_price == 'Price Bundle empty'){
                                $(".purchase_button").hide();
                            }
                        }
                    });
                }, 1000);
            });

        });
    </script>
@endsection
