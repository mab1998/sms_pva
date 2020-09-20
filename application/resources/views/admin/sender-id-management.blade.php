@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30 clearfix">
            <h2 class="page-title inline-block">{{language_data('All')}} {{language_data('Sender ID')}}</h2>

            <a href="{{url('sms/add-sender-id')}}" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> {{language_data('Add Sender ID')}}</a>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('All')}} {{language_data('Sender ID')}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 10%;">{{language_data('SL')}}#</th>
                                    <th style="width: 45%;">{{language_data('Sender ID')}}</th>
                                    <th style="width: 20%;">{{language_data('Status')}}</th>
                                    <th style="width: 25%;">{{language_data('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($sender_id as $si)
                                    <tr>
                                        <td data-label="SL">{{ $loop->iteration }}</td>
                                        <td data-label="Sender ID"><p>{{$si->sender_id}}</p></td>

                                        @if($si->status=='unblock')
                                            <td data-label="Status"><p class="label label-success">{{language_data('Unblock')}}</p></td>
                                        @elseif($si->status=='block')
                                            <td data-label="Status"><p class="label label-danger">{{language_data('Block')}}</p></td>
                                        @else
                                            <td data-label="Status"><p class="label label-warning">{{language_data('Pending')}}</p></td>
                                        @endif

                                        <td data-label="Actions">
                                            <a class="btn btn-success btn-xs" href="{{url('sms/view-sender-id/'.$si->id)}}" ><i class="fa fa-edit"></i> {{language_data('Manage')}}</a>
                                            <a href="#" class="btn btn-danger btn-xs cdelete" id="{{$si->id}}"><i class="fa fa-trash"></i> {{language_data('Delete')}}</a>
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
    {!! Html::script("assets/js/bootbox.min.js")!!}

    <script>
        $(document).ready(function(){
              $('.data-table').DataTable({
                language: {
                  url: '{!! url("assets/libs/data-table/i18n/".get_language_code()->language.".lang") !!}'
                },
                responsive: true
              });

            /*For Delete Sender ID*/
            $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                var id = this.id;
              bootbox.confirm("{!! language_data('Are you sure') !!}?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/sms/delete-sender-id/" + id;
                    }
                });
            });

        });
    </script>
@endsection