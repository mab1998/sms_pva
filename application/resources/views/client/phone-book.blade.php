@extends('client')

{{--External Style Section--}}
@section('style')
    {!! Html::style("assets/libs/data-table/datatables.min.css") !!}
@endsection


@section('content')

    <section class="wrapper-bottom-sec">
        <div class="p-30">
            <h2 class="page-title">{{language_data('Phone Book',Auth::guard('client')->user()->lan_id)}}</h2>
        </div>
        <div class="p-30 p-t-none p-b-none">
            @include('notification.notify')
            <div class="row">

                <div class="col-lg-4">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Add New List',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body">
                            <form class="" role="form" method="post" action="{{url('user/post-phone-book')}}">

                                <div class="form-group">
                                    <label>{{language_data('List name',Auth::guard('client')->user()->lan_id)}}</label>
                                    <input type="text" class="form-control" name="list_name">
                                </div>

                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="submit" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"></i> {{language_data('Add',Auth::guard('client')->user()->lan_id)}} </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">{{language_data('Phone Book',Auth::guard('client')->user()->lan_id)}}</h3>
                        </div>
                        <div class="panel-body p-none">
                            <table class="table data-table table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 10%">{{language_data('SL',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 35%">{{language_data('List name',Auth::guard('client')->user()->lan_id)}}</th>
                                    <th style="width: 55%">{{language_data('Action',Auth::guard('client')->user()->lan_id)}}</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($clientGroups as $cg)
                                    <tr>
                                        <td>
                                            <p>{{ $loop->iteration }}</p>
                                        </td>
                                        <td>
                                            <p>{{$cg->group_name}} </p>
                                        </td>
                                        <td>

                                            <a href="{{url('user/view-contact/'.$cg->id)}}" class="btn btn-primary btn-xs"><i class="fa fa-eye"></i> {{language_data('View Contacts',Auth::guard('client')->user()->lan_id)}}</a>
                                            <a href="{{url('user/add-contact/'.$cg->id)}}" class="btn btn-complete btn-xs"><i class="fa fa-plus"></i> {{language_data('Add Contact',Auth::guard('client')->user()->lan_id)}}</a>
                                            <a class="btn btn-success btn-xs" href="#" data-toggle="modal" data-target=".modal_edit_list_{{$cg->id}}"><i class="fa fa-edit"></i> {{language_data('Edit',Auth::guard('client')->user()->lan_id)}}</a>
                                            @include('client.modal-edit-contact-list')

                                            <a href="#" class="btn btn-danger btn-xs cdelete" id="{{$cg->id}}"><i class="fa fa-trash"></i> {{language_data('Delete',Auth::guard('client')->user()->lan_id)}}</a>
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
              url: '{!! url("assets/libs/data-table/i18n/".get_language_code(Auth::guard('client')->user()->lan_id)->language.".lang") !!}'
            },
            responsive: true
          })

            /*For Delete Group*/
            $( "body" ).delegate( ".cdelete", "click",function (e) {
                e.preventDefault();
                var id = this.id;
              bootbox.confirm("{!! language_data('Are you sure',Auth::guard('client')->user()->lan_id) !!} ?", function (result) {
                    if (result) {
                        var _url = $("#_url").val();
                        window.location.href = _url + "/user/sms/delete-import-phone-number/" + id;
                    }
                });
            });

        });
    </script>
@endsection
