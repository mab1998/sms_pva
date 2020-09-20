@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('View Contacts',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <button id="deleteTriger" class="btn btn-danger btn-xs pull-right"><i class="fa fa-trash"></i> {{language_data('Bulk Delete',Auth::guard('client')->user()->lan_id)}}</button>
                            <h3 class="panel-title">{{language_data('View Contacts',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 5%">

                                        <div class="coder-checkbox">
                                            <input type="checkbox"  id="bulkDelete"  />
                                            <span class="co-check-ui"></span>
                                        </div>

                                    </th>
                                    <th style="width: 20%;">{{language_data('Phone Number',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 15%;">{{language_data('Name',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 20%;">{{language_data('Email',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 15%;">{{language_data('User name',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 10%;">{{language_data('Company',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 15%;">{{language_data('Action',Auth::guard('client')->user()->lan_id)}}</th>
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

            var oTable = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! url('user/get-all-contact/'.$id) !!}',
                columns: [
                    {data: 'id', name: 'id',orderable: false, searchable: false},
                    {data: 'phone_number', name: 'phone_number'},
                    {data: 'first_name', name: 'first_name'},
                    {data: 'email_address', name: 'email_address'},
                    {data: 'user_name', name: 'user_name'},
                    {data: 'company', name: 'company'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                language: {
                    url: '{!! url("assets/libs/data-table/i18n/".get_language_code(Auth::guard('client')->user()->lan_id)->language.".lang") !!}'
                },
                responsive: true
            });



            $("#bulkDelete").on('click',function() { // bulk checked
                var status = this.checked;
                $(".deleteRow").each( function() {
                    $(this).prop("checked",status);
                });
            });

            var deleteTriger =  $('#deleteTriger');
            deleteTriger.hide();

            $( ".panel" ).delegate( ".deleteRow, #bulkDelete", "change",function (e) {
                $('#deleteTriger').toggle($('.deleteRow:checked').length > 0);
            });


            deleteTriger.on("click", function(event){ // triggering delete one by one
                if( $('.deleteRow:checked').length > 0 ){  // at-least one checkbox checked
                    var ids = [];
                    $('.deleteRow').each(function(){
                        if($(this).is(':checked')) {
                            ids.push($(this).val());
                        }
                    });
                    var ids_string = ids.toString();  // array to string conversion

                    $.ajax({
                        type: "POST",
                        url: '{!! url('/user/sms/delete-bulk-contact/') !!}',
                        data: {data_ids:ids_string},
                        success: function(result) {
                            deleteTriger.hide();
                            oTable.draw(); // redrawing datatable
                        },
                        async:false
                    });
                }
            });

            /*For Delete Group*/
            $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                var id = this.id;
              bootbox.confirm("{!! language_data('Are you sure',Auth::guard('client')->user()->lan_id) !!} ?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/user/delete-contact/" + id;
                    }
                });
            });

        });
    </script>
@endsection