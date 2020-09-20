@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Support Tickets',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')

            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Support Tickets',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{language_data('Email',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th>{{language_data('Subject',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th>{{language_data('Date',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th>{{language_data('Status',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th class="text-right" width="20%">{{language_data('Action',Auth::guard('client')->user()->lan_id)}}</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($st as $in)
                                    <tr>
                                        <td>{{$loop->iteration}} </td>
                                        <td>{{$in->email}}</td>
                                        <td>{{$in->subject}}</td>
                                        <td>{{get_date_format($in->date)}}</td>
                                        <td>
                                            @if($in->status=='Pending')
                                                <span class="label label-danger">{{language_data('Pending',Auth::guard('client')->user()->lan_id)}}</span>
                                            @elseif($in->status=='Answered')
                                                <span class="label label-success">{{language_data('Answered',Auth::guard('client')->user()->lan_id)}}</span>
                                            @elseif($in->status=='Customer Reply')
                                                <span class="label label-info">{{language_data('Customer Reply',Auth::guard('client')->user()->lan_id)}}</span>
                                            @else
                                                <span class="label label-primary">{{language_data('Closed',Auth::guard('client')->user()->lan_id)}}</span>
                                            @endif
                                        </td>

                                        <td class="text-right">
                                            <a href="{{url('user/tickets/view-ticket/'.$in->id)}}" class="btn btn-success btn-xs"><i class="fa fa-eye"></i> {{language_data('View',Auth::guard('client')->user()->lan_id)}}</a>
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
        $(document).ready(function () {

            /*For DataTable*/
          $('.data-table').DataTable({
            language: {
              url: '{!! url("assets/libs/data-table/i18n/".get_language_code(Auth::guard('client')->user()->lan_id)->language.".lang") !!}'
            },
            responsive: true
          })
        });
    </script>


@endsection
