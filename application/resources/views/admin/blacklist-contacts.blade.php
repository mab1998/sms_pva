@extends('admin')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
    <style>
        label.active.btn.btn-default {
            color: #ffffff !important;
            background-color: #7E57C2 !important;
            border-color: #7E57C2 !important;
        }
    </style>
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Blacklist Contacts')}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-5">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Add New Contact')}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('sms/post-blacklist-contact')}}">


                                <div class="form-group">
                                    <label>{{language_data('Paste Numbers')}}</label>
                                    <span class="help text-uppercase pull-right">{{language_data('Total Number Of Recipients')}}
                                        : <span class="number_of_recipients bold text-success m-r-5">0</span></span>
                                    <textarea class="form-control" rows="5" name="import_numbers" id="recipients" required></textarea>
                                </div>


                                <div class="form-group">
                                    <div class="btn-group btn-group-sm" data-toggle="buttons">

                                        <label class="btn btn-default active">
                                            <input type="radio" name="delimiter" value="automatic" checked="">{{language_data('Automatic')}}
                                        </label>

                                        <label class="btn btn-default">
                                            <input type="radio" name="delimiter" value=";">;
                                        </label>

                                        <label class="btn btn-default">
                                            <input type="radio" name="delimiter" value=",">,
                                        </label>

                                        <label class="btn btn-default">
                                            <input type="radio" name="delimiter" value="|">|
                                        </label>

                                        <label class="btn btn-default">
                                            <input type="radio" name="delimiter" value="tab">{{language_data('Tab')}}
                                        </label>

                                        <label class="btn btn-default">
                                            <input type="radio" name="delimiter" value="new_line">{{language_data('New Line')}}
                                        </label>
                                    </div>
                                </div>
                                <br>
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> {{language_data('Add')}} </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="panel">
                        <div class="panel-heading">
                            <button id="deleteTriger" class="btn btn-danger btn-xs pull-right"><i class="fa fa-trash"></i> {{language_data('Bulk Delete')}}</button>
                            <h3 class="panel-title">{{language_data('Blacklist Contacts')}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 10%">

                                        <div class="coder-checkbox">
                                            <input type="checkbox"  id="bulkDelete"  />
                                            <span class="co-check-ui"></span>
                                        </div>

                                    </th>
                                    <th style="width: 60%">{{language_data('Numbers')}}</th>
                                    <th style="width: 30%">{{language_data('Action')}}</th>
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

            var number_of_recipients_ajax = 0,
                number_of_recipients_manual = 0,
                $get_recipients = $('#recipients');

            var oTable =  $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! url('sms/get-blacklist-contact/') !!}',
                columns: [
                    {data: 'id', name: 'id',orderable: false, searchable: false},
                    {data: 'numbers', name: 'numbers'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
              language: {
                url: '{!! url("assets/libs/data-table/i18n/".get_language_code()->language.".lang") !!}'
              },
              responsive: true
            });

            /*For Delete Group*/
            $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                var id = this.id;
              bootbox.confirm("{!! language_data('Are you sure') !!}?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/sms/delete-blacklist-contact/" + id;
                    }
                });
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
                        url: '{!! url('/sms/delete-bulk-blacklist-contact/') !!}',
                        data: {data_ids:ids_string},
                        success: function(result) {
                            if (result.status == 'success'){
                                alertify.log("<i class='fa fa-times-circle'></i> <span>"+ result.message +"</span>", "success");
                            }else {
                                alertify.log("<i class='fa fa-times-circle'></i> <span>"+ result.message +"</span>", "error");
                            }
                            deleteTriger.hide();
                            oTable.draw(); // redrawing datatable
                        },
                        async:false
                    });
                }
            });


            function get_delimiter() {
                return $('input[name=delimiter]:checked').val();
            }

            function get_recipients_count() {

                var recipients_value = $get_recipients[0].value.trim();

                if (recipients_value) {
                    var delimiter = get_delimiter();

                    if (delimiter == 'automatic') {
                        number_of_recipients_manual = splitMulti(recipients_value, [',', '\n', ';', '|']).length;
                    } else if (delimiter == ';') {
                        number_of_recipients_manual = recipients_value.split(';').length;
                    } else if (delimiter == ',') {
                        number_of_recipients_manual = recipients_value.split(',').length;
                    } else if (delimiter == '|') {
                        number_of_recipients_manual = recipients_value.split('|').length;
                    } else if (delimiter == 'tab') {
                        number_of_recipients_manual = recipients_value.split(' ').length;
                    } else if (delimiter == 'new_line') {
                        number_of_recipients_manual = recipients_value.split('\n').length;
                    } else {
                        number_of_recipients_manual = 0;
                    }
                } else {
                    number_of_recipients_manual = 0;
                }
                var total = number_of_recipients_manual + Number(number_of_recipients_ajax);

                $('.number_of_recipients').text(total);
            }

            $get_recipients.keyup(get_recipients_count);


            $("input[name='delimiter']").change(function () {
                get_recipients_count();
            });

        });
    </script>
@endsection
