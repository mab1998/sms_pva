@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Update Contact')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-sm-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <button id="deleteTriger" class="btn btn-danger btn-xs pull-right m-r-20"><i class="fa fa-trash"></i> {{language_data('Bulk Delete')}}</button>
                            <h3 class="panel-title">{{language_data('Update Contact')}}</h3>
                        </div>
                        <div class="panel-body">


                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 10%">

                                        <div class="coder-checkbox">
                                            <input type="checkbox"  id="bulkDelete"  />
                                            <span class="co-check-ui"></span>
                                        </div>

                                    </th>
                                    <th style="width: 20%;">{{language_data('Receiver')}}</th>
                                    <th style="width: 40%;">{{language_data('Message')}}</th>
                                    <th style="width: 10%;">{{language_data('Amount')}}</th>
                                    <th style="width: 20%;">{{language_data('Action')}}</th>
                                </tr>
                                </thead>
                            </table>
                            <input type="hidden" value="{{$id}}" id="campaign_id">
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
    {!! Html::script("assets/libs/data-table/datatables.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    {!! Html::script("assets/js/bootbox.min.js")!!}

    <script>
        $(document).ready(function(){


            let oTable = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! url('sms/get-recurring-sms-contact-data/'.$id) !!}',
                },
                columns: [
                    {data: 'id', name: 'id',orderable: false, searchable: false},
                    {data: 'receiver', name: 'receiver'},
                    {data: 'message', name: 'message'},
                    {data: 'amount', name: 'amount'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                language: {
                    url: '{!! url("assets/libs/data-table/i18n/".get_language_code()->language.".lang") !!}'
                },
                responsive: true,
                dom: 'lBrtip',
                lengthMenu: [[10,25, 100, -1], [10,25, 100, "All"]],
                pageLength: 10,
                order: [[ 1, "desc" ]],
                buttons: [
                    {
                        extend: 'excel',
                        text: '<span class="fa fa-file-excel-o"></span> {!! language_data('Excel') !!}',
                        exportOptions: {
                            columns: [1,2,3],
                            modifier: {
                                search: 'applied',
                                order: 'applied',
                            }
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<span class="fa fa-file-pdf-o"></span> {!! language_data('PDF') !!}',
                        exportOptions: {
                            columns: [1,2,3],
                            modifier: {
                                search: 'applied',
                                order: 'applied',
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        text: '<span class="fa fa-file-excel-o"></span> {!! language_data('CSV') !!}',
                        exportOptions: {
                            columns: [1,2,3],
                            modifier: {
                                search: 'applied',
                                order: 'applied',
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: '<span class="fa fa-print"></span> {!! language_data('Print') !!}',
                        exportOptions: {
                            columns: [1,2,3],
                            modifier: {
                                search: 'applied',
                                order: 'applied',
                            }
                        }
                    }
                ],
            });


            $("#bulkDelete").on('click',function() { // bulk checked
                let status = this.checked;
                $(".deleteRow").each( function() {
                    $(this).prop("checked",status);
                });
            });

           let deleteTriger =  $('#deleteTriger');
           deleteTriger.hide();

            $( ".panel" ).delegate( ".deleteRow, #bulkDelete", "change",function (e) {
                $('#deleteTriger').toggle($('.deleteRow:checked').length > 0);
            });


            deleteTriger.on("click", function(event){ // triggering delete one by one
                if( $('.deleteRow:checked').length > 0 ){  // at-least one checkbox checked
                    let ids = [];
                    $('.deleteRow').each(function(){
                        if($(this).is(':checked')) {
                            ids.push($(this).val());
                        }
                    });
                    let ids_string = ids.toString();  // array to string conversion

                    $.ajax({
                        type: "POST",
                        url: '{!! url('/sms/bulk-recurring-sms-contact-delete/') !!}',
                        data: {
                            data_ids:ids_string,
                            campaign_id : $('#campaign_id').val()
                        },
                        success: function(result) {

                            oTable.draw(); // redrawing datatable
                            if (result.status == 'success'){
                                alertify.log("<i class='fa fa-check-circle-o'></i> <span>"+ result.message +"</span>", "success");
                            }else {
                                alertify.log("<i class='fa fa-check-circle-o'></i> <span>"+ result.message +"</span>", "error");
                            }
                        },
                        async:false
                    });
                }
            });




            /*For Delete SMS*/
            $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                let id = this.id;
              bootbox.confirm("{!! language_data('Are you sure') !!}?", function (result) {
                    if (result) {
                        let _url = $("#_url").val();
                        window.location.href = _url + "/sms/delete-recurring-sms-contact/" + id;
                    }
                });
            });

        });
    </script>
@endsection