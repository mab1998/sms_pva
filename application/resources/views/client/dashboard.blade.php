@extends('client')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30"></div>
        <div class="p-15 p-t-none p-b-none m-l-10 m-r-10">

            @if(app_config('AppStage') == 'Demo')
                <div class="alert alert-info alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    Sending message will not work on demo version. Its only store in system. But in live version it will work perfectly
                </div>
            @endif

            @include('notification.notify')
        </div>

        <div class="p-15 p-t-none p-b-none">
            <div class="row">
                <div class="col-md-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title text-center">{{language_data('Invoices History',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">
                            {!! $invoices_json->render() !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title text-center">{{language_data('Tickets History',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">
                            {!! $tickets_json->render() !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title text-center">{{language_data('SMS Success History',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">
                            {!! $sms_status_json->render() !!}
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <div class="p-15 p-t-none p-b-none">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title text-center">{{language_data('SMS History By Date',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">
                            {!! $sms_history->render() !!}
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <div class="p-15 p-t-none p-b-none">
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel-body ">
                        <div class="row">
                            <div class="panel">
                                <div class="panel-heading">
                                    <h3 class="panel-title">{{language_data('Recent 5 Invoices',Auth::guard('client')->user()->lan_id)}}</h3>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-hover table-ultra-responsive">
                                        <thead>
                                        <tr>
                                            <th style="width: 45px;">{{language_data('SL',Auth::guard('client')->user()->lan_id)}}</th>
                                            <th style="width: 20px;">{{language_data('Amount',Auth::guard('client')->user()->lan_id)}}</th>
                                            <th style="width: 20px;">{{language_data('Due Date',Auth::guard('client')->user()->lan_id)}}</th>
                                            <th style="width: 15px;">{{language_data('Status',Auth::guard('client')->user()->lan_id)}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        @foreach($recent_five_invoices as $inv)
                                            <tr>
                                                <td data-label="{{language_data('SL',Auth::guard('client')->user()->lan_id)}}">
                                                    <p> {{$loop->iteration}} </p>
                                                </td>
                                                <td data-label="{{language_data('Amount',Auth::guard('client')->user()->lan_id)}}"><p><a href="{{url('user/invoices/view/'.$inv->id)}}">{{us_money_format($inv->total)}}</a> </p>
                                                </td>
                                                <td data-label="{{language_data('Due Date',Auth::guard('client')->user()->lan_id)}}"><p>{{get_date_format($inv->duedate)}}</p></td>
                                                @if($inv->status=='Paid')
                                                    <td data-label="{{language_data('Status',Auth::guard('client')->user()->lan_id)}}"><p class="label label-success label-xs">{{language_data('Paid',Auth::guard('client')->user()->lan_id)}}</p></td>
                                                @elseif($inv->status=='Unpaid')
                                                    <td data-label="{{language_data('Status',Auth::guard('client')->user()->lan_id)}}"><p class="label label-warning label-xs">{{language_data('Unpaid',Auth::guard('client')->user()->lan_id)}}</p></td>
                                                @elseif($inv->status=='Partially Paid')
                                                    <td data-label="{{language_data('Status',Auth::guard('client')->user()->lan_id)}}"><p class="label label-info label-xs">{{language_data('Partially Paid',Auth::guard('client')->user()->lan_id)}}</p></td>
                                                @else
                                                    <td data-label="{{language_data('Status',Auth::guard('client')->user()->lan_id)}}"><p class="label label-danger label-xs">{{language_data('Cancelled',Auth::guard('client')->user()->lan_id)}}</p></td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-lg-6 p-none">
                    <div class="panel-body ">
                        <div class="row">
                            <div class="panel">
                                <div class="panel-heading">
                                    <h3 class="panel-title">{{language_data('Recent 5 Support Tickets',Auth::guard('client')->user()->lan_id)}}</h3>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-hover table-ultra-responsive">
                                        <thead>
                                        <tr>
                                            <th style="width: 30%;">{{language_data('SL',Auth::guard('client')->user()->lan_id)}}</th>
                                            <th style="width: 50%;">{{language_data('Subject',Auth::guard('client')->user()->lan_id)}}</th>
                                            <th style="width: 20%;">{{language_data('Date',Auth::guard('client')->user()->lan_id)}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($recent_five_tickets as $rtic)
                                            <tr>
                                                <td data-label="{{language_data('SL',Auth::guard('client')->user()->lan_id)}}">
                                                    <p>{{$loop->iteration}}</p>
                                                </td>
                                                <td data-label="{{language_data('Subject',Auth::guard('client')->user()->lan_id)}}">
                                                    <p><a href="{{url('user/tickets/view-ticket/'.$rtic->id)}}">{{$rtic->subject}}</a></p>
                                                </td>
                                                <td data-label="{{language_data('Date',Auth::guard('client')->user()->lan_id)}}">
                                                    <p>{{get_date_format($rtic->date)}}</p>
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

            </div>
        </div>


    </section>

@endsection


{{--External Style Section--}}
@section('style')
    {!! Html::script("assets/libs/chartjs/chart.js")!!}
@endsection
