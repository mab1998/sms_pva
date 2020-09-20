@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css") !!}
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Campaign Details',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>

        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
        </div>

        <div class="p-30 p-t-none p-b-none">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#overview" aria-controls="overview" role="tab"
                                                                  data-toggle="tab"><i class="fa fa-globe"></i> {{language_data('Overview',Auth::guard('client')->user()->lan_id)}}</a>
                        <li role="presentation"><a href="#update-campaign" aria-controls="update-campaign" role="tab"
                                                   data-toggle="tab"><i class="fa fa-edit"></i> {{language_data('Update Campaign',Auth::guard('client')->user()->lan_id)}}</a>
                        </li>
                        <li role="presentation"><a href="#recipients" aria-controls="recipients" role="tab"
                                                   data-toggle="tab"><i class="fa fa-list" aria-hidden="true"></i>
                                {{language_data('Recipients',Auth::guard('client')->user()->lan_id)}}</a></li>
                    </ul>

                    <!-- Tab panes -->
                </div>
                <div class="col-lg-12">
                    <div class="tab-content panel p-20">

                        <div role="tabpanel" class="tab-pane active" id="overview">

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="row text-center">

                                        <div class="col-sm-3 m-b-15">
                                            <div class="z-shad-1">
                                                <div class="bg-primary text-white p-15 clearfix">
                                                    <span class="pull-left font-45 m-l-10"><i
                                                                class="fa fa-user"></i></span>
                                                    <div class="pull-right text-right m-t-15">
                                                        <span class="small m-b-5 font-15">{{$campaign->total_recipient}}
                                                            {{language_data('Recipients',Auth::guard('client')->user()->lan_id)}}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-sm-3 m-b-15">
                                            <div class="z-shad-1">
                                                <div class="bg-complete text-white p-15 clearfix">
                                                    <span class="pull-left font-45 m-l-10"><i
                                                                class="fa fa-check"></i></span>
                                                    <div class="pull-right text-right m-t-15">
                                                        <span class="small m-b-5 font-15">{{$campaign->total_delivered}}
                                                            {{language_data('Delivered',Auth::guard('client')->user()->lan_id)}}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-sm-3 m-b-15">
                                            <div class="z-shad-1">
                                                <div class="bg-danger text-white p-15 clearfix">
                                                    <span class="pull-left font-45 m-l-10"><i class="fa fa-close"></i></span>
                                                    <div class="pull-right text-right m-t-15">
                                                        <span class="small m-b-5 font-15">{{$campaign->total_failed}}
                                                            {{language_data('Failed',Auth::guard('client')->user()->lan_id)}}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-sm-3 m-b-15">
                                            <div class="z-shad-1">
                                                <div class="bg-success text-white p-15 clearfix">
                                                    <span class="pull-left font-45 m-l-10"><i
                                                                class="fa fa-stack"></i></span>
                                                    <div class="pull-right text-right m-t-15">
                                                        <span class="small m-b-5 font-15">{{$queued}}
                                                            {{language_data('Queued',Auth::guard('client')->user()->lan_id)}}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>

                            <div class="p-15">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="panel-heading">
                                            <h3 class="panel-title text-left">{{language_data('Campaign Details',Auth::guard('client')->user()->lan_id)}}</h3>
                                        </div>
                                        <div class="panel-body">
                                            <h3 class="panel-title">{{language_data('Campaign ID',Auth::guard('client')->user()->lan_id)}}: {{$campaign->campaign_id}}</h3>
                                            <br>
                                            <h3 class="panel-title">{{language_data('Campaign Type',Auth::guard('client')->user()->lan_id)}}: {{ucwords($campaign->camp_type)}}</h3>
                                            <br>
                                            <h3 class="panel-title">{{language_data('SMS Type',Auth::guard('client')->user()->lan_id)}}: {{ucwords($campaign->sms_type)}}</h3>
                                            <br>
                                            <h3 class="panel-title">{{language_data('Status',Auth::guard('client')->user()->lan_id)}}: {{$campaign->status}}</h3>
                                            <br>
                                            <h3 class="panel-title">{{language_data('Run At',Auth::guard('client')->user()->lan_id)}}: {{date('jS M y h:i A', strtotime($campaign->run_at))}}</h3>
                                            <br>
                                            <h3 class="panel-title">{{language_data('Delivered At',Auth::guard('client')->user()->lan_id)}}: @if($campaign->delivery_at != null) {{date('jS M y h:i A', strtotime($campaign->delivery_at))}} @endif</h3>
                                        </div>
                                    </div>
                                    <div class="col-md-6">

                                        <div class="panel-heading">
                                            <h3 class="panel-title text-center">{{language_data('Campaign Status',Auth::guard('client')->user()->lan_id)}}</h3>
                                        </div>
                                        <div class="panel-body">
                                            {!! $campaign_chart->render() !!}
                                        </div>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div role="tabpanel" class="tab-pane" id="update-campaign">
                            <form role="form" method="post" action="{{url('user/sms/post-update-campaign')}}" enctype="multipart/form-data">

                                <div class="row">
                                    <div class="col-md-6">


                                        <div class="form-group">
                                            <label>{{language_data('Campaign Keyword',Auth::guard('client')->user()->lan_id)}}</label>
                                            <select class="selectpicker form-control" name="keyword[]" data-live-search="true" multiple>
                                                @foreach($keyword as $kw)
                                                    <option value="{{$kw->keyword_name}}"
                                                            @if(in_array_r($kw->keyword_name,$selected_keywords)) selected @endif>{{$kw->title}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @if($campaign->status == 'Delivered')
                                            <div class="form-group">
                                                <label>{{language_data('Status',Auth::guard('client')->user()->lan_id)}}</label>
                                                <select class="selectpicker form-control" disabled>
                                                    <option>{{language_data('Delivered',Auth::guard('client')->user()->lan_id)}}</option>
                                                </select>
                                            </div>


                                            <div class="form-group">
                                                <label>{{language_data('Schedule Time',Auth::guard('client')->user()->lan_id)}}</label>
                                                <input type="text" class="form-control" disabled value="{{date('m/d/y H:i y', strtotime($campaign->run_at))}}">
                                            </div>

                                        @else
                                            @if($campaign->sms_type == 'mms')

                                                <div class="form-group">
                                                    <label>{{language_data('Existing MMS File',Auth::guard('client')->user()->lan_id)}}</label>
                                                    <p><a href="{{$campaign->media_url}}" target="_blank">{{$campaign->media_url}}</a></p>
                                                </div>

                                                <div class="form-group">
                                                    <label>{{language_data('Update MMS File',Auth::guard('client')->user()->lan_id)}}</label>
                                                    <div class="form-group input-group input-group-file">
                                                        <span class="input-group-btn">
                                                            <span class="btn btn-primary btn-file">
                                                                {{language_data('Browse',Auth::guard('client')->user()->lan_id)}} <input type="file" class="form-control" name="image" accept="image/*">
                                                            </span>
                                                        </span>
                                                        <input type="text" class="form-control" readonly="">
                                                    </div>
                                                </div>

                                            @endif


                                            @if($campaign->camp_type != 'regular')
                                                <div class="form-group">
                                                    <label>{{language_data('Status',Auth::guard('client')->user()->lan_id)}}</label>
                                                    <select class="selectpicker form-control" name="status">
                                                        <option value="Scheduled" @if($campaign->status == 'Scheduled') selected @endif> {{language_data('Scheduled',Auth::guard('client')->user()->lan_id)}}</option>
                                                        <option value="Stop" @if($campaign->status == 'Stop') selected @endif>{{language_data('Stop',Auth::guard('client')->user()->lan_id)}}</option>
                                                        <option value="Paused" @if($campaign->status == 'Paused') selected @endif>{{language_data('Paused',Auth::guard('client')->user()->lan_id)}}</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label>{{language_data('Schedule Time',Auth::guard('client')->user()->lan_id)}}</label>
                                                    <input type="text" class="form-control dateTimePicker" name="schedule_time" value="{{date('m/d/y H:i y', strtotime($campaign->run_at))}}">
                                                </div>

                                            @endif
                                        @endif

                                    </div>


                                    <div class="col-md-12">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" value="{{$campaign->id}}" name="campaign_id" id="campaign_id">
                                        @if($campaign->status != 'Delivered')
                                        <input type="submit" value="{{language_data('Update',Auth::guard('client')->user()->lan_id)}}" class="btn btn-success">
                                        @endif
                                    </div>

                                </div>


                            </form>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="recipients">
                            <button id="deleteTriger" class="btn btn-danger btn-xs pull-right m-r-20"><i class="fa fa-trash"></i> {{language_data('Bulk Delete',Auth::guard('client')->user()->lan_id)}}</button>
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 5%">
                                        <div class="coder-checkbox">
                                            <input type="checkbox" id="bulkDelete"/>
                                            <span class="co-check-ui"></span>
                                        </div>

                                    </th>
                                    <th style="width: 20%;">{{language_data('Number',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 25%;">{{language_data('Message',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 10%;">{{language_data('Amount',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 20%;">{{language_data('Status',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 20%;">{{language_data('Action',Auth::guard('client')->user()->lan_id)}}</th>
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
    {!! Html::script("assets/libs/data-table/datatables.min.js")!!}
    {!! Html::script("assets/libs/chartjs/chart.js")!!}
    {!! Html::script("assets/libs/moment/moment.min.js")!!}
    {!! Html::script("assets/libs/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js")!!}
    {!! Html::script("assets/js/form-elements-page.js")!!}
    {!! Html::script("assets/js/bootbox.min.js")!!}

    <script>
        $(document).ready(function () {
            var oTable = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! url('user/sms/get-campaign-recipients/'.$campaign->campaign_id) !!}',
                columns: [
                    {data: 'id', name: 'id', orderable: false, searchable: false},
                    {data: 'number', name: 'number'},
                    {data: 'message', name: 'message'},
                    {data: 'amount', name: 'amount'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                language: {
                    url: '{!! url("assets/libs/data-table/i18n/".get_language_code()->language.".lang") !!}'
                },
                responsive: true,
            });

            $("#bulkDelete").on('click', function () { // bulk checked
                var status = this.checked;
                $(".deleteRow").each(function () {
                    $(this).prop("checked", status);
                });
            });

            var deleteTriger = $('#deleteTriger');
            deleteTriger.hide();

            $(".panel").delegate(".deleteRow, #bulkDelete", "change", function (e) {
                $('#deleteTriger').toggle($('.deleteRow:checked').length > 0);
            });


            deleteTriger.on("click", function (event) { // triggering delete one by one
                if ($('.deleteRow:checked').length > 0) {  // at-least one checkbox checked
                    var ids = [];
                    $('.deleteRow').each(function () {
                        if ($(this).is(':checked')) {
                            ids.push($(this).val());
                        }
                    });
                    var ids_string = ids.toString();  // array to string conversion

                    $.ajax({
                        type: "POST",
                        url: '{!! url('user/sms/bulk-campaign-recipients-delete/') !!}',
                        data: {
                            data_ids: ids_string,
                            campaign_id : $('#campaign_id').val()
                        },
                        success: function (result) {
                            if (result.status == 'success') {
                                alertify.log("<i class='fa fa-times-circle'></i> <span>" + result.message + "</span>", "success");
                            } else {
                                alertify.log("<i class='fa fa-times-circle'></i> <span>" + result.message + "</span>", "error");
                            }
                            oTable.draw(); // redrawing datatable
                        },
                        async: false
                    });
                }
            });

            /*For Delete Recipient*/
            $("body").delegate(".cdelete", "click", function (e) {
                e.preventDefault();
                var id = this.id;
                bootbox.confirm("{!! language_data('Are you sure') !!}?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/user/sms/delete-campaign-recipient/" + id;
                    }
                });
            });
        });
    </script>
@endsection
