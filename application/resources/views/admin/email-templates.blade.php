@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Email Templates')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Email Templates')}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 10%;">{{language_data('SL')}}#</th>
                                    <th style="width: 30%;">{{language_data('Template Name')}}</th>
                                    <th style="width: 35%;">{{language_data('Subject')}}</th>
                                    <th style="width: 10%;">{{language_data('Status')}}</th>
                                    <th style="width: 15%;">{{language_data('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($d as $et)
                                    <tr>
                                        <td data-label="{{language_data('SL')}}">{{$loop->iteration}}</td>
                                        <td data-label="{{language_data('Template Name')}}"><p><a href="{{url('settings/email-template-manage/'.$et->id)}}"> {{$et->tplname}}</a></p></td>
                                        <td data-label="{{language_data('Subject')}}"><p>{{$et->subject}}</p></td>
                                        @if($et->status=='1')
                                            <td data-label="{{language_data('Status')}}"><p class="btn btn-success btn-xs">{{language_data('Active')}}</p></td>
                                        @else
                                            <td data-label="{{language_data('Status')}}"><p class="btn btn-warning btn-xs">{{language_data('Inactive')}}</p></td>
                                        @endif
                                        <td data-label="{{language_data('Action')}}">
                                            <a class="btn btn-success btn-xs"
                                               href="{{url('settings/email-template-manage/'.$et->id)}}"><i class="fa fa-edit"></i> {{language_data('Manage')}}</a>
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
          $('.data-table').DataTable({
            language: {
              url: '{!! url("assets/libs/data-table/i18n/".get_language_code()->language.".lang") !!}'
            },
            responsive: true
          });

        });
    </script>
@endsection
