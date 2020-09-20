@extends('client')
@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Message Details',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">

            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading"></div>
                        <div class="panel-body">
                            <div class="col-lg-6">
                                <table class="table table-ultra-responsive">
                                    <tr>
                                        <td class="text-right">{{language_data('Sending User',Auth::guard('client')->user()->lan_id)}}:</td>
                                        <td>{{client_info($inbox_info->userid)->fname}} {{client_info($inbox_info->userid)->lname}}</td>
                                    </tr>

                                    <tr>
                                        <td class="text-right">{{language_data('Created At',Auth::guard('client')->user()->lan_id)}}:</td>
                                        <td>{{$inbox_info->updated_at}}</td>
                                    </tr>

                                    <tr>
                                        <td class="text-right">{{language_data('From',Auth::guard('client')->user()->lan_id)}}:</td>
                                        <td>{{$inbox_info->sender}}</td>
                                    </tr>

                                    <tr>
                                        <td class="text-right">{{language_data('To',Auth::guard('client')->user()->lan_id)}}:</td>
                                        <td>{{$inbox_info->receiver}}</td>
                                    </tr>

                                </table>
                            </div>
                            <div class="col-lg-6">
                                <table class="table table-ultra-responsive">

                                    <tr>
                                        <td class="text-right">{{language_data('Direction',Auth::guard('client')->user()->lan_id)}}:</td>
                                        @if($inbox_info->send_by=='sender')
                                            <td><p>{{language_data('Outgoing',Auth::guard('client')->user()->lan_id)}}</p></td>
                                        @else
                                            <td><p>{{language_data('Incoming',Auth::guard('client')->user()->lan_id)}}</p></td>
                                        @endif
                                    </tr>


                                    <tr>
                                        <td class="text-right">{{language_data('Segments',Auth::guard('client')->user()->lan_id)}}:</td>
                                        <td>{{$inbox_info->amount}}</td>
                                    </tr>


                                    <tr>
                                        <td class="text-right">{{language_data('Status',Auth::guard('client')->user()->lan_id)}}:</td>
                                        <td><span>{{$inbox_info->status}}</span></td>
                                    </tr>

                                    <tr>
                                        <td class="text-right">{{language_data('message',Auth::guard('client')->user()->lan_id)}}:</td>
                                        <td><span>{{$inbox_info->message}}</span></td>
                                    </tr>


                                </table>
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
