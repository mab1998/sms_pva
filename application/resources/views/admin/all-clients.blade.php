@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('All Clients')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('All Clients')}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 20%;">{{language_data('Name')}}</th>
                                    <th style="width: 10%;">{{language_data('User name')}}</th>
                                    <th style="width: 10%;">{{language_data('Created')}}</th>
                                    <th style="width: 15%;">{{language_data('Created By')}}</th>
                                    <th style="width: 10%;">{{language_data('Api Access')}}</th>
                                    <th style="width: 10%;">{{language_data('Status')}}</th>
                                    <th style="width: 25%;">{{language_data('Action')}}</th>
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
                ajax: '{!! url('clients/get-all-clients-data/') !!}',
                columns: [
                  {data: 'name', name: 'name'},
                  {data: 'username', name: 'username'},
                  {data: 'datecreated', name: 'datecreated'},
                  {data: 'parent', name: 'parent'},
                  {data: 'api_access', name: 'api_access'},
                  {data: 'status', name: 'status'},
                  {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                language: {
                  url: '{!! url("assets/libs/data-table/i18n/".get_language_code()->language.".lang") !!}'
                },
                responsive: true
              })



            /*For Delete client*/
            $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                var id = this.id;
              bootbox.confirm("{!! language_data('Are you sure') !!}?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/clients/delete-client/" + id;
                    }
                });
            });

        });
    </script>
@endsection