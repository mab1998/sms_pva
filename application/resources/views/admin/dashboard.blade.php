@extends('admin')

@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30"></div>
        <div class="p-15 p-t-none p-b-none m-l-10 m-r-10">

            @if(app_config('AppStage') == 'Demo')
                <div class="alert alert-info alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    Sending message will not work on demo version. Its only store in system. But in live version it will work perfectly.
                </div>
            @endif

            @include('notification.notify')
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="panel-body">
                    <div class="row text-center">

                        <div class="col-sm-3 m-b-15">
                            <div class="z-shad-1">
                                <div class="bg-success text-white p-15 clearfix">
                                    <span class="pull-left font-45 m-l-10"><i class="fa fa-user"></i></span>

                                    <div class="pull-right text-right m-t-15">
                                        <span class="small m-b-5 font-15">{{$total_clients}} {{language_data('Client')}}(s)</span>
                                        <br>
                                        <a href="{{url('clients/add')}}" class="btn btn-complete btn-xs text-uppercase">{{language_data('Add New')}}</a>
                                    </div>

                                </div>
                            </div>
                        </div>


                        <div class="col-sm-3 m-b-15">
                            <div class="z-shad-1">
                                <div class="bg-primary text-white p-15 clearfix">
                                    <span class="pull-left font-45 m-l-10"><i class="fa fa-users"></i></span>

                                    <div class="pull-right text-right m-t-15">
                                        <span class="small m-b-5 font-15">{{$total_groups}} {{language_data('Client Group')}}(s)</span>
                                        <br>
                                        <a href="{{url('clients/groups')}}" class="btn btn-complete btn-xs text-uppercase">{{language_data('Add New')}}</a>
                                    </div>

                                </div>
                            </div>
                        </div>


                        <div class="col-sm-3 m-b-15">
                            <div class="z-shad-1">
                                <div class="bg-warning text-white p-15 clearfix">
                                    <span class="pull-left font-45 m-l-10"><i class="fa fa-credit-card"></i></span>

                                    <div class="pull-right text-right m-t-15">
                                        <span class="small m-b-5 font-15">{{$total_invoice}} {{language_data('Invoices')}}</span>
                                        <br>
                                        <a href="{{url('invoices/add')}}" class="btn btn-complete btn-xs text-uppercase">{{language_data('Add New')}}</a>
                                    </div>

                                </div>
                            </div>
                        </div>


                        <div class="col-sm-3 m-b-15">
                            <div class="z-shad-1">
                                <div class="bg-danger text-white p-15 clearfix">
                                    <span class="pull-left font-45 m-l-10"><i class="fa fa-ticket"></i></span>

                                    <div class="pull-right text-right m-t-15">
                                        <span class="small m-b-5 font-15">{{$total_tickets}} {{language_data('Tickets')}}</span>
                                        <br>
                                        <a href="{{url('support-tickets/create-new')}}" class="btn btn-complete btn-xs text-uppercase">{{language_data('Add New')}}</a>
                                    </div>

                                </div>
                            </div>
                        </div>



                    </div>
                </div>
            </div>
        </div>

        <div class="p-15 p-t-none p-b-none">
            <div class="row">
                <div class="col-md-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title text-center">{{language_data('Invoices History')}}</h3>
                        </div>
                        <div class="panel-body">
                            {!! $invoices_json->render() !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title text-center">{{language_data('Tickets History')}}</h3>
                        </div>
                        <div class="panel-body">
                            {!! $tickets_json->render() !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title text-center">{{language_data('SMS Success History')}}</h3>
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
                            <h3 class="panel-title text-center">{{language_data('SMS History By Date')}}</h3>
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
                                    <h3 class="panel-title">{{language_data('Recent 5 Invoices')}}</h3>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-hover table-ultra-responsive">
                                        <thead>
                                        <tr>
                                            <th style="width: 45px;">{{language_data('Client')}}</th>
                                            <th style="width: 20px;">{{language_data('Amount')}}</th>
                                            <th style="width: 20px;">{{language_data('Due Date')}}</th>
                                            <th style="width: 15px;">{{language_data('Status')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        @foreach($recent_five_invoices as $inv)
                                            <tr>
                                                <td data-label="{{language_data('Client')}}">
                                                    <p><a href="{{url('clients/view/'.$inv->cl_id)}}"> {{$inv->client_name}} </a></p>
                                                </td>
                                                <td data-label="{{language_data('Amount')}}"><p><a href="{{url('invoices/view/'.$inv->id)}}">{{us_money_format($inv->total)}}</a> </p>
                                                </td>
                                                <td data-label="{{language_data('Due Date')}}"><p>{{get_date_format($inv->duedate)}}</p></td>
                                                @if($inv->status=='Paid')
                                                    <td data-label="{{language_data('Status')}}"><p class="label label-success label-xs">{{language_data('Paid')}}</p></td>
                                                @elseif($inv->status=='Unpaid')
                                                    <td data-label="{{language_data('Status')}}"><p class="label label-warning label-xs">{{language_data('Unpaid')}}</p></td>
                                                @elseif($inv->status=='Partially Paid')
                                                    <td data-label="{{language_data('Status')}}"><p class="label label-info label-xs">{{language_data('Partially Paid')}}</p></td>
                                                @else
                                                    <td data-label="{{language_data('Status')}}"><p class="label label-danger label-xs">{{language_data('Cancelled')}}</p></td>
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
                                    <h3 class="panel-title">{{language_data('Recent 5 Support Tickets')}}</h3>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-hover table-ultra-responsive">
                                        <thead>
                                        <tr>
                                            <th style="width: 30%;">{{language_data('Email')}}</th>
                                            <th style="width: 50%;">{{language_data('Subject')}}</th>
                                            <th style="width: 20%;">{{language_data('Date')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($recent_five_tickets as $rtic)
                                            <tr>
                                                <td data-label="{{language_data('Email')}}">
                                                    <p><a href="{{url('clients/view/'.$rtic->cl_id)}}"> {{$rtic->email}}</a></p>
                                                </td>
                                                <td data-label="{{language_data('Subject')}}">
                                                    <p><a href="{{url('support-tickets/view-ticket/'.$rtic->id)}}">{{$rtic->subject}}</a></p>
                                                </td>
                                                <td data-label="{{language_data('Date')}}">
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
