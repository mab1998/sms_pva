@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30 clearfix">
            <h2 class="page-title inline-block">SMPP {{language_data('SMS Gateway')}}</h2>

            <a href="{{url('sms/add-smpp-sms-gateways')}}" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> {{language_data('Add Gateway')}}</a>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">SMPP {{language_data('SMS Gateway')}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 10%;">{{language_data('SL')}}#</th>
                                    <th style="width: 20%;">{{language_data('Gateway Name')}}</th>
                                    <th style="width: 10%;">{{language_data('Schedule SMS')}}</th>
                                    <th style="width: 10%;">{{language_data('Two Way')}}</th>
                                    <th style="width: 10%;">{{language_data('MMS')}}</th>
                                    <th style="width: 10%;">{{language_data('Voice')}}</th>
                                    <th style="width: 10%;">{{language_data('Status')}}</th>
                                    <th style="width: 20%;">{{language_data('Action')}}</th>
                                </tr>
                                </thead>
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
            processing: true,
            serverSide: true,
            ajax: '{!! url('sms/get-all-smpp-gateways-data/') !!}',
            columns: [
              {data: 'id', name: 'id', orderable: false, searchable: false},
              {data: 'name', name: 'name'},
              {data: 'schedule', name: 'schedule', searchable: false},
              {data: 'two_way', name: 'two_way', searchable: false},
              {data: 'mms', name: 'mms', searchable: false},
              {data: 'voice', name: 'voice', searchable: false},
              {data: 'status', name: 'status', searchable: false},
              {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            language: {
              url: '{!! url("assets/libs/data-table/i18n/".get_language_code()->language.".lang") !!}'
            },
            responsive: true
          });


            /*For Delete Gateway*/
            $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                var id = this.id;
              bootbox.confirm("{!! language_data('Are you sure') !!}?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/sms/delete-sms-gateway/" + id;
                    }
                });
            });

        });
    </script>
@endsection