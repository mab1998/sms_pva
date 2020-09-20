@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('All Invoices',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('All Invoices',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 10%;">#</th>
                                    <th style="width: 10%;">{{language_data('Amount',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 15%;">{{language_data('Invoice Date',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 15%;">{{language_data('Due Date',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 10%;">{{language_data('Status',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 15%;">{{language_data('Type',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 30%;">{{language_data('Manage',Auth::guard('client')->user()->lan_id)}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($invoices as $in)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{app_config('CurrencyCode')}} {{$in->total}}</td>
                                        <td>{{get_date_format($in->created)}}</td>
                                        <td>{{get_date_format($in->duedate)}}</td>
                                        <td>
                                            @if($in->status=='Unpaid')
                                                <span class="label label-warning">{{language_data('Unpaid',Auth::guard('client')->user()->lan_id)}}</span>
                                            @elseif($in->status=='Paid')
                                                <span class="label label-success">{{language_data('Paid',Auth::guard('client')->user()->lan_id)}}</span>
                                            @elseif($in->status=='Cancelled')
                                                <span class="label label-danger">{{language_data('Cancelled',Auth::guard('client')->user()->lan_id)}}</span>
                                            @else
                                                <span class="label label-info">{{language_data('Partially Paid',Auth::guard('client')->user()->lan_id)}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($in->recurring=='0')
                                                <span class="label label-success"> {{language_data('Onetime',Auth::guard('client')->user()->lan_id)}}</span>
                                            @else
                                                <span class="label label-info"> {{language_data('Recurring',Auth::guard('client')->user()->lan_id)}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{url('user/invoices/view/'.$in->id)}}" class="btn btn-success btn-xs"><i class="fa fa-eye"></i> {{language_data('View',Auth::guard('client')->user()->lan_id)}}</a>
                                        </td>
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

    <script>
        $(document).ready(function(){
          $('.data-table').DataTable({
            language: {
              url: '{!! url("assets/libs/data-table/i18n/".get_language_code(Auth::guard('client')->user()->lan_id)->language.".lang") !!}'
            },
            responsive: true
          })
        });
    </script>
@endsection
