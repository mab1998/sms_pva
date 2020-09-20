@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
    {!! Html::style("assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title inline-block">{{language_data('Campaign Reports')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Search Condition')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" id="search-form">

                                <div class="row">

                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label for="camp_type">{{language_data('Campaign Type')}}</label>
                                            <select class="selectpicker form-control" name="camp_type" id="camp_type">
                                                <option value="0">{{language_data('All')}}</option>
                                                <option value="regular">{{language_data('Regular')}}</option>
                                                <option value="scheduled">{{language_data('Scheduled')}}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label for="campaign_id">{{language_data('Campaign ID')}}</label>
                                            <input type="text" id="campaign_id" class="form-control" name="campaign_id">
                                        </div>
                                    </div>

                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label for="from">{{language_data('From')}}</label>
                                            <input type="text" id="sender" class="form-control" name="sender">
                                        </div>
                                    </div>


                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label for="status">{{language_data('Status')}}</label>
                                            <input type="text" id="status" class="form-control" name="status">
                                        </div>
                                    </div>

                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label for="date_from">{{language_data('Date')}} {{language_data('From')}}</label>
                                            <input type="text" id="date_from" class="form-control dateTimePicker" name="date_from">
                                        </div>
                                    </div>

                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label for="date_to">{{language_data('Date')}} {{language_data('To')}}</label>
                                            <input type="text" id="date_to" class="form-control dateTimePicker" name="date_to">
                                        </div>
                                    </div>

                                </div>
                                <input type="hidden" name="_token" value="{{csrf_token()}}">
                                <button type="submit" class="btn btn-success pull-right"><i class="fa fa-search"></i> {{language_data('Search')}}</button>

                            </form>
                        </div>
                    </div>
                </div>


                <div class="col-sm-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <button id="deleteTriger" class="btn btn-danger btn-xs pull-right m-r-20"><i class="fa fa-trash"></i> {{language_data('Bulk Delete')}}</button>
                            <h3 class="panel-title">{{language_data('Campaign Reports')}}</h3>
                        </div>
                        <div class="panel-body">


                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 5%">

                                        <div class="coder-checkbox">
                                            <input type="checkbox"  id="bulkDelete"  />
                                            <span class="co-check-ui"></span>
                                        </div>

                                    </th>
                                    <th style="width: 15%;">{{language_data('SL')}}</th>
                                    <th style="width: 20%;">{{language_data('Send By')}}</th>
                                    <th style="width: 10%;">{{language_data('From')}}</th>
                                    <th style="width: 15%;">{{language_data('Recipients')}}</th>
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
    {!! Html::script("assets/libs/moment/moment.min.js")!!}
    {!! Html::script("assets/libs/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js")!!}
    {!! Html::script("assets/libs/data-table/datatables.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    {!! Html::script("assets/js/bootbox.min.js")!!}

    <script>
        $(document).ready(function(){


            /*Linked Date*/

            $("#date_from").on("dp.change", function (e) {
                $('#date_to').data("DateTimePicker").minDate(e.date);
            });

            $("#date_to").on("dp.change", function (e) {
                $('#date_from').data("DateTimePicker").maxDate(e.date);
            });

            var oTable = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! url('sms/get-campaign-history/') !!}',
                    data: function (d) {
                        d.campaign_id = $('input[name=campaign_id]').val();
                        d.camp_type = $('select[name=camp_type]').val();
                        d.sender = $('input[name=sender]').val();
                        d.status = $('input[name=status]').val();
                        d.date_from = $('input[name=date_from]').val();
                        d.date_to = $('input[name=date_to]').val();
                    }
                },
                columns: [
                    {data: 'id', name: 'id',orderable: false, searchable: false},
                    {data: 'campaign_id', name: 'campaign_id'},
                    {data: 'user_id', name: 'user_id', orderable: false, searchable: false},
                    {data: 'sender', name: 'sender'},
                    {data: 'total_recipient', name: 'total_recipient', align:'center'},
                    {data: 'status', name: 'status'},
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
                            columns: [1,2,3,4,5,6],
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
                            columns: [1,2,3,4,5,6],
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
                            columns: [1,2,3,4,5,6],
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
                            columns: [1,2,3,4,5,6],
                            modifier: {
                                search: 'applied',
                                order: 'applied',
                            }
                        }
                    }
                ],
            });


            $('#search-form').on('submit', function(e) {
                oTable.draw();
                e.preventDefault();
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
                        url: '{!! url('/sms/bulk-campaign-delete/') !!}',
                        data: {data_ids:ids_string},
                        success: function(result) {
                            if (result.status == 'success'){
                                alertify.log("<i class='fa fa-times-circle'></i> <span>"+ result.msg +"</span>", "success");
                            }else {
                                alertify.log("<i class='fa fa-times-circle'></i> <span>"+ result.msg +"</span>", "error");
                            }
                            deleteTriger.hide();
                            oTable.draw(); // redrawing datatable
                        },
                        async:false
                    });
                }
            });


            /*For Delete SMS*/
            $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                var id = this.id;
                bootbox.confirm("{!! language_data('Are you sure') !!}?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/sms/delete-campaign/" + id;
                    }
                });
            });

        });
    </script>
@endsection
