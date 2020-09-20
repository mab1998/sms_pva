@extends('admin')
@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Message Details')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">

            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading"></div>
                        <div class="panel-body">
                            <div class="col-lg-6">
                                <table class="table table-ultra-responsive text-uppercase">
                                    <tr>
                                        <td class="text-right">{{language_data('Sending User')}}:</td>

                                        @if($inbox_info->userid=='0')
                                            <td>{{language_data('Admin')}}</td>
                                        @else
                                            <td><a href="{{url('clients/view/'.$inbox_info->user_id)}}">{{client_info($inbox_info->user_id)->fname}} {{client_info($inbox_info->user_id)->lname}}</a> </td>
                                        @endif
                                    </tr>

                                    <tr>
                                        <td class="text-right">{{language_data('Created At')}}:</td>
                                        <td>{{$inbox_info->updated_at}}</td>
                                    </tr>

                                    <tr>
                                        <td class="text-right">{{language_data('From')}}:</td>
                                        <td>{{$inbox_info->sender}}</td>
                                    </tr>

                                </table>
                            </div>
                            <div class="col-lg-6">
                                <table class="table table-ultra-responsive text-uppercase">

                                    <tr>
                                        <td class="text-right">{{language_data('To')}}:</td>
                                        <td>{{$inbox_info->receiver}}</td>
                                    </tr>

                                    <tr>
                                        <td class="text-right">{{language_data('Status')}}:</td>
                                        <td><span class="text-danger">{{$inbox_info->status}}</span></td>
                                    </tr>

                                    <tr>
                                        <td class="text-right">{{language_data('Message')}}:</td>
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
